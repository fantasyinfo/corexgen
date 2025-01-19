<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\TasksEditRequest;
use App\Http\Requests\TasksRequest;
use App\Models\Milestone;
use App\Models\Tasks;
use App\Models\Timesheet;
use App\Services\ContractService;
use App\Services\EstimateService;
use App\Services\ProjectService;
use App\Services\ProposalService;
use App\Services\TasksService;
use App\Traits\AuditFilter;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\StatusStatsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use App\Models\CRM\CRMLeads;
use App\Services\CustomFieldService;
use Illuminate\Support\Facades\DB;


class TasksController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use CategoryGroupTagsFilter;
    use AuditFilter;
    use StatusStatsFilter;
    //

    /**
     * Number of items per page for pagination
     * @var int
     */
    protected $perPage = 10;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.crm.tasks.';

    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }



    protected $tasksService;

    protected $customFieldService;
    protected $proposalService;
    protected $contractService;
    protected $estimateService;
    protected $projectService;

    public function __construct(

        TasksService $tasksService,
        ProposalService $proposalService,
        ContractService $contractService,
        EstimateService $estimateService,
        CustomFieldService $customFieldService,
        ProjectService $projectService

    ) {

        $this->tasksService = $tasksService;
        $this->customFieldService = $customFieldService;
        $this->proposalService = $proposalService;
        $this->contractService = $contractService;
        $this->estimateService = $estimateService;
        $this->projectService = $projectService;
    }


    /**
     * tasks view , fetch
     */
    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->tasksService->getDatatablesResponse($request);
        }

        $headerStatus = $this->getHeaderStages(
            \App\Models\Tasks::class,
            CATEGORY_GROUP_TAGS_TYPES['KEY']['tasks_status'],
            CATEGORY_GROUP_TAGS_RELATIONS['KEY']['tasks'],
            'tasks',
            PermissionsHelper::$plansPermissionsKeys['TASKS']
        );

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Tasks Management',
            'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'type' => 'Tasks',
            'headerStatus' => $headerStatus,
            'tasksStatus' => $this->tasksService->getTasksStatus(),
            'projects' => $this->projectService->getAllProjects(),
            'teamMates' => getTeamMates(),
        ]);
    }



    /**
     * get header status
     */
    private function getHeaderStages($model, $type, $relation, $table, $permission)
    {
        $user = Auth::user();

        // fetch totals status by clause
        $statusQuery = $this->getGroupByStageQuery($model, $type, $relation);
        $groupData = $this->applyTenantFilter($statusQuery['groupQuery'], $table)->get()->toArray();
        $totalData = $this->applyTenantFilter($statusQuery['totalQuery'], $table)->count();
        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[$permission]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $totalData,
            ];
        }

        return [
            'totalAllow' => $usages['totalAllow'],
            'currentUsage' => $totalData,
            'groupData' => $groupData
        ];
    }


    /**
     * store the task
     */
    public function store(TasksRequest $request)
    {

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);
            }



            // Create lead
            $task = $this->tasksService->createTask($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($task, $validatedData);
            }

            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['TASKS']]), '+', '1');

            // redirect back to refrer ...
            // Handle redirect to referrer
            $redirect = $this->_redirectBackToRefrer($request->validated());
            if ($redirect) {
                return $redirect;
            }

            return redirect()
                ->route($this->getTenantRoute() . 'tasks.index')
                ->with('success', 'Task created successfully.');
        } catch (\Exception $e) {
            \Log::error('Task creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);
            return redirect()
                ->back()
                ->withInput()
                ->with('active_tab', $request->input('active_tab', 'general'))
                ->with('error', $e->getMessage());
        }
    }


    /**
     * redirect back to view if its coming from refrer
     */

    private function _redirectBackToRefrer(array $data)
    {
        if (isset($data['_ref_type'], $data['_ref_id'], $data['_ref_refrer'])) {
            return redirect()->route(
                getPanelRoutes($data['_ref_refrer']),
                ['id' => $data['_ref_id']]
            )->with('success', $data['_ref_type'] . " Task created successfully.");
        }

        // Return null if no redirection logic matches
        return null;
    }

    /**
     * create the task
     */
    public function create()
    {
        $this->checkCurrentUsage(strtolower(PermissionsHelper::$plansPermissionsKeys['TASKS']));

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);
        }

        // milestones
        $milestones = collect();
        $milestones = $this->applyTenantFilter(Milestone::query())->get();

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Task',
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'tasksStatus' => $this->tasksService->getTasksStatus(),
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'projects' => $this->projectService->getAllProjects(),
            'milestones' => $milestones

        ]);
    }

    /**
     * update the task
     */
    public function update(TasksEditRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // dd($request->all());

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);
            }



            $task = $this->tasksService->updateTask($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($task, $validatedData);
            }


            // kanban board return....
            if ($request->has('from_view') && $request->input('from_view')) {
                return redirect()
                    ->back()
                    ->with('success', 'Task updated successfully..');
            }

            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->tenantRoute . 'tasks.index')
                ->with('success', 'Task updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Task updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the lead. ' . $e->getMessage());
        }
    }

    /**
     * edit the task
     */
    public function edit($id)
    {
        $query = Tasks::query()->with([
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'tasks');

        $task = $query->firstOrFail();

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($task);
        }



        // milestones
        $milestones = collect();
        $milestones = $this->applyTenantFilter(Milestone::query())->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Task',
            'task' => $task,
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'projects' => $this->projectService->getAllProjects(),
            'tasksStatus' => $this->tasksService->getTasksStatus(),
            'milestones' => $milestones
        ]);
    }

    /**
     * destory the task
     */
    public function destroy($id)
    {
        try {
            // Delete the user
            $task = Tasks::find($id);
            if ($task) {

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($task);

                // delete  now
                $task->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['TASKS']]), '-', '1');

                return redirect()->back()->with('success', 'Task deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the client: client not found with this id.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the client: ' . $e->getMessage());
        }
    }

    /**
     * bulk delete the task
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        try {
            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    // First, delete custom field values
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], $ids);

                    // Then delete the tasks
                    Tasks::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['TASKS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected tasks deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No tasks selected for deletion.'], 400);
        } catch (\Exception $e) {
            \Log::error('Bulk deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ids' => $ids
            ]);

            return response()->json(
                ['error' => 'Failed to delete the tasks: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * view the task
     */
    public function view(Request $request, $id)
    {
        $query = Tasks::query()->with([
            'milestone',
            'timeSheets',
            'project',
            'stage:id,name,color',
            'assignedBy:id,name',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'tasks');

        $task = $query->firstOrFail();

        // custom fields

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($task);
        }

        // fetch teams actvities
        $activitesQuery = $this->getActivites(\App\Models\Tasks::class, $id);
        $activitesQuery = $this->applyTenantFilter($activitesQuery);
        // $activities = $activitesQuery->get();

        // milestones
        $milestones = collect();
        $milestones = $this->applyTenantFilter(Milestone::query())->get();
        //  dd($activitesQuery->toArray());

        // timesheets
        $timesheets = collect();
        $timesheets = $this->applyTenantFilter(Timesheet::where('task_id', $id)->with('task', 'user'))->get();

        if ($request->input('fromkanban') && $request->input('fromkanban') == true) {


            $view = view($this->getViewFilePath('components._kanbanView'), [
                'title' => 'View Task',
                'task' => $task,
                'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
                'tasksStatus' => $this->tasksService->getTasksStatus(),
                'teamMates' => getTeamMates(),
                'cfOldValues' => $cfOldValues,
                'customFields' => $customFields,
                'activities' => $activitesQuery,
                'projects' => $this->projectService->getAllProjects(),
                'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
                'milestones' => $milestones,
                'timesheets' => $timesheets,
                'taskUsers' => $this->getAssignee($id, true)
            ]);

            // Render the view to capture the stacks
            $renderedView = $view->render();

            // Get the styles and scripts from the stacks
            $styles = collect($view->gatherData()['__env']->yieldPushContent('style'))->implode('');
            $scripts = collect($view->gatherData()['__env']->yieldPushContent('scripts'))->implode('');

            return response()->json([
                'html' => $renderedView,
                'styles' => $styles,
                'scripts' => $scripts
            ]);
        }


        return view($this->getViewFilePath('view'), [
            'title' => 'View Task',
            'task' => $task,
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'tasksStatus' => $this->tasksService->getTasksStatus(),
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'customFields' => $customFields,
            'activities' => $activitesQuery,
            'projects' => $this->projectService->getAllProjects(),
            'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
            'milestones' => $milestones,
            'timesheets' => $timesheets,
            'taskUsers' => $this->getAssignee($id, true)

        ]);
    }

    /**
     * change status the task
     */
    public function changeStatus($id, $status)
    {
        try {
            CRMLeads::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Tasks status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the tasks status: ' . $e->getMessage());
        }
    }


    // kanban board stuff
    /**
     * change stage the task
     */
    public function changeStage($leadid, $stageid)
    {

        try {
            Tasks::query()->where('id', '=', $leadid)->update(['status_id' => $stageid]);

            if (isset($_GET['from_kanban']) && $_GET['from_kanban']) {
                // Return success response as JSON
                return response()->json([
                    'success' => true,
                    'message' => 'Tasks status changed successfully.',
                ]);
            }

            // for table view
            return redirect()->back()->with('success', 'Tasks stage changed successfully.');

        } catch (\Exception $e) {

            if (isset($_GET['from_kanban']) && $_GET['from_kanban']) {
                // Handle any exceptions and return error as JSON
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to change the tasks stages: ' . $e->getMessage(),
                ], 500); // Use HTTP stages 500 for errors
            }

            // for table view

            return redirect()->back()->with('error', 'Failed to changed the tasks stages: ' . $e->getMessage());
        }
    }

    /**
     * view kanban of the task
     */
    public function kanban(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['tasks'], Auth::user()->company_id);
        }


        return view($this->getViewFilePath('kanban'), [
            'filters' => $request->all(),
            'title' => 'Tasks Management',
            'permissions' => PermissionsHelper::getPermissionsArray('TASKS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'type' => 'Tasks',
            'stages' => $this->tasksService->getKanbanBoardStages($request->all()),
            'tasksStatus' => $this->tasksService->getTasksStatus(),
            'projects' => $this->projectService->getAllProjects(),
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
        ]);
    }

    /**
     * kanban load the task
     */
    public function kanbanLoad(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $data = collect();
        $data = $this->tasksService->getKanbanLoad($request->all());
        return response()->json($data);
    }

    /**
     * kanban edit the task
     */
    public function kanbanEdit($id)
    {
        $query = CRMLeads::query()->with([
            'address' => fn($q) => $q
                ->select(['id', 'street_address', 'postal_code', 'city_id', 'country_id'])
                ->with([
                    'city:id,name',
                    'country:id,name'
                ]),
            'customFields',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'tasks');

        $lead = $query->firstOrFail();

        // custom fields

        $cfOldValues = [];
        if (!is_null(Auth::user()->company_id)) {

            // fetch already existing values
            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }


        return response()->json([
            'lead' => $lead->toArray(),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'cfOldValues' => $cfOldValues->toArray()
        ]);
    }

    /**
     * kanbanview the task
     */
    public function kanbanView($id)
    {
        $query = Tasks::query()->with([
            'stage:id,name,color',
            'assignedBy:id,name',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'tasks');

        $lead = $query->firstOrFail();

        // custom fields

        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {

            // fetch already existing values
            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }

        return response()->json([
            'lead' => $lead,
            'module' => PANEL_MODULES[$this->getPanelModule()]['tasks'],
            'cfOldValues' => $cfOldValues
        ]);
    }

    /**
     * get Assignee of the task
     */
    public function getAssignee(int $taskid, $isCollcet = false)
    {
        $task = $this->applyTenantFilter(Tasks::query()
            ->where('id', $taskid)
            ->with(['assignees:id,name']) // Only select needed fields
            ->first());

        if ($isCollcet) {
            return $task->assignees;
        }
        return response()->json($task ? $task->assignees : []);
    }


    /**
     * add assignee to tasks
     */
    public function addAssignee(Request $request)
    {
        $request->validate([
            'assign_to' => 'array|nullable|exists:users,id',
            'id' => 'required|exists:tasks,id',
        ]);

        try {
            $task = $this->applyTenantFilter(Tasks::query()->where('id', '=', $request->input('id')))->firstOrFail();

            $this->tasksService->assignTasksToUserIfProvided($request->only(['assign_to', 'id']), $task);

            return redirect()->back()->with('success', 'Task assingee addedd successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to addedd the task assingee: ' . $e->getMessage());
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectEditRequest;
use App\Http\Requests\ProjectRequest;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Timesheet;
use App\Services\ContractService;
use App\Services\EstimateService;
use App\Services\ProposalService;
use App\Traits\AuditFilter;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\StatusStatsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use App\Services\ClientService;
use App\Services\CustomFieldService;
use App\Services\InvoiceService;
use App\Services\ProjectService;
use App\Services\TasksService;
use Illuminate\Support\Facades\DB;


class ProjectController extends Controller
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
    private $viewDir = 'dashboard.crm.projects.';

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



    protected $projectService;

    protected $customFieldService;
    protected $proposalService;
    protected $contractService;
    protected $estimateService;

    protected $clientService;
    protected $tasksService;
    protected $invoiceService;

    public function __construct(
        ProposalService $proposalService,
        ContractService $contractService,
        EstimateService $estimateService,
        CustomFieldService $customFieldService,
        ProjectService $projectService,
        ClientService $clientService,
        TasksService $tasksService,
        InvoiceService $invoiceService,
    ) {


        $this->customFieldService = $customFieldService;
        $this->proposalService = $proposalService;
        $this->contractService = $contractService;
        $this->estimateService = $estimateService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
        $this->tasksService = $tasksService;
        $this->invoiceService = $invoiceService;
    }


    /**
     * view and fetch the projects
     */

    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->projectService->getDatatablesResponse($request);
        }


        $headerStatus = $this->getHeaderStatus(\App\Models\Project::class, PermissionsHelper::$plansPermissionsKeys['PROJECTS']);

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Projects Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'type' => 'Projects',
            'headerStatus' => $headerStatus,
            'teamMates' => getTeamMates(),
            'clients' => $this->clientService->getAllClients()
        ]);
    }


    /**
     * get header status
     */
    private function getHeaderStatus($model, $permission)
    {
        $user = Auth::user();

        // fetch totals status by clause
        $statusQuery = $this->getGroupByStatusQuery($model);
        $groupData = $this->applyTenantFilter($statusQuery['groupQuery'])->get()->toArray();
        $totalData = $this->applyTenantFilter($statusQuery['totalQuery'])->count();
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
     * store the project details
     */
    public function store(ProjectRequest $request)
    {

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], Auth::user()->company_id);
            }


            // Create lead
            $project = $this->projectService->createProject($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($project, $validatedData);
            }

            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROJECTS']]), '+', '1');

            return redirect()
                ->route($this->getTenantRoute() . 'projects.index')
                ->with('success', 'Project created successfully.');
        } catch (\Exception $e) {
            \Log::error('Project creation failed', [
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
     * create project
     */
    public function create()
    {
        $this->checkCurrentUsage(strtolower(PermissionsHelper::$plansPermissionsKeys['PROJECTS']));



        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], Auth::user()->company_id);
        }

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Project',
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'clients' => $this->clientService->getAllClients()
        ]);
    }

    /**
     * update project
     */
    public function update(ProjectEditRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // dd($request->all());

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], Auth::user()->company_id);
            }



            $project = $this->projectService->updateProject($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($project, $validatedData);
            }


            // kanban board return....
            if ($request->has('from_view') && $request->input('from_view')) {
                return redirect()
                    ->back()
                    ->with('success', 'Project updated successfully..');
            }

            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->tenantRoute . 'projects.index')
                ->with('success', 'Project updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Project updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the project. ' . $e->getMessage());
        }
    }

    /**
     * edit project
     */
    public function edit($id)
    {
        $query = Project::query()->with([
            'customFields',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'projects');

        $project = $query->firstOrFail();

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($project);
        }


        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Project',
            'project' => $project,
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'clients' => $this->clientService->getAllClients()
        ]);
    }

    /**
     * destory project
     */
    public function destroy($id)
    {
        try {
            // Delete the user
            $project = Project::find($id);
            if ($project) {

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($project);

                // delete  now
                $project->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROJECTS']]), '-', '1');

                return redirect()->back()->with('success', 'Project deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the client: client not found with this id.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the client: ' . $e->getMessage());
        }
    }

    /**
     * bulk delete project
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        try {
            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    // First, delete custom field values
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], $ids);

                    // Then delete the projects
                    Project::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROJECTS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected projects deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No projects selected for deletion.'], 400);
        } catch (\Exception $e) {
            \Log::error('Bulk deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ids' => $ids
            ]);

            return response()->json(
                ['error' => 'Failed to delete the projects: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * view project
     */
    public function view($id)
    {
        $query = Project::query()->with([
            'customFields',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name','users.profile_photo_path'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'projects');

        $project = $query->firstOrFail();

        // custom fields

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['project'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($project);
        }

        // fetch teams actvities
        $activitesQuery = $this->getActivites(\App\Models\Project::class, $id);
        $activitesQuery = $this->applyTenantFilter($activitesQuery);
        // $activities = $activitesQuery->get();

        //  dd($activitesQuery->toArray());


        // get proposals
        $proposals = collect();
        $proposals = $this->proposalService->getProposals(\App\Models\CRM\CRMClients::class, $project?->client?->id);


        // estimates
        $estimates = collect();
        $estimates = $this->estimateService->getEstimates(\App\Models\CRM\CRMClients::class, $project?->client?->id);

        // contracts

        $contracts = collect();
        $contracts = $this->contractService->getContracts(\App\Models\CRM\CRMClients::class, $project?->client?->id);

        // tasks

        $tasks = collect();
        $tasks = $this->tasksService->getAllTasks($id);


        // milestones
        $milestones = collect();
        $milestones = $this->applyTenantFilter(Milestone::where('project_id', $id))->get();

        // timesheets
        $timesheets = collect();
        $taskIds = $tasks->pluck('id');
        $timesheets = $this->applyTenantFilter(Timesheet::whereIn('task_id', $taskIds)->with('task', 'user', 'invoice'))->get();


        // invoices

        $invoices = collect();
        $invoices = $this->invoiceService->getInvoices($id);




        return view($this->getViewFilePath('view'), [
            'title' => 'View Project',
            'project' => $project,
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'customFields' => $customFields,
            'activities' => $activitesQuery,
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'proposals' => $proposals,
            'contracts' => $contracts,
            'estimates' => $estimates,
            'tasks' => $tasks,
            'milestones' => $milestones,
            'timesheets' => $timesheets,
            'invoices' => $invoices
        ]);
    }

    /**
     * change status of project
     */
    public function changeStatus($id, $status)
    {
        try {
            Project::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Project status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the projects status: ' . $e->getMessage());
        }
    }

    /**
     * add assignee to project
     */
    public function addAssignee(Request $request)
    {
        $request->validate([
            'assign_to' => 'array|nullable|exists:users,id',
            'id' => 'required|exists:projects,id',
        ]);

        try {
            $project = $this->applyTenantFilter(Project::query()->where('id', '=', $request->input('id')))->firstOrFail();

            $this->projectService->assignprojectsToUserIfProvided($request->only(['assign_to', 'id']), $project);

            return redirect()->back()->with('success', 'Project assingee addedd successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to addedd the projects assingee: ' . $e->getMessage());
        }
    }



    // milestones
    /**
     * store milestone of a project
     */
    public function storeMilestones(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:success,danger,warning,dark,light,info',
            'project_id' => 'required|exists:projects,id',
        ]);

        $milestone = Milestone::create($validated);
        return response()->json($milestone);
    }

    /**
     * edit milestones of a  project
     */
    public function editMilestones($id)
    {
        $milestone = $this->applyTenantFilter(Milestone::find($id));
        return response()->json($milestone);
    }


    /**
     * update milestones of a  project
     */
    public function updateMilestones(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:milestones,id',
            'name' => 'required|string|max:255',
            'color' => 'required|string|in:success,danger,warning,dark,light,info',
            'project_id' => 'required|exists:projects,id',
        ]);

        $milestone = $this->applyTenantFilter(Milestone::find($validated['id']))->update($validated);
        return response()->json($milestone);
    }

    /**
     * destory milestones of a  project
     */
    public function destroyMilestones($id)
    {
        $milestone = $this->applyTenantFilter(Milestone::find($id))->delete();
        return response()->json(['message' => 'Milestone deleted successfully']);
    }


    // timesheets

    /**
     * store timesheets of a  project
     */
    public function storeTimesheets(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        $duration = calculateTimeDifference($validated['start_date'], $validated['end_date']);

        $validated['duration'] = $duration['duration'];

        $timesheet = Timesheet::create($validated);
        return response()->json($timesheet);
    }


    /**
     * edit timesheets of a  project
     */
    public function editTimesheets($id)
    {
        $timesheet = $this->applyTenantFilter(Timesheet::find($id));
        return response()->json($timesheet);
    }


    /**
     * update timesheets of a  project
     */

    public function updateTimesheets(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:timesheets,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string'
        ]);

        $duration = calculateTimeDifference($validated['start_date'], $validated['end_date']);

        $validated['duration'] = $duration['duration'];

        $timeSheet = $this->applyTenantFilter(Timesheet::find($validated['id']))->update($validated);
        return response()->json($timeSheet);
    }


    /**
     * destory timesheets of a  project
     */
    public function destroyTimesheets($id)
    {
        $timeSheet = $this->applyTenantFilter(Timesheet::find($id))->delete();
        return response()->json(['message' => 'TimeSheet deleted successfully']);
    }

}

<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProjectEditRequest;
use App\Http\Requests\ProjectRequest;
use App\Models\Country;
use App\Models\Project;
use App\Services\ContractService;
use App\Services\EstimateService;
use App\Services\ProposalService;
use App\Traits\AuditFilter;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use App\Models\CRM\projects;
use App\Services\ClientService;
use App\Services\CustomFieldService;

use App\Services\ProjectService;
use Illuminate\Support\Facades\DB;


class ProjectController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use CategoryGroupTagsFilter;
    use AuditFilter;
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

    public function __construct(


        ProposalService $proposalService,
        ContractService $contractService,
        EstimateService $estimateService,
        CustomFieldService $customFieldService,
        ProjectService $projectService,
        ClientService $clientService
    ) {


        $this->customFieldService = $customFieldService;
        $this->proposalService = $proposalService;
        $this->contractService = $contractService;
        $this->estimateService = $estimateService;
        $this->projectService = $projectService;
        $this->clientService = $clientService;
    }


    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->projectService->getDatatablesResponse($request);
        }



        $user = Auth::user();
        $userQuery = Project::query();

        $userQuery = $this->applyTenantFilter($userQuery);

        // Get all totals in a single query
        $usersTotals = $userQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['PROJECTS']['STATUS']['ACTIVE']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['PROJECTS']['STATUS']['CANCELED']
            ))
        ])->first();

        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROJECTS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Projects Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'type' => 'Projects',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,
            'teamMates' => getTeamMates(),
            'clients' => $this->clientService->getAllClients()
        ]);
    }



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
    public function view($id)
    {
        $query = projects::query()->with([
            'address' => fn($q) => $q
                ->select(['id', 'street_address', 'postal_code', 'city_id', 'country_id'])
                ->with([

                    'city:id,name',
                    'country:id,name'
                ]),
            'group:id,name,color',
            'source:id,name,color',
            'stage:id,name,color',
            'customFields',
            'assignedBy:id,name',
            'assignees' => fn($q) => $q
                ->select(['users.id', 'users.name'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'projects');

        $lead = $query->firstOrFail();

        // custom fields

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['projects'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }

        // fetch teams actvities
        $activitesQuery = $this->getActivites(\App\Models\CRM\projects::class, $id);
        $activitesQuery = $this->applyTenantFilter($activitesQuery);
        // $activities = $activitesQuery->get();

        //  dd($activitesQuery->toArray());


        // get proposals
        $proposals = collect();
        $proposals = $this->proposalService->getProposals(\App\Models\CRM\projects::class, $id);

        // estimates
        $estimates = collect();
        $estimates = $this->estimateService->getEstimates(\App\Models\CRM\projects::class, $id);

        // contracts

        $contracts = collect();
        $contracts = $this->contractService->getContracts(\App\Models\CRM\projects::class, $id);

        return view($this->getViewFilePath('view'), [
            'title' => 'View Project',
            'lead' => $lead,
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'leadsGroups' => $this->projectService->getLeadsGroups(),
            'leadsSources' => $this->projectService->getLeadsSources(),
            'leadsStatus' => $this->projectService->getLeadsStatus(),
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'customFields' => $customFields,
            'activities' => $activitesQuery,
            'countries' => Country::all(),
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'proposals' => $proposals,
            'contracts' => $contracts,
            'estimates' => $estimates,
        ]);
    }
  

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



}

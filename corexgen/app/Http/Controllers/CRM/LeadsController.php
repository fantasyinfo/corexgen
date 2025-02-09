<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeadsEditRequest;
use App\Http\Requests\LeadsRequest;
use App\Models\CategoryGroupTag;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Models\WebToLeadForm;
use App\Notifications\LeadsStatusChanged;
use App\Services\ContractService;
use App\Services\Csv\ClientsCsvRowProcessor;
use App\Services\Csv\LeadsCsvRowProcessor;
use App\Services\EstimateService;
use App\Services\ProposalService;
use App\Traits\AuditFilter;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\StatusStatsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\CsvImportJob;
use App\Models\Company;
use App\Models\CRM\CRMLeads;
use App\Repositories\LeadsRepository;
use App\Services\CustomFieldService;
use App\Services\LeadsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class LeadsController extends Controller
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
    private $viewDir = 'dashboard.crm.leads.';

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



    protected $leadsService;

    protected $customFieldService;
    protected $proposalService;
    protected $contractService;
    protected $estimateService;

    public function __construct(

        LeadsService $leadsService,
        ProposalService $proposalService,
        ContractService $contractService,
        EstimateService $estimateService,
        CustomFieldService $customFieldService
    ) {

        $this->leadsService = $leadsService;
        $this->customFieldService = $customFieldService;
        $this->proposalService = $proposalService;
        $this->contractService = $contractService;
        $this->estimateService = $estimateService;
    }


    /**
     * view and fetch leads table
     */
    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->leadsService->getDatatablesResponse($request);
        }



        $headerStatus = $this->getHeaderStages(
            \App\Models\CRM\CRMLeads::class,
            CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'],
            CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads'],
            'leads',
            PermissionsHelper::$plansPermissionsKeys['LEADS']
        );


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Leads Management',
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'type' => 'Leads',
            'headerStatus' => $headerStatus,
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'teamMates' => getTeamMates(),
        ]);
    }

    /**
     * get header stages of leads
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
     * store new lead
     */
    public function store(LeadsRequest $request)
    {



        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);
            }


            // Create lead
            $lead = $this->leadsService->createLead($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($lead['lead'], $validatedData);
            }

            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]), '+', '1');

            return redirect()
                ->route($this->getTenantRoute() . 'leads.index')
                ->with('success', 'Lead created successfully.');
        } catch (\Exception $e) {
            \Log::error('Lead creation failed', [
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
     * create new lead
     */
    public function create()
    {
        $this->checkCurrentUsage(strtolower(PermissionsHelper::$plansPermissionsKeys['LEADS']));
        $countries = Country::all();


        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);
        }

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Lead',
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'customFields' => $customFields,
            'teamMates' => getTeamMates()

        ]);
    }

    /**
     * update  lead
     */
    public function update(LeadsEditRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // dd($request->all());

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);
            }



            $lead = $this->leadsService->updateLead($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($lead['lead'], $validatedData);
            }


            // kanban board return....
            if ($request->has('from_view') && $request->input('from_view')) {
                return redirect()
                    ->back()
                    ->with('success', 'Lead updated successfully..');
            }

            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->tenantRoute . 'leads.index')
                ->with('success', 'Lead updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Lead updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the lead. ' . $e->getMessage());
        }
    }

    /**
     * edit  lead
     */
    public function edit($id)
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

        $query = $this->applyTenantFilter($query, 'leads');

        $lead = $query->firstOrFail();


        // countries
        $countries = Country::all();


        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }



        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Lead',
            'lead' => $lead,
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues
        ]);
    }



    /**
     * delete the lead
     */
    public function destroy($id)
    {
        try {
            // Delete the user
            $lead = CRMLeads::find($id);
            if ($lead) {

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($lead);

                // delete  now
                $lead->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]), '-', '1');

                return redirect()->back()->with('success', 'Lead deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the client: client not found with this id.');
            }
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the client: ' . $e->getMessage());
        }
    }

    /**
     * exoort lead
     */
    public function export(Request $request, LeadsRepository $leadsRepository)
    {
        // Apply filters and fetch leads
        $leads = $this->applyTenantFilter($leadsRepository->getLeadsQuery($request))->get();


        $csvData = $this->generateCSVForLeads($leads);
        return response($csvData['csvContent'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$csvData['file']}");
    }

    /**
     * generate csv for lead
     */
    public function generateCSVForLeads($leads)
    {

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Lead ID',
            'Type',
            'Company Name',
            'Title',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Group',
            'Source',
            'Stage',
            'Street Address',
            'City ID',
            'City Name',
            'Country ID',
            'Country Name',
            'Pincode',
            'Status',
            'Created At',
        ]; // CSV headers

        foreach ($leads as $lead) {
            // Prepare consolidated data

            $csvData[] = [
                $lead->id,
                $lead->type,
                $lead->company_name,
                $lead->title,
                $lead->first_name,
                $lead->last_name,
                $lead->email,
                $lead->phone ?? '',
                $lead->group?->name,
                $lead->source?->name,
                $lead->stage?->name,
                $address?->street_address ?? '',
                $address?->city_id ?? '',
                $address?->city?->name ?? '',
                $address?->country_id ?? '',
                $address?->country?->name ?? '',
                $address?->postal_code ?? '',
                $lead->status,
                $lead?->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        // Convert data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', array_map(fn($value) => '"' . str_replace('"', '""', $value) . '"', $row)) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'leads_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return [
            'file' => $fileName,
            'csvContent' => $csvContent
        ];
    }

    /**
     * import lead
     */
    public function importView()
    {


        $expectedHeaders = [
            'Type' => [
                'key' => 'Type',
                'message' => 'required, string, e.g., Individual or Company',
            ],
            'Company Name' => [
                'key' => 'Company Name',
                'message' => 'string, required only if type is company e.g., Abc Ltd Pty, Xyz Limited',
            ],
            'Title' => [
                'key' => 'Title',
                'message' => 'required, string, e.g., Need information about this x service',
            ],
            'First Name' => [
                'key' => 'First Name',
                'message' => 'required, string, e.g., John, Anna',
            ],
            'Last Name' => [
                'key' => 'Last Name',
                'message' => 'required, string, e.g., Doe, Smith',
            ],
            'Email' => [
                'key' => 'Email',
                'message' => 'required, email, string,  e.g., john.doe@example.com',
            ],
            'Phone' => [
                'key' => 'Phone',
                'message' => 'phone , string,  e.g., +1-555-123-4567',
            ],
            'Group ID' => [
                'key' => 'Group ID',
                'message' => 'required,  exists:from Group ID,id, e.g., 1 ,2,3 ',
            ],
            'Source ID' => [
                'key' => 'Source ID',
                'message' => 'required,  exists:from Source ID,id, e.g., 1 ,2,3 ',
            ],
            'Status ID' => [
                'key' => 'Status ID',
                'message' => 'required,  exists:from Status ID / Stage ID,id, e.g., 1 ,2,3 ',
            ],
            'Street Address' => [
                'key' => 'Street Address',
                'message' => 'string, optional, e.g., 123 Elm Street',
            ],
            'City Name' => [
                'key' => 'City Name',
                'message' => 'string, optional, e.g., Springfield, London',
            ],
            'Country ID' => [
                'key' => 'Country ID',
                'message' => 'string or integer, optional, e.g., 1 for USA, 44 for UK',
            ],
            'Pincode' => [
                'key' => 'Pincode',
                'message' => 'string or integer, optional, e.g., 12345, E1 6AN',
            ],
        ];


        $sampleData = [
            [
                'Type' => 'Individual',
                'Company Name' => '',
                'Title' => 'I need details for macbokk m1 pro',
                'First Name' => 'John',
                'Last Name' => 'Doe',
                'Email' => 'john.doe@example.com',
                'Phone' => '+91 8989898989',
                'Group ID' => '3',
                'Source ID' => '5',
                'Status ID' => '11',
                'Street Address' => '123 Elm Street',
                'City Name' => 'Springfield',
                'Country ID' => '1',
                'Pincode' => '12345',
            ],
            [
                'Type' => 'Company',
                'Company Name' => 'ABC Multi Brach Hospital For Children',
                'Title' => 'Need infomartion about this service',
                'First Name' => 'Anna',
                'Last Name' => 'Smith',
                'Email' => 'anna.smith@example.org',
                'Phone' => '+44 8787878787',
                'Group ID' => '4',
                'Source ID' => '6',
                'Status ID' => '12',
                'Street Address' => '456 Oak Avenue',
                'City Name' => 'London',
                'Country ID' => '44',
                'Pincode' => 'E1 6AN',
            ],
        ];




        return view($this->getViewFilePath('import'), [

            'title' => 'Import Leads',
            'headers' => $expectedHeaders,
            'data' => $sampleData,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
        ]);
    }

    /**
     * import leads
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:' . BULK_CSV_UPLOAD_FILE_SIZE, // Validate file type and size
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $file = $request->file('file');
            $filePath = $file->storeAs('csv', uniqid() . '_' . $file->getClientOriginalName()); // Store file in a persistent directory
            $absoluteFilePath = storage_path('app/' . $filePath); // Get the absolute path

            // Validation rules for each row
            $rules = [
                'Type' => ['required', 'string', Rule::in(['Individual', 'Company'])],
                'Company Name' => ['nullable', 'required_if:Type,Company'],
                'Title' => ['required', 'string'],
                'First Name' => ['required', 'string'],
                'Last Name' => ['required', 'string'],
                'Email' => ['required', 'string'],
                'Phone' => ['required', 'string'],
                'Group ID' => ['required', 'string', 'exists:category_group_tag,id'],
                'Source ID' => ['required', 'string', 'exists:category_group_tag,id'],
                'Status ID' => ['required', 'string', 'exists:category_group_tag,id'],
                'Street Address' => ['nullable', 'string', 'max:255'], // Allow empty
                'City Name' => ['nullable', 'string'], // Allow empty
                'Country ID' => ['nullable', 'exists:countries,id'], // Allow empty
                'Pincode' => ['nullable', 'string'], // Allow empty
            ];

            // Expected CSV headers
            $expectedHeaders = ['Type', 'Company Name', 'Title', 'First Name', 'Last Name', 'Email', 'Phone', 'Group ID', 'Source ID', 'Status ID', 'Street Address', 'City Name', 'Country ID', 'Pincode'];

            // Dispatch the job
            CsvImportJob::dispatch(
                $absoluteFilePath,
                $rules,
                LeadsCsvRowProcessor::class,
                $expectedHeaders,
                [
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::id(),
                    'is_tenant' => Auth::user()->is_tenant,
                    'import_type' => 'Leads'
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'CSV file uploaded successfully. Processing will happen in the background.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }



    /**
     * bulk delete the leads
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        try {
            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    // First, delete custom field values
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], $ids);

                    // Then delete the leads
                    CRMLeads::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected leads deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No leads selected for deletion.'], 400);
        } catch (\Exception $e) {
            \Log::error('Bulk deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ids' => $ids
            ]);

            return response()->json(
                ['error' => 'Failed to delete the leads: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * view lead
     */
    public function view(Request $request, $id)
    {
        $query = CRMLeads::query()->with([
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
                ->select(['users.id', 'users.name','users.profile_photo_path'])
                ->withOnly([])
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'leads');

        $lead = $query->firstOrFail();

        // custom fields

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }

        // fetch teams actvities
        $activitesQuery = $this->getActivites(\App\Models\CRM\CRMLeads::class, $id);
        $activitesQuery = $this->applyTenantFilter($activitesQuery);
        // $activities = $activitesQuery->get();

        //  dd($activitesQuery->toArray());


        // get proposals
        $proposals = collect();
        $proposals = $this->proposalService->getProposals(\App\Models\CRM\CRMLeads::class, $id);

        // estimates
        $estimates = collect();
        $estimates = $this->estimateService->getEstimates(\App\Models\CRM\CRMLeads::class, $id);

        // contracts

        $contracts = collect();
        $contracts = $this->contractService->getContracts(\App\Models\CRM\CRMLeads::class, $id);


        if ($request->input('fromkanban') && $request->input('fromkanban') == true) {


            $view = view($this->getViewFilePath('components._kanbanView'), [
                'title' => 'View Lead',
                'lead' => $lead,
                'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
                'leadsGroups' => $this->leadsService->getLeadsGroups(),
                'leadsSources' => $this->leadsService->getLeadsSources(),
                'leadsStatus' => $this->leadsService->getLeadsStatus(),
                'teamMates' => getTeamMates(),
                'cfOldValues' => $cfOldValues,
                'customFields' => $customFields,
                'activities' => $activitesQuery,
                'countries' => Country::all(),
                'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
                'proposals' => $proposals,
                'contracts' => $contracts,
                'estimates' => $estimates,
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
            'title' => 'View Lead',
            'lead' => $lead,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'teamMates' => getTeamMates(),
            'cfOldValues' => $cfOldValues,
            'customFields' => $customFields,
            'activities' => $activitesQuery,
            'countries' => Country::all(),
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'proposals' => $proposals,
            'contracts' => $contracts,
            'estimates' => $estimates,
        ]);
    }
    public function profile()
    {
    }

    /**
     * change status of lead
     */
    public function changeStatus($id, $status)
    {
        try {
            CRMLeads::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Leads status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the leads status: ' . $e->getMessage());
        }
    }


    // kanban board stuff
    /**
     * lead kanban board change stage
     */
    public function changeStage($leadid, $stageid)
    {

        try {

            // Fetch the lead
            $lead = CRMLeads::with('assignees')->findOrFail($leadid);

            // Fetch the new status
            $newStatus = CategoryGroupTag::findOrFail($stageid);

            // Update the lead's status
            $lead->update(['status_id' => $stageid]);

            // Notify all assignees
            $user = Auth::user();
            $mailSettings = $user->company->getMailSettings();

            foreach ($lead->assignees as $assignee) {

                $assignee->notify(new LeadsStatusChanged($lead, $user, $newStatus, $mailSettings));
            }


            if (isset($_GET['from_kanban']) && $_GET['from_kanban']) {
                // Return success response as JSON
                return response()->json([
                    'success' => true,
                    'message' => 'Leads status changed successfully.',
                ]);
            }

            // for table view
            return redirect()->back()->with('success', 'Leads stage changed successfully.');
        } catch (\Exception $e) {

            if (isset($_GET['from_kanban']) && $_GET['from_kanban']) {
                // Handle any exceptions and return error as JSON
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to change the leads stages: ' . $e->getMessage(),
                ], 500); // Use HTTP stages 500 for errors
            }

            // for table view

            return redirect()->back()->with('error', 'Failed to changed the leads stages: ' . $e->getMessage());
        }
    }

    /**
     * view leads kanban board
     */
    public function kanban(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmleads'], Auth::user()->company_id);
        }


        return view($this->getViewFilePath('kanban'), [
            'filters' => $request->all(),
            'title' => 'Leads Management',
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'type' => 'Leads',
            'stages' => $this->leadsService->getKanbanBoardStages($request->all()),
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'customFields' => $customFields,
            'teamMates' => getTeamMates(),
            'countries' => Country::all(),
        ]);
    }

    /**
     * load kanban board for leads
     */
    public function kanbanLoad(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $data = collect();
        $data = $this->leadsService->getKanbanLoad($request->all());
        return response()->json($data);
    }

    /**
     * edit the lead via kanban board
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

        $query = $this->applyTenantFilter($query, 'leads');

        $lead = $query->firstOrFail();

        // custom fields

        $cfOldValues = [];
        if (!is_null(Auth::user()->company_id)) {

            // fetch already existing values
            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }


        return response()->json([
            'lead' => $lead->toArray(),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'cfOldValues' => $cfOldValues->toArray()
        ]);
    }

    /**
     * view lead via kanban board 
     */
    public function kanbanView($id)
    {
        $query = CRMLeads::query()->with([
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

        $query = $this->applyTenantFilter($query, 'leads');

        $lead = $query->firstOrFail();

        // custom fields

        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {

            // fetch already existing values
            $cfOldValues = $this->customFieldService->getValuesForEntity($lead);
        }


        return response()->json([
            'lead' => $lead,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'cfOldValues' => $cfOldValues
        ]);
    }


    /**
     * lead form view
     */
    public function leadForm($id)
    {
        if (!$id) {
            return redirect()->route(getPanelRoutes('home'));
        }

        $countries = Country::all();

        $formData = WebToLeadForm::where('uuid', $id)
            ->firstOrFail();

        return view($this->getViewFilePath('leadForm'), [
            'title' => 'Create Lead',
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'formData' => $formData
        ]);
    }
    /**
     * lead form store
     */
    public function leadFormStore(Request $request)
    {

        try {
            // Start database transaction
            DB::beginTransaction();

            // Validate incoming request
            $validated = $request->validate([
                'web_to_leads_form_id' => 'required|string|exists:web_to_leads_form,id',
                'web_to_leads_form_uuid' => 'required|string|exists:web_to_leads_form,uuid',
                'company_id' => 'required|string|exists:companies,id',
                'company_name' => 'nullable|string|max:255', // client company name
                'title' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'nullable|min:7|max:15',
                'preferred_contact_method' => 'nullable|in:Email,Phone,In-Person',
                'group_id' => 'required|exists:category_group_tag,id',
                'source_id' => 'required|exists:category_group_tag,id',
                'status_id' => 'required|exists:category_group_tag,id',
                'address_street_address' => 'nullable|string|max:255',
                'address_country_id' => 'nullable|exists:countries,id',
                'address_city_name' => 'nullable|string|max:255',
                'address_pincode' => 'nullable|string|max:20',
            ]);

            // Log the incoming lead attempt
            Log::info('New lead form submission', [
                'company_id' => $validated['company_id'],
                'email' => $validated['email'],
                'source' => $validated['source_id']
            ]);

            // Validate category group tags
            $this->validateCategoryGroupTags($validated);

            // Create the lead
            $lead = $this->leadsService->createLead($validated);

            // Commit transaction
            DB::commit();

            // Log successful lead creation
            Log::info('Lead successfully created', [
                'lead_id' => $lead['lead']->id,
                'company_id' => $lead['lead']->company_id
            ]);

            return redirect()->back()->with(
                'success',
                'Thank you for your interest! Our team will contact you shortly.'
            );
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Lead validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password'])
            ]);

            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput()
                ->with('error', 'Please check the form for errors and try again.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Lead creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'company_id' => $request->input('company_id')
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Unable to process your request at this time. Please try again later.');
        }
    }


    /**
     * Validate category group tags for the lead
     *
     * @param array $validated
     */
    private function validateCategoryGroupTags(array $validated)
    {
        $validations = [
            'group_id' => [
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'],
                'message' => 'Invalid lead group selected. Please check and try again.'
            ],
            'source_id' => [
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'],
                'message' => 'Invalid lead source selected. Please check and try again.'
            ],
            'status_id' => [
                'type' => CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'],
                'message' => 'Invalid lead status selected. Please check and try again.'
            ]
        ];

        foreach ($validations as $field => $config) {
            $isValid = $this->checkIsValidCGTID(
                $validated[$field],
                $validated['company_id'],
                $config['type'],
                CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']
            );

            if (!$isValid) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', $config['message']);
            }
        }
    }


    /**
     * Convert to client/customer
     */

    public function convert($id)
    {
        // Grab the lead; 404 if not found
        $lead = $this->applyTenantFilter(
            CRMLeads::query()->where('id', $id)
        )->firstOrFail();

        // (Optional) If a lead is already converted, handle that case
        if ($lead->is_converted) {
            return redirect()->back()->with('info', 'Lead is already converted.');
        }

        try {
            $converted = $this->leadsService->convertToClient($lead->toArray());

            if ($converted) {
                $lead->update(['is_converted' => true]);
                return redirect()->back()->with('success', 'Converted to client.');
            } else {
                return redirect()->back()->withErrors('Failed to convert lead to client.');
            }
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Lead conversion failed', [
                'lead_id' => $lead->id,
                'error_message' => $e->getMessage(),
            ]);

            // Return a friendly error message
            return redirect()->back()
                ->withErrors('An error occurred while converting the lead: ' . $e->getMessage());
        }
    }


    /**
     * add assignee to leads
     */
    public function addAssignee(Request $request)
    {
        $request->validate([
            'assign_to' => 'array|nullable|exists:users,id',
            'id' => 'required|exists:leads,id',
        ]);

        try {
            $lead = $this->applyTenantFilter(CRMLeads::query()->where('id', '=', $request->input('id')))->firstOrFail();

            $this->leadsService->assignLeadsToUserIfProvided($request->only(['assign_to', 'id']), $lead);

            return redirect()->back()->with('success', 'Task assingee addedd successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to addedd the task assingee: ' . $e->getMessage());
        }
    }


    /**
     * show the api docs for this leads form 
     * @param mixed $id

     */
    public function leadsToWebAPI($id)
    {

        $form = WebToLeadForm::where('company_id', Auth::user()->company_id)->findOrFail($id);

        return view($this->getViewFilePath('leadToWebAPI'), [
            'title' => 'Leads API Docs',
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'type' => 'Leads',
            'form' => $form,

        ]);
    }


    /**
     * capture leads via api
     */

    public function leadsCreateAPI(Request $request)
    {
        try {

            // Retrieve the X-API-Key header
            $apiKey = $request->header('X-API-Key');

            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'API Key is missing.',
                ], 400); // HTTP 400 Bad Request
            }

            $company = Company::where('api_token', $apiKey)->first();
            // Validate the API Key (Example validation logic)
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid API Key.',
                ], 401); // HTTP 401 Unauthorized
            }


            // Start database transaction
            DB::beginTransaction();

            // Validate incoming request
            $validated = $request->validate([
                'uuid' => 'required|string|exists:web_to_leads_form,uuid',
                'company_name' => 'nullable|string|max:255', // client company name
                'title' => 'required|string|max:255',
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|email',
                'phone' => 'nullable|string|min:7|max:15',
                'preferred_contact_method' => 'nullable|in:Email,Phone,In-Person',
                'group_id' => 'required|exists:category_group_tag,id',
                'source_id' => 'required|exists:category_group_tag,id',
                'status_id' => 'required|exists:category_group_tag,id',
                'address_street_address' => 'nullable|string|max:255',
                'address_country_id' => 'nullable|exists:countries,id',
                'address_city_name' => 'nullable|string|max:255',
                'address_pincode' => 'nullable|string|max:20',
            ]);



            $form = WebToLeadForm::where('uuid', $validated['uuid'])->where('company_id', $company->id)->first();

            // Validate the API Key (Example validation logic)
            if (!$form) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid UUID.',
                ], 401); // HTTP 401 Unauthorized
            }

            // Log the incoming lead attempt
            Log::info('New lead form submission', [
                'email' => $validated['email'],
                'source' => $validated['source_id'],
            ]);



            // create required fields dynamically via api token and form uuid
            $validated['company_id'] = $company->id;
            $validated['web_to_leads_form_id'] = $form->id;
            $validated['web_to_leads_form_uuid'] = $form->uuid;

            // Validate category group tags (custom logic)
            $this->validateCategoryGroupTags($validated);

            // Create the lead
            $lead = $this->leadsService->createLead($validated);

            // Commit transaction
            DB::commit();

            // Log successful lead creation
            Log::info('Lead successfully created', [
                'lead_id' => $lead['lead']->id,
                'company_id' => $lead['lead']->company_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Lead created successfully.',
                'data' => [
                    'lead_id' => $lead['lead']->id,
                    'created_at' => $lead['lead']->created_at->toIso8601String(),
                ],
            ], 201); // HTTP 201 Created

        } catch (ValidationException $e) {
            DB::rollBack();

            Log::warning('Lead validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except(['password']),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $e->errors(),
            ], 422); // HTTP 422 Unprocessable Entity

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Lead creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'error' => $e->getMessage(),
            ], 500); // HTTP 500 Internal Server Error
        }
    }

}

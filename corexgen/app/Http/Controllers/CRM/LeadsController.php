<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\LeadsEditRequest;
use App\Http\Requests\LeadsRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Services\ContractService;
use App\Services\Csv\ClientsCsvRowProcessor;
use App\Services\EstimateService;
use App\Services\ProposalService;
use App\Traits\AuditFilter;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\CsvImportJob;
use App\Models\CRM\CRMLeads;
use App\Services\CustomFieldService;
use App\Services\LeadsService;
use Illuminate\Support\Facades\DB;


class LeadsController extends Controller
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


    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->leadsService->getDatatablesResponse($request);
        }



        $user = Auth::user();
        $userQuery = CRMLeads::query();

        $userQuery = $this->applyTenantFilter($userQuery);

        // Get all totals in a single query
        $usersTotals = $userQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['LEADS']['STATUS']['ACTIVE']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['LEADS']['STATUS']['DEACTIVE']
            ))
        ])->first();

        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Leads Management',
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'type' => 'Leads',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,
            'leadsGroups' => $this->leadsService->getLeadsGroups(),
            'leadsSources' => $this->leadsService->getLeadsSources(),
            'leadsStatus' => $this->leadsService->getLeadsStatus(),
            'teamMates' => getTeamMates(),
        ]);
    }



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
    public function export(Request $request)
    {
        // Apply filters and fetch leads
        $leads = CRMClients::query()
            ->where('company_id', Auth::user()->company_id)
            ->with([
                'addresses' => function ($query) {
                    $query->with(['country', 'city'])
                        ->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id');
                }
            ])
            ->when(
                $request->filled('name'),
                fn($q) => $q->where(function ($subQuery) use ($request) {
                    $subQuery->where('first_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('middle_name', 'LIKE', "%{$request->name}%")
                        ->orWhere('last_name', 'LIKE', "%{$request->name}%");
                })
            )
            ->when(
                $request->filled('email'),
                fn($q) => $q->whereJsonContains('email', $request->email)
            )
            ->when(
                $request->filled('phone'),
                fn($q) => $q->whereJsonContains('phone', $request->phone)
            )
            ->when(
                $request->filled('status') && $request->status != 0,
                fn($q) => $q->where('status', $request->status)
            )
            ->when(
                $request->filled('start_date'),
                fn($q) => $q->whereDate('created_at', '>=', $request->start_date)
            )
            ->when(
                $request->filled('end_date'),
                fn($q) => $q->whereDate('created_at', '<=', $request->end_date)
            )
            ->get();

        $csvData = $this->generateCSVForClients($leads);
        return response($csvData['csvContent'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$csvData['file']}");
    }

    public function generateCSVForClients($leads)
    {

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Lead ID',
            'Type',
            'Company Name',
            'Title',
            'First Name',
            'Middle Name',
            'Last Name',
            'Emails',
            'Phones',
            'Social Media Links',
            'CGT ID',
            'Street Address',
            'City ID',
            'City Name',
            'Country ID',
            'Country Name',
            'Pincode',
            'Status',
            'Created At',
        ]; // CSV headers

        foreach ($leads as $client) {
            // Prepare consolidated data
            $emails = isset($client->email) ? implode('; ', $client->email) : '';
            $phones = isset($client->phone) ? implode('; ', $client->phone) : '';
            $socialMedia = isset($client->social_media)
                ? implode('; ', array_map(fn($key, $value) => "$key: $value", array_keys($client->social_media), $client->social_media))
                : '';

            // Use the first address as a representative for each client
            $address = $client->addresses->first();

            $csvData[] = [
                $client->id,
                $client->type,
                $client->company_name,
                $client->title,
                $client->first_name,
                $client->middle_name,
                $client->last_name,
                $emails,
                $phones,
                $socialMedia,
                $client->cgt_id,
                $address?->street_address ?? '',
                $address?->city_id ?? '',
                $address?->city?->name ?? '',
                $address?->country_id ?? '',
                $address?->country?->name ?? '',
                $address?->postal_code ?? '',
                $client->status,
                $client?->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        }

        // Convert data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', array_map(fn($value) => '"' . str_replace('"', '""', $value) . '"', $row)) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'clients_export_' . now()->format('Y_m_d_H_i_s') . '.csv';

        return [
            'file' => $fileName,
            'csvContent' => $csvContent
        ];
    }

    public function importView()
    {


        $expectedHeaders = [
            'Type' => [
                'key' => 'Type',
                'message' => 'string, e.g., Individual or Company',
            ],
            'Company Name' => [
                'key' => 'Company Name',
                'message' => 'string, required only if type is company e.g., Abc Ltd Pty, Xyz Limited',
            ],
            'Title' => [
                'key' => 'Title',
                'message' => 'string, e.g., Mr, Miss, Dr, Master',
            ],
            'First Name' => [
                'key' => 'First Name',
                'message' => 'string, e.g., John, Anna',
            ],
            'Middle Name' => [
                'key' => 'Middle Name',
                'message' => 'string, optional, e.g., Edward, Marie',
            ],
            'Last Name' => [
                'key' => 'Last Name',
                'message' => 'string, e.g., Doe, Smith',
            ],
            'Emails' => [
                'key' => 'Emails',
                'message' => 'array, comma-separated, e.g., john.doe@example.com, jane.doe@example.org',
            ],
            'Phones' => [
                'key' => 'Phones',
                'message' => 'array, comma-separated, e.g., +1-555-123-4567, +1-555-765-4321',
            ],
            'Social Media Links' => [
                'key' => 'Social Media Links',
                'message' => 'array, optional, comma-separated, e.g., x: https://x.com/user, fb: https://www.facebook.com/user',
            ],
            'CGT ID' => [
                'key' => 'CGT ID',
                'message' => 'required,  exists:from CGT ID,id, e.g., 1 ,2,3 ',
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
                'Title' => 'Mr',
                'First Name' => 'John',
                'Middle Name' => 'Edward',
                'Last Name' => 'Doe',
                'Emails' => 'john.doe@example.com; jane.doe@example.org',
                'Phones' => '+91 8989898989; +1 89898989898',
                'Social Media Links' => 'x: https://x.com/johndoe, fb: https://www.facebook.com/johndoe, in: https://www.instagram.com/johndoe, ln: https://www.linkedin.com/in/johndoe',
                'CGT ID' => '2',
                'Street Address' => '123 Elm Street',
                'City Name' => 'Springfield',
                'Country ID' => '1',
                'Pincode' => '12345',
            ],
            [
                'Type' => 'Company',
                'Company Name' => 'ABC Multi Brach Hospital For Children',
                'Title' => 'Dr',
                'First Name' => 'Anna',
                'Middle Name' => 'Marie',
                'Last Name' => 'Smith',
                'Emails' => 'anna.smith@example.org; contact@smithco.com',
                'Phones' => '+44 8787878787; +44 7676767676',
                'Social Media Links' => 'x: https://x.com/smithco, fb: https://www.facebook.com/smithco, in: https://www.instagram.com/smithco, ln: https://www.linkedin.com/company/smithco',
                'CGT ID' => '1',
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
                'Title' => ['nullable', 'string'],
                'First Name' => ['required', 'string'],
                'Middle Name' => ['nullable', 'string'],
                'Last Name' => ['required', 'string'],
                'Emails' => ['required', 'string'],
                'Phones' => ['required', 'string'],
                'Social Media Links' => ['nullable', 'string'], // Allow empty
                'CGT ID' => ['required', 'string', 'exists:category_group_tag,id'],
                'Street Address' => ['nullable', 'string', 'max:255'], // Allow empty
                'City Name' => ['nullable', 'string'], // Allow empty
                'Country ID' => ['nullable', 'exists:countries,id'], // Allow empty
                'Pincode' => ['nullable', 'string'], // Allow empty
            ];

            // Expected CSV headers
            $expectedHeaders = ['Type', 'Company Name', 'Title', 'First Name', 'Middle Name', 'Last Name', 'Emails', 'Phones', 'Social Media Links', 'CGT ID', 'Street Address', 'City Name', 'Country ID', 'Pincode'];

            // Dispatch the job
            CsvImportJob::dispatch(
                $absoluteFilePath,
                $rules,
                ClientsCsvRowProcessor::class,
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
    public function view($id)
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
    public function changeStage($leadid, $stageid)
    {

        try {
            CRMLeads::query()->where('id', '=', $leadid)->update(['status_id' => $stageid]);

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

    public function kanbanLoad(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $data = collect();
        $data = $this->leadsService->getKanbanLoad($request->all());
        return response()->json($data);
    }

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




}

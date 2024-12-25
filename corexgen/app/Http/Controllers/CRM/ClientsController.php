<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest;
use App\Http\Requests\ClientsEditRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Repositories\ClientRepository;
use App\Services\Csv\ClientsCsvRowProcessor;
use App\Services\ClientService;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Traits\SubscriptionUsageFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Jobs\CsvImportJob;
use App\Models\CategoryGroupTag;
use App\Services\CustomFieldService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClientsController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use CategoryGroupTagsFilter;
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
    private $viewDir = 'dashboard.crm.clients.';

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


    protected $clientRepository;
    protected $clientService;

    protected $customFieldService;

    public function __construct(
        ClientRepository $clientRepository,
        ClientService $clientService,
        CustomFieldService $customFieldService
    ) {
        $this->clientRepository = $clientRepository;
        $this->clientService = $clientService;
        $this->customFieldService = $customFieldService;
    }


    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->clientService->getDatatablesResponse($request);
        }



        $user = Auth::user();
        $userQuery = CRMClients::query();

        $userQuery = $this->applyTenantFilter($userQuery);

        // Get all totals in a single query
        $usersTotals = $userQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['CLIENTS']['STATUS']['ACTIVE']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['CLIENTS']['STATUS']['DEACTIVE']
            ))
        ])->first();

        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Clients Management',
            'permissions' => PermissionsHelper::getPermissionsArray('CLIENTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'type' => 'Clients',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,
        ]);
    }
    public function store(ClientRequest $request)
    {


        // dd($request->all());

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmclients'], Auth::user()->company_id);
            }


            // Create client
            $client = $this->clientService->createClient($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($client['client'], $validatedData);
            }

            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]), '+', '1');

            return redirect()
                ->route($this->getTenantRoute() . 'clients.index')
                ->with('success', 'Client created successfully.');

        } catch (\Exception $e) {
            \Log::error('Client creation failed', [
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
        $this->checkCurrentUsage(strtolower(PermissionsHelper::$plansPermissionsKeys['CLIENTS']));
        $countries = Country::all();


        $categoryQuery = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients']);
        $categoryQuery = $this->applyTenantFilter($categoryQuery);
        $categories = $categoryQuery->get();

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmclients'], Auth::user()->company_id);
        }

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Client',
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'categories' => $categories,
            'customFields' => $customFields

        ]);
    }

    public function update(ClientsEditRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        // dd($request->all());

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmclients'], Auth::user()->company_id);
            }



            $client = $this->clientService->updateClient($request->validated());


            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($client['client'], $validatedData);
            }


            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->tenantRoute . 'clients.index')
                ->with('success', 'Client updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Client updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the client. ' . $e->getMessage());
        }

    }
    public function edit($id)
    {
        $query = CRMClients::query()->with([
            'addresses' => function ($query) {
                $query->select('addresses.id', 'addresses.street_address', 'addresses.postal_code', 'addresses.city_id', 'addresses.country_id')
                    ->withPivot('type');
            },
            'customFields'
        ])->where('id', $id);

        $query = $this->applyTenantFilter($query, 'clients');

        $client = $query->firstOrFail();


        // countries
        $countries = Country::all();

        // category groups tags
        $categoryQuery = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['categories'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['clients']);
        $categoryQuery = $this->applyTenantFilter($categoryQuery);
        $categories = $categoryQuery->get();

        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmclients'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($client);
        }



        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Client',
            'client' => $client,
            'countries' => $countries,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'categories' => $categories,
            'customFields' => $customFields,
            'cfOldValues' => $cfOldValues
        ]);
    }


    public function destroy($id)
    {
        try {
            // Delete the user
            $client = CRMClients::find($id);
            if ($client) {

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($client);

                // delete  now
                $client->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]), '-', '1');

                return redirect()->back()->with('success', 'Client deleted successfully.');
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
        // Apply filters and fetch clients
        $clients = CRMClients::query()
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

        $csvData = $this->generateCSVForClients($clients);
        return response($csvData['csvContent'])
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$csvData['file']}");
    }

    public function generateCSVForClients($clients)
    {

        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'Client ID',
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

        foreach ($clients as $client) {
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

            'title' => 'Import Clients',
            'headers' => $expectedHeaders,
            'data' => $sampleData,
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
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
                    'import_type' => 'Clients'
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
     * Parse social media links into key-value JSON
     */
    private function parseSocialMedia($links)
    {
        $result = [];
        if (empty($links))
            return $result; // Return empty array if no links provided
        $pairs = explode(';', $links);

        foreach ($pairs as $pair) {
            $keyValue = explode("':", $pair);
            if (count($keyValue) === 2) {
                $key = trim($keyValue[0], " '");
                $value = trim($keyValue[1]);
                $result[$key] = $value;
            }
        }

        return $result;
    }



    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids');

        try {
            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    // First, delete custom field values
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['crmclients'], $ids);

                    // Then delete the clients
                    CRMClients::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected clients deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No clients selected for deletion.'], 400);

        } catch (\Exception $e) {
            \Log::error('Bulk deletion failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'ids' => $ids
            ]);

            return response()->json(
                ['error' => 'Failed to delete the clients: ' . $e->getMessage()],
                500
            );
        }
    }
    public function view()
    {

    }
    public function profile()
    {

    }

    public function changeStatus($id, $status)
    {
        try {
            CRMClients::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Clients status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the clients status: ' . $e->getMessage());
        }
    }




}


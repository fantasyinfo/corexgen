<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\CompaniesRequest;
use App\Jobs\CsvImportJob;
use App\Models\Company;
use App\Models\Country;
use App\Models\Plans;
use App\Services\CompanyService;
use App\Services\Csv\CompaniesCsvRowProcessor;
use App\Traits\StatusStatsFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * CompaniesController handles CRUD operations for Compaines
 * 
 * This controller manages Compaines-related functionality including:
 * - Listing Compaines with server-side DataTables
 * - Creating new Compaines
 * - Editing existing Compaines
 * - Exporting Compaines to CSV
 * - Importing Compaines from CSV
 * - Changing Compaines here status removed,
 *  - New VErsion Check
 */

class CompaniesController extends Controller
{
    //
    use TenantFilter;
    use StatusStatsFilter;

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
    private $viewDir = 'dashboard.companies.';

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


    protected $companyRepository;
    protected $companyService;

    public function __construct(
        CompanyRepository $companyRepository,
        CompanyService $companyService
    ) {
        $this->companyRepository = $companyRepository;
        $this->companyService = $companyService;
    }


    /**
     * Display list of company with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $this->tenantRoute = $this->getTenantRoute();



        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->companyService->getDatatablesResponse($request);
        }


        $plans = Plans::with('planFeatures')->get();
        $country = Country::all();



        $headerStatus = $this->getHeaderStatus(\App\Models\Company::class, PermissionsHelper::$plansPermissionsKeys['COMPANIES']);

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Companies Management',
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'plans' => $plans,
            'country' => $country,
            'type' => 'Companies',
            'headerStatus' => $headerStatus
        ]);
    }


    private function getHeaderStatus($model, $permission, $isSeftDelete = true)
    {
        $user = Auth::user();

        // fetch totals status by clause
        $statusQuery = $this->getGroupByStatusQuery($model, $isSeftDelete);
        $groupData = $statusQuery['groupQuery']->get()->toArray();
        $totalData = $statusQuery['totalQuery']->count();
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
     * Storing the data of user into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(CompaniesRequest $request, CompanyService $companyService)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {
            $company = $companyService->createCompany($request->validated());


            return redirect()
                ->route($this->getTenantRoute() . 'companies.index')
                ->with('success', 'Company created successfully.');
        } catch (\Exception $e) {
            \Log::error('Company creation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while creating the company. ' . $e->getMessage());
        }
    }





    /**
     * Return the view for creating new company with plans
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $plans = Plans::with('planFeatures')->get();
        $country = Country::all();


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Company',
            'plans' => $plans,
            'country' => $country,
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],

        ]);
    }

    /**
     * showing the edit company view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {


        $query = Company::query()
            ->with([
                'users' => function ($query) use ($id) {
                    $query->where('role_id', null)
                        ->where('company_id', $id)
                        ->select('id', 'name', 'company_id', 'role_id');
                },
                'addresses' => function ($query) {
                    $query->with('country')
                        ->with('city')
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                }
            ])
            ->where('id', $id)
            ->select('companies.*', 'companies.name as cname');

        $company = $query->firstOrFail();
        // dd($company);



        $plans = Plans::with('planFeatures')->get();
        $country = Country::all();


        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Company',
            'company' => $company,
            'plans' => $plans,
            'country' => $country,
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
        ]);
    }


    /**
     * Method update 
     * for updating the company
     *
     * @param Request $request [explicite description]
     *

     */
    public function update(CompaniesRequest $request, CompanyService $companyService)
    {


        $this->tenantRoute = $this->getTenantRoute();

        try {
            $companyService->updateCompany($request->validated());

            // If validation fails, it will throw an exception
            return redirect()
                ->back()
                ->with('success', 'Company updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Company updation failed', [
                'error' => $e->getMessage(),
                'data' => $request->validated()
            ]);

            return redirect()
                ->back()
                ->with('error', 'An error occurred while updating the company. ' . $e->getMessage());
        }
    }



    /**
     * Method export 
     *
     * @param Request $request [exporting the company]
     *
     * @return void
     */
    public function export(Request $request, CompanyRepository $companyRepository)
    {
        $companies = $companyRepository->getCompanyQuery($request)->with([
            'addresses' => function ($query) {
                $query->with('country')
                    ->with('city')
                    ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
            }
        ])->get();

        // dd($companies);

        // Generate CSV content
        $csvData = [];
        $csvData[] = [
            'ID',
            'Company Name',
            'Email',
            'Phone',
            'Street Address',
            'City ID',
            'City Name',
            'Country ID',
            'Country Name',
            'Pincode',
            'Plan Id',
            'Plan Name',
            'Billing Cycle',
            'Subscription Start Date',
            'Subscription End Date',
            'Subscription Renew Date',
            'Subscription Upgrade Date',
            'Status',
            'Created At',

        ]; // CSV headers

        foreach ($companies as $company) {
            $subscriptionStartDate = optional($company->subscriptions->first())['start_date']
                ? Carbon::parse($company->subscriptions->first()['start_date'])->format('Y-m-d H:i:s')
                : null;
            $subscriptionEndDate = optional($company->subscriptions->first())['end_date']
                ? Carbon::parse($company->subscriptions->first()['end_date'])->format('Y-m-d H:i:s')
                : null;
            $subscriptionRenewDate = optional($company->subscriptions->first())['next_billing_date']
                ? Carbon::parse($company->subscriptions->first()['next_billing_date'])->format('Y-m-d H:i:s')
                : null;
            $subscriptionUpgradeDate = optional($company->subscriptions->first())['upgrade_date']
                ? Carbon::parse($company->subscriptions->first()['upgrade_date'])->format('Y-m-d H:i:s')
                : null;

            $csvData[] = [
                $company->id,
                $company->name,
                $company->email,
                $company->phone,
                optional($company?->addresses)->street_address,
                optional($company?->addresses?->city)->id,
                optional($company?->addresses?->city)->name,
                optional($company?->addresses?->country)->id,
                optional($company?->addresses?->country)->name,
                optional($company?->addresses)->postal_code,
                optional($company->plans)->id,
                optional($company->plans)->name,
                optional($company->plans)->billing_cycle,
                $subscriptionStartDate,
                $subscriptionEndDate,
                $subscriptionRenewDate,
                $subscriptionUpgradeDate,
                $company->status,
                $company->created_at->format('Y-m-d H:i:s'),
            ];
        }

        // Convert the data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'companies_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    public function importView()
    {
        $expectedHeaders = [
            'Company Name' => [
                'key' => 'Company Name',
                'message' => 'string, e.g., Abc Public Digital Ltd or Pharma Pvt Ltd',
            ],
            'Owner Full Name' => [
                'key' => 'Owner Full Name',
                'message' => 'string,  e.g., Gaurav Kumar Sharma, John Doe',
            ],
            'Email' => [
                'key' => 'Email',
                'message' => 'string, email, e.g., john@doe.com',
            ],
            'Phone' => [
                'key' => 'Phone',
                'message' => 'string, number between 10 to 15 digit e.g.,9989009656, 12312312321',
            ],
            'Password' => [
                'key' => 'Password',
                'message' => 'string, must be 8 charactes long secretPass e.g.SecretPass',
            ],
            'Plan ID' => [
                'key' => 'Plan ID',
                'message' => 'string, plans table id e.g.1 , 2, 3',
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
                'Company Name' => 'Abc Limited',
                'Owner Full Name' => 'John Doe',
                'Email' => 'john@mail.com',
                'Phone' => '9898767767',
                'Password' => 'John@Secret123',
                'Plan ID' => '1',
                'Street Address' => '123 Elm Street',
                'City Name' => 'Springfield',
                'Country ID' => '1',
                'Pincode' => '12345',
            ],
            [
                'Company Name' => 'Parul Digitals Limited',
                'Owner Full Name' => 'Parul Doe',
                'Email' => 'originaparul@mail.com',
                'Phone' => '1213232321',
                'Password' => 'ParulPa@Secret123',
                'Plan ID' => '2',
                'Street Address' => '456 Oak Avenue',
                'City Name' => 'London',
                'Country ID' => '44',
                'Pincode' => 'E1 6AN',
            ],
        ];



        return view($this->getViewFilePath('import'), [

            'title' => 'Import Companies',
            'headers' => $expectedHeaders,
            'data' => $sampleData,

            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
        ]);
    }

    /**
     * Method import bulk import company
     *
     * @param Request $request [bulk import company]
     *
     * @return void
     */
    public function import(Request $request, CompanyService $companyService)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:' . BULK_CSV_UPLOAD_FILE_SIZE . '', // Validate file type and size
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

            $rules = [
                'Company Name' => ['required', 'string', 'max:255'],
                'Owner Full Name' => ['required', 'string', 'max:255'],
                'Email' => ['required', 'email', 'unique:companies,email', 'unique:users,email'],
                'Phone' => ['required', 'digits_between:10,15'],
                'Password' => ['required', 'string', 'min:8'],
                'Plan ID' => ['required', 'exists:plans,id'],
                'Street Address' => ['nullable', 'string', 'max:255'], // Allow empty
                'City Name' => ['nullable', 'string'], // Allow empty
                'Country ID' => ['nullable', 'exists:countries,id'], // Allow empty
                'Pincode' => ['nullable', 'string'], // Allow empty
            ];


            // Expected CSV headers
            $expectedHeaders = ['Company Name', 'Owner Full Name', 'Email', 'Phone', 'Password', 'Plan ID', 'Street Address', 'City Name', 'Country ID', 'Pincode'];

            // Dispatch the job
            CsvImportJob::dispatch(
                $absoluteFilePath,
                $rules,
                CompaniesCsvRowProcessor::class,
                $expectedHeaders,
                [
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::id(),
                    'is_tenant' => Auth::user()->is_tenant,
                    'import_type' => 'Companies'
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
     * Deleting the company
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $company = Company::findOrFail($id);


            $company->delete();

            DB::commit();
            return redirect()->back()->with('success', 'Company deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Failed to delete the company: ' . $e->getMessage());
        }
    }
    /**
     * Bulk Delete the companies
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids');

            if (!is_array($ids) || empty($ids)) {
                return response()->json(['message' => 'No companies selected for deletion.'], 400);
            }

            DB::beginTransaction();

            Company::whereIn('id', $ids)->each(function ($company) {
                $company->delete();
            });

            DB::commit();
            return response()->json(['message' => 'Selected companies deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Bulk company deletion failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete companies: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Method changeStatus (change company status)
     *
     * @param $id $id [explicite id of company]
     * @param $status $status [explicite status to change]
     *

     */
    public function changeStatus($id, $status)
    {
        try {
            // Delete the role
            Company::query()->where('id', '=', $id)->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'Company status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the company status: ' . $e->getMessage());
        }
    }


    public function loginas($companyid)
    {
        \DB::enableQueryLog();

        $user = User::where('company_id', $companyid)
            ->where('role_id', null)
            ->where('is_tenant', 0)
            ->first();

        \Log::info('User Switching Debug', [
            'companyid' => $companyid,
            'query' => \DB::getQueryLog(),
            'user_found' => $user ? true : false,
            'user_details' => $user ? $user->toArray() : null
        ]);

        if (!$user) {
            return redirect()->back()->with('error', 'Company account not found.');
        }

        try {
            $guard = Auth::guard('web');

            // Store original user ID if needed for back-switching
            $originalUserId = $guard->id();
            session()->put('original_user_id', $originalUserId);

            \Log::info('Active Guard Before Logout', ['guard' => get_class($guard)]);

            if ($guard->check()) {
                $guard->logout();
            }

            // Start new session
            request()->session()->flush();
            request()->session()->regenerate(true); // true parameter forces regeneration

            // Login the new user
            $guard->loginUsingId($user->id);

            // Verify login
            if (!$guard->check() || $guard->id() !== $user->id) {
                throw new \Exception('Login verification failed.');
            }

            \Log::info('Successful User Switch', [
                'new_user_id' => $user->id,
                'authenticated_user_id' => $guard->id(),
                'authenticated_user_email' => $user->email,
                'guard' => Auth::getDefaultDriver()
            ]);

            session()->put('login_as', true);

            // Regenerate CSRF token
            session()->regenerateToken();

            return redirect()->route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home')
                ->with('success', 'Logged in as company owner');

        } catch (\Exception $e) {
            \Log::error('User Switching Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempted_user_id' => $user->id,
                'current_user_id' => Auth::id() ?: 'Not authenticated'
            ]);

            return redirect()->back()->with('error', 'Failed to switch user account: ' . $e->getMessage());
        }
    }

    public function loginback()
    {
        session()->remove('login_as');
        \DB::enableQueryLog();

        $user = User::where('is_tenant', '1')
            ->where('role_id', null)
            ->where('company_id', null)
            ->first();

        \Log::info('Tenant Switching back Debug', [
            'tenant_id' => $user->id,
            'query' => \DB::getQueryLog(),
            'user_found' => $user ? true : false,
            'user_details' => $user ? $user->toArray() : null
        ]);

        if (!$user) {
            return redirect()->back()->with('error', 'Tenant account not found.');
        }

        try {
            $guard = Auth::guard('web');

            \Log::info('Active Guard Before Logout', ['guard' => get_class($guard)]);

            if ($guard->check()) {
                $guard->logout();
            }

            // Start new session
            request()->session()->flush();
            request()->session()->regenerate(true); // true parameter forces regeneration

            // Login the user
            $guard->loginUsingId($user->id);

            // Verify login
            if (!$guard->check() || $guard->id() !== $user->id) {
                throw new \Exception('Login verification failed.');
            }

            \Log::info('Successful Tenant back ', [
                'new_user_id' => $user->id,
                'authenticated_user_id' => $guard->id(),
                'authenticated_user_email' => $user->email,
                'guard' => Auth::getDefaultDriver()
            ]);

            // Regenerate CSRF token
            session()->regenerateToken();

            return redirect()->route(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.home')
                ->with('success', 'Logged back from company account to owner acc');

        } catch (\Exception $e) {
            \Log::error('User Switching Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'attempted_user_id' => $user->id,
                'current_user_id' => Auth::id() ?: 'Not authenticated'
            ]);

            return redirect()->back()->with('error', 'Failed to switch user account: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:companies,id',
            'password' => [
                'required',
                'string',
            ]
        ], [
            'id.exists' => 'Invalid user ID.'
        ]);

        // Check validation
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            // Find user and update password
            $user = User::where('company_id', '=', $request->input('id'))->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Compnay User not found.'
                ], 404);
            }


            $user->password = Hash::make($request->input('password'));
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Password change error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }

    public function view($id)
    {
        $query = Company::query()
            ->with([
                'plans',
                'latestSubscription',
                'paymentTransactions',
                'subscriptions',
                'users' => function ($query) use ($id) {
                    $query->where('role_id', null)
                        ->where('company_id', $id)
                        ->select('id', 'name', 'company_id', 'role_id')->first();
                },
                'addresses' => function ($query) {
                    $query->with('country')
                        ->with('city')
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                }
            ])
            ->where('id', $id)
            ->select('companies.*', 'companies.name as cname');

        $company = $query->firstOrFail();

        // dd($company);

        return view($this->getViewFilePath('view'), [

            'title' => 'View Company',
            'company' => $company,
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
        ]);
    }
}

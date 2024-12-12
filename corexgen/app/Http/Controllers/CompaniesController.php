<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\CRM\CompaniesRequest;
use App\Http\Requests\UserRequest;
use App\Models\Company;
use App\Models\Country;
use App\Models\CRM\CRMRole;
use App\Models\Plans;
use App\Services\CompanyService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
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
        $country = Country::with('cities')->get();

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Companies Management',
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'plans' => $plans,
            'country' => $country
        ]);
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
     * @return void
     */
    public function update(CompaniesRequest $request, CompanyService $companyService)
    {


        $this->tenantRoute = $this->getTenantRoute();

        try {
            $companyService->updateCompany($request->validated());

            // If validation fails, it will throw an exception
            return redirect()
                ->route($this->getTenantRoute() . 'companies.index')
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
            'Address Id',
            'Street Address',
            'City Id',
            'City Name',
            'Country Id',
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
                optional($company->addresses)->id,
                optional($company->addresses)->street_address,
                optional($company->addresses->city)->id,
                optional($company->addresses->city)->name,
                optional($company->addresses->country)->id,
                optional($company->addresses->country)->name,
                optional($company->addresses)->postal_code,
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
            'file' => 'required|mimes:csv,txt|max:2048', // Validate file type and size
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ]);
        }
    
        try {
            $file = $request->file('file');
            $data = array_map('str_getcsv', file($file->getRealPath()));
            $header = array_map('trim', array_shift($data)); // Extract and trim header row
    
            $rules = [
                'Company Name' => ['required', 'string', 'max:255'],
                'Owner Full Name' => ['required', 'string', 'max:255'],
                'Email' => ['required', 'email', 'unique:companies,email', 'unique:users,email'],
                'Phone' => ['required', 'digits_between:10,15'],
                'Password' => ['required', 'string', 'min:8'],
                'Plan ID' => ['required', 'exists:plans,id'],
                'Street Address' => ['nullable', 'string', 'max:255'],
                'Country ID' => ['nullable', 'exists:countries,id'],
                'City ID' => ['nullable', 'exists:cities,id'],
                'Pincode' => ['nullable', 'string', 'max:10'],
            ];
    
            $skippedRows = [];
            $successfulImportsCount = 0;
    
            foreach ($data as $index => $row) {
                $row = array_combine($header, $row);
                $rowValidator = Validator::make($row, $rules);
    
                if ($rowValidator->fails()) {
                    // Log or collect skipped rows with errors
                    $skippedRows[] = [
                        'row' => $index + 2, // Adding 2 to account for header row and zero-based index
                        'errors' => $rowValidator->errors()->all(),
                    ];
                    continue; // Skip this row
                }
    
                // Pass each validated row to the CompanyService
                $companyService->createCompany([
                    'cname' => $row['Company Name'],
                    'name' => $row['Owner Full Name'],
                    'email' => $row['Email'],
                    'phone' => $row['Phone'],
                    'password' => $row['Password'], // Pass raw password, handle hashing in the service
                    'plan_id' => $row['Plan ID'],
                    'address_street_address' => $row['Street Address'] ?? null,
                    'address_country_id' => $row['Country ID'] ?? null,
                    'address_city_id' => $row['City ID'] ?? null,
                    'address_pincode' => $row['Pincode'] ?? null,
                ]);
    
                $successfulImportsCount++;
            }
    
            return response()->json([
                'success' => true,
                'message' => 'CSV processed successfully.',
                'imported_count' => $successfulImportsCount,
                'skipped_count' => count($skippedRows),
                'skipped_rows' => $skippedRows, // Optional: Include details about skipped rows
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
            // Delete the user

            Company::query()->where('id', '=', $id)->delete();

            // Return success response
            return redirect()->back()->with('success', 'Company deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
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

        $ids = $request->input('ids');

        try {
            // Delete the companies

            if (is_array($ids) && count($ids) > 0) {
                // Validate ownership/permissions if necessary
                Company::query()->whereIn('id', $ids)->delete();

                return response()->json(['message' => 'Selected companiess deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No companiess selected for deletion.'], 400);





        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the companies: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change company status)
     *
     * @param $id $id [explicite id of company]
     * @param $status $status [explicite status to change]
     *
     * @return void
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
        // Enable query logging for detailed investigation
        \DB::enableQueryLog();

        // Fetch the company owner with extensive logging
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
            // Explicitly use the 'web' guard
            $guard = Auth::guard('web');

            \Log::info('Active Guard Before Logout', ['guard' => get_class($guard)]);

            // Logout the current user if authenticated
            if ($guard->check()) {
                $guard->logout();
            }

            // Completely reset the session
            request()->session()->flush();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            // Log in the new user using 'web' guard
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

            // Redirect to the desired dashboard or view
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

<?php

namespace App\Http\Controllers\CRM;

use App\Http\Controllers\Controller;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\CRM\CompaniesRequest;
use App\Http\Requests\CRM\CRMUserRequest;
use App\Models\Company;
use App\Models\Country;
use App\Models\CRM\CRMRole;
use App\Models\Plans;
use App\Services\CompanyService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
    private $viewDir = 'dashboard.crm.companies.';

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


    /**
     * Display list of users with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        $query = Company::query();

        $plans = Plans::with('planFeatures')->get();
        $country = Country::with('cities')->get();


        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('name', 'LIKE', "%{$request->name}%"));
        $query->when($request->filled('email'), fn($q) => $q->where('email', 'LIKE', "%{$request->status}%"));
        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));





        // Server-side DataTables response
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('actions', function ($user) {
                    return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
                        'id' => $user->id
                    ])->render();
                })
                ->editColumn('created_at', function ($user) {
                    return $user->created_at->format('d M Y');
                })
                ->editColumn('status', function ($user) {
                    return View::make(getComponentsDirFilePath('dt-status'), [
                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
                        'id' => $user->id,
                        'status' => [
                            'current_status' => $user->status,
                            'available_status' => CRM_STATUS_TYPES['COMPANIES']['STATUS'],
                            'bt_class' => CRM_STATUS_TYPES['COMPANIES']['BT_CLASSES'],
                        ]
                    ])->render();
                })
                ->rawColumns(['actions', 'status']) // Add 'status' to raw columns
                ->make(true);
        }

        $roles = $this->applyTenantFilter(CRMRole::query())->get();

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
     * showing the edit user view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {


        $query = Company::query()
            ->with([
                'users' => function ($query) {
                    $query->where('role_id', null)
                        ->select('id', 'name', 'company_id', 'role_id');
                }
            ])
            ->with([
                'addresses' => function ($query) {
                    $query->with('country')
                        ->with('city')
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                }
            ])
            ->where('id', $id)
            ->select('companies.*', 'companies.name as cname');

        // $company = $query->toSql();
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
     * for updating the user
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
     * @param Request $request [exporting the users]
     *
     * @return void
     */
    public function export(Request $request)
    {
        $query = User::query()->with('role');

        if (panelAccess() == PANEL_TYPES['SUPER_PANEL']) {
            $query->where('is_tenant', '=', true);
        }

        // Apply tenant filter (if necessary)
        $query = $this->applyTenantFilter($query, 'users');

        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('name', 'LIKE', "%{$request->name}%"));
        $query->when($request->filled('email'), fn($q) => $q->where('email', 'LIKE', "%{$request->status}%"));

        if ($request->filled('role_id') && $request->role_id != '0') {

            $query->when($request->filled('role_id'), fn($q) => $q->where('role_id', $request->role_id));
        }

        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));
        $query->when($request->filled('start_date'), fn($q) => $q->whereDate('created_at', '>=', $request->start_date));
        $query->when($request->filled('end_date'), fn($q) => $q->whereDate('created_at', '<=', $request->end_date));

        // For debugging: dd the SQL and bindings

        // Get the filtered data
        $roles = $query->get();

        // Generate CSV content
        $csvData = [];
        $csvData[] = ['ID', 'Full Name', 'Email', 'Role', 'Status', 'Created At', 'Updated At']; // CSV headers

        foreach ($roles as $role) {
            $csvData[] = [
                $role->id,
                $role->name,
                $role->email,
                $role->role_name,
                $role->status,
                $role->created_at->format('Y-m-d H:i:s'),
                $role->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        // Convert the data to CSV string
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        // Return the response with the CSV content as a file
        $fileName = 'users_export_' . now()->format('Y_m_d_H_i_s') . '.csv';
        return response($csvContent)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename={$fileName}");
    }

    /**
     * Method import bulk import users
     *
     * @param Request $request [bulk import users]
     *
     * @return void
     */
    public function import(Request $request)
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
            $header = array_shift($data);

            foreach ($data as $row) {
                $row = array_combine($header, $row);

                // Skip if email already exists
                if (User::where('email', $row['email'])->exists()) {
                    continue;
                }

                // Check if role_id exists in crm_roles table
                $roleExists = $row['role_id'] && DB::table('crm_roles')->where('id', $row['role_id'])->exists();

                if (!$roleExists) {
                    // Skip this row if the role doesn't exist
                    continue;
                }

                User::create([
                    'name' => $row['name'] ?? '',
                    'email' => $row['email'] ?? '',
                    'password' => Hash::make($row['password']) ?? '',
                    'role_id' => $row['role_id'] ?? '',
                    'company_id' => Auth::user()->company_id

                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Users imported successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ]);
        }
    }


    /**
     * Deleting the user
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the user

            $this->applyTenantFilter(User::query()->where('id', '=', $id))->delete();

            // Return success response
            return redirect()->back()->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change user status)
     *
     * @param $id $id [explicite id of user]
     * @param $status $status [explicite status to change]
     *
     * @return void
     */
    public function changeStatus($id, $status)
    {
        try {
            // Delete the role

            $this->applyTenantFilter(User::query()->where('id', '=', $id))->update(['status' => $status]);
            // Return success response
            return redirect()->back()->with('success', 'User status changed successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to changed the user status: ' . $e->getMessage());
        }
    }


    public function view($companyid)
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

}

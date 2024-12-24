<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\UserRequest;
use App\Jobs\CsvImportJob;
use App\Models\Country;
use App\Models\CRM\CRMRole;
use App\Repositories\UserRepository;
use App\Services\Csv\UsersCsvRowProcessor;
use App\Services\CustomFieldService;
use App\Services\UserService;
use App\Traits\SubscriptionUsageFilter;
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
 * UserController handles CRUD operations for Users
 * 
 * This controller manages user-related functionality including:
 * - Listing User with server-side DataTables
 * - Creating new User
 * - Editing existing User
 * - Exporting User to CSV
 * - Importing User from CSV
 * - Changing user here status removed,
 *  - New VErsion Check
 */

class UserController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;

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
    private $viewDir = 'dashboard.users.';

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


    protected $userRepository;
    protected $userService;

    protected $customFieldService;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        CustomFieldService $customFieldService

    ) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->customFieldService = $customFieldService;
    }

    /**
     * Display list of users with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->userService->getDatatablesResponse($request);
        }

        // Regular view rendering
        $roles = $this->applyTenantFilter(CRMRole::query())->get();




        // Fetch the totals in a single query

        // Build base query for user totals
        $user = Auth::user();
        $userQuery = User::where('deleted_at', null)->whereNot('id', $user->id);

        $userQuery = $this->applyTenantFilter($userQuery);

        // Get all totals in a single query
        $usersTotals = $userQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['USERS']['STATUS']['DEACTIVE']
            ))
        ])->first();

        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Users Management',
            'roles' => $roles,
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'type' => 'Users',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,
        ]);
    }



    /**
     * Storing the data of user into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(UserRequest $request, UserService $userService)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['user'], Auth::user()->company_id);
            }


            $userData = $request->validated();
            $userData['company_id'] = Auth::user()->company_id;
            $userData['is_tenant'] = Auth::user()->is_tenant;

            $user = $userService->createUser($userData);

            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($user, $validatedData);
            }

            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]), '+', '1');

            return redirect()->route($this->tenantRoute . 'users.index')
                ->with('success', 'User created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while creating the user: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new user with roles
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['USERS']));

        $roles = $this->applyTenantFilter(CRMRole::query())->get();
        $country = Country::all();

        $customFields = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['user'], Auth::user()->company_id);
        }

        return view($this->getViewFilePath('create'), [
            'title' => 'Create User',
            'roles' => $roles,
            'country' => $country,
            'customFields' => $customFields
        ]);
    }

    /**
     * showing the edit user view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {


        $query = User::query()
            ->with([
                'addresses' => function ($query) {
                    $query->with('country')
                        ->with('city')
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                }
            ])
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $user = $query->firstOrFail();

        $roles = $this->applyTenantFilter(CRMRole::query())->get();

        $country = Country::all();



        // custom fields
        $customFields = collect();
        $cfOldValues = collect();
        if (!is_null(Auth::user()->company_id)) {
            $customFields = $this->customFieldService->getFieldsForEntity(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['user'], Auth::user()->company_id);

            // fetch already existing values

            $cfOldValues = $this->customFieldService->getValuesForEntity($user);
        }

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit User',
            'user' => $user,
            'roles' => $roles,
            'country' => $country,
            'customFields' => $customFields,
            'cfOldValues' => $cfOldValues
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
    public function update(UserRequest $request, UserService $userService)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {

            // custom fields validation if any
            $validatedData = [];
            if ($request->has('custom_fields') && !is_null(Auth::user()->company_id)) {
                $validator = new CustomFieldsValidation();
                $validatedData = $validator->validate($request->input('custom_fields'), CUSTOM_FIELDS_RELATION_TYPES['KEYS']['user'], Auth::user()->company_id);
            }

            $user = $userService->updateUser($request->validated());

            // insert custom fields values to db
            if ($request->has('custom_fields') && !empty($validatedData) && count($validatedData) > 0 && !is_null(Auth::user()->company_id)) {
                $this->customFieldService->saveValues($user, $validatedData);
            }

            if ($request->boolean('is_profile')) {
                return redirect()
                    ->route($this->tenantRoute . 'users.profile')
                    ->with('success', __('Profile updated successfully.'));
            }
            return redirect()->route($this->tenantRoute . 'users.index')
                ->with('success', 'User updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the user: ' . $e->getMessage());
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


    public function importView()
    {
        $expectedHeaders = [
            'Name' => [
                'key' => 'Name',
                'message' => 'string, e.g., John Kumar Verma or Jamei Sharma',
            ],
            'Email' => [
                'key' => 'Email',
                'message' => 'string, email, e.g., john@emails.com',
            ],
            'Password' => [
                'key' => 'Password',
                'message' => 'string, e.g., Secret@123#',
            ],
            'Role' => [
                'key' => 'Role',
                'message' => 'string,  e.g., Accounts Manager, Sales Executive',
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

                'Name' => 'John Doe',
                'Email' => 'john@mail.com',
                'Password' => 'John@Secret123',
                'Role' => 'Manager',
                'Street Address' => '123 Elm Street',
                'City Name' => 'Springfield',
                'Country ID' => '1',
                'Pincode' => '12345',
            ],
            [
                'Name' => 'Parul Sharma',
                'Email' => 'originaparul@mail.com',
                'Password' => 'ParulPa@Secret123',
                'Role' => 'Digital Head',
                'Street Address' => '456 Oak Avenue',
                'City Name' => 'London',
                'Country ID' => '44',
                'Pincode' => 'E1 6AN',
            ],
        ];



        return view($this->getViewFilePath('import'), [

            'title' => 'Import Users',
            'headers' => $expectedHeaders,
            'data' => $sampleData,

            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
        ]);
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
                'Name' => ['required', 'string'],
                'Email' => ['required', 'email', 'unique:users,email'],
                'Password' => ['required', 'string'],
                'Role' => ['required', 'string'],
                'Street Address' => ['nullable', 'string'],
                'City Name' => ['nullable', 'string'],
                'Country ID' => ['nullable', 'exists:countries,id'],
                'Pincode' => ['nullable', 'string'],
            ];

            // Expected CSV headers
            $expectedHeaders = [
                'Name',
                'Email',
                'Password',
                'Role',
                'Street Address',
                'City Name',
                'Country ID',
                'Pincode'
            ];


            // Dispatch the job
            CsvImportJob::dispatch(
                $absoluteFilePath,
                $rules,
                UsersCsvRowProcessor::class,
                $expectedHeaders,
                [
                    'company_id' => Auth::user()->company_id,
                    'user_id' => Auth::id(),
                    'is_tenant' => Auth::user()->is_tenant,
                    'import_type' => 'Users'
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
     * Deleting the user
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the user

            $user = User::find($id);
            if ($user) {

                // delete its custom fields also if any
                $this->customFieldService->deleteEntityValues($user);

                // delete  now
                $user->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]), '-', '1');

                return redirect()->back()->with('success', 'User deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the User: User not found with this id.');
            }

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



    /**
     * Bulk Delete the user
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the user

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {
                    // First, delete custom field values
                    $this->customFieldService->bulkDeleteEntityValues(CUSTOM_FIELDS_RELATION_TYPES['KEYS']['user'], $ids);

                    // Then delete the clients
                    User::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected users deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No users selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }

    public function changePassword(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
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
            $user = User::findOrFail($request->input('id'));

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
        $query = User::query()
            ->with([
                'addresses' => function ($query) {
                    $query->with('country')

                        ->with(['city' => fn($q) => $q->select('id', 'name as city_name')])
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                },
                'role'
            ])
            ->where('id', $id)->get();

        $query = $this->applyTenantFilter($query);
        $user = $query->firstOrFail();

        // dd($user);

        $customFields = $this->customFieldService->getValuesForEntity($user);

        return view($this->getViewFilePath('view'), [

            'title' => 'View User',
            'user' => $user,
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'customFields' => $customFields

        ]);
    }




    public function profile()
    {

        $query = User::query()
            ->with([
                'addresses' => function ($query) {
                    $query->with('country') // Load country relationship
                        ->with(['city' => fn($q) => $q->select('id', 'name as city_name')]) // Fix for city
                        ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                },
                'role' // Load role relationship
            ])
            ->where('id', Auth::id());

        $query = $this->applyTenantFilter($query);
        $user = $query->firstOrFail();



        $country = Country::all();
        return view($this->getViewFilePath('profile'), [

            'title' => 'Profile',
            'user' => $user,
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'country' => $country
        ]);
    }


}

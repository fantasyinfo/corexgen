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
use App\Services\LeadsService;
use App\Services\UserService;
use App\Traits\MediaTrait;
use App\Traits\StatusStatsFilter;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Auth;
use App\Models\Media;
use App\Services\ProjectService;
use App\Services\TasksService;

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
    use MediaTrait;
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
    protected $leadService;
    protected $tasksService;
    protected $projectService;

    public function __construct(
        UserRepository $userRepository,
        UserService $userService,
        CustomFieldService $customFieldService,
        LeadsService $leadService,
        TasksService $tasksService,
        ProjectService $projectService,

    ) {
        $this->userRepository = $userRepository;
        $this->userService = $userService;
        $this->customFieldService = $customFieldService;
        $this->leadService = $leadService;
        $this->tasksService = $tasksService;
        $this->projectService = $projectService;
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

        $headerStatus = $this->getHeaderStatus(\App\Models\User::class, PermissionsHelper::$plansPermissionsKeys['USERS']);


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Users Management',
            'roles' => $roles,
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'type' => 'Users',
            'headerStatus' => $headerStatus,
        ]);
    }



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


            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $oldMedia = $user->profile_photo_path ? Media::where('file_path', $user->profile_photo_path)->first() : null;

                $attributes = [
                    'folder' => 'avatars',
                    'company_id' => Auth::user()->company_id,
                    'updated_by' => Auth::id(),
                    'created_by' => Auth::id(),
                ];

                $media = $this->createMedia($request->file('avatar'), $attributes);

                // Update the profile_photo_path column in the user's table
                $user->profile_photo_path = $media->file_path;
                $user->save();

                // Optionally delete the old media record
                if ($oldMedia) {
                    $this->updateMedia($request->file('avatar'), $oldMedia, $attributes);
                }
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
    public function updatePassword(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:users,id',
            'old_password' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',          // Must contain at least one uppercase letter
                'regex:/[a-z]/',          // Must contain at least one lowercase letter
                'regex:/[0-9]/',          // Must contain at least one digit
                'regex:/[@$!%*?&#]/',     // Must contain a special character
                'confirmed'               // Must match the confirmation password
            ]
        ], [
            'id.exists' => 'Invalid user ID.',
            'password.regex' => 'The password must include at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'password.confirmed' => 'The password confirmation does not match.',
        ]);

        // Check validation
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Check if the authenticated user matches the user being updated
        if (Auth::id() != $request->input('id')) {
            return redirect()
                ->back()
                ->withErrors(['message' => 'You are not authorized to change this password.'])
                ->withInput();
        }

        try {
            // Find the user
            $user = User::findOrFail($request->input('id'));

            // Verify old password
            if (!Hash::check($request->input('old_password'), $user->password)) {
                return redirect()
                    ->back()
                    ->withErrors(['old_password' => 'The old password is incorrect.'])
                    ->withInput();
            }

            // Update the password
            $user->password = Hash::make($request->input('password'));
            $user->save();

            // Redirect back with success message
            return redirect()
                ->back()
                ->with('success', 'Password updated successfully.');
        } catch (\Exception $e) {
            \Log::error('Password change error: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['message' => 'An unexpected error occurred. Please try again.']);
        }
    }




    public function view($id)
    {
        $query = User::query()
            ->with([
                'company',
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



        // leads
        $leads = collect();
        $leads = $this->leadService->getLeadsByUser($id);

        // taks
        $tasks = collect();
        $tasks = $this->tasksService->getTasksByUser($id);

        // projects
        $projects = collect();
        $projects = $this->projectService->getProjectsByUser($id);

        return view($this->getViewFilePath('view'), [

            'title' => 'View User',
            'user' => $user,
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'customFields' => $customFields,
            'roles' => $roles,
            'countries' => $country,
            'cfOldValues' => $cfOldValues,
            'leads' => $leads,
            'tasks' => $tasks,
            'projects' => $projects

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




    public function loginas($userid)
    {
        \DB::enableQueryLog();

        $user = User::where('id', $userid)->first();

        \Log::info('User Switching Debug', [
            'query' => \DB::getQueryLog(),
            'user_found' => $user ? true : false,
            'user_details' => $user ? $user->toArray() : null
        ]);

        if (!$user) {
            return redirect()->back()->with('error', 'User account not found.');
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

            session()->put('login_as_team_member', true);
            session()->put('login_from_id', Auth::id());

            // Regenerate CSRF token
            session()->regenerateToken();

            return redirect()->route(getPanelRoutes('home'))
                ->with('success', 'Logged in as team member');

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
        session()->remove('login_as_team_member');
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

}

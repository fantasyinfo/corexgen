<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProposalRequest;
use App\Http\Requests\UserRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMTemplates;
use App\Services\UserService;
use App\Traits\MediaTrait;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Media;
use App\Repositories\ProposalRepository;
use App\Services\ProposalService;

/**
 * UserController handles CRUD operations for Proposals
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

class ProposalController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use MediaTrait;

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
    private $viewDir = 'dashboard.crm.proposals.';

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


    protected $proposalRepository;
    protected $proposalService;

    protected $customFieldService;

    public function __construct(
        ProposalRepository $proposalRepository,
        ProposalService $proposalService,


    ) {
        $this->proposalRepository = $proposalRepository;
        $this->proposalService = $proposalService;

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
            return $this->proposalService->getDatatablesResponse($request);
        }


        // Fetch the totals in a single query

        // Build base query for user totals
        $user = Auth::user();
        $proposalQuery = CRMProposals::query();

        $proposalQuery = $this->applyTenantFilter($proposalQuery);

        // Get all totals in a single query
        $usersTotals = $proposalQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['PROPOSALS']['STATUS']['ACCEPTED']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['PROPOSALS']['STATUS']['DECLINED']
            ))
        ])->first();



        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROPOSALS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }

        $templates = $this->applyTenantFilter(CRMTemplates::query()->where('type', 'Proposals'))->get();

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Proposals Management',
            'templates' => $templates,
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'type' => 'Proposals',
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
    public function store(ProposalRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {

        
            $proposal = $proposalService->createProposal($request->validated());

       
            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PORPOSALS']]), '+', '1');

            return redirect()->route($this->tenantRoute . 'propoals.index')
                ->with('success', 'Proposals created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Proposals: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new user with roles
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['PROPOSALS']));
        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Proposals'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


        // Fetch the last `_id` from the database
        $lastId = CRMProposals::latest('id')->value('id');
        $incrementedId = $lastId ? (int) filter_var($lastId, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        $defaultId = 'PRO-' . str_pad($incrementedId, 4, '0', STR_PAD_LEFT);


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Proposal',
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'lastId' => old('_id', $defaultId),
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

}

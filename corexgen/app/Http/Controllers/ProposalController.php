<?php

namespace App\Http\Controllers;

use App\Helpers\CustomFieldsValidation;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProposalEditRequest;
use App\Http\Requests\ProposalRequest;
use App\Http\Requests\UserRequest;
use App\Models\Country;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMRole;
use App\Models\CRM\CRMSettings;
use App\Models\CRM\CRMTemplates;
use App\Services\UserService;
use App\Traits\IsSMTPValid;
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
    use IsSMTPValid;

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
                CRM_STATUS_TYPES['PROPOSALS']['STATUS']['OPEN']
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

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


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
            'clients' => $clients,
            'leads' => $leads,

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


            $proposal = $this->proposalService->createProposal($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROPOSALS']]), '+', '1');

            return redirect()->route($this->tenantRoute . 'proposals.index')
                ->with('success', 'Proposals created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
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

        $query = CRMProposals::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $proposal = $query->firstOrFail();


        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Proposals'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Proposal',
            'proposal' => $proposal,
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,

        ]);
    }


    /**
     * Method update 
     * for updating the user
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ProposalEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {


            $proposal = $this->proposalService->updateProposal($request->validated());

            return redirect()->route($this->tenantRoute . 'proposals.index')
                ->with('success', 'proposal updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the proposal: ' . $e->getMessage());
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

            $user = CRMProposals::find($id);
            if ($user) {
                // delete  now
                $user->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROPOSALS']]), '-', '1');

                return redirect()->back()->with('success', 'Proposal deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the Proposal: Proposal not found with this id.');
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the proposal: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change user status)
     *
     * @param $id $id [explicite id of user]
     * @param $status $status [explicite status to change]
     *
     */
    public function changeStatusAction($id, $action)
    {
        try {

            $proposal = $this->applyTenantFilter(CRMProposals::query()->with(['typable', 'template'])->where('id', '=', $id))->first();

            if ($proposal->typable_type === CRMLeads::class) {
                $toEmail = $proposal->typable->email;
            } else if ($proposal->typable_type === CRMClients::class) {
                $toEmail = $proposal->typable->primary_email;
            }


            if ($action == 'SENT') {

                if (empty($toEmail) || is_null($toEmail)) {
                    return redirect()->back()->with('error', 'No Email found for this client/lead');
                }
                $mailSettings = $this->_getMailSettings();
                // Validate SMTP settings
                if ($this->_isSMTPValid($mailSettings)) {
                    // Queue the email sending job
                    $emailDetails = [
                        'from' => $mailSettings['Mail From Address'],
                        'to' => $toEmail, // Replace with actual recipient
                        'subject' => $proposal?->title,
                        'details' => $proposal?->details,
                        'template' => $proposal?->template?->template_details
                    ];

                    // dd($emailDetails);

                    // dispatch(new SendEmailJob($mailSettings, $emailDetails));
                } else {
                    return redirect()->back()->with('error', 'SMTP settings are invalid, Please add/update the correct Mail Settings.');
                }
            }

            $proposal->update(['status' => $action]);

            return redirect()->back()->with('success', 'User status changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to change the user status: ' . $e->getMessage());
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



                    // Then delete the clients
                    CRMProposals::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['PROPOSALS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected proposals deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No proposals selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }




    public function view($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMProposals::query()
                ->with([
                    'typable' => function ($query) {
                        if (request('typable_type') === CRMClients::class) {
                            // For Clients - many-to-many relationship
                            $query->with([
                                'addresses' => function ($addressQuery) {
                                $addressQuery->select(
                                    'addresses.id',
                                    'addresses.street_address',
                                    'addresses.postal_code',
                                    'addresses.city_id',
                                    'addresses.country_id'
                                )
                                    ->withPivot('type');
                            }
                            ]);
                        } elseif (request('typable_type') === CRMLeads::class) {
                            // For Leads - single address relationship
                            $query->with([
                                'address' => function ($addressQuery) {
                                $addressQuery->with(['country', 'city'])
                                    ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                            }
                            ]);
                        }
                    },
                    'template',
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }
                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($proposalQuery);
        $proposal = $query->firstOrFail();


        return view($this->getViewFilePath('view'), [
            'title' => 'View Proposal',
            'proposal' => $proposal,
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],

        ]);
    }
    public function print($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMProposals::query()
                ->with([
                    'typable' => function ($query) {
                        if (request('typable_type') === CRMClients::class) {
                            // For Clients - many-to-many relationship
                            $query->with([
                                'addresses' => function ($addressQuery) {
                                $addressQuery->select(
                                    'addresses.id',
                                    'addresses.street_address',
                                    'addresses.postal_code',
                                    'addresses.city_id',
                                    'addresses.country_id'
                                )
                                    ->withPivot('type');
                            }
                            ]);
                        } elseif (request('typable_type') === CRMLeads::class) {
                            // For Leads - single address relationship
                            $query->with([
                                'address' => function ($addressQuery) {
                                $addressQuery->with(['country', 'city'])
                                    ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                            }
                            ]);
                        }
                    },
                    'template',
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }
                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($proposalQuery);
        $proposal = $query->firstOrFail();


        return view($this->getViewFilePath('print'), [
            'title' => 'Print Proposal',
            'proposal' => $proposal,
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],

        ]);
    }



}

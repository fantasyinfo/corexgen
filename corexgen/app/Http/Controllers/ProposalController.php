<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProposalEditRequest;
use App\Http\Requests\ProposalRequest;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMTemplates;
use App\Services\ProductServicesService;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\StatusStatsFilter;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ProposalRepository;
use App\Services\ProposalService;
use Illuminate\Support\Str;

/**
 * proposalController handles CRUD operations for Proposals
 * 
 * This controller manages proposal-related functionality including:
 * - Listing proposal with server-side DataTables
 * - Creating new proposal
 * - Editing existing proposal
 * - Exporting proposal to CSV
 * - Importing proposal from CSV
 * - Changing proposal here status removed,
 *  - New VErsion Check
 */

class ProposalController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use MediaTrait;
    use IsSMTPValid;
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
    protected $productServicesService;

    public function __construct(
        ProposalRepository $proposalRepository,
        ProposalService $proposalService,
        ProductServicesService $productServicesService


    ) {
        $this->proposalRepository = $proposalRepository;
        $this->proposalService = $proposalService;
        $this->productServicesService = $productServicesService;

    }

    /**
     * Display list of proposal with filtering and DataTables support
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
        $headerStatus = $this->getHeaderStatus(\App\Models\CRM\CRMProposals::class, PermissionsHelper::$plansPermissionsKeys['PROPOSALS']);

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
            'headerStatus' => $headerStatus,
            'clients' => $clients,
            'leads' => $leads,

        ]);
    }



    /**
     * get header status
     */
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
     * Storing the data of proposal into db
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


            // redirect back to refrer ...
            // Handle redirect to referrer
            $redirect = $this->_redirectBackToRefrer($request->validated());
            if ($redirect) {
                return $redirect;
            }

            return redirect()->route($this->tenantRoute . 'proposals.index')
                ->with('success', 'Proposals created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Proposals: ' . $e->getMessage());
        }
    }


    /**
     * redirect back to view if its coming from refrer
     */

    private function _redirectBackToRefrer(array $data)
    {
        if (isset($data['_ref_type'], $data['_ref_id'], $data['_ref_refrer'])) {
            return redirect()->route(
                getPanelRoutes($data['_ref_refrer']),
                ['id' => $data['_ref_id']]
            )->with('success', $data['_ref_type'] . " Proposals created successfully.");
        }

        // Return null if no redirection logic matches
        return null;
    }


    /**
     * Return the view for creating new proposal 
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
        $lastId = CRMProposals::where('company_id', Auth::user()->company_id)->latest('id')->value('id');
        $incrementedId = $lastId ? (int) filter_var($lastId, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        $defaultId = str_pad($incrementedId, 4, '0', STR_PAD_LEFT);


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Proposal',
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'lastId' => old('_id', $defaultId),
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()
        ]);
    }

    /**
     * showing the edit proposal view
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
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()

        ]);
    }


    /**
     * Method update 
     * for updating the proposal
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
     * Deleting the proposal
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the proposal

            $user = $this->applyTenantFilter(CRMProposals::find($id));
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
     * Method changeStatus (change proposal status)
     *
     * @param $id $id [explicite id of proposal]
     * @param $status $status [explicite status to change]
     *
     */
    public function changeStatusAction($id, $action)
    {
        try {
            $proposal = $this->applyTenantFilter(CRMProposals::find($id));

            if ($action === 'SENT') {
                return $this->sendProposal($id);
            }

            // Handle other status changes
            $proposal->update(['status' => $action]);
            return redirect()->back()->with('success', 'Proposal status updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }




    /**
     * Bulk Delete the proposal
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the proposal

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {



                    // Then delete the proposal
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




    /**
     * View proposal
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
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

    /**
     * View as client proposal
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function viewOpen($id)
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
                ->where('uuid', '=', $id)
        );



        $query = $this->applyTenantFilter($proposalQuery);
        $proposal = $query->firstOrFail();


        return view($this->getViewFilePath('viewOpen'), [
            'title' => 'View Proposal',
            'proposal' => $proposal,
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],

        ]);
    }

    /**
     * print proposal
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
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

    /**
     * send  proposal
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function sendProposal($id)
    {
        $fromAPI = false;
        if (isset($_SERVER['QUERY_STRING']) && Str::contains($_SERVER['QUERY_STRING'], 'api=true')) {
            $fromAPI = true;
        }

        try {
            $proposal = $this->applyTenantFilter(
                CRMProposals::query()
                    ->with([
                        'typable',
                        'template',
                        'company.addresses' => function ($query) {
                            $query->with(['country', 'city'])
                                ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                        }
                    ])
            )->findOrFail($id);

            // Get recipient email based on typable type
            $toEmail = $proposal->typable_type === CRMLeads::class
                ? $proposal->typable->email
                : $proposal->typable->primary_email;

            if (empty($toEmail)) {
                $errorResponse = ['error' => 'No email found for this client/lead'];
                return $fromAPI
                    ? response()->json($errorResponse)
                    : redirect()->back()->with('error', $errorResponse['error']);
            }

            // Send proposal email
            $this->proposalService->sendProposalOnEmail($proposal, $this->getViewFilePath('print'));

            // Update proposal status
            // if($proposal->status != 'ACCEPTED'){

            //     $proposal->update(['status' => 'SENT']);
            // }

            $successResponse = ['success' => 'Proposal has been sent in the background job and will be delivered soon.'];
            return $fromAPI
                ? response()->json($successResponse)
                : redirect()->back()->with('success', $successResponse['success']);
        } catch (\Exception $e) {
            $errorMessage = 'Something went wrong: ' . $e->getMessage();
            return $fromAPI
                ? response()->json(['error' => $errorMessage])
                : redirect()->back()->with('error', $errorMessage);
        }
    }


    /**
        * accept proposal

        */
    public function accept(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'id' => 'required|exists:proposals,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|max:100',
                'signature' => 'required|string',
            ]);

            // Find the proposal using the tenant filter
            $proposal = CRMProposals::find($validatedData['id']);

            if (!$proposal) {
                throw new \Exception('Proposal not found.');
            }

            // Update the proposal status and acceptance details
            $proposal->update([
                'status' => 'ACCEPTED',
                'accepted_details' => [
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'signature' => $validatedData['signature'],
                    'accepted_at' => now()
                ],
            ]);

            return redirect()->back()->with('success', 'Proposal status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }


}

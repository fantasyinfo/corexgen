<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Http\Requests\ProposalEditRequest;
use App\Http\Requests\ProposalRequest;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMEstimate;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMTemplates;
use App\Services\ProductServicesService;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\EstimateService;
use Illuminate\Support\Str;

/**
 * EstimatesController handles CRUD operations for Estimates
 * 
 * This controller manages Estimates-related functionality including:
 * - Listing Estimates with server-side DataTables
 * - Creating new Estimates
 * - Editing existing Estimates
 * - Exporting Estimates to CSV
 * - Importing Estimates from CSV
 * - Changing Estimates here status removed,
 *  - New VErsion Check
 */

class EstimatesController extends Controller
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
    private $viewDir = 'dashboard.crm.estimates.';

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


  
    protected $estimateService;

    protected $customFieldService;
    protected $productServicesService;

    public function __construct(

        EstimateService $estimateService,
        ProductServicesService $productServicesService


    ) {

        $this->estimateService = $estimateService;
        $this->productServicesService = $productServicesService;

    }

    /**
     * Display list of Estimates with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->estimateService->getDatatablesResponse($request);
        }


        // Fetch the totals in a single query

        // Build base query for Estimates totals
        $user = Auth::user();
        $proposalQuery = CRMEstimate::query();

        $proposalQuery = $this->applyTenantFilter($proposalQuery);

        // Get all totals in a single query
        $usersTotals = $proposalQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
                CRM_STATUS_TYPES['ESTIMATES']['STATUS']['OPEN']
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
                CRM_STATUS_TYPES['ESTIMATES']['STATUS']['DECLINED']
            ))
        ])->first();



        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['ESTIMATES']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }

        $templates = $this->applyTenantFilter(CRMTemplates::query()->where('type', 'Estimates'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Estimates Management',
            'templates' => $templates,
            'permissions' => PermissionsHelper::getPermissionsArray('ESTIMATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['estimates'],
            'type' => 'Estimates',
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
     * Storing the data of Estimates into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ProposalRequest $request)
    {



        $this->tenantRoute = $this->getTenantRoute();

        try {


            $estimate = $this->estimateService->createEstimate($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['ESTIMATES']]), '+', '1');



            return redirect()->route($this->tenantRoute . 'estimates.index')
                ->with('success', 'Estimates created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Estimates: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new Estimates 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['ESTIMATES']));
        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Estimates'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


        // Fetch the last `_id` from the database
        $lastId = CRMEstimate::where('company_id', Auth::user()->company_id)->latest('id')->value('id');
        $incrementedId = $lastId ? (int) filter_var($lastId, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        $defaultId = str_pad($incrementedId, 4, '0', STR_PAD_LEFT);


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Estimate',
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'lastId' => old('_id', $defaultId),
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()
        ]);
    }

    /**
     * showing the edit Estimates view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        $query = CRMEstimate::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $estimate = $query->firstOrFail();


        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Estimates'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Estimate',
            'estimate' => $estimate,
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()

        ]);
    }


    /**
     * Method update 
     * for updating the Estimates
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ProposalEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {


            $estimate = $this->estimateService->updateEstimate($request->validated());

            return redirect()->route($this->tenantRoute . 'estimates.index')
                ->with('success', 'estimate updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the estimate: ' . $e->getMessage());
        }
    }





    /**
     * Deleting the Estimates
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the estimate

            $estimate = $this->applyTenantFilter(CRMEstimate::find($id));
            if ($estimate) {
                // delete  now
                $estimate->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['ESTIMATES']]), '-', '1');

                return redirect()->back()->with('success', 'Estimate deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the Estimate: Estimate not found with this id.');
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the estimate: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change Estimates status)
     *
     * @param $id $id [explicite id of Estimates]
     * @param $status $status [explicite status to change]
     *
     */
    public function changeStatusAction($id, $action)
    {
        try {
            $estimate = $this->applyTenantFilter(CRMEstimate::find($id));

            if ($action === 'SENT') {
                return $this->sendProposal($id);
            }

            // Handle other status changes
            $estimate->update(['status' => $action]);
            return redirect()->back()->with('success', 'Estimate status updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }




    /**
     * Bulk Delete the Estimates
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the Estimates

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {



                    // Then delete the Estimates
                    CRMEstimate::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['ESTIMATES']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected estimates deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No estimates selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }




    /**
     * View Estimates
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMEstimate::query()
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
        $estimate = $query->firstOrFail();


        return view($this->getViewFilePath('view'), [
            'title' => 'View Estimate',
            'estimate' => $estimate,
            'module' => PANEL_MODULES[$this->getPanelModule()]['estimates'],

        ]);
    }

    /**
     * View as client Estimates
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function viewOpen($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMEstimate::query()
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
        $estimate = $query->firstOrFail();


        return view($this->getViewFilePath('viewOpen'), [
            'title' => 'View Estimate',
            'estimate' => $estimate,
            'module' => PANEL_MODULES[$this->getPanelModule()]['estimates'],

        ]);
    }

    /**
     * print Estimates
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function print($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMEstimate::query()
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
        $estimate = $query->firstOrFail();


        return view($this->getViewFilePath('print'), [
            'title' => 'Print Estimate',
            'estimate' => $estimate,
            'module' => PANEL_MODULES[$this->getPanelModule()]['estimates'],

        ]);
    }

    /**
     * send  Estimates
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
            $estimate = $this->applyTenantFilter(
                CRMEstimate::query()
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
            $toEmail = $estimate->typable_type === CRMLeads::class
                ? $estimate->typable->email
                : $estimate->typable->primary_email;

            if (empty($toEmail)) {
                $errorResponse = ['error' => 'No email found for this client/lead'];
                return $fromAPI
                    ? response()->json($errorResponse)
                    : redirect()->back()->with('error', $errorResponse['error']);
            }

            // Send estimate email
            $this->estimateService->sendProposalOnEmail($estimate, $this->getViewFilePath('print'));

            // Update estimate status
            // if($estimate->status != 'ACCEPTED'){

            //     $estimate->update(['status' => 'SENT']);
            // }

            $successResponse = ['success' => 'Estimate has been sent in the background job and will be delivered soon.'];
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
        * accept Estimates

        */
    public function accept(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'id' => 'required|exists:estimates,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|max:100',
                'signature' => 'required|string',
            ]);

            // Find the estimate using the tenant filter
            $estimate = $this->applyTenantFilter(CRMEstimate::find($validatedData['id']));

            if (!$estimate) {
                throw new \Exception('Estimate not found.');
            }

            // Update the estimate status and acceptance details
            $estimate->update([
                'status' => 'ACCEPTED',
                'accepted_details' => [
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'signature' => $validatedData['signature'],
                    'accepted_at' => now()
                ],
            ]);

            return redirect()->back()->with('success', 'Estimate status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }


}

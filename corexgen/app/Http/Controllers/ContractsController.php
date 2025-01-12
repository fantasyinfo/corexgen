<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Http\Requests\ContractEditRequest;
use App\Http\Requests\ContractRequest;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMContract;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMTemplates;
use App\Services\ContractService;
use App\Services\ProductServicesService;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\StatusStatsFilter;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * ContractsController handles CRUD operations for Contracts
 * 
 * This controller manages Contracts-related functionality including:
 * - Listing Contracts with server-side DataTables
 * - Creating new Contracts
 * - Editing existing Contracts
 * - Exporting Contracts to CSV
 * - Importing Contracts from CSV
 * - Changing Contracts here status removed,
 *  - New VErsion Check
 */

class ContractsController extends Controller
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
    private $viewDir = 'dashboard.crm.contracts.';

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



    protected $contractService;

    protected $customFieldService;
    protected $productServicesService;

    public function __construct(

        ContractService $contractService,
        ProductServicesService $productServicesService


    ) {

        $this->contractService = $contractService;
        $this->productServicesService = $productServicesService;

    }

    /**
     * Display list of Contracts with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->contractService->getDatatablesResponse($request);
        }


        // Fetch the totals in a single query
        $headerStatus = $this->getHeaderStatus(\App\Models\CRM\CRMContract::class, PermissionsHelper::$plansPermissionsKeys['CONTRACTS']);

        $templates = $this->applyTenantFilter(CRMTemplates::query()->where('type', 'Contracts'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Contracts Management',
            'templates' => $templates,
            'permissions' => PermissionsHelper::getPermissionsArray('CONTRACTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['contracts'],
            'type' => 'Contracts',
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
     * Storing the data of Contracts into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ContractRequest $request)
    {



        $this->tenantRoute = $this->getTenantRoute();

        try {


            $contract = $this->contractService->createContract($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CONTRACTS']]), '+', '1');



            return redirect()->route($this->tenantRoute . 'contracts.index')
                ->with('success', 'Contracts created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Contracts: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new Contracts 
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['CONTRACTS']));
        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Contracts'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();


        // Fetch the last `_id` from the database
        $lastId = CRMContract::where('company_id', Auth::user()->company_id)->latest('id')->value('id');
        $incrementedId = $lastId ? (int) filter_var($lastId, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        $defaultId = str_pad($incrementedId, 4, '0', STR_PAD_LEFT);


        return view($this->getViewFilePath('create'), [
            'title' => 'Create Contract',
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'lastId' => old('_id', $defaultId),
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()
        ]);
    }

    /**
     * showing the edit Contracts view
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit($id)
    {

        $query = CRMContract::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $contract = $query->firstOrFail();


        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Contracts'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Contract',
            'contract' => $contract,
            'templates' => $templates,
            'clients' => $clients,
            'leads' => $leads,
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes()

        ]);
    }


    /**
     * Method update 
     * for updating the Contracts
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ContractEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {
            $contract = $this->contractService->updateContract($request->validated());

            return redirect()->route($this->tenantRoute . 'contracts.index')
                ->with('success', 'contract updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the contract: ' . $e->getMessage());
        }
    }





    /**
     * Deleting the Contracts
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the Contracts

            $user = $this->applyTenantFilter(CRMContract::find($id));
            if ($user) {
                // delete  now
                $user->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CONTRACTS']]), '-', '1');

                return redirect()->back()->with('success', 'Contract deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the Contract: Contract not found with this id.');
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the contract: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change Contracts status)
     *
     * @param $id $id [explicite id of Contracts]
     * @param $status $status [explicite status to change]
     *
     */
    public function changeStatusAction($id, $action)
    {
        try {
            $contract = $this->applyTenantFilter(CRMContract::find($id));

            if ($action === 'SENT') {
                return $this->sendContract($id);
            }

            // Handle other status changes
            $contract->update(['status' => $action]);
            return redirect()->back()->with('success', 'Contract status updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }




    /**
     * Bulk Delete the Contracts
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the Contracts

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {



                    // Then delete the Contracts
                    CRMContract::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CONTRACTS']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected contracts deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No contracts selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }




    /**
     * View Contracts
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function view($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMContract::query()
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
        $contract = $query->firstOrFail();


        return view($this->getViewFilePath('view'), [
            'title' => 'View Contract',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['contracts'],

        ]);
    }

    /**
     * View as client Contracts
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function viewOpen($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMContract::query()
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
        $contract = $query->firstOrFail();


        return view($this->getViewFilePath('viewOpen'), [
            'title' => 'View Contract',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['contracts'],

        ]);
    }

    /**
     * print Contracts
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function print($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            CRMContract::query()
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
        $contract = $query->firstOrFail();


        return view($this->getViewFilePath('print'), [
            'title' => 'Print Contract',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['contracts'],

        ]);
    }

    /**
     * send  Contracts
     * @param mixed $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function sendContract($id)
    {
        $fromAPI = false;
        if (isset($_SERVER['QUERY_STRING']) && Str::contains($_SERVER['QUERY_STRING'], 'api=true')) {
            $fromAPI = true;
        }

        try {
            $contract = $this->applyTenantFilter(
                CRMContract::query()
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
            $toEmail = $contract->typable_type === CRMLeads::class
                ? $contract->typable->email
                : $contract->typable->primary_email;

            if (empty($toEmail)) {
                $errorResponse = ['error' => 'No email found for this client/lead'];
                return $fromAPI
                    ? response()->json($errorResponse)
                    : redirect()->back()->with('error', $errorResponse['error']);
            }

            // Send contract email
            $this->contractService->sendContractOnEmail($contract, $this->getViewFilePath('print'));

            // Update contract status
            // if($contract->status != 'ACCEPTED'){

            //     $contract->update(['status' => 'SENT']);
            // }

            $successResponse = ['success' => 'Contract has been sent in the background job and will be delivered soon.'];
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
        * accept Contracts

        */
    public function accept(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'id' => 'required|exists:contract,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|max:100',
                'signature' => 'required|string',
            ]);

            // Find the contract using the tenant filter
            $contract = $this->applyTenantFilter(CRMContract::find($validatedData['id']));

            if (!$contract) {
                throw new \Exception('Contract not found.');
            }

            // Update the contract status and acceptance details
            $contract->update([
                'status' => 'ACCEPTED',
                'accepted_details' => [
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'signature' => $validatedData['signature'],
                    'accepted_at' => now()
                ],
            ]);

            return redirect()->back()->with('success', 'Contract status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }

         /**
     * accept the company
     */
    public function acceptCompany(Request $request)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'id' => 'required|exists:contract,id',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'email' => 'required|email|max:100',
                'signature' => 'required|string',
            ]);

            // Find the contract using the tenant filter
            $contract = $this->applyTenantFilter(CRMContract::find($validatedData['id']));

            if (!$contract) {
                throw new \Exception('Contract not found.');
            }

            // Update the contract status and acceptance details
            $contract->update([
                'company_accepted_details' => [
                    'first_name' => $validatedData['first_name'],
                    'last_name' => $validatedData['last_name'],
                    'email' => $validatedData['email'],
                    'signature' => $validatedData['signature'],
                    'accepted_at' => now()
                ],
                'statusCompany' => 1
            ]);

            return redirect()->back()->with('success', 'Contract signed status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }

}

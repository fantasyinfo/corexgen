<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Http\Requests\ContractEditRequest;
use App\Http\Requests\ContractRequest;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMTemplates;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\ProductServicesService;
use App\Services\TasksService;
use App\Traits\IsSMTPValid;
use App\Traits\MediaTrait;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * InvoiceController handles CRUD operations for Invoices
 * 
 * This controller manages Invoices-related functionality including:
 * - Listing Invoices with server-side DataTables
 * - Creating new Invoices
 * - Editing existing Invoices
 * - Exporting Invoices to CSV
 * - Importing Invoices from CSV
 * - Changing Invoices here status removed,
 *  - New VErsion Check
 */

class InvoiceController extends Controller
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
    private $viewDir = 'dashboard.crm.invoices.';

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



    protected $invoiceService;

    protected $customFieldService;
    protected $productServicesService;
    protected $taskService;

    public function __construct(

        InvoiceService $invoiceService,
        ProductServicesService $productServicesService,
        TasksService $taskService



    ) {

        $this->invoiceService = $invoiceService;
        $this->productServicesService = $productServicesService;
        $this->taskService = $taskService;

    }

    /**
     * Display list of Invoices with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     */
    public function index(Request $request)
    {
        // Ajax DataTables request
        if ($request->ajax()) {
            return $this->invoiceService->getDatatablesResponse($request);
        }


        // Fetch the totals in a single query

        // Build base query for Invoices totals
        $user = Auth::user();
        $proposalQuery = Invoice::query();

        $proposalQuery = $this->applyTenantFilter($proposalQuery);

        // Get all totals in a single query
        // $usersTotals = $proposalQuery->select([
        //     DB::raw('COUNT(*) as totalUsers'),
        //     DB::raw(sprintf(
        //         'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalActive',
        //         CRM_STATUS_TYPES['INVOICES']['STATUS']['OPEN']
        //     )),
        //     DB::raw(sprintf(
        //         'SUM(CASE WHEN status = "%s" THEN 1 ELSE 0 END) as totalInactive',
        //         CRM_STATUS_TYPES['INVOICES']['STATUS']['DECLINED']
        //     ))
        // ])->first();



        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['INVOICES']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => 0,
            ];
        }

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Invoices Management',
            'permissions' => PermissionsHelper::getPermissionsArray('INVOICES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],
            'type' => 'Invoices',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => 0,
            'total_inactive' => 0,
            'total_ussers' => 0,
            'clients' => $clients,

        ]);
    }



    /**
     * Storing the data of Invoices into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(ContractRequest $request)
    {



        $this->tenantRoute = $this->getTenantRoute();

        try {


            $contract = $this->invoiceService->createContract($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['INVOICES']]), '+', '1');



            return redirect()->route($this->tenantRoute . 'invoices.index')
                ->with('success', 'Invoices created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->with('error', 'An error occurred while creating the Invoices: ' . $e->getMessage());
        }
    }




    /**
     * Return the view for creating new Invoices 

     */
    public function create()
    {

        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['INVOICES']));
       

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        // Fetch the last `_id` from the database
        $lastId = Invoice::where('company_id', Auth::user()->company_id)->latest('id')->value('id');
        $incrementedId = $lastId ? (int) filter_var($lastId, FILTER_SANITIZE_NUMBER_INT) + 1 : 1;
        $defaultId = str_pad($incrementedId, 4, '0', STR_PAD_LEFT);

        $tasks = collect();
        $tasks = $this->taskService->getAllTasks();

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Invoice',
            'lastId' => old('_id', $defaultId),
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes(),
            'tasks' => $tasks,
            'clients' => $clients,
        ]);
    }

    /**
     * showing the edit Invoices view
     * @param mixed $id
 
     */
    public function edit($id)
    {

        $query = Invoice::query()
            ->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $contract = $query->firstOrFail();


        // Regular view rendering
        $templates = $this->applyTenantFilter(CRMTemplates::query()->select('id', 'title')->where('type', 'Invoices'))->get();

        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();

        $leads = $this->applyTenantFilter(CRMLeads::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'email')->get();

        return view($this->getViewFilePath('edit'), [

            'title' => 'Edit Invoice',
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
     * for updating the Invoices
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(ContractEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {
            $contract = $this->invoiceService->updateContract($request->validated());

            return redirect()->route($this->tenantRoute . 'invoices.index')
                ->with('success', 'contract updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the contract: ' . $e->getMessage());
        }
    }





    /**
     * Deleting the Invoices
     * @param mixed $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {

        try {
            // Delete the Invoices

            $user = $this->applyTenantFilter(Invoice::find($id));
            if ($user) {
                // delete  now
                $user->delete();

                // update the subscription usage
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['INVOICES']]), '-', '1');

                return redirect()->back()->with('success', 'Invoice deleted successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to delete the Invoice: Invoice not found with this id.');
            }

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the contract: ' . $e->getMessage());
        }
    }


    /**
     * Method changeStatus (change Invoices status)
     *
     * @param $id $id [explicite id of Invoices]
     * @param $status $status [explicite status to change]
     *
     */
    public function changeStatusAction($id, $action)
    {
        try {
            $contract = $this->applyTenantFilter(Invoice::find($id));

            if ($action === 'SENT') {
                return $this->sendContract($id);
            }

            // Handle other status changes
            $contract->update(['status' => $action]);
            return redirect()->back()->with('success', 'Invoice status updated successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }




    /**
     * Bulk Delete the Invoices
     * Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');


        try {
            // Delete the Invoices

            if (is_array($ids) && count($ids) > 0) {
                DB::transaction(function () use ($ids) {



                    // Then delete the Invoices
                    Invoice::whereIn('id', $ids)->delete();

                    $this->updateUsage(
                        strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['INVOICES']]),
                        '-',
                        count($ids)
                    );
                });

                return response()->json(['message' => 'Selected invoices deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No invoices selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the user: ' . $e->getMessage());
        }
    }




    /**
     * View Invoices
     * @param mixed $id
     * @return \Illuminate\Invoices\View\Factory|\Illuminate\Invoices\View\View
     */
    public function view($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            Invoice::query()
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
            'title' => 'View Invoice',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * View as client Invoices
     * @param mixed $id
     * @return \Illuminate\Invoices\View\Factory|\Illuminate\Invoices\View\View
     */
    public function viewOpen($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            Invoice::query()
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
            'title' => 'View Invoice',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * print Invoices
     * @param mixed $id
     * @return \Illuminate\Invoices\View\Factory|\Illuminate\Invoices\View\View
     */
    public function print($id)
    {
        $proposalQuery = $this->applyTenantFilter(
            Invoice::query()
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
            'title' => 'Print Invoice',
            'contract' => $contract,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * send  Invoices
     * @param mixed $id
     * @return \Illuminate\Invoices\View\Factory|\Illuminate\Invoices\View\View
     */
    public function sendContract($id)
    {
        $fromAPI = false;
        if (isset($_SERVER['QUERY_STRING']) && Str::contains($_SERVER['QUERY_STRING'], 'api=true')) {
            $fromAPI = true;
        }

        try {
            $contract = $this->applyTenantFilter(
                Invoice::query()
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
            $this->invoiceService->sendContractOnEmail($contract, $this->getViewFilePath('print'));

            // Update contract status
            // if($contract->status != 'ACCEPTED'){

            //     $contract->update(['status' => 'SENT']);
            // }

            $successResponse = ['success' => 'Invoice has been sent in the background job and will be delivered soon.'];
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
        * accept Invoices

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
            $contract = $this->applyTenantFilter(Invoice::find($validatedData['id']));

            if (!$contract) {
                throw new \Exception('Invoice not found.');
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

            return redirect()->back()->with('success', 'Invoice status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }

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
            $contract = $this->applyTenantFilter(Invoice::find($validatedData['id']));

            if (!$contract) {
                throw new \Exception('Invoice not found.');
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

            return redirect()->back()->with('success', 'Invoice signed status updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to process the request: ' . $e->getMessage());
        }
    }

}

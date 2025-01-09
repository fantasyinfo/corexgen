<?php

namespace App\Http\Controllers;


use App\Helpers\PermissionsHelper;
use App\Http\Requests\InvoiceEditRequest;
use App\Http\Requests\InvoiceRequest;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\Invoice;
use App\Models\Timesheet;
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

        $tasks = collect();
        $tasks = $this->taskService->getAllTasks();

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
            'tasks' => $tasks

        ]);
    }



    /**
     * Storing the data of Invoices into db
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(InvoiceRequest $request)
    {


        $this->tenantRoute = $this->getTenantRoute();


        try {


            $invoice = $this->invoiceService->createInvoice($request->validated());


            // update current usage
            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['INVOICES']]), '+', '1');


            if ($request->validated()['timesheet_id'] && $request->validated()['timesheet_id'] > 0) {
                Timesheet::find($request->validated()['timesheet_id'])->update(['invoice_generated' => 1]);
                return response()->json($invoice);
            }

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
        $invoice = $query->firstOrFail();


        $clients = $this->applyTenantFilter(CRMClients::query())->select('id', 'first_name', 'last_name', 'type', 'company_name', 'primary_email')->get();


        $tasks = collect();
        $tasks = $this->taskService->getAllTasks();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Invoice',
            'invoice' => $invoice,
            'clients' => $clients,
            'products' => $this->productServicesService->getAllProducts(),
            'tax' => $this->productServicesService->getProductTaxes(),
            'tasks' => $tasks,

        ]);
    }


    /**
     * Method update 
     * for updating the Invoices
     *
     * @param Request $request [explicite description]
     *
     */
    public function update(InvoiceEditRequest $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        try {
            $invoice = $this->invoiceService->updateInvoice($request->validated());

            return redirect()->route($this->tenantRoute . 'invoices.index')
                ->with('success', 'invoice updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An error occurred while updating the invoice: ' . $e->getMessage());
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
            return redirect()->back()->with('error', 'Failed to delete the invoice: ' . $e->getMessage());
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
            $invoice = $this->applyTenantFilter(Invoice::find($id));

            if ($action === 'SENT') {
                return $this->sendInvoice($id);
            }

            // Handle other status changes
            $invoice->update(['status' => $action]);
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
     */
    public function view($id)
    {
        $invoiceQuery = $this->applyTenantFilter(
            Invoice::query()
                ->with([
                    'client',
                    'task',
                    'project',
     
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }

                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($invoiceQuery);
        $invoice = $query->firstOrFail();


        return view($this->getViewFilePath('view'), [
            'title' => 'View Invoice',
            'invoice' => $invoice,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * View as client Invoices
     * @param mixed $id
     */
    public function viewOpen($id)
    {
        $invoiceQuery = $this->applyTenantFilter(
            Invoice::query()
                ->with([
                    'client',
                    'task',
                    'project',
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }

                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($invoiceQuery);
        $invoice = $query->firstOrFail();


        return view($this->getViewFilePath('viewOpen'), [
            'title' => 'View Invoice',
            'invoice' => $invoice,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * print Invoices
     * @param mixed $id

     */
    public function print($id)
    {
        $invoiceQuery = $this->applyTenantFilter(
            Invoice::query()
                ->with([
                    'client',
                    'task',
                    'project',
                    'company.addresses' => function ($query) {
                        $query->with(['country', 'city'])
                            ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                    }

                ])
                ->where('id', '=', $id)
        );



        $query = $this->applyTenantFilter($invoiceQuery);
        $invoice = $query->firstOrFail();


        return view($this->getViewFilePath('print'), [
            'title' => 'Print Invoice',
            'invoice' => $invoice,
            'module' => PANEL_MODULES[$this->getPanelModule()]['invoices'],

        ]);
    }

    /**
     * send  Invoices
     * @param mixed $id

     */
    public function sendInvoice($id)
    {
        $fromAPI = false;
        if (isset($_SERVER['QUERY_STRING']) && Str::contains($_SERVER['QUERY_STRING'], 'api=true')) {
            $fromAPI = true;
        }

        try {
            $invoiceQuery = $this->applyTenantFilter(
                Invoice::query()
                    ->with([
                        'client',
                        'task',
                        'project',
                        'company.addresses' => function ($query) {
                            $query->with(['country', 'city'])
                                ->select('id', 'country_id', 'city_id', 'street_address', 'postal_code');
                        }

                    ])
                    ->where('id', '=', $id)
            );

            $invoice = $invoiceQuery->firstOrFail();

            // Get recipient email based on typable type
            $toEmail = $invoice->client->primary_email;

            if (empty($toEmail)) {
                $errorResponse = ['error' => 'No email found for this client'];
                return $fromAPI
                    ? response()->json($errorResponse)
                    : redirect()->back()->with('error', $errorResponse['error']);
            }

            // Send invoice email
            $this->invoiceService->sendInvoiceOnEmail($invoice, $this->getViewFilePath('print'));

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


}

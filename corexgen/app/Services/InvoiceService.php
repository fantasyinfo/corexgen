<?php

namespace App\Services;


use App\Jobs\SendInvoice;
use App\Traits\IsSMTPValid;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PermissionsHelper;
use App\Models\Invoice;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\DB;

class InvoiceService
{


    use TenantFilter;
    use IsSMTPValid;

    protected $invoiceRepository;

    private $tenantRoute;
    private $productServicesService;
    public function __construct(InvoiceRepository $invoiceRepository, ProductServicesService $productServicesService)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->productServicesService = $productServicesService;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     *create invoice
     */
    public function createInvoice($data)
    {
        // First, let's log or dd the incoming data
        // dd($data);  // Uncomment to check input

        DB::beginTransaction();
        try {
            // Create invoice
            $invoice = Invoice::create($data);

            // Store the ID in case we need it
            $invoiceId = $invoice->id;

            // Add your products
            $this->checkIFProductAddedThenAdd($data, $invoice);

            DB::commit();

            // Return the fresh instance
            return Invoice::find($invoiceId);

        } catch (\Exception $e) {
            DB::rollback();
            // Log the error or handle it appropriately
            throw $e;
        }
    }

    /**
     *check if product added then add them into the invoice
     */
    public function checkIfProductAddedThenAdd($data, $invoice)
    {
        $product_details = [];
        $json_data = ['products' => []];

        if (
            !empty($data['product_title']) &&
            is_array($data['product_title']) &&
            array_filter($data['product_title'], 'trim')
        ) {
            foreach ($data['product_title'] as $k => $title) {
                $trimmedTitle = trim($title);
                if ($trimmedTitle !== '') {
                    $taxData = $this->productServicesService->getProductTaxes('name', $data['product_tax'][$k]);
                    info('product id', [$data['product_id'][$k]]);
                    $product_details[] = [
                        'title' => $trimmedTitle,
                        'description' => trim($data['product_description'][$k] ?? ''),
                        'qty' => (float) ($data['product_qty'][$k] ?? 0),
                        'rate' => (float) ($data['product_rate'][$k] ?? 0.00),
                        'tax' => @$taxData[0]?->name ?? null,
                        'tax_id' => @$taxData[0]?->id ?? null,
                        'product_id' => (int) ($data['product_id'][$k] ?? 0)
                    ];
                }
            }

            // Only add additional fields if there are valid products
            if (!empty($product_details)) {
                $json_data = [
                    'products' => $product_details,
                    'additional_fields' => [
                        'discount' => (float) ($data['discount'] ?? 0),
                        'adjustment' => (float) ($data['adjustment'] ?? 0),
                        'currency_code' => getSettingValue('Currency Code'),
                        'currency_symbol' => getSettingValue('Currency Symbol')
                    ],
                ];
            }
        }

        $invoice->update([
            'product_details' => json_encode($json_data)
        ]);
    }

    /**
     *update invoice
     */
    public function updateInvoice($data)
    {

        $query = $this->applyTenantFilter(Invoice::where('id', $data['id']));
        $invoice = $query->first();

        $this->checkIFProductAddedThenAdd($data, $invoice);
        // Create the invoice with all required fields
        $invoice->update($data);

        return $invoice;
    }

    /**
     *get invoices
     */

    public function getInvoices($project_id = null)
    {
        if ($project_id == null) {
            return $this->applyTenantFilter(Invoice::query()->with(['task', 'timesheet', 'client', 'company'])->latest()->get());
        }
        return $this->applyTenantFilter(Invoice::query()->with(['task', 'timesheet', 'client', 'company'])->where('project_id', $project_id)->latest()->get());
    }


    /**
     *send invoice on email
     */
    public function sendInvoiceOnEmail(Invoice $invoice, $view = "dashboard.crm.invoices.print"): bool
    {
        try {
            $mailSettings = $this->_getMailSettings();

            $toEmail = $invoice->client->primary_email;

            // Prepare email details
            $emailDetails = [
                'from' => $mailSettings['Mail From Address'],
                'to' => $toEmail,
                'subject' => $invoice->title,
                'details' => $invoice->details
            ];

            // Dispatch the job
            SendInvoice::dispatch(
                $mailSettings,
                $emailDetails,
                $invoice,
                $view
            );
            return true;
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            \Log::error('Failed to send invoice email', [
                'error' => $e->getMessage(),
                'invoice_id' => $invoice->id ?? null,
            ]);

            // Optionally, you can rethrow the exception if needed
            // throw $e;

            return false; // Return false or a default response to indicate failure
        }
    }



    /**
     *get dt tbl lists invoice
     */
    public function getDatatablesResponse($request)
    {
        $query = $this->invoiceRepository->getInvoiceQuery($request);

        $module = PANEL_MODULES[$this->getPanelModule()]['invoices'];
        $tmodule = PANEL_MODULES[$this->getPanelModule()]['tasks'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['clients'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($invoice) {
                return $this->renderActionsColumn($invoice);
            })
            ->editColumn('task', function ($invoice) use ($tmodule) {
                $taskId = $invoice?->task?->id;
                $taskTitle = $invoice?->task?->title;

                if ($taskId && $taskTitle) {
                    return "<a class='dt-link' href='" . route($this->tenantRoute . $tmodule . '.view', $taskId) . "' target='_blank'>$taskTitle</a>";
                }

                return null; // Handle the case where task data is missing
            })

            ->editColumn('to', function ($invoice) use ($cmodule) {
                $clientId = $invoice?->client?->id;
                $clientName = $invoice?->client?->first_name . " " . $invoice?->client?->last_name;

                if ($clientId && $clientName) {
                    return "<a class='dt-link' href='" . route($this->tenantRoute . $cmodule . '.view', $clientId) . "' target='_blank'>$clientName</a>";
                }

                return null; // Handle the case where client data is missing
            })

            ->editColumn('_id', function ($invoice) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $invoice?->id) . "' target='_blank'>$invoice->_prefix  $invoice->_id</a>";
            })
            ->editColumn('total_amount', function ($invoice) {
                return $invoice?->total_amount ? getSettingValue('Currency Symbol') . ' ' . number_format($invoice->total_amount) : "0";
            })
            ->editColumn('created_at', fn($invoice) => $invoice?->created_at ? formatDateTime($invoice->created_at) : '')
            ->editColumn('issue_date', fn($invoice) => $invoice?->issue_date ? formatDateTime($invoice->issue_date) : '')
            ->editColumn('due_date', fn($invoice) => $invoice?->due_date ? formatDateTime($invoice->due_date) : '')
            ->editColumn('status', function ($invoice) {
                return "<span class='badge bg-" . CRM_STATUS_TYPES['INVOICES']['BT_CLASSES'][$invoice->status] . "'>$invoice->status</span>";
            })
            ->rawColumns(['actions', 'client', 'value', '_id', 'to', 'task', 'status', 'name'])
            ->make(true);
    }

    /**
     *render action col for invoice
     */
    protected function renderActionsColumn($contract)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['invoices'];
        $permissions = PermissionsHelper::getPermissionsArray('INVOICES');
        $id = $contract->id;
        $tenantRoute = $this->tenantRoute;


        // ['SENT','SUCCESS', 'OVERDUE', 'PENDING']


        $action = '<div class="dropdown text-end">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </button>';

        $action .= '<ul class="dropdown-menu dropdown-menu-end">';


        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'SENT']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-paper-plane me-2"></i> Send on Email
                </a>
            </li>';



        if ($contract->status !== 'SUCCESS') {
            $action .= '<li class="m-1 p-1">
                            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'SUCCESS']) . '" data-toggle="tooltip" title="Edit">
                            <i class="fas fa-check me-2"></i> Mark Paid
                            </a>
                        </li>';

        }



        // Check if the user has permission to update
        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']) && $contract->status !== 'ACCEPTED') {
            $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.edit', $id) . '" data-toggle="tooltip" title="Edit">
                 <i class="fas fa-pencil-alt me-2"></i> Edit
                </a>
            </li>';
        }

        // Check if the user has permission to delete
        if (hasPermission(strtoupper($module) . '.' . $permissions['DELETE']['KEY'])) {
            $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal" 
                    data-id="' . $id . '" data-route="' . route($tenantRoute . $module . '.destroy', $id) . '" data-toggle="tooltip" title="Delete">
                  <i class="fas fa-trash-alt me-2"></i> Delete
                </a>
            </li>';
        }







        $action .= '</ul></div>';

        return $action;
    }


}
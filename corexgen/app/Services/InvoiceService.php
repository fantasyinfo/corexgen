<?php

namespace App\Services;


use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMContract;
use App\Models\CRM\CRMLeads;
use App\Traits\IsSMTPValid;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use App\Helpers\PermissionsHelper;
use App\Jobs\SendContract;
use App\Models\Invoice;
use App\Repositories\ContractRepository;
use App\Repositories\InvoiceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceService
{


    use TenantFilter;
    use IsSMTPValid;

    protected $invoiceRepository;

    private $tenantRoute;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


    public function createInvoice($data)
    {

        // Create the proposal with all required fields
        $invoice = Invoice::create($data);
        $this->checkIFProductAddedThenAdd($data, $invoice);
        return $invoice;
    }

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
                    $product_details[] = [
                        'title' => $trimmedTitle,
                        'description' => trim($data['product_description'][$k] ?? ''),
                        'qty' => (float) ($data['product_qty'][$k] ?? 0),
                        'rate' => (float) ($data['product_rate'][$k] ?? 0.00),
                        'tax' => $data['product_tax'][$k] ?? null,
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
                    ],
                ];
            }
        }

        $invoice->update([
            'product_details' => json_encode($json_data)
        ]);
    }


    public function updateInvoice($data)
    {

        $query = $this->applyTenantFilter(Invoice::where('id', $data['id']));
        $invoice = $query->first();

        $this->checkIFProductAddedThenAdd($data, $invoice);
        // Create the invoice with all required fields
        $invoice->update($data);

        return $invoice;
    }



    public function getContracts($typable_type, $typable_id)
    {
        return $this->applyTenantFilter(CRMContract::query()->where('typable_type', $typable_type)->where('typable_id', $typable_id)->latest()->get());
    }


    public function sendContractOnEmail(CRMContract $contract, $view = "dashboard.crm.invoices.print"): bool
    {
        try {
            $mailSettings = $this->_getMailSettings();

            $toEmail = $contract->typable_type === CRMLeads::class
                ? $contract->typable->email
                : $contract->typable->primary_email;

            // Prepare email details
            $emailDetails = [
                'from' => $mailSettings['Mail From Address'],
                'to' => $toEmail,
                'subject' => $contract->title,
                'details' => $contract->details,
                'template' => $contract->template?->template_details
            ];

            // Dispatch the job
            SendContract::dispatch(
                $mailSettings,
                $emailDetails,
                $contract,
                $view
            );
            return true;
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            \Log::error('Failed to send contract email', [
                'error' => $e->getMessage(),
                'contract_id' => $contract->id ?? null,
            ]);

            // Optionally, you can rethrow the exception if needed
            // throw $e;

            return false; // Return false or a default response to indicate failure
        }
    }



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
                return $invoice?->total_amount ? number_format($invoice->total_amount) : "0";
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

    protected function renderActionsColumn($contract)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['invoices'];
        $permissions = PermissionsHelper::getPermissionsArray('INVOICES');
        $id = $contract->id;
        $tenantRoute = $this->tenantRoute;


        // ['DRAFT', 'SENT', 'OPEN', 'DECLINED', 'ACCEPTED', 'EXPIRED','REVISED']


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



        if ($contract->status !== 'ACCEPTED') {


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'ACCEPTED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-check me-2"></i> Mark Accepted
            </a>
        </li>';


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'DECLINED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-times me-2"></i> Mark Decline
            </a>
        </li>';


            $action .= '<li class="m-1 p-1">
            <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'REVISED']) . '" data-toggle="tooltip" title="Edit">
            <i class="fas fa-file-alt me-2"></i>Mark Revised
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
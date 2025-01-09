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


    public function createContract($data)
    {
        // Generate a unique URL slug
        $data['url'] = Str::slug($data['title'] . '-' . $data['_id'], '-');

        // Handle `_id` and `_prefix`
        if (isset($data['_prefix'], $data['_id']) && Str::contains($data['_id'], $data['_prefix'])) {
            $data['_id'] = Str::replace($data['_prefix'], '', $data['_id']);
        }

        // Set the typable relationship data before creation
        if ($data['type'] == 'client') {
            $data['typable_type'] = CRMClients::class;
            $data['typable_id'] = $data['client_id'];
        } elseif ($data['type'] == 'lead') {
            $data['typable_type'] = CRMLeads::class;
            $data['typable_id'] = $data['lead_id'];
        }

        $data['company_id'] = Auth::user()->company_id;
        // Create the proposal with all required fields
        $proposal = CRMContract::create($data);


        return $proposal;
    }
    public function updateContract($data)
    {

        $query = $this->applyTenantFilter(CRMContract::where('id', $data['id']));
        $proposal = $query->first();


        // Set the typable relationship data before creation
        if ($data['type'] == 'client') {
            $data['typable_type'] = CRMClients::class;
            $data['typable_id'] = $data['client_id'];
        } elseif ($data['type'] == 'lead') {
            $data['typable_type'] = CRMLeads::class;
            $data['typable_id'] = $data['lead_id'];
        }

        // Create the proposal with all required fields
        $proposal->update($data);



        return $proposal;
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
                return "<a class='dt-link' href='" . route($this->tenantRoute . $tmodule . '.view', $invoice?->task?->id) . "' target='_blank'>$invoice?->task?->title</a>";
            })
            ->editColumn('client', function ($invoice) use ($cmodule) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $cmodule . '.view', $invoice?->client?->id) . "' target='_blank'>$invoice?->client?->first_name</a>";
            })
            ->editColumn('_id', function ($invoice) use($module) {
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
            ->rawColumns(['actions','client', 'value', 'to', 'title', 'status', 'name'])
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
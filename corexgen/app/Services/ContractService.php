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
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ContractService
{


    use TenantFilter;
    use IsSMTPValid;

    protected $contractRepository;

    private $tenantRoute;

    public function __construct(ContractRepository $contractRepository)
    {
        $this->contractRepository = $contractRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }



    /**
     * create contract
     */
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
    /**
     * update contract
     */
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

    /**
     * get contract
     */

    public function getContracts($typable_type, $typable_id)
    {
        return $this->applyTenantFilter(CRMContract::query()->where('typable_type', $typable_type)->where('typable_id', $typable_id)->latest()->get());
    }

    /**
     * send contract on email
     */
    public function sendContractOnEmail(CRMContract $contract, $view = "dashboard.crm.contracts.print"): bool
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


    /**
     * get dt table of contracts
     */
    public function getDatatablesResponse($request)
    {
        $query = $this->contractRepository->getContractQuery($request);

        // Eager load the relationships with specific email field selection
        $query->with([
            'typable' => function ($query) {
                $query->when($query->getModel() instanceof CRMLeads, function ($q) {
                    $q->select('id', 'email'); // Select single email field for leads
                })->when($query->getModel() instanceof CRMClients, function ($q) {
                    $q->select('id', 'primary_email');
                });
            }
        ]);

        $module = PANEL_MODULES[$this->getPanelModule()]['contracts'];
        $lmodule = PANEL_MODULES[$this->getPanelModule()]['leads'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['clients'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($contract) {
                return $this->renderActionsColumn($contract);
            })
            ->editColumn('title', function ($contract) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $contract->id) . "' target='_blank'>$contract->title</a>";
            })
            ->editColumn('to', function ($contract) use ($lmodule, $cmodule) {
                if (!$contract->typable) {
                    return "<span>No recipient found</span>";
                }

                $module = $contract->typable_type === CRMLeads::class ? $lmodule : $cmodule;
                $type = $contract->typable_type === CRMLeads::class ? "Leads" : "Clients";

                // Handle email retrieval based on model type
                $email = $contract->typable_type === CRMLeads::class
                    ? $contract->typable->email  // Direct email field for leads
                    : $contract->typable->primary_email; // Primary email for clients
    
                $email = $email ?? 'No email found';

                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $contract->typable->id) . "' target='_blank'>$type [$email]</a>";
            })
            ->editColumn('_id', function ($contract) {
                return "$contract->_prefix $contract->_id ";
            })
            ->editColumn('value', function ($contract) {
                return $contract?->value ? "$ " . number_format($contract->value) : "0";
            })
            ->editColumn('created_at', fn($contract) => $contract?->created_at ? formatDateTime($contract->created_at) : '')
            ->editColumn('creating_date', fn($contract) => $contract?->creating_date ? formatDateTime($contract->creating_date) : '')
            ->editColumn('valid_date', fn($contract) => $contract?->valid_date ? formatDateTime($contract->valid_date) : '')
            ->editColumn('status', function ($contract) {
                return "<span class='badge bg-" . CRM_STATUS_TYPES['CONTRACTS']['BT_CLASSES'][$contract->status] . "'>$contract->status</span>";
            })
            ->rawColumns(['actions', 'value', 'to', 'title', 'status', 'name'])
            ->make(true);
    }

    /**
     * render contract action col
     */
    protected function renderActionsColumn($contract)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['contracts'];
        $permissions = PermissionsHelper::getPermissionsArray('CONTRACTS');
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
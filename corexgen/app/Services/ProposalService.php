<?php

namespace App\Services;

use App\Jobs\SendProposal;
use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Repositories\ProposalRepository;
use App\Traits\IsSMTPValid;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProposalService
{


    use TenantFilter;
    use IsSMTPValid;

    protected $proposalRepository;

    private $tenantRoute;

    public function __construct(ProposalRepository $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     *  create proposal
     */
    public function createProposal($data)
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
        $proposal = CRMProposals::create($data);

        $this->checkIFProductAddedThenAdd($data, $proposal);
        return $proposal;
    }

    /**
     *  update proposal
     */
    public function updateProposal($data)
    {

        $query = $this->applyTenantFilter(CRMProposals::where('id', $data['id']));
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

        $this->checkIFProductAddedThenAdd($data, $proposal);

        return $proposal;
    }

    /**
     *  check if product added then add them 
     */
    public function checkIfProductAddedThenAdd($data, $proposal)
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

        $proposal->update([
            'product_details' => json_encode($json_data)
        ]);
    }

    /**
     *  get proposals
     */
    public function getProposals($typable_type, $typable_id)
    {
        return $this->applyTenantFilter(CRMProposals::query()->where('typable_type', $typable_type)->where('typable_id', $typable_id)->latest()->get());
    }


    /**
     *  send proposal on email
     */
    public function sendProposalOnEmail(CRMProposals $proposal, $view = "dashboard.crm.proposals.print"): bool
    {
        try {
            $mailSettings = $this->_getMailSettings();

            $toEmail = $proposal->typable_type === CRMLeads::class
                ? $proposal->typable->email
                : $proposal->typable->primary_email;

            // Prepare email details
            $emailDetails = [
                'from' => $mailSettings['Mail From Address'],
                'to' => $toEmail,
                'subject' => $proposal->title,
                'details' => $proposal->details,
                'template' => $proposal->template?->template_details
            ];

            // Dispatch the job
            SendProposal::dispatch(
                $mailSettings,
                $emailDetails,
                $proposal,
                $view
            );
            return true;
        } catch (\Throwable $e) {
            // Log the error or handle it as needed
            \Log::error('Failed to send proposal email', [
                'error' => $e->getMessage(),
                'proposal_id' => $proposal->id ?? null,
            ]);

            // Optionally, you can rethrow the exception if needed
            // throw $e;

            return false; // Return false or a default response to indicate failure
        }
    }


    /**
     *  get dt response
     */
    public function getDatatablesResponse($request)
    {
        $query = $this->proposalRepository->getProposalQuery($request);

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

        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $lmodule = PANEL_MODULES[$this->getPanelModule()]['leads'];
        $cmodule = PANEL_MODULES[$this->getPanelModule()]['clients'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($proposal) {
                return $this->renderActionsColumn($proposal);
            })
            ->editColumn('title', function ($proposal) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $proposal->id) . "' target='_blank'>$proposal->title</a>";
            })
            ->editColumn('to', function ($proposal) use ($lmodule, $cmodule) {
                if (!$proposal->typable) {
                    return "<span>No recipient found</span>";
                }

                $module = $proposal->typable_type === CRMLeads::class ? $lmodule : $cmodule;
                $type = $proposal->typable_type === CRMLeads::class ? "Leads" : "Clients";

                // Handle email retrieval based on model type
                $email = $proposal->typable_type === CRMLeads::class
                    ? $proposal->typable->email  // Direct email field for leads
                    : $proposal->typable->primary_email; // Primary email for clients
    
                $email = $email ?? 'No email found';

                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $proposal->typable->id) . "' target='_blank'>$type [$email]</a>";
            })
            ->editColumn('_id', function ($proposal) {
                return "$proposal->_prefix $proposal->_id ";
            })
            ->editColumn('value', function ($proposal) {
                return $proposal?->value ? "$ " . number_format($proposal->value) : "0";
            })
            ->editColumn('created_at', fn($proposal) => $proposal?->created_at ? formatDateTime($proposal->created_at) : '')
            ->editColumn('creating_date', fn($proposal) => $proposal?->creating_date ? formatDateTime($proposal->creating_date) : '')
            ->editColumn('valid_date', fn($proposal) => $proposal?->valid_date ? formatDateTime($proposal->valid_date) : '')
            ->editColumn('status', function ($proposal) {
                return "<span class='badge bg-" . CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'][$proposal->status] . "'>$proposal->status</span>";
            })
            ->rawColumns(['actions', 'value', 'to', 'title', 'status', 'name'])
            ->make(true);
    }

    /**
     *  render action col
     */
    protected function renderActionsColumn($proposal)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $permissions = PermissionsHelper::getPermissionsArray('PROPOSALS');
        $id = $proposal->id;
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



        if ($proposal->status !== 'ACCEPTED') {


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
        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY']) && $proposal->status !== 'ACCEPTED') {
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
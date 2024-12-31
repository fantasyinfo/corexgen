<?php

namespace App\Services;

use App\Models\CRM\CRMClients;
use App\Models\CRM\CRMLeads;
use App\Models\CRM\CRMProposals;
use App\Repositories\ProposalRepository;
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

    protected $proposalRepository;

    private $tenantRoute;

    public function __construct(ProposalRepository $proposalRepository)
    {
        $this->proposalRepository = $proposalRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


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

        return $proposal;
    }
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
        $proposal = $proposal->update($data);

        return $proposal;
    }



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
            ->editColumn('created_at', fn($proposal) => $proposal?->created_at ? Carbon::parse($proposal->created_at)->format('d M Y') : '')
            ->editColumn('creating_date', fn($proposal) => $proposal?->creating_date ? Carbon::parse($proposal->creating_date)->format('d M Y') : '')
            ->editColumn('valid_date', fn($proposal) => $proposal?->valid_date ? Carbon::parse($proposal->valid_date)->format('d M Y') : '')
            ->editColumn('status', function ($proposal) {
                return "<span class='badge bg-" . CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'][$proposal->status] . "'>$proposal->status</span>";
            })
            ->rawColumns(['actions', 'value', 'to', 'title', 'status', 'name'])
            ->make(true);
    }

    protected function renderActionsColumn($proposal)
    {
        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $permissions = PermissionsHelper::getPermissionsArray('PROPOSALS');
        $id = $proposal->id;
        $tenantRoute = $this->tenantRoute;

        $action = '<div class="dropdown text-end">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-ellipsis-h"></i>
            </button>';

        $action .= '<ul class="dropdown-menu dropdown-menu-end">';


        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'send']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-paper-plane me-2"></i> Send on Email
                </a>
            </li>';
        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'accepted']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-check me-2"></i> Mark Accepted
                </a>
            </li>';
        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'decline']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-times me-2"></i> Mark Decline
                </a>
            </li>';
        $action .= '<li class="m-1 p-1">
                <a class="dropdown-item" href="' . route($tenantRoute . $module . '.changeStatusAction', ['id' => $id, 'action' => 'revised']) . '" data-toggle="tooltip" title="Edit">
                <i class="fas fa-file-alt me-2"></i>Mark Revised
                </a>
            </li>';


        // Check if the user has permission to update
        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY'])) {
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
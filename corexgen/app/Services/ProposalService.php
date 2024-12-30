<?php

namespace App\Services;

use App\Repositories\ProposalRepository;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;

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



    public function getDatatablesResponse($request)
    {
        $query = $this->proposalRepository->getProposalQuery($request);


        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($proposal) {
                return $this->renderActionsColumn($proposal);
            })
            ->editColumn('title', function ($proposal) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $proposal->id) . "' target='_blank'>$proposal->title</a>";
            })
            ->editColumn('created_at', fn($proposal) => $proposal?->created_at ? $proposal?->created_at->format('d M Y') : '')
            ->editColumn('status', function ($proposal) {
                return $this->renderStatusColumn($proposal);
            })
            ->rawColumns(['actions', 'status', 'name'])
            ->make(true);
    }

    protected function renderActionsColumn($proposal)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'id' => $proposal->id
        ])->render();
    }

    protected function renderStatusColumn($proposal)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'id' => $proposal->id,
            'status' => [
                'current_status' => $proposal->status,
                'available_status' => CRM_STATUS_TYPES['PROPOSALS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['PROPOSALS']['BT_CLASSES'],
            ]
        ])->render();
    }
}
<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\CRM\CRMTemplates;
use App\Traits\TenantFilter;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;

class TemplateService
{

    use TenantFilter;


    private $tenantRoute;

    public function getDatatablesResponseProposals($request, $type)
    {
        $query = $this->applyTenantFilter(CRMTemplates::query()->with('createdBy')->where('type', $type));

        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', fn($template) => $this->getActionsColumn($template, $module))
            ->editColumn('created_by', function ($template) {
                return $template->createdBy->name;
            })
            ->editColumn('title', function ($template) use ($module) {
                $viewRoute = route($this->tenantRoute . $module . '.view', $template->id);
                return "<a class='dt-link' href='{$viewRoute}' target='_blank'>{$template->title}</a>";
            })
            ->editColumn('created_at', fn($template) => $template?->created_at ? $template?->created_at->format('d M Y') : '')
            ->rawColumns(['actions', 'title'])
            ->make(true);
    }

    private function getActionsColumn($template, $module)
    {
        $editRoute = route($this->tenantRoute . $module . '.editProposals', $template->id);
        $deleteRoute = route($this->tenantRoute . $module . '.destroyProposals', $template->id);

        $actions = "";
        $permissions = PermissionsHelper::getPermissionsArray('PROPOSALS_TEMPLATES');

        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY'])) {
            $actions .= " <a 
            href='{$editRoute}' 
            data-toggle='tooltip' 
            title='Edit' 
            class='btn btn-sm btn-outline-warning me-2'
            >
            <i class='fas fa-pencil-alt me-2'></i>
            </a>";
        }

        if (hasPermission(strtoupper($module) . '.' . $permissions['UPDATE']['KEY'])) {
            $actions .= "<a href='#' class='btn btn-sm btn-outline-danger' data-bs-toggle='modal' data-bs-target='#deleteModal' 
            data-id='{$template->id}' data-route='{$deleteRoute}' 
            data-toggle='tooltip' title='Delete'><i class='fas fa-trash-alt me-2'></i>
         </a>";
        }

        return $actions;
    }


}
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

    public function getDatatablesResponse($request, $type, $module, $permission, $viewRoute,$editRoute, $deleteRoute)
    {
        $query = $this->applyTenantFilter(CRMTemplates::query()->with('createdBy')->where('type', $type));

        $module = PANEL_MODULES[$this->getPanelModule()][$module];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', fn($template) => $this->getActionsColumn($template, $module, $permission, $editRoute, $deleteRoute))
            ->editColumn('created_by', function ($template) {
                return $template->createdBy->name;
            })
            ->editColumn('title', function ($template) use ($module,$viewRoute) {
                $viewRoute = route($this->tenantRoute . $module . '.'. $viewRoute, $template->id);
                return "<a class='dt-link' href='{$viewRoute}' target='_blank'>{$template->title}</a>";
            })
            ->editColumn('created_at', fn($template) => $template?->created_at ? $template?->created_at->format('d M Y') : '')
            ->rawColumns(['actions', 'title'])
            ->make(true);
    }

    private function getActionsColumn($template, $module, $permission, $editRoute, $deleteRoute)
    {
        $editRoute = route($this->tenantRoute . $module . '.' . $editRoute, $template->id);
        $deleteRoute = route($this->tenantRoute . $module . '.' . $deleteRoute, $template->id);

        $actions = "";
        $permissions = PermissionsHelper::getPermissionsArray($permission);

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
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

    public function getDatatablesResponseProposals($request, $model)
    {
        // Add your logic here

        $query = $this->applyTenantFilter(CRMTemplates::query()->where('templateable_type', $model));

        $module = PANEL_MODULES[$this->getPanelModule()]['proposals'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($template) {
                return $this->renderActionsColumn($template);
            })
            ->editColumn('title', function ($template) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $template->id) . "' target='_blank'>$template->title</a>";
            })
            ->editColumn('created_at', fn($template) => $template?->created_at ? $template?->created_at->format('d M Y') : '')
            ->rawColumns(['actions', 'title'])
            ->make(true);

    }

    protected function renderActionsColumn($template)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS_TEMPLATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'id' => $template->id
        ])->render();
    }


}
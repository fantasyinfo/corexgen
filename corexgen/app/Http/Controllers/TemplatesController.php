<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\CRM\CRMProposals;
use App\Services\TemplateService;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemplatesController extends Controller
{
    //

    use TenantFilter;

    //
    /**
     * Number of items per page for pagination
     * @var int
     */
    protected $perPage = 10;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.crm.templates.';

    protected $templateService;
    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }



    public function __construct(TemplateService $templateService)
    {

        $this->templateService = $templateService;

    }



    public function indexProposals(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->templateService->getDatatablesResponseProposals($request, CRMProposals::class);
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Proposals Template Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS_TEMPLATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'type' => 'proposals'
        ]);
    }

    public function createProposals()
    {
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Proposals Template',
            'type' => 'proposals'
        ]);
    }
}

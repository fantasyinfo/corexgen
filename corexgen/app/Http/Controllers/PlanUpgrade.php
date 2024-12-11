<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Plans;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanUpgrade extends Controller
{

    //
    use TenantFilter;

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
    private $viewDir = 'dashboard.crm.planupgrade.';

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


    /**
     * Display list of plans 
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $plans = Plans::query()->with('planFeatures')->get();

        $this->tenantRoute = $this->getTenantRoute();


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Memberships Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PLANUPGRADE'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['planupgrade'],
            'plans' => $plans,
            'current_plan_id' => Auth::user()->company_id
        ]);
    }
}

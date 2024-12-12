<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Http\Controllers\Controller;
use App\Models\CRM\CRMSettings;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    //
    use TenantFilter;
    /**
     * Display a listing of the resource.
     */

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
    private $viewDir = 'dashboard.settings.';

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
    public function index()
    {
        $this->tenantRoute = $this->getTenantRoute();

        $general_settings = CRMSettings::all();

        return view($this->getViewFilePath('index'), [
            'general_settings' => $general_settings,
            'title' => 'Settings Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['settings'],
        ]);
    }

    public function upgrade(){
        
    }
}

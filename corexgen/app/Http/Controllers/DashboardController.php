<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    use TenantFilter;

    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.home.';

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

    public function companyHome()
    {
        return view($this->getViewFilePath('companyHome'), [
            'title' => 'Dashboard Management',
            'permissions' => PermissionsHelper::getPermissionsArray('DASHBOARD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['dashboard'],
            'type' => 'Dashboard',
        ]);
    }

    public function superHome()
    {
        return view($this->getViewFilePath('superHome'), [
            'title' => 'Dashboard Management',
            'permissions' => PermissionsHelper::getPermissionsArray('DASHBOARD'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['dashboard'],
            'type' => 'Dashboard',
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\Audit;
use App\Models\ImportHistory;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AuditController extends Controller
{
    //
    use TenantFilter;

    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'dashboard.audit.';

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
     * view and fetch the latest audit for activities
     */
    public function index()
    {
        $audits = Audit::with('user')->latest()->limit(50)->get();
        return view(
            $this->getViewFilePath('index'),
            [
                'audits' => $audits,
                'title' => 'Audit Logs',
            ]
        );
    }


    /**
     * bulk import fetch status
     */
    public function bulkimport(Request $request)
    {
        //

        $this->tenantRoute = $this->getTenantRoute();

        $query = ImportHistory::query();
        $this->applyTenantFilter($query);
        // Server-side DataTables response
        if ($request->ajax()) {
            return DataTables::of($query)->make(true);
        }

        return view($this->getViewFilePath('bulk'), [
            'filters' => $request->all(),
            'title' => 'Bulk Imports Management',
            'permissions' => PermissionsHelper::getPermissionsArray('BULK_IMPORT_STATUS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['audit'],
        ]);
    }
}

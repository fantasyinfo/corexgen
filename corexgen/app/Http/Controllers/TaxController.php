<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use App\Models\Tax;

/**
 * TaxController handles CRUD operations for Tax
 * 
 * This controller manages tax-related functionality including:
 * - Listing taxs with server-side DataTables
 * - Creating new taxs
 * - Editing existing taxs
 * - Changing tax status
 */

class TaxController extends Controller
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
    private $viewDir = 'dashboard.crm.tax.';

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
     * Display list of taxs with filtering and DataTables support
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Initialize query with tenant filtering
        $query = Tax::query()->with('country');
  
        $this->tenantRoute = $this->getTenantRoute();

        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('name', 'LIKE', "%{$request->name}%"));
        $query->when($request->filled('status'), fn($q) => $q->where('status', $request->status));
        $query->when($request->filled('tax_rate'), fn($q) => $q->where('tax_rate', $request->tax_rate));
        $query->when($request->filled('tax_type'), fn($q) => $q->where('tax_type', $request->tax_type));


      
        // Server-side DataTables response
        if ($request->ajax()) {
            return DataTables::of($query)
                ->addColumn('actions', function ($tax) {
                    return View::make(getComponentsDirFilePath('dt-actions-buttons'), [

                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('TAX'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['tax'],
                        'id' => $tax->id

                    ])->render();
                })
                // ->editColumn('created_at', fn($tax) => $tax->created_at->format('d M Y'))
                ->editColumn('status', function ($tax) {
                    return View::make(getComponentsDirFilePath('dt-status'), [

                        'tenantRoute' => $this->tenantRoute,
                        'permissions' => PermissionsHelper::getPermissionsArray('TAX'),
                        'module' => PANEL_MODULES[$this->getPanelModule()]['tax'],
                        'id' => $tax->id,
                        'status' => [
                            'current_status' => $tax->status,
                            'available_status' => CRM_STATUS_TYPES['TAX_RATES']['STATUS'],
                            'bt_class' => CRM_STATUS_TYPES['TAX_RATES']['BT_CLASSES'],

                        ]
                    ])->render();
                })
                ->rawColumns(['actions', 'status'])
                ->make(true);
        }

        // Render index view with filterss
        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Tax Management',
            'permissions' => PermissionsHelper::getPermissionsArray('TAX'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tax'],
        ]);
    }
}

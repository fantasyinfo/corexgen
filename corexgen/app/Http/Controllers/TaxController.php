<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\TaxRequest;
use App\Models\Country;
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
        $query = Tax::query()
            ->with('country')
            ->leftJoin('countries', 'countries.id', '=', 'tax_rates.country_id') // Join the countries table
            ->select('tax_rates.*', 'countries.name as country_name'); // Add country_name as a column

        $this->tenantRoute = $this->getTenantRoute();

        // Apply dynamic filters based on request input
        $query->when($request->filled('name'), fn($q) => $q->where('tax_rates.name', 'LIKE', "%{$request->name}%"));


        if($request->filled('country_id') && $request->country_id != '0') {
            $query->when($request->filled('country_id'), fn($q) => $q->where('country_id', $request->country_id));
        }




        $query->when($request->filled('status'), fn($q) => $q->where('tax_rates.status', $request->status));
        $query->when($request->filled('tax_rate'), fn($q) => $q->where('tax_rates.tax_rate', 'LIKE', "%{$request->tax_rate}%"));
        $query->when($request->filled('tax_type'), fn($q) => $q->where('tax_rates.tax_type', 'LIKE', "%{$request->tax_type}%"));

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
                ->editColumn('created_at', fn($tax) => $tax->created_at->format('d M Y'))
                ->editColumn('country_name', fn($tax) => $tax->country_name ?? '')
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

        $countries = Country::all();

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Tax Management',
            'permissions' => PermissionsHelper::getPermissionsArray('TAX'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['tax'],
            'countries' => $countries
        ]);
    }


    /**
     * Show create tax form
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $countries = Country::all();
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Tax',
            'countries' => $countries
        ]);
    }


    /**
     * Store a newly created role
     * 
     * @param TaxRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TaxRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();
        try {
            // Validate and create role
            $validated = $request->validated();
            Tax::create($validated);

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'tax.index')
                ->with('success', 'Tax created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the role: ' . $e->getMessage());
        }
    }


     /**
     * Show edit tax form
     * 
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        // Apply tenant filtering to tax query
        $query = Tax::query()->where('id', $id);
        $tax = $query->firstOrFail();

        $countries = Country::all();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Tax',
            'tax' => $tax,
            'countries' => $countries
        ]);
    }


     /**
     * Update an existing tax
     * 
     * @param TaxRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(TaxRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        try {
            // Validate and update role
            $validated = $request->validated();
            $query = Tax::query()->where('id', $request->id);
            $query->update($validated);

            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'tax.index')
                ->with('success', 'Tax updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during tax update
            return redirect()->back()
                ->with('error', 'An error occurred while updating the tax: ' . $e->getMessage());
        }
    }


     /**
     * Delete a tax
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        try {
            // Apply tenant filtering and delete role
            $query = Tax::query()->where('id', $id);
            $query->delete();

            // Redirect with success message
            return redirect()->back()->with('success', 'Tax deleted successfully.');
        } catch (\Exception $e) {
            // Handle any deletion errors
            return redirect()->back()->with('error', 'Failed to delete the tax: ' . $e->getMessage());
        }
    }

     /**
     * Chaning the status for tax
     * 
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus($id, $status)
    {
        try {
            // Apply tenant filtering and find role
            $query = Tax::query()->where('id', $id);
            $query->update(['status' => $status]);
            // Redirect with success message
            return redirect()->back()->with('success', 'Tax status changed successfully.');
        } catch (\Exception $e) {
            // Handle any status change errors
            return redirect()->back()->with('error', 'Failed to change the tax status: ' . $e->getMessage());
        }
    }

}

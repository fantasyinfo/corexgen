<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use App\Http\Requests\PlansRequest;
use App\Http\Requests\TaxRequest;
use App\Models\Country;
use App\Models\Plans;
use App\Models\PlansFeatures;
use App\Models\Tax;

/**
 * PlansController handles CRUD operations for Plan
 * 
 * This controller manages plan-related functionality including:
 * - Listing plans with server-side DataTables
 * - Creating new plans
 * - Editing existing plans
 * - Changing plan status
 */

class PlansController extends Controller
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
    private $viewDir = 'dashboard.crm.plans.';

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
        $plans = Plans::query()->with('plans_features')->get();

        $this->tenantRoute = $this->getTenantRoute();




        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Plans Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PLANS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['plans'],
            'plans' => $plans
        ]);
    }


    /**
     * Show create tax form
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Plan'
        ]);
    }


    /**
     * Store a newly created plans
     * 
     * @param PlansRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PlansRequest $request)
    {
        $this->tenantRoute = $this->getTenantRoute();
    
        try {
            // Validate and create the plan
            $validated = $request->validated();
            $plan = Plans::create($validated); // This returns the model instance
            $planId = $plan->id; // Extract the ID from the created plan
    
            // Loop through all request input
            foreach ($request->all() as $key => $value) {
                // Check if the key starts with 'features_'
                if (str_starts_with($key, 'features_')) {
                    // Extract the feature name (remove the 'features_' prefix)
                    $featureName = str_replace('features_', '', $key);
    
                    // Create a new feature entry
                    PlansFeatures::create([
                        'plan_id' => $planId,
                        'module_name' => $featureName,
                        'value' => $value
                    ]);
                }
            }
    
            // Redirect with success message
            return redirect()->route($this->tenantRoute . 'plans.index')
                ->with('success', 'Plan created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the plan: ' . $e->getMessage());
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

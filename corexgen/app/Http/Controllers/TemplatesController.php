<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Models\CRM\CRMProposals;
use App\Models\CRM\CRMTemplates;
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
    private $viewDir = 'dashboard.crm.crmtemplates.';

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


    /**
     * index view of proposal fetch and view
     */
    public function indexProposals(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->templateService->getDatatablesResponse($request, 'Proposals', 'proposals', 'PROPOSALS_TEMPLATES', 'viewProposals', 'editProposals', 'destroyProposals');
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Proposals Template Management',
            'permissions' => PermissionsHelper::getPermissionsArray('PROPOSALS_TEMPLATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['proposals'],
            'type' => 'Proposals',

        ]);
    }

    /**
     * create proposal
     */
    public function createProposals()
    {
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Proposals Template',
            'type' => 'Proposals',
            'store' => 'proposals.storeProposals',
        ]);
    }

    /**
     * store proposal
     */
    public function storeProposals(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string'
        ]);

        try {
            $template = CRMTemplates::create([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
                'type' => 'Proposals',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route($this->tenantRoute . 'proposals.indexProposals')
                ->with('success', 'Proposal Template created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Proposal Template: ' . $e->getMessage());
        }
    }

    /**
     * destory proposal
     */
    public function destroyProposals($id)
    {
        try {
            //code...
            $this->applyTenantFilter(CRMTemplates::where('id', $id))->delete();

            return redirect()->back()->with('success', 'Template deleted successfully');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Proposal Template: ' . $e->getMessage());
        }
    }
    /**
     * edit proposal
     */
    public function editProposals($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Proposals Template',
            'type' => 'Proposals',
            'store' => 'proposals.updateProposals',
            'template' => $template
        ]);
    }
    /**
     * update proposal
     */
    public function updateProposals(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string',
            'id' => 'integer|exists:templates,id'
        ]);

        try {

            $template = $this->applyTenantFilter(CRMTemplates::where('id', $validated['id']))->firstOrFail();

            $template->update([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
            ]);

            return redirect()->route($this->tenantRoute . 'proposals.editProposals', ['id' => $template->id])
                ->with('success', 'Proposal Template updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while updated the Proposal Template: ' . $e->getMessage());
        }
    }

    /**
     * view proposal
     */
    public function viewProposals($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('view'), [
            'title' => 'View Proposals Template',
            'type' => 'Proposals',
            'template' => $template
        ]);
    }



    // estimate
    /**
     * index of estimate view and fetch
     */
    public function indexEstimates(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->templateService->getDatatablesResponse($request, 'Estimates', 'estimates', 'ESTIMATES_TEMPLATES', 'viewEstimates', 'editEstimates', 'destroyEstimates');
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Estimates Template Management',
            'permissions' => PermissionsHelper::getPermissionsArray('ESTIMATES_TEMPLATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['estimates'],
            'type' => 'Estimates',

        ]);
    }

    /**
     * create estimate
     */
    public function createEstimates()
    {
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Estimates Template',
            'type' => 'Estimates',
            'store' => 'estimates.storeEstimates',
        ]);
    }

    /**
     * store estimate
     */

    public function storeEstimates(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string'
        ]);

        try {
            $template = CRMTemplates::create([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
                'type' => 'Estimates',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route($this->tenantRoute . 'estimates.indexEstimates')
                ->with('success', 'Estimates Template created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Estimates Template: ' . $e->getMessage());
        }
    }

    /**
     * destory estimate
     */
    public function destroyEstimates($id)
    {
        try {
            //code...
            $this->applyTenantFilter(CRMTemplates::where('id', $id))->delete();

            return redirect()->back()->with('success', 'Template deleted successfully');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Estimate Template: ' . $e->getMessage());
        }
    }

    /**
     * edit estimate
     */
    public function editEstimates($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Estimate Template',
            'type' => 'Estimate',
            'store' => 'estimates.updateEstimates',
            'template' => $template
        ]);
    }

    /**
     * update estimate
     */
    public function updateEstimates(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string',
            'id' => 'integer|exists:templates,id'
        ]);

        try {

            $template = $this->applyTenantFilter(CRMTemplates::where('id', $validated['id']))->firstOrFail();

            $template->update([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
            ]);

            return redirect()->route($this->tenantRoute . 'estimates.editEstimates', ['id' => $template->id])
                ->with('success', 'Estimates Template updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while updated the Estimates Template: ' . $e->getMessage());
        }
    }

    /**
     * view estimate
     */
    public function viewEstimates($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('view'), [
            'title' => 'View Estimates Template',
            'type' => 'Estimates',
            'template' => $template
        ]);
    }

    // contracts
    /**
     * view and fetch the contracts index
     */
    public function indexContracts(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->templateService->getDatatablesResponse($request, 'Contracts', 'contracts', 'CONTRACTS_TEMPLATES', 'viewContracts', 'editContracts', 'destroyContracts');
        }

        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Contracts Template Management',
            'permissions' => PermissionsHelper::getPermissionsArray('CONTRACTS_TEMPLATES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['contracts'],
            'type' => 'Contracts',

        ]);
    }

    /**
     * create contracts
     */
    public function createContracts()
    {
        return view($this->getViewFilePath('create'), [
            'title' => 'Create Contracts Template',
            'type' => 'Contracts',
            'store' => 'contracts.storeContracts',
        ]);
    }

    /**
     * store contracts
     */
    public function storeContracts(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string'
        ]);

        try {
            $template = CRMTemplates::create([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
                'type' => 'Contracts',
                'company_id' => Auth::user()->company_id,
                'created_by' => Auth::id()
            ]);

            return redirect()->route($this->tenantRoute . 'contracts.indexContracts')
                ->with('success', 'Contracts Template created successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Contracts Template: ' . $e->getMessage());
        }
    }

    /**
     * destory contracts
     */
    public function destroyContracts($id)
    {
        try {
            //code...
            $this->applyTenantFilter(CRMTemplates::where('id', $id))->delete();

            return redirect()->back()->with('success', 'Template deleted successfully');
        } catch (\Exception $e) {
            //throw $th;
            return redirect()->back()
                ->with('error', 'An error occurred while creating the Contracts Template: ' . $e->getMessage());
        }
    }

    /**
     * edit contracts
     */
    public function editContracts($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Contracts Template',
            'type' => 'Contracts',
            'store' => 'contracts.updateContracts',
            'template' => $template
        ]);
    }

    /**
     * update contracts
     */
    public function updateContracts(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'template_details' => 'required|string',
            'id' => 'integer|exists:templates,id'
        ]);

        try {

            $template = $this->applyTenantFilter(CRMTemplates::where('id', $validated['id']))->firstOrFail();

            $template->update([
                'title' => trim($validated['title']),
                'template_details' => $validated['template_details'],
            ]);

            return redirect()->route($this->tenantRoute . 'contracts.editContracts', ['id' => $template->id])
                ->with('success', 'Contracts Template updated successfully.');
        } catch (\Exception $e) {
            // Handle any errors during role creation
            return redirect()->back()
                ->with('error', 'An error occurred while updated the Contracts Template: ' . $e->getMessage());
        }
    }

    /**
     * view contracts
     */
    public function viewContracts($id)
    {
        $template = $this->applyTenantFilter(CRMTemplates::where('id', $id))->firstOrFail();

        return view($this->getViewFilePath('view'), [
            'title' => 'View Contracts Template',
            'type' => 'Contracts',
            'template' => $template
        ]);
    }
}

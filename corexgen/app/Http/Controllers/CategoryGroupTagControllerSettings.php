<?php
namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Traits\TenantFilter;
use Illuminate\Http\Request;
use App\Models\CategoryGroupTag;
use Illuminate\Support\Facades\Auth;

class CategoryGroupTagControllerSettings extends Controller
{

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



    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {


        $type = $request->query('type');
        $relationType = $request->query('relation_type');

        $query = CategoryGroupTag::where('company_id', Auth::user()->company_id);

        if ($type) {
            $query->where('type', $type);
        }

        if ($relationType) {
            $query->where('relation_type', $relationType);
        }



        $tags = $query->get();

        return response()->json($tags);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'type' => 'required|string',
            'relation_type' => 'required|string',
        ]);

        $validated['company_id'] = Auth::user()->company_id;
        $validated['created_at'] = now();
        $validated['updated_at'] = now();

        $tag = CategoryGroupTag::create($validated);

        return response()->json(['success' => true, 'tag' => $tag], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'color' => 'sometimes|string|max:255',
            'id' => 'required|exists:category_group_tag,id'
        ]);

        $tag = CategoryGroupTag::where('company_id', Auth::user()->company_id)->findOrFail($validated['id']);
        $tag->update($validated);

        return response()->json(['success' => true, 'tag' => $tag]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $tag = CategoryGroupTag::where('company_id', Auth::user()->company_id)->findOrFail($id);
        $tag->delete();

        return response()->json(['success' => true, 'message' => 'Deleted successfully']);
    }

    public function indexClientCategory(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'title' => 'Clients Category Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Categories Category Settings',
            'page' => 'clientsCategory'
        ]);

    }
    public function indexLeadsGroups(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Leads Groups Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Leads Groups Settings',
            'page' => 'leadsGroups'
        ]);

    }


    public function indexLeadsStatus(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Leads Status Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Leads Status Settings',
            'page' => 'leadsStatus'
        ]);

    }

    public function indexLeadsSources(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Leads Sources Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Leads Sources Settings',
            'page' => 'leadsSources'
        ]);

    }
    public function indexProductCategories(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Products Categories Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Products Categories Settings',
            'page' => 'productsCategories'
        ]);

    }
    public function indexProductTaxes(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Products Taxes Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Products Taxes Settings',
            'page' => 'productsTaxes'
        ]);

    }
    public function indexTasksStatus(Request $request)
    {
        return view($this->getViewFilePath('ctgSettings'), [
            'filters' => $request->all(),
            'title' => 'Tasks Status Management',
            'permissions' => PermissionsHelper::getPermissionsArray('SETTINGS_CTG'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['cgt'],
            'type' => 'Tasks Status Settings',
            'page' => 'tasksStatus'
        ]);

    }



}

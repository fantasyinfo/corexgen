<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Services\CustomFieldService;
use App\Traits\StatusStatsFilter;
use App\Traits\SubscriptionUsageFilter;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;

class CustomFieldController extends Controller
{

    use TenantFilter;
    use SubscriptionUsageFilter;
    use StatusStatsFilter;
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
    private $viewDir = 'dashboard.crm.customfields.';

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


    protected $customFieldService;

    public function __construct(CustomFieldService $customFieldService)
    {
        $this->customFieldService = $customFieldService;
    }

    public function index(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();


        // Server-side DataTables response
        if ($request->ajax()) {
            return $this->customFieldService->getDatatablesResponse($request);
        }



        $user = Auth::user();
        $userQuery = CustomFieldDefinition::query();

        $userQuery = $this->applyTenantFilter($userQuery);

        // Get all totals in a single query
        $usersTotals = $userQuery->select([
            DB::raw('COUNT(*) as totalUsers'),
            DB::raw(sprintf(
                'SUM(CASE WHEN is_active = "%s" THEN 1 ELSE 0 END) as totalActive',
                '1'
            )),
            DB::raw(sprintf(
                'SUM(CASE WHEN is_active = "%s" THEN 1 ELSE 0 END) as totalInactive',
                '0'
            ))
        ])->first();

        // fetch usage

        if (!$user->is_tenant && !is_null($user->company_id)) {
            $usages = $this->fetchTotalAllowAndUsedUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS']]));
        } else if ($user->is_tenant) {
            $usages = [
                'totalAllow' => '-1',
                'currentUsage' => $usersTotals->totalUsers,
            ];
        }


        return view($this->getViewFilePath('index'), [
            'filters' => $request->all(),
            'title' => 'Custom Fields Management',
            'permissions' => PermissionsHelper::getPermissionsArray('CUSTOM_FIELDS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['customfields'],
            'type' => 'Custom Fields',
            'total_allow' => $usages['totalAllow'],
            'total_used' => $usages['currentUsage'],
            'total_active' => $usersTotals->totalActive,
            'total_inactive' => $usersTotals->totalInactive,
            'total_ussers' => $usersTotals->totalUsers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:' . implode(',', array_keys(CUSTOM_FIELDS_INPUT_TYPES)),
            'fields.*.entity_type' => 'required|in:' . implode(',', array_keys(CUSTOM_FIELDS_RELATION_TYPES['KEYS'])),
            'fields.*.options' => 'required_if:fields.*.type,select',
            'fields.*.is_required' => 'nullable'
        ]);

        DB::beginTransaction();
        try {
            foreach ($data['fields'] as $field) {
                $fieldData = [
                    'company_id' => Auth::user()->company_id,
                    'field_label' => $field['label'],
                    'field_name' => Str::slug($field['label']),
                    'field_type' => $field['type'],
                    'entity_type' => $field['entity_type'],
                    'is_required' => !empty($field['is_required']),
                    'is_active' => true
                ];

                // Handle options for select type
                if ($field['type'] === 'select' && !empty($field['options'])) {
                    $fieldData['options'] = array_filter(explode("\n", $field['options']));
                }

                $this->customFieldService->createDefinition($fieldData);

                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS']]), '+', '1');
            }

            DB::commit();
            return redirect()
                ->route(getPanelRoutes('customfields.index'))
                ->with('success', __('Fields created successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('Error creating custom fields'));
        }
    }



    public function create()
    {
        $this->checkCurrentUsage(strtolower(PLANS_FEATURES['CUSTOM_FIELDS']));

        return view($this->getViewFilePath('create'), [
            'title' => 'Create Custom Fields'
        ]);
    }


    public function edit($id)
    {
        // Apply tenant filtering to role query
        $query = CustomFieldDefinition::query()->where('id', $id);
        $query = $this->applyTenantFilter($query);
        $customfield = $query->firstOrFail();

        $fieldData = [
            'id' => $customfield->id,
            'fields' => [
                [
                    'label' => $customfield->field_label,
                    'type' => $customfield->field_type,
                    'entity_type' => $customfield->entity_type,
                    'options' => is_array($customfield->options) ? implode("\n", $customfield->options) : '',
                    'is_required' => $customfield->is_required
                ]
            ]
        ];

        return view($this->getViewFilePath('edit'), [
            'title' => 'Edit Custom Field',
            'customfield' => $fieldData
        ]);
    }

    public function update(Request $request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $data = $request->validate([
            'id' => 'required|exists:custom_field_definitions,id',
            'fields.*.label' => 'required|string|max:255',
            'fields.*.type' => 'required|in:' . implode(',', array_keys(CUSTOM_FIELDS_INPUT_TYPES)),
            'fields.*.entity_type' => 'required|in:' . implode(',', array_keys(CUSTOM_FIELDS_RELATION_TYPES['KEYS'])),
            'fields.*.options' => 'required_if:fields.*.type,select',
            'fields.*.is_required' => 'nullable'
        ]);

        $query = CustomFieldDefinition::query()->where('id', $data['id']);
        $query = $this->applyTenantFilter($query);
        $customfield = $query->firstOrFail();

        $fieldData = [
            'field_label' => $data['fields'][0]['label'],
            'field_name' => Str::slug($data['fields'][0]['label']),
            'field_type' => $data['fields'][0]['type'],
            'entity_type' => $data['fields'][0]['entity_type'],
            'is_required' => !empty($data['fields'][0]['is_required']),
            'options' => $data['fields'][0]['type'] === 'select' ?
                array_filter(explode("\n", $data['fields'][0]['options'])) :
                null
        ];

        $this->customFieldService->updateDefinition($customfield, $fieldData);

        return redirect()
            ->route(getPanelRoutes('customfields.index'))
            ->with('success', __('Custom Field updated successfully'));
    }

    public function destroy($id)
    {
        try {
            // Apply tenant filtering and delete role
            $query = CustomFieldDefinition::query()->where('id', $id);
            $query = $this->applyTenantFilter($query);
            $query->delete();

            $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS']]), '-', '1');
            // Redirect with success message
            return redirect()->back()->with('success', 'Custom Field deleted successfully.');
        } catch (\Exception $e) {
            // Handle any deletion errors
            return redirect()->back()->with('error', 'Failed to delete the Custom Field: ' . $e->getMessage());
        }
    }

    public function changeStatus($id, $status)
    {
        try {
            // Apply tenant filtering and find role
            $query = CustomFieldDefinition::query()->where('id', $id);
            $query = $this->applyTenantFilter($query);
            $query->update(['is_active' => $status == 'Active' ? '1' : '0']);
            // Redirect with success message
            return redirect()->back()->with('success', 'Custom Fields status changed successfully.');
        } catch (\Exception $e) {
            // Handle any status change errors
            return redirect()->back()->with('error', 'Failed to change the Custom Fields status: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {

        $ids = $request->input('ids');

        try {
            // Delete the role

            if (is_array($ids) && count($ids) > 0) {
                // Validate ownership/permissions if necessary
                $this->applyTenantFilter(CustomFieldDefinition::query()->whereIn('id', $ids))->delete();
                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS']]), '-', count($ids));
                return response()->json(['message' => 'Selected custom fields deleted successfully.'], 200);
            }

            return response()->json(['message' => 'No custom fields selected for deletion.'], 400);

        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->back()->with('error', 'Failed to delete the custom fields: ' . $e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\PermissionsHelper;
use App\Services\CustomFieldService;
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
                '1'
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
            'fields.*.type' => 'required|in:text,number,select,date,textarea',
            'fields.*.entity_type' => 'required|in:client,user,role',
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

                $this->updateUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CUSTOM_FIELDS']]), '+','1');
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
}
<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;
use App\Repositories\CustomFieldsRepository;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class CustomFieldService
{

    use TenantFilter;


    private $tenantRoute;

    protected $customFieldsRepository;

    public function __construct(CustomFieldsRepository $customFieldsRepository)
    {
        $this->customFieldsRepository = $customFieldsRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     * create definition 
     */
    public function createDefinition(array $data)
    {
        return DB::transaction(function () use ($data) {
            return CustomFieldDefinition::create($data);
        });
    }

    /**
     * update definition 
     */
    public function updateDefinition(CustomFieldDefinition $definition, array $data)
    {
        return DB::transaction(function () use ($definition, $data) {
            $definition->update($data);
            return $definition;
        });
    }

    /**
     * get gields for entitiy definition 
     */
    public function getFieldsForEntity(string $entityType, int $companyId)
    {
        return CustomFieldDefinition::where('entity_type', $entityType)
            ->where('is_active', true)
            ->where('company_id', $companyId)
            ->get();
    }

    /**
     * save values of custom fields 
     */
    public function saveValues($entity, array $values)
    {
        $entityType = $entity->getCustomFieldEntityType();
        // \Log::info('entity type', [$entityType]);
        $companyId = $entity->company_id ?? null;

        $definitions = $this->getFieldsForEntity($entityType, $companyId)
            ->keyBy('id');

        $fieldValues = $values['custom_fields'] ?? [];

        // \Log::info('definitions found', [$definitions]);
        // \Log::info('values', $fieldValues);


        foreach ($fieldValues as $definitionId => $value) {
            if (!isset($definitions[$definitionId])) {
                \Log::info('skipping because id not found', [
                    //'matching_id' => $definitions[$definitionId],
                    'id' => $definitionId,
                    'data' => $definitions,
                ]);
                continue;
            }

            $definition = $definitions[$definitionId];

            // Validate value against rules
            // if ($definition->validation_rules) {
            //     Validator::make(
            //         ['value' => $value],
            //         ['value' => $definition->validation_rules]
            //     )->validate();
            // }

            try {
                $customFieldValueSaved = CustomFieldValue::updateOrCreate(
                    [
                        'definition_id' => $definitionId,
                        'entity_id' => $entity->id,
                    ],
                    ['field_value' => $value]
                );
                // \Log::info('Custom field value saved successfully', [$customFieldValueSaved]);
            } catch (\Exception $e) {
                \Log::error('Error saving custom field value', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * get values of entitity  
     */
    public function getValuesForEntity($entity)
    {
        return CustomFieldValue::with('definition')
            ->whereHas('definition', function ($query) use ($entity) {
                $query->where('entity_type', $entity->getCustomFieldEntityType())
                    ->where(function ($q) use ($entity) {
                        $q->where('company_id', $entity->company_id)
                            ->orWhereNull('company_id');
                    });
            })
            ->where('entity_id', $entity->id)
            ->get();
    }

    /**
     * delete entity value 
     */
    public function deleteEntityValues($entity)
    {
        return DB::transaction(function () use ($entity) {
            return CustomFieldValue::where('entity_id', $entity->id)
                ->whereHas('definition', function ($query) use ($entity) {
                    $query->where('entity_type', $entity->getCustomFieldEntityType());
                })
                ->delete();
        });
    }

    /**
     * bulk delete entity value 
     */

    public function bulkDeleteEntityValues(string $entityType, array $entityIds)
    {
        return DB::transaction(function () use ($entityType, $entityIds) {
            return CustomFieldValue::whereIn('entity_id', $entityIds)
                ->whereHas('definition', function ($query) use ($entityType) {
                    $query->where('entity_type', $entityType);
                })
                ->delete();
        });
    }


    /**
     * get dt tbl of custom fields
     */
    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->customFieldsRepository->getCustomFieldsQuery($request);

        $query = $this->applyTenantFilter($query);
        //dd($query->get()->toArray());
        $module = PANEL_MODULES[$this->getPanelModule()]['customfields'];

        return DataTables::of($query)
            ->addColumn('actions', function ($customfield) {
                return $this->renderActionsColumn($customfield);
            })
            ->editColumn('created_at', function ($customfield) {
                return formatDateTime($customfield->created_at);
            })
            ->editColumn('entity_type', function ($customfield) {
                return CUSTOM_FIELDS_RELATION_TYPES['VALUES'][$customfield->entity_type];
            })
            ->editColumn('field_type', function ($customfield) {
                return CUSTOM_FIELDS_INPUT_TYPES[$customfield->field_type];
            })
            ->editColumn('is_required', function ($customfield) {
                return $customfield->is_required == '1' ? '<span class="text-success"> True</span>' : '<span class="text-danger"> False</span>';
            })
            ->editColumn('status', function ($customfield) {
                return $this->renderStatusColumn($customfield);
            })
            ->rawColumns(['actions', 'status', 'field_type', 'is_required', 'name', 'entity_type']) // Add 'status' to raw columns
            ->make(true);
    }

    /**
     * render action col of dt table of custom fields
     */
    protected function renderActionsColumn($customfield)
    {


        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('CUSTOM_FIELDS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['customfields'],
            'id' => $customfield->id
        ])->render();
    }

    /**
     * render status col of dt table of custom fields
     */
    protected function renderStatusColumn($customfield)
    {


        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('CUSTOM_FIELDS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['customfields'],
            'id' => $customfield->id,
            'status' => [
                'current_status' => $customfield->is_active == '1' ? 'Active' : 'Inactive',
                'available_status' => ['0' => 'Inactive', '1' => 'Active'],
                'bt_class' => ['Active' => 'success', 'Inactive' => 'danger'],
            ]
        ])->render();
    }
}
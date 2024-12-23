<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\CustomFieldDefinition;
use App\Models\CustomFieldValue;

use App\Repositories\CustomFieldsRepository;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Carbon\Carbon;
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

    public function createDefinition(array $data)
    {
        return DB::transaction(function () use ($data) {
            return CustomFieldDefinition::create($data);
        });
    }

    public function updateDefinition(CustomFieldDefinition $definition, array $data)
    {
        return DB::transaction(function () use ($definition, $data) {
            $definition->update($data);
            return $definition;
        });
    }

    public function getFieldsForEntity(string $entityType, ?int $companyId = null)
    {
        return CustomFieldDefinition::forCompany($companyId)
            ->where('entity_type', $entityType)
            ->where('is_active', true)
            ->get();
    }

    public function saveValues($entity, array $values)
    {
        $entityType = $entity->getCustomFieldEntityType();
        $companyId = $entity->company_id ?? null;

        $definitions = $this->getFieldsForEntity($entityType, $companyId)
            ->keyBy('id');

        foreach ($values as $definitionId => $value) {
            if (!isset($definitions[$definitionId])) {
                continue;
            }

            $definition = $definitions[$definitionId];

            // Validate value against rules
            if ($definition->validation_rules) {
                Validator::make(
                    ['value' => $value],
                    ['value' => $definition->validation_rules]
                )->validate();
            }

            CustomFieldValue::updateOrCreate(
                [
                    'definition_id' => $definitionId,
                    'entity_id' => $entity->id,
                ],
                ['field_value' => $value]
            );
        }
    }

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
                return Carbon::parse($customfield->created_at)->format('d M Y');
            })
            ->editColumn('status', function ($customfield) {
                return $this->renderStatusColumn($customfield);
            })
            ->rawColumns(['actions', 'status', 'name']) // Add 'status' to raw columns
            ->make(true);
    }

    protected function renderActionsColumn($customfield)
    {


        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('CUSTOM_FIELDS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['customfields'],
            'id' => $customfield->id
        ])->render();
    }

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
<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\CategoryGroupTag;
use App\Models\City;
use App\Models\CRM\CRMLeads;
use App\Repositories\LeadsRepository;
use App\Repositories\ProjectRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class ProjectService
{

    use TenantFilter;
    use MediaTrait;
    use CategoryGroupTagsFilter;


    protected $projectRepository;

    private $tenantRoute;


    private $clientService;

    public function __construct(ProjectRepository $projectRepository, ClientService $clientService)
    {
        $this->projectRepository = $projectRepository;
        $this->clientService = $clientService;
        $this->tenantRoute = $this->getTenantRoute();
    }

    public function createLead(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $validGroupID = $this->checkIsValidCGTID($validatedData['group_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validSourceID = $this->checkIsValidCGTID($validatedData['source_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validStatusID = $this->checkIsValidCGTID($validatedData['status_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            if (!$validGroupID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Group ID ");
            }
            if (!$validSourceID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Source ID ");
            }
            if (!$validStatusID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Stage ID ");
            }

            $address = $this->createAddressIfProvided($validatedData);

            if (isset($address) && isset($address?->id)) {
                $validatedData['address_id'] = $address?->id;
            }

            // convert to boolean of checkbox
            if (isset($validatedData['is_converted']) && $validatedData['is_converted'] == 'on') {
                $validatedData['is_converted'] = '1';
                // create a client users now //todo::
                $isClientAlreadyExists = false;
                if (isset($validatedData['email'])) {
                    $isClientAlreadyExists = $this->clientService->findClientWithType($validatedData['email'], 'email');
                } else if (isset($validatedData['phone'])) {
                    $isClientAlreadyExists = $this->clientService->findClientWithType($validatedData['phone'], 'phone');
                }
                if (!$isClientAlreadyExists) {
                    $clientData = [
                        'type' => $validatedData['type'],
                        'company_name' => $validatedData['company_name'],
                        'first_name' => $validatedData['first_name'],
                        'last_name' => $validatedData['last_name'],
                        'primary_email' => $validatedData['email'],
                        'primary_phone' => $validatedData['phone'] ?? '9876543210',
                        'company_id' => Auth::user()->company_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ];

                    $this->clientService->createClient($clientData);
                }

            }
            $lead = CRMLeads::create($validatedData);


            // assign leads 
            $this->assignLeadsToUserIfProvided($validatedData, $lead);

            return [
                'lead' => $lead,
                // 'lead_address' => $lead_address
            ];
        });
    }
    public function updateLead(array $validatedData)
    {
        // Validate that company ID is provided
        if (empty($validatedData['id'])) {
            throw new \InvalidArgumentException('Lead ID is required for updating');
        }

        return DB::transaction(function () use ($validatedData) {

            $validGroupID = $this->checkIsValidCGTID($validatedData['group_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validSourceID = $this->checkIsValidCGTID($validatedData['source_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validStatusID = $this->checkIsValidCGTID($validatedData['status_id'], Auth::user()->company_id, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            if (!$validGroupID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Group ID ");
            }
            if (!$validSourceID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Source ID ");
            }
            if (!$validStatusID) {
                throw new \InvalidArgumentException("Failed to create lead beacuse invalid Stage ID ");
            }


            // Retrieve the existing client
            $lead = CRMLeads::findOrFail($validatedData['id']);

            unset($validatedData['id']);


            $address = $this->updateUserAddress($lead, $validatedData, );

            if (isset($address) && isset($address?->id)) {
                $validatedData['address_id'] = $address?->id;
            }

            // \Log::info('Is_converted found ' .$validatedData['is_converted'] );
            // convert to boolean of checkbox
            if (isset($validatedData['is_converted']) && $validatedData['is_converted'] == 'on') {
                $validatedData['is_converted'] = '1';

                // \Log::info('Is_converted yes');
                // create a client users now //todo::
                // first ck if there is no client already created with the given email or phone
                $isClientAlreadyExists = false;
                if (isset($validatedData['email'])) {
                    $isClientAlreadyExists = $this->clientService->findClientWithType($validatedData['email'], 'email');
                    // \Log::info('Client searching via email  '.$validatedData['email'].' =>  ' . $isClientAlreadyExists);
                } else if (isset($validatedData['phone'])) {
                    $isClientAlreadyExists = $this->clientService->findClientWithType($validatedData['phone'], 'phone');
                    // \Log::info('Client searching via phone  '.$validatedData['phone'].' =>  ' . $isClientAlreadyExists);
                }
                if (!$isClientAlreadyExists) {
                    // \Log::info('Client not found via email or  phone');
                    $clientData = [
                        'type' => $validatedData['type'],
                        'company_name' => $validatedData['company_name'],
                        'first_name' => $validatedData['first_name'],
                        'last_name' => $validatedData['last_name'],
                        'primary_email' => $validatedData['email'],
                        'primary_phone' => $validatedData['phone'] ?? '9876543210',
                        'company_id' => Auth::user()->company_id,
                        'created_by' => Auth::id(),
                        'updated_by' => Auth::id(),
                    ];

                    $client = $this->clientService->createClient($clientData);
                    //    \Log::info('client created,', $client);
                } else {
                    // \Log::info('client found, ' . $isClientAlreadyExists);
                }


            }
            $lead->update($validatedData);


            // assign leads 
            $this->assignLeadsToUserIfProvided($validatedData, $lead);

            return [
                'lead' => $lead,
                // 'lead_address' => $lead_address
            ];
        });
    }


    private function assignLeadsToUserIfProvided(array $validatedData, CRMLeads $lead)
    {
        if (!empty($validatedData['assign_to']) && is_array($validatedData['assign_to'])) {
            // Retrieve current assignees from the database
            $existingAssignees = $lead->assignees()->pluck('lead_user.user_id')->sort()->values()->toArray();
            $newAssignees = collect($validatedData['assign_to'])->sort()->values()->toArray();
    
            // Skip if assignees are identical - NO NEED TO CREATE AUDIT
            if ($existingAssignees === $newAssignees) {
                return;
            }
    
            // Prepare data for pivot table
            $companyId = Auth::user()->company_id;
            $assignToData = collect($validatedData['assign_to'])->mapWithKeys(function ($userId) use ($companyId) {
                return [
                    $userId => [
                        'company_id' => $companyId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];
            })->toArray();
    
            // Sync assignments
            $lead->assignees()->sync($assignToData);
    
        } else {
            // Handle detachment of assignees
            $existingAssignees = $lead->assignees()->pluck('lead_user.user_id')->toArray();
    
            if (empty($existingAssignees)) {
                return; // No existing assignees, skip detachment and logging
            }
    
            $lead->assignees()->detach();
    
         
        }
    }
    



    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->projectRepository->getProjectsQuery($request);
        $query = $this->applyTenantFilter($query, 'projects');

        $module = PANEL_MODULES[$this->getPanelModule()]['projects'];

   

        return DataTables::of($query)
            ->addColumn('actions', function ($project) {
                return $this->renderActionsColumn($project);
            })
            ->editColumn('created_at', function ($project) {
                return formatDateTime($project?->created_at);
            })
            ->editColumn('title', function ($project) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $project->id) . "' target='_blank'>$project->title</a>";
            })
            ->editColumn('assign_to', function ($project) {
                return "<span class='badge badge-pill bg-" . $project->stage->color . "'>{$project->stage->name}</span>";
            })
            ->editColumn('status', function ($project) {
                return $this->renderStatusColumn($project);
            })
            ->rawColumns(['actions', 'title', 'name']) // Include any HTML columns
            ->make(true);
    }



    protected function renderActionsColumn($project)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'id' => $project->id
        ])->render();
    }


    protected function renderStatusColumn($project)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('PROJECTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['projects'],
            'id' => $project->id,
            'status' => [
                'current_status' => $project->status,
                'available_status' => CRM_STATUS_TYPES['PROJECTS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['PROJECTS']['BT_CLASSES'],
            ]
        ])->render();
    }

    public function getKanbanBoardStages($request)
    {
        $query = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

        $query = $this->applyTenantFilter($query);

        return $query->select(['id', 'name', 'color'])->get();

    }

    public function getKanbanLoad($request)
    {
        $query = $this->leadsRepository->getKanbanLoad($request);
        $query = $this->applyTenantFilter($query, 'leads');
        return $query->get()->groupBy('stage_name');
    }
}

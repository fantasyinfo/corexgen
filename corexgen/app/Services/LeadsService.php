<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\CategoryGroupTag;
use App\Models\City;
use App\Models\CRM\CRMLeads;
use App\Repositories\LeadsRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LeadsService
{


    use TenantFilter;
    use MediaTrait;
    use CategoryGroupTagsFilter;


    protected $leadsRepository;

    private $tenantRoute;


    private $clientService;

    public function __construct(LeadsRepository $leadsRepository, ClientService $clientService)
    {
        $this->leadsRepository = $leadsRepository;
        $this->clientService = $clientService;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     *create lead
     */
    public function createLead(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            if (Auth::check()) {
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

    /**
     *update lead
     */
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


    /**
     *assign leads to users 
     */
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


    /**
     * convert to customer client
     */

    public function convertToClient(array $validatedData): bool
    {
        $isClientAlreadyExists = false;

        if (isset($validatedData['email'])) {
            $isClientAlreadyExists = $this->clientService->findClientWithType($validatedData['email'], 'email');
        } elseif (isset($validatedData['phone'])) {
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

            // Attempt client creation
            $client = $this->clientService->createClient($clientData);

            // Return true if a client was created, false otherwise
            return $client ? true : false;
        }

        // If client already exists, return false
        return false;
    }

    /**
     * create address if provided 
     */
    private function createAddressIfProvided(array $data): ?Address
    {
        $requiredAddressFields = [
            'address_street_address',
            'address_country_id',
            'address_city_name',
            'address_pincode'
        ];

        if (!$this->hasAllAddressFields($data, $requiredAddressFields)) {
            return null;
        }

        $cityId = $this->findOrCreateCity($data['address_city_name'], $data['address_country_id']);

        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $cityId,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ]);
    }

    /**
     * find or create city
     */
    private function findOrCreateCity($cityName, $countryId)
    {
        $city = City::firstOrCreate(
            ['name' => $cityName, 'country_id' => $countryId],
            ['name' => $cityName, 'country_id' => $countryId]
        );

        return $city->id;
    }

    /**
     * update user address
     */
    private function updateUserAddress(CRMLeads $lead, array $data): ?Address
    {

        \Log::info('User', [$lead]);
        \Log::info('Data', [$data]);
        // Check if address fields are provided
        $requiredAddressFields = [
            'address_street_address',
            'address_country_id',
            'address_city_name',
            'address_pincode'
        ];

        if (!$this->hasAllAddressFields($data, $requiredAddressFields)) {
            return null;
        }

        $cityId = $this->findOrCreateCity($data['address_city_name'], $data['address_country_id']);
        // If company already has an address, update it
        if ($lead->address_id) {

            $address = Address::findOrFail($lead->address_id);
            $address->update([
                'street_address' => $data['address_street_address'],
                'postal_code' => $data['address_pincode'],
                'city_id' => $cityId,
                'country_id' => $data['address_country_id'],
            ]);
            return $address;
        }

        // If no existing address, create a new one
        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $cityId,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ]);
    }

    /**
     * validate has address fields
     */
    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty($data[$field])
        );
    }


    /**
     * get leads with groups
     */
    public function getLeadsGroups()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }
    /**
     * get leads with sources
     */
    public function getLeadsSources()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }
    /**
     *get leads with status/stages
     */
    public function getLeadsStatus()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }


    /**
     * get leads by user
     */
    public function getLeadsByUser(int $user_id)
    {
        // Get leads assigned to the given user
        $leads = CRMLeads::with(['assignedBy', 'stage'])->whereHas('assignees', function ($query) use ($user_id) {
            $query->where('user_id', $user_id);
        })->with('assignees')->get();

        // Apply tenant filter (ensure this function modifies or filters the results as intended)
        return $this->applyTenantFilter($leads);
    }



    /**
     * get dt tbl response of leads lists
     */
    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->leadsRepository->getLeadsQuery($request);
        $query = $this->applyTenantFilter($query, 'leads');

        $module = PANEL_MODULES[$this->getPanelModule()]['leads'];
        $umodule = PANEL_MODULES[$this->getPanelModule()]['users'];

        $stages = $this->getLeadsStatus();

        return DataTables::of($query)
            ->addColumn('actions', function ($lead) {
                return $this->renderActionsColumn($lead);
            })
            ->editColumn('created_at', function ($lead) {
                return formatDateTime($lead?->created_at);
            })
            ->editColumn('title', function ($lead) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $lead->id) . "' target='_blank'>$lead->title</a>";
            })
            ->editColumn('group', function ($lead) {
                return "<span class='badge badge-pill bg-" . $lead?->group?->color . "'>{$lead?->group?->name}</span>";
            })
            ->editColumn('source', function ($lead) {
                return "<span class='badge badge-pill bg-" . $lead?->source?->color . "'>{$lead?->source?->name}</span>";
            })
            ->editColumn('stage', function ($lead) use ($stages) {
                // return "<span class='badge badge-pill bg-" . $lead->stage->color . "'>{$lead->stage->name}</span>";
                return $this->renderStageColumn($lead, $stages);
            })
            ->editColumn('assign_to', function ($lead) use ($umodule) {
                $assign_to = "";
                foreach ($lead->assignees as $user) {
                    $assign_to .= '<a href="' . route($this->tenantRoute . $umodule . '.view', ['id' => $user->id]) . '">';
                    $assign_to .= '<img src="' . asset(
                        'storage/' . ($user->profile_photo_path ?? 'avatars/default.webp')
                    ) . '" alt="' . $user->name . '" title="' . $user->name . '" style="width:40px; height:40px; border-radius:50%;" />';
                    $assign_to .= '</a>';
                }
                return $assign_to;
            })


            // ->editColumn('name', function ($lead) use ($module) {
            //     $fullName = trim("{$lead->title} {$lead->first_name} {$lead->middle_name} {$lead->last_name}");
            //     return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $lead->id) . "' target='_blank'>$fullName</a>";
            // })
            ->editColumn('address', function ($lead) {
                if (isset($lead->address)) {
                    return "{$lead->address->street_address}, {$lead->address->city->name} {$lead->address->country->name}, Postal: {$lead->address->postal_code}";
                }
                return 'N/A';
            })
            // ->editColumn('status', function ($lead) {
            //     return $this->renderStatusColumn($lead);
            // })
            ->rawColumns(['actions', 'assign_to', 'title', 'group', 'source', 'stage', 'name']) // Include any HTML columns
            ->make(true);
    }


    /**
     * render action col of leads dt
     */
    protected function renderActionsColumn($lead)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'id' => $lead->id
        ])->render();
    }

    /**
     * render stage col
     */
    protected function renderStageColumn($lead, $stages)
    {
        return View::make(getComponentsDirFilePath('dt-leads-stage'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'id' => $lead->id,
            'status' => [
                'current_status' => $lead->status_id,
                'available_status' => $stages
            ]
        ])->render();
    }

    /**
     * get all kanban stages of leads
     */
    public function getKanbanBoardStages($request)
    {
        $query = CategoryGroupTag::where('type', CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'])
            ->where('relation_type', CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

        $query = $this->applyTenantFilter($query);

        return $query->select(['id', 'name', 'color'])->get();

    }

    /**
     * get kanban board view by all stages
     */
    public function getKanbanLoad($request)
    {
        $query = $this->leadsRepository->getKanbanLoad($request);
        $query = $this->applyTenantFilter($query, 'leads');
        return $query->get()->groupBy('stage_name');
    }
}

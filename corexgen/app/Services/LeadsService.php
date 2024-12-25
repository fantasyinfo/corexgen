<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\City;
use App\Models\CRM\CRMLeads;
use App\Models\LeadUser;
use App\Repositories\LeadsRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

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
            $lead = CRMLeads::create($validatedData);
            //$lead_address = $this->createOrUpdateAddresses($validatedData, $lead);

            // assign leads 
            $this->assignLeadsToUserIfProvided($validatedData, $lead);

            return [
                'lead' => $lead,
                // 'lead_address' => $lead_address
            ];
        });
    }


    private function assignLeadsToUserIfProvided($validatedData, CRMLeads $lead)
    {
        if (isset($validatedData['assign_to']) && is_array($validatedData['assign_to'])) {
            // Prepare data for pivot table
            $assignToData = [];
            foreach ($validatedData['assign_to'] as $userId) {
                $assignToData[] = [
                    'lead_id' => $lead->id,
                    'user_id' => $userId,
                    'company_id' => Auth::user()->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            LeadUser::insert($assignToData);
        }
    }
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

    private function findOrCreateCity($cityName, $countryId)
    {
        $city = City::firstOrCreate(
            ['name' => $cityName, 'country_id' => $countryId],
            ['name' => $cityName, 'country_id' => $countryId]
        );

        return $city->id;
    }
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

    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty($data[$field])
        );
    }



    public function getLeadsGroups()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }
    public function getLeadsSources()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }
    public function getLeadsStatus()
    {
        $leadsGroups = $this->getCategoryGroupTags(CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);
        $leadsGroups = $this->applyTenantFilter($leadsGroups);
        return $leadsGroups->get();
    }


    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->leadsRepository->getLeadsQuery($request);
        $query = $this->applyTenantFilter($query, 'leads');

        $module = PANEL_MODULES[$this->getPanelModule()]['leads'];

        return DataTables::of($query)
            ->addColumn('actions', function ($lead) {
                return $this->renderActionsColumn($lead);
            })
            ->editColumn('created_at', function ($lead) {
                return Carbon::parse($lead->created_at)->format('d M Y');
            })
            ->editColumn('group', function ($lead) {
                return "<span style='color:" . $lead->group->color . ";'>{$lead->group->name}</span>";
            })
            ->editColumn('source', function ($lead) {
                return "<span style='color:" . $lead->source->color . ";'>{$lead->source->name}</span>";
            })
            ->editColumn('stage', function ($lead) {
                return "<span style='color:" . $lead->stage->color . ";'>{$lead->stage->name}</span>";
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
            ->rawColumns(['actions','group','source','stage',  'name']) // Include any HTML columns
            ->make(true);
    }



    protected function renderActionsColumn($lead)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'id' => $lead->id
        ])->render();
    }

    protected function renderStatusColumn($lead)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('LEADS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['leads'],
            'id' => $lead->id,
            'status' => [
                'current_status' => $lead->status,
                'available_status' => CRM_STATUS_TYPES['LEADS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['LEADS']['BT_CLASSES'],
            ]
        ])->render();
    }
}

<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\City;
use App\Models\CRM\CRMLeads;
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
                $assignToData[$userId] = [
                    'company_id' => Auth::user()->company_id, // Replace with appropriate company_id logic
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Attach users to the lead
            $lead->assignees()->attach($assignToData);
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

        $query = $this->leadsRepository->getClientsQuery($request);
        $query = $this->applyTenantFilter($query, 'clients');

        $module = PANEL_MODULES[$this->getPanelModule()]['clients'];

        return DataTables::of($query)
            ->addColumn('actions', function ($client) {
                return $this->renderActionsColumn($client);
            })
            ->editColumn('created_at', function ($client) {
                return Carbon::parse($client->created_at)->format('d M Y');
            })
            ->editColumn('category_name', function ($client) {
                return "<span style='color:" . $client->category_color . ";'>$client->category_name</span>";
            })
            // ->editColumn('name', function ($client) use ($module) {
            //     $fullName = trim("{$client->title} {$client->first_name} {$client->middle_name} {$client->last_name}");
            //     return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $client->id) . "' target='_blank'>$fullName</a>";
            // })
            ->editColumn('email', function ($client) {
                return isset($client->primary_email) ? $client->primary_email : 'N/A';
            })
            ->editColumn('phone', function ($client) {
                return isset($client->primary_phone) ? $client->primary_phone : 'N/A';
            })
            ->editColumn('address', function ($client) {
                if (isset($client->addresses[0])) {
                    $address = $client->addresses[0];
                    return "{$address['street_address']}, Postal: {$address['postal_code']}";
                }
                return 'N/A';
            })
            ->editColumn('status', function ($client) {
                return $this->renderStatusColumn($client);
            })
            ->rawColumns(['actions', 'category_name', 'name', 'status']) // Include any HTML columns
            ->make(true);
    }



    protected function renderActionsColumn($client)
    {


        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('CLIENTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'id' => $client->id
        ])->render();
    }

    protected function renderStatusColumn($client)
    {


        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('CLIENTS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['clients'],
            'id' => $client->id,
            'status' => [
                'current_status' => $client->status,
                'available_status' => CRM_STATUS_TYPES['CLIENTS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['CLIENTS']['BT_CLASSES'],
            ]
        ])->render();
    }
}

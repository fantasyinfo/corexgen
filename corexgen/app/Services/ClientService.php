<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\City;
use App\Models\ClientAddress;
use App\Models\CRM\CRMClients;
use App\Repositories\ClientRepository;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ClientService
{

    use TenantFilter;
    use MediaTrait;


    protected $clientRepository;

    private $tenantRoute;


    public function __construct(ClientRepository $clientRepository)
    {
        $this->clientRepository = $clientRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }

    public function createClient(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $client = CRMClients::create($validatedData);
            $client_address = $this->createAddressIfProvided($validatedData, $client);

            return [
                'client' => $client,
                'client_address' => $client_address
            ];
        });
    }


    public function createAddressIfProvided($validatedData, $client)
    {
        if (empty($validatedData['addresses'])) {
            return [];
        }

        $createdAddresses = [];
        foreach ($validatedData['addresses'] as $address) {
            // Step 1: Create or get the city ID
            $cityId = $this->findOrCreateCity($address['city'], $address['country_id']);

            // Step 2: Create the address
            $addressId = $this->createAddress($address, $cityId);

            // Step 3: Link the address to the client
            $this->linkClientAddress($client->id, $addressId, $address['type']);

            $createdAddresses[] = $addressId;
        }

        return $createdAddresses;
    }

    private function findOrCreateCity($cityName, $countryId)
    {
        $city = City::firstOrCreate(
            ['name' => $cityName, 'country_id' => $countryId],
            ['name' => $cityName, 'country_id' => $countryId]
        );

        return $city->id;
    }

    private function createAddress($addressData, $cityId)
    {
        $address = Address::create([
            'street_address' => $addressData['street_address'],
            'postal_code' => $addressData['pincode'],
            'city_id' => $cityId,
            'country_id' => $addressData['country_id'],
        ]);

        return $address->id;
    }

    private function linkClientAddress($clientId, $addressId, $type)
    {
        ClientAddress::create([
            'client_id' => $clientId,
            'address_id' => $addressId,
            'type' => $type,
        ]);
    }


    public function getDatatablesResponse($request)
    {
        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->clientRepository->getClientsQuery($request);
        $query = $this->applyTenantFilter($query, 'clients');

        $module = PANEL_MODULES[$this->getPanelModule()]['clients'];

        return DataTables::of($query)
            ->addColumn('actions', function ($client) {
                return $this->renderActionsColumn($client);
            })
            ->editColumn('created_at', function ($client) {
                return Carbon::parse($client->created_at)->format('d M Y');
            })
            ->editColumn('name', function ($client) use ($module) {
                $fullName = trim("{$client->title} {$client->first_name} {$client->middle_name} {$client->last_name}");
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $client->id) . "' target='_blank'>$fullName</a>";
            })
            ->editColumn('email', function ($client) {
                return isset($client->email[0]) ? $client->email[0] : 'N/A';
            })
            ->editColumn('phone', function ($client) {
                return isset($client->phone[0]) ? $client->phone[0] : 'N/A';
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
            ->rawColumns(['actions', 'name', 'status']) // Include any HTML columns
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
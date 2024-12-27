<?php

namespace App\Services;

use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\City;
use App\Models\ClientAddress;
use App\Models\CRM\CRMClients;
use App\Repositories\ClientRepository;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\MediaTrait;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

class ClientService
{

    use TenantFilter;
    use MediaTrait;
    use CategoryGroupTagsFilter;


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

            if (isset($validatedData['cgt_id'])) {
                $validCGTID = $this->checkIsValidCGTID($validatedData['cgt_id'], Auth::user()->company_id, 'categories', 'clients');


                if (!$validCGTID) {
                    throw new \InvalidArgumentException("Failed to create client beacuse invalid CGT ID ");
                }
            }




            $client = CRMClients::create($validatedData);
            $client_address = $this->createOrUpdateAddresses($validatedData, $client);

            return [
                'client' => $client,
                'client_address' => $client_address
            ];
        });
    }




    public function updateClient(array $validatedData)
    {
        // Validate that company ID is provided
        if (empty($validatedData['id'])) {
            throw new \InvalidArgumentException('Client ID is required for updating');
        }

        return DB::transaction(function () use ($validatedData) {

            $validCGTID = $this->checkIsValidCGTID($validatedData['cgt_id'], Auth::user()->company_id, 'categories', 'clients');

            if (!$validCGTID) {
                throw new \InvalidArgumentException("Failed to update client beacuse invalid CGT ID ");
            }


            // Retrieve the existing client
            $client = CRMClients::findOrFail($validatedData['id']);

            unset($validatedData['id']);
            $client->update($validatedData);

            $client_address = $this->createOrUpdateAddresses($validatedData, $client);

            return [
                'client' => $client,
                'client_address' => $client_address
            ];
        });
    }

    public function createOrUpdateAddresses($validatedData, $client)
    {
        if (empty($validatedData['addresses'])) {
            return [];
        }

        $processedAddresses = [];
        foreach ($validatedData['addresses'] as $address) {

            if (
                empty($address['city']) ||
                empty($address['country_id']) ||
                empty($address['type']) ||
                empty($address['street_address']) ||
                empty($address['pincode'])
            ) {
                continue; // Skip this address if any required field is missing
            }
            // Step 1: Create or get the city ID
            $cityId = $this->findOrCreateCity($address['city'], $address['country_id']);

            // Step 2: Create or update the address
            $addressId = $this->createOrUpdateAddress($address, $cityId);

            // Step 3: Link the address to the client
            $this->linkOrUpdateClientAddress($client->id, $addressId, $address['type']);

            $processedAddresses[] = $addressId;
        }

        return $processedAddresses;
    }

    private function findOrCreateCity($cityName, $countryId)
    {
        $city = City::firstOrCreate(
            ['name' => $cityName, 'country_id' => $countryId],
            ['name' => $cityName, 'country_id' => $countryId]
        );

        return $city->id;
    }

    private function createOrUpdateAddress($addressData, $cityId)
    {
        $address = Address::updateOrCreate(
            [
                'street_address' => $addressData['street_address'],
                'city_id' => $cityId,
                'country_id' => $addressData['country_id'],
            ],
            [
                'postal_code' => $addressData['pincode'],
                'country_id' => $addressData['country_id'],

            ]
        );

        return $address->id;
    }

    private function linkOrUpdateClientAddress($clientId, $addressId, $type)
    {
        ClientAddress::updateOrCreate(
            [
                'client_id' => $clientId,
                'address_id' => $addressId,
            ],
            [
                'type' => $type,
            ]
        );
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
            ->editColumn('category_name', function ($client) {
                return "<span class='badge badge-pill bg-" . $client->category_color . "'>$client->category_name</span>";
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


    public function findClientWithType($search, $type = 'email'): bool
    {
        if ($type == 'email') {
            return CRMClients::where('primary_email', $search)->exists();
        } else if ($type = 'phone') {
            return CRMClients::where('primary_phone', $search)->exists();
        }
        return CRMClients::where('primary_email', $search)->exists();
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
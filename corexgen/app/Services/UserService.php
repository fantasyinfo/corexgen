<?php

namespace App\Services;

use App\Models\City;
use App\Repositories\UserRepository;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Helpers\PermissionsHelper;
use App\Models\Address;
use App\Models\User;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{

    use TenantFilter;

    protected $userRepository;

    private $tenantRoute;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }


    /**
     *  create user
     */
    public function createUser(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            // create address 
            $address = $this->createAddressIfProvided($validatedData);
            // create user
            $user = $this->registerUser($validatedData, $address?->id);

            //

            return $user;
        });
    }


    /**
     *  update user
     */
    public function updateUser(array $validatedData)
    {
        return DB::transaction(function () use ($validatedData) {

            $query = $this->applyTenantFilter(User::where('id', $validatedData['id']));
            $user = $query->first();

            // update address 
            $address = $this->updateUserAddress($user, $validatedData);
            // update user
            $user->update(array_merge($validatedData, ['address_id' => $address?->id]));

            //

            return $user;
        });
    }



    /**
     *  register user
     */
    public function registerUser(array $validatedData, $address_id = null, )
    {
        $validatedData = array_merge($validatedData, [
            'is_tenant' => $validatedData['is_tenant'], // Explicitly pass or default this value
            'password' => Hash::make($validatedData['password']),
            'company_id' => $validatedData['company_id'], // Use provided or fallback
            'address_id' => $address_id,
        ]);

        return User::create($validatedData);
    }


    /**
     *  create address if provided
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
     *  find or create city
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
     *  update user address
     */
    private function updateUserAddress(User $user, array $data): ?Address
    {

        \Log::info('User', [$user]);
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
        if ($user->address_id) {

            $address = Address::findOrFail($user->address_id);
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
     *  has all address fields
     */
    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty($data[$field])
        );
    }

    /**
     *  get dt table response of users lists
     */

    public function getDatatablesResponse($request)
    {
        $query = $this->userRepository->getUsersQuery($request);


        $module = PANEL_MODULES[$this->getPanelModule()]['users'];
        $this->tenantRoute = $this->getTenantRoute();

        return DataTables::of($query)
            ->addColumn('actions', function ($user) {
                return $this->renderActionsColumn($user);
            })
            ->editColumn('name', function ($user) use ($module) {
                return "<a class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $user->id) . "' target='_blank'>$user->name</a>";
            })
            ->editColumn('created_at', fn($user) => $user?->created_at ? formatDateTime($user?->created_at) : '')
            ->editColumn('status', function ($user) {
                return $this->renderStatusColumn($user);
            })
            ->rawColumns(['actions', 'status', 'name'])
            ->make(true);
    }

    /**
     *  render action col
     */
    protected function renderActionsColumn($user)
    {
        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'id' => $user->id
        ])->render();
    }

    /**
     *  render status col
     */
    protected function renderStatusColumn($user)
    {
        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->getTenantRoute(),
            'permissions' => PermissionsHelper::getPermissionsArray('USERS'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['users'],
            'id' => $user->id,
            'status' => [
                'current_status' => $user->status,
                'available_status' => CRM_STATUS_TYPES['USERS']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['USERS']['BT_CLASSES'],
            ]
        ])->render();
    }
}
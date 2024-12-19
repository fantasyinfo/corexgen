<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Company;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\Subscription;
use App\Models\User;
use App\Traits\MediaTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PermissionsHelper;
use App\Models\City;
use App\Models\CRM\CRMPermissions;
use App\Models\CRM\CRMRolePermissions;
use App\Models\CRM\CRMSettings;
use App\Repositories\CompanyRepository;
use App\Traits\TenantFilter;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class CompanyService
{
    use TenantFilter;
    use MediaTrait;
    protected $companyRepository;

    private $tenantRoute;


    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
        $this->tenantRoute = $this->getTenantRoute();
    }




    public function createCompany(array $validatedData)
    {
        // temporary basic
        // create compnay acc with status of onbording
        // create user acc 

        return DB::transaction(function () use ($validatedData) {
            $address = $this->createAddressIfProvided($validatedData);

            $userFullName = $validatedData['name'];
            $company = Company::create(array_merge($validatedData, [
                'address_id' => $address?->id,
                'name' => $validatedData['cname'],
            ]));

            $companyAdminUser = $this->createCompanyUser($company, $validatedData, $userFullName);


            if($validatedData['from_admin'] == true){
                $this->updateCompanyPlanAndPermissions($company,$validatedData['plan_id']);
                $this->generateAllSettings($company->id);
                $company->update(['status' => CRM_STATUS_TYPES['COMPANIES']['STATUS']['ACTIVE']]);
            }
            return [
                'company' => $company,
                'company_admin' => $companyAdminUser
            ];
        });
    }





    public function generateAllSettings($companyid){
        $this->generateGeneralSettingsForCompany($companyid);
        $this->generateMailSettingsForCompany($companyid);
    }
    public function generateGeneralSettingsForCompany($companyid)
    {
        foreach (CRM_COMPANY_GENERAL_SETTINGS as $setting) {
            $media = null;

            // Handle image-specific logic
            if ($setting['input_type'] == 'image') {
                if ($setting['name'] === 'client_company_logo') {
                    $relativePath = $setting['value']; // Relative path for Storage
                    $absolutePath = storage_path('app/public/' . $relativePath); // Absolute path for file operations

                    if (Storage::disk('public')->exists($relativePath)) {
                        $media = $this->createMedia($relativePath, [
                            'folder' => 'logos',
                            'created_by' => Auth::id(), // Fixed admin ID
                            'updated_by' => Auth::id(),
                        ]);
                    } else {
                        \Log::warning("File not found for media creation: {$absolutePath}");
                    }
                }
            }

            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => $media->id ?? null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => $setting['is_tenant'],
                'company_id' => $companyid,
                'type' => 'General',
                'updated_by' => Auth::id(), // Fixed admin ID
                'created_by' => Auth::id(),
            ]);
        }
    }
    public function generateMailSettingsForCompany($companyid){
        foreach (CRM_COMPANY_MAIL_SETTINGS as $setting) {
            // Create CRM setting
            CRMSettings::create([
                'key' => $setting['key'],
                'value' => $setting['value'],
                'is_media_setting' => $setting['is_media_setting'],
                'media_id' => null,
                'input_type' => $setting['input_type'],
                'value_type' => $setting['value_type'],
                'name' => $setting['name'],
                'company_id' => $companyid,
                'placeholder' => $setting['placeholder'] ?? '',
                'is_tenant' => @$setting['is_tenant'] ?? false,
                'type' => 'Mail',
                'updated_by' => Auth::id(), // Fixed admin ID
                'created_by' => Auth::id(),
            ]);
        }
    }


    // public function createCompany(array $validatedData)
    // {
    //     // temporary basic
    //     // create compnay acc with status of onbording
    //     // create user acc 

    //     return DB::transaction(function () use ($validatedData) {
    //         $address = $this->createAddressIfProvided($validatedData);

    //         $userFullName = $validatedData['name'];
    //         $company = Company::create(array_merge($validatedData, [
    //             'address_id' => $address?->id,
    //             'name' => $validatedData['cname']
    //         ]));

    //         $companyAdminUser = $this->createCompanyUser($company, $validatedData, $userFullName);

    //         // todo:: add payment trasation 
    //         $paymentDetails = @$validatedData['payment_details'] ?? [];

    //         $this->createPaymentTransaction($validatedData['plan_id'], $company->id, $paymentDetails);
    //         // add subscription


    //         $this->givePermissionsToCompany($company, $companyAdminUser);

    //         // $this->createMenuItemsForCompanyPanel($validatedData['plan_id']);


    //         // todo:: add permissions to this user

    //         return $company;
    //     });
    // }



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

        $city = City::create([
            'name' => $data['address_city_name'],
            'country_id' => $data['address_country_id']
        ]);
        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $city->id,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ]);
    }

    private function createCompanyUser(Company $company, array $data, $userFullName)
    {
        unset($data['name']);
        return User::create([
            ...$data,
            'name' => $userFullName,
            'is_tenant' => false,
            'company_id' => $company->id,
            'status' => CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE'],
            'password' => Hash::make($data['password']),
        ]);
    }

    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty ($data[$field])
        );
    }

    public function createPaymentTransaction($planid, $companyid, $paymentDetails)
    {
        // Validate input parameters
        if (!$planid || !$companyid) {
            throw new \InvalidArgumentException('Plan ID and Company ID are required');
        }

        // Get plan details from plan id with error handling
        $plansDetails = Plans::findOrFail($planid);

        // Prepare payment transaction data with improved default handling
        $paymentTransactionData = [
            'plan_id' => $planid,
            'company_id' => $companyid,
            'amount' => $plansDetails->offer_price ?? 0,
            'currency' => $paymentDetails['currency'] ?? getSettingValue('Panel Currency Code'),
            'payment_gateway' => $paymentDetails['payment_gateway'] ?? 'COD',
            'payment_type' => $paymentDetails['payment_type'] ?? 'OFFLINE',
            'transaction_reference' => $paymentDetails['transaction_reference'] ?? null,
            'transaction_date' => now()
        ];

        // Get previous plan details if it's an upgrade
        $previousPlan = PaymentTransaction::where('company_id', $companyid)
            ->latest('created_at')
            ->first();

        // \Log::info('Previous Plan ID', $previousPlan->toArray());
        $previousPlanId = $previousPlan ? $previousPlan->plan_id : null;

        // Create payment transaction
        $paymentTransaction = PaymentTransaction::create($paymentTransactionData);

        // Calculate end date and next billing date based on billing cycle
        $startDate = now();
        $billingCycle = $plansDetails->billing_cycle;

        // Use Carbon for more robust date calculations
        $endDate = match ($billingCycle) {
            PLANS_BILLING_CYCLES['BILLINGS']['1 MONTH'] => $startDate->addMonth(),
            PLANS_BILLING_CYCLES['BILLINGS']['3 MONTHS'] => $startDate->addMonths(3),
            PLANS_BILLING_CYCLES['BILLINGS']['6 MONTHS'] => $startDate->addMonths(6),
            PLANS_BILLING_CYCLES['BILLINGS']['1 YEAR'] => $startDate->addYear(),
            PLANS_BILLING_CYCLES['BILLINGS']['UNLIMITED'] => null, // No end date
            default => $startDate->addMonth()
        };

        // Prepare subscription data
        $subscriptionData = [
            'plan_id' => $planid,
            'company_id' => $companyid,
            'payment_id' => $paymentTransaction->id,
            'start_date' => now(),
            'end_date' => $endDate,
            'next_billing_date' => $endDate, // Set next billing date to end date
            'billing_cycle' => $billingCycle,
            'previous_plan_id' => $previousPlanId,
            'upgrade_date' => $previousPlanId ? $startDate : null,
        ];

        // Create subscription
        $subscription = Subscription::create($subscriptionData);

        return [
            'payment_transaction' => $paymentTransaction,
            'subscription' => $subscription
        ];
    }



    public function givePermissionsToCompany($company, $companyAdminUser)
    {
        return;
        // Validate input
        if (!$company || !$company->plan_id) {
            \Log::error('Company or plan_id is missing', [
                'company' => $company,
            ]);
            return false;
        }

        $plan = Plans::where('id', $company->plan_id)->with('planFeatures')->first();

        // Check if plan exists
        if (!$plan) {
            \Log::warning('No plan found for company', [
                'company_id' => $company->id,
                'plan_id' => $company->plan_id
            ]);
            return false;
        }

        // Check if plan has features
        if (!$plan->planFeatures->isNotEmpty()) {
            \Log::info('No plan features found', [
                'company_id' => $company->id,
                'plan_id' => $company->plan_id
            ]);
            return false;
        }

        $permissionToPush = [];
        try {

            // first delete all existing
            CRMRolePermissions::where('company_id', $company->id)->where('role_id', null)->delete();


            foreach ($plan->planFeatures as $pf) {
                $featureName = strtoupper($pf->module_name);

                // Skip if feature is disabled
                if ($pf->value == 0) {
                    continue;
                }

                // Check if feature exists in permissions
                if (!isset(PermissionsHelper::$PERMISSIONS_IDS[$featureName])) {
                    \Log::warning('Unknown feature in plan', [
                        'feature_name' => $featureName,
                        'plan_id' => $company->plan_id
                    ]);
                    continue;
                }

                // Get permissions for this feature
                $permissionOFModule = PermissionsHelper::$PERMISSIONS_IDS[$featureName];

                $permissionKeys = array_keys($permissionOFModule);



                $pmi = CRMPermissions::where('permission_id', $permissionKeys[0])->get()->toArray();


                $pmItem = CRMPermissions::find($pmi[0]['parent_menu_id']);

                $permissionToPush[] = [
                    'company_id' => $company->id, // Note: changed from plan_id to company->id
                    'role_id' => null,
                    'permission_id' => $pmItem->permission_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];



                foreach ($permissionKeys as $p) {
                    $permissionToPush[] = [
                        'company_id' => $company->id, // Note: changed from plan_id to company->id
                        'role_id' => null,
                        'permission_id' => $p,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }
            }

            // Bulk insert with chunk to handle large datasets
            if (!empty($permissionToPush)) {
                $chunks = array_chunk($permissionToPush, 100);
                foreach ($chunks as $chunk) {
                    CRMRolePermissions::insert($chunk);
                }
            }

            $this->addDefaultFeatuersToCompany($company);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error assigning permissions', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }


    public function addDefaultFeatuersToCompany($company)
    {
        $permissionToPush = [];
        foreach (PermissionsHelper::defaultFeatuers() as $featureName) {
            // Get permissions for this feature
            $permissionOFModule = PermissionsHelper::$PERMISSIONS_IDS[$featureName];

            $permissionKeys = array_keys($permissionOFModule);



            $pmi = CRMPermissions::where('permission_id', $permissionKeys[0])->get()->toArray();


            $pmItem = CRMPermissions::find($pmi[0]['parent_menu_id']);

            $permissionToPush[] = [
                'company_id' => $company->id, // Note: changed from plan_id to company->id
                'role_id' => null,
                'permission_id' => $pmItem->permission_id,
                'created_at' => now(),
                'updated_at' => now()
            ];



            foreach ($permissionKeys as $p) {
                $permissionToPush[] = [
                    'company_id' => $company->id, // Note: changed from plan_id to company->id
                    'role_id' => null,
                    'permission_id' => $p,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }
        // Bulk insert with chunk to handle large datasets
        if (!empty($permissionToPush)) {
            $chunks = array_chunk($permissionToPush, 100);
            foreach ($chunks as $chunk) {
                CRMRolePermissions::insert($chunk);
            }
        }
        return true;
    }



    public function createMenuItemsForCompanyPanel($planId)
    {
        return;

        $plansFeatures = Plans::with('planFeatures')->findOrFail($planId);

        foreach (CRM_MENU_ITEMS_COMPANY as $category => $menuData) {
            // Determine if menu should be created
            $shouldCreateMenu =
                (isset($menuData['is_default']) && $menuData['is_default']) ||
                $this->isMenuAllowedByPlanFeatures($plansFeatures, $menuData);

            if ($shouldCreateMenu) {
                $this->insertMenuWithChildren($category, $menuData);
            }
        }
    }

    private function isMenuAllowedByPlanFeatures($plansFeatures, $menuData)
    {
        if (!isset($menuData['permission_plan'])) {
            return false;
        }

        return $plansFeatures->planFeatures->some(function ($pf) use ($menuData) {
            return
                strtoupper($pf->module_name) == $menuData['permission_plan'] &&
                $pf->value != 0;
        });
    }

    private function insertMenuWithChildren($category, $menuData)
    {
        DB::beginTransaction();
        try {
            // Check if parent menu already exists
            $existingParentMenu = DB::table('crm_menu')
                ->where('menu_name', $category)
                ->where('panel_type', PANEL_TYPES['COMPANY_PANEL'])
                ->first();

            $parentMenuId = $existingParentMenu
                ? $existingParentMenu->id
                : DB::table('crm_menu')->insertGetId([
                    'menu_name' => $category,
                    'menu_url' => '',
                    'parent_menu' => '1',
                    'parent_menu_id' => null,
                    'menu_icon' => $menuData['menu_icon'],
                    'permission_id' => $menuData['permission_id'],
                    'panel_type' => PANEL_TYPES['COMPANY_PANEL'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $childMenus = collect($menuData['children'])->map(function ($childMenuData, $menuName) use ($parentMenuId) {
                // Check if child menu already exists
                $existingChildMenu = DB::table('crm_menu')
                    ->where('menu_name', $menuName)
                    ->where('parent_menu_id', $parentMenuId)
                    ->first();

                if ($existingChildMenu) {
                    return null; // Skip existing child menus
                }

                return [
                    'menu_name' => $menuName,
                    'menu_url' => $childMenuData['menu_url'],
                    'parent_menu' => '2',
                    'parent_menu_id' => $parentMenuId,
                    'menu_icon' => $childMenuData['menu_icon'],
                    'permission_id' => $childMenuData['permission_id'],
                    'panel_type' => PANEL_TYPES['COMPANY_PANEL'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
                ->filter() // Remove null entries
                ->toArray();

            if (!empty($childMenus)) {
                DB::table('crm_menu')->insert($childMenus);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Menu creation failed: ' . $e->getMessage());
            throw $e;
        }
    }



    // company update 
    public function updateCompany(array $validatedData)
    {
        // dd($validatedData);
        // Validate that company ID is provided
        if (empty($validatedData['id'])) {
            throw new \InvalidArgumentException('Company ID is required for updating');
        }

        return DB::transaction(function () use ($validatedData) {
            // Retrieve the existing company
            $company = Company::findOrFail($validatedData['id']);

            $userFullName = $validatedData['name'];

            // Update company basic details
            $company->fill(collect($validatedData)->except([
                'id',
                'address_street_address',
                'address_country_id',
                'address_city_id',
                'address_pincode',
                'plan_id'
            ])
                ->merge(['name' => $validatedData['cname']])
                ->toArray());

            // Handle address update
            $address = $this->updateCompanyAddress($company, $validatedData);

            // Update address_id if a new address was created
            if ($address) {
                $company->address_id = $address->id;
            }

            // update user

            $userC = User::where('company_id', $company->id)->where('role_id', null)->where('is_tenant', '0')->first();



            if ($userC) {

                $userC->name = $userFullName;
                $userC->save(); // Changed from update() to save()

            }


            // Handle plan and permission update if plan_id is provided
            if (!empty($validatedData['plan_id']) && $validatedData['plan_id'] != $company->plan_id) {
                $this->updateCompanyPlanAndPermissions($company, $validatedData['plan_id']);
            }

            // Save company updates
            $company->save();

            return $company;
        });
    }

    private function updateCompanyAddress(Company $company, array $data): ?Address
    {
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

        $city = City::where('name', $data['address_city_name'])->where('country_id', $data['address_country_id'])->first();
        if (!$city) {
            $city = City::create([
                'name' => $data['address_city_name'],
                'country_id' => $data['address_country_id']
            ]);
        }
        // If company already has an address, update it
        if ($company->address_id) {

            $address = Address::findOrFail($company->address_id);
            $address->update([
                'street_address' => $data['address_street_address'],
                'postal_code' => $data['address_pincode'],
                'city_id' => $city->id,
                'country_id' => $data['address_country_id'],
            ]);
            return $address;
        }

        // If no existing address, create a new one
        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $city->id,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ]);
    }

    private function updateCompanyPlanAndPermissions(Company $company, $newPlanId)
    {
 
        // Create a new payment transaction for the new plan
        $paymentTransactionResult = $this->createPaymentTransaction($newPlanId, $company->id, []);

        // Update company plan
        $company->plan_id = $newPlanId;

        // Remove existing permissions
        CRMRolePermissions::where('company_id', $company->id)->delete();

        // Get company admin user (assuming first user)
        $companyAdminUser = User::where('company_id', $company->id)->first();

        // Reassign permissions based on new plan
        $this->givePermissionsToCompany($company, $companyAdminUser);

        // Optionally, create new menu items for the company panel
       $this->createMenuItemsForCompanyPanel($newPlanId);

        return $company;
    }


    /// index 

    public function getDatatablesResponse($request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        $query = $this->companyRepository->getCompanyQuery($request);

        // dd($query->get()->toArray());
        $module = PANEL_MODULES[$this->getPanelModule()]['companies'];

        return DataTables::of($query)
            ->addColumn('actions', function ($company) {
                return $this->renderActionsColumn($company);
            })
            ->editColumn('created_at', function ($company) {
                return Carbon::parse($company->created_at)->format('d M Y');
            })
            ->editColumn('name', function ($company) use ($module) {
                return "<a  class='dt-link' href='" . route($this->tenantRoute . $module . '.view', $company->id) . "' target='_blank'>$company->name</a>";
            })
            ->editColumn('status', function ($company) {
                return $this->renderStatusColumn($company);
            })
            ->editColumn('plan_name', function ($company) {
                return $company->plan_name;
            })
            ->editColumn('billing_cycle', function ($company) {
                return $company->billing_cycle;
            })
            ->editColumn('start_date', function ($company) {
                return Carbon::parse($company->start_date)->format('d M Y');
            })
            ->editColumn('end_date', function ($company) {
                return Carbon::parse($company->end_date)->format('d M Y');
            })
            ->editColumn('next_billing_date', function ($company) {
                return Carbon::parse($company->next_billing_date)->format('d M Y');
            })
            ->rawColumns(['plan_name', 'billing_cycle', 'start_date', 'end_date', 'next_billing_date', 'actions', 'status', 'name']) // Add 'status' to raw columns
            ->make(true);
    }


    protected function renderActionsColumn($company)
    {


        return View::make(getComponentsDirFilePath('dt-actions-buttons'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'id' => $company->id
        ])->render();
    }

    protected function renderStatusColumn($company)
    {


        return View::make(getComponentsDirFilePath('dt-status'), [
            'tenantRoute' => $this->tenantRoute,
            'permissions' => PermissionsHelper::getPermissionsArray('COMPANIES'),
            'module' => PANEL_MODULES[$this->getPanelModule()]['companies'],
            'id' => $company->id,
            'status' => [
                'current_status' => $company->status,
                'available_status' => CRM_STATUS_TYPES['COMPANIES']['STATUS'],
                'bt_class' => CRM_STATUS_TYPES['COMPANIES']['BT_CLASSES'],
            ]
        ])->render();
    }
}


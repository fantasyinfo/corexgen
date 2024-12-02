<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Company;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\PermissionsHelper;
use App\Models\CRM\CRMRolePermissions;

class CompanyService
{
    public function createCompany(array $validatedData)
    {

        return DB::transaction(function () use ($validatedData) {
            $address = $this->createAddressIfProvided($validatedData);

            $company = Company::create(array_merge($validatedData, [
                'address_id' => $address?->id,
            ]));

            $companyAdminUser = $this->createCompanyUser($company, $validatedData);

            // todo:: add payment trasation 
            $paymentDetails = [];

            $this->createPaymentTransaction($validatedData['plan_id'], $company->id, $paymentDetails);
            // add subscription


            $this->givePermissionsToCompany($company, $companyAdminUser);

            // $this->createMenuItemsForCompanyPanel($validatedData['plan_id']);

            
            // todo:: add permissions to this user

            return $company;
        });
    }

    private function createAddressIfProvided(array $data): ?Address
    {
        $requiredAddressFields = [
            'address_street_address',
            'address_country_id',
            'address_city_id',
            'address_pincode'
        ];

        if (!$this->hasAllAddressFields($data, $requiredAddressFields)) {
            return null;
        }

        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $data['address_city_id'],
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['COMPANY']['SHOW']['HOME'],
        ]);
    }

    private function createCompanyUser(Company $company, array $data)
    {
        return User::create([
            ...$data,
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
            'currency' => 'USD',
            'payment_gateway' => $paymentDetails['payment_gateway'] ?? 'COD',
            'payment_type' => $paymentDetails['payment_type'] ?? 'OFFLINE',
            'transaction_reference' => $paymentDetails['transaction_reference'] ?? null,
            'transaction_date' => now()
        ];

        // Get previous plan details if it's an upgrade
        $previousPlan = PaymentTransaction::where('company_id', $companyid)
            ->latest('created_at')
            ->first();
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
        } catch (\Exception $e) {
            \Log::error('Error assigning permissions', [
                'company_id' => $company->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }



    public function createMenuItemsForCompanyPanel($planId)
    {
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
            $parentMenuId = DB::table('crm_menu')->insertGetId([
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
            })->toArray();

            DB::table('crm_menu')->insert($childMenus);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Menu creation failed: ' . $e->getMessage());
            throw $e;
        }
    }


}


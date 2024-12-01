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

class CompanyService
{
    public function createCompany(array $validatedData)
    {

        return DB::transaction(function () use ($validatedData) {
            $address = $this->createAddressIfProvided($validatedData);

            $company = Company::create(array_merge($validatedData, [
                'address_id' => $address?->id,
            ]));

            $this->createCompanyUser($company, $validatedData);

            // todo:: add payment trasation 
            $paymentDetails = [];

            $this->createPaymentTransaction($validatedData['plan_id'], $company->id, $paymentDetails);
            // add subscription



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
        $endDate = match($billingCycle) {
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
}


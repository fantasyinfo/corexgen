<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Company;
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

            // todo:: add subscription
            // todo:: add payment methods
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
}


<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyOnboarding;
use App\Models\Country;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CompanyOnboardingController extends Controller
{
    public function showOnboardingForm()
    {
        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::firstOrCreate(
            ['company_id' => $company->id],
            ['status' => 'not_started']
        );

        $countries = Country::all();
        // Get timezones from PHP
        $timezones = DateTimeZone::listIdentifiers();
        return view('companyonbording.index', compact('company', 'onboarding', 'countries', 'timezones'));
    }

    public function saveAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_street_address' => 'nullable|string|max:255',
            'address_city_id' => 'nullable',
            'address_pincode' => 'nullable|string|max:10',
            'address_country_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();

        $fullAddress = implode(', ', [
            $request->address_street_address,
            $request->address_city_id,
            $request->address_pincode,
            $request->address_country_id
        ]);

        $onboarding->update([
            'address' => $fullAddress,
            'status' => 'address_captured'
        ]);

        // create address
        $validatedData = $validator->validated(); // Use validated() here
        $address = $this->createAddressIfProvided($validatedData);

        $company->address_id = $address?->id;

        return response()->json([
            'success' => true,
            'message' => 'Address saved successfully',
            'nextStep' => 'currency'
        ]);
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
    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty ($data[$field])
        );
    }


    public function saveCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'currency_code' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();

        $onboarding->update([
            'currency_code' => $request->currency_code,
            'currency_symbol' => $request->currency_symbol,
            'status' => 'currency_captured'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Currency details saved successfully',
            'nextStep' => 'timezone'
        ]);
    }

    public function saveTimezone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'timezone' => 'required|timezone'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();

        $onboarding->update([
            'timezone' => $request->timezone,
            'status' => 'timezone_captured'
        ]);

        // Check if payment is required
        $company = $company->fresh();
        if ($company->plan->requires_payment) {
            return response()->json([
                'success' => true,
                'message' => 'Timezone saved successfully',
                'nextStep' => 'payment'
            ]);
        }

        // If no payment required, complete onboarding
        $onboarding->update([
            'payment_completed' => true,
            'status' => 'completed'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'nextStep' => 'dashboard'
        ]);
    }

    public function processPayment(Request $request)
    {
        // Integrate with your payment gateway here
        // This is a placeholder implementation
        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();

        // Simulate payment processing
        $paymentSuccess = $this->simulatePaymentGateway($request);

        if ($paymentSuccess) {
            $onboarding->update([
                'payment_completed' => true,
                'status' => 'completed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful',
                'nextStep' => 'dashboard'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Payment failed'
        ], 400);
    }

    private function simulatePaymentGateway($request)
    {
        // Implement actual payment gateway integration
        // This is just a simulation
        return true;
    }
}
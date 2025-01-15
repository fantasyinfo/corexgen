<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\City;
use App\Models\PaymentGateway;
use App\Traits\MediaTrait;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\CompanyOnboarding;
use App\Models\Country;
use App\Models\CRM\CRMSettings;
use App\Models\Plans;
use App\Services\CompanyService;
use App\Services\PaymentGatewayFactory;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CompanyOnboardingController extends Controller
{

    use MediaTrait;

           /**
     * company onboarding form
     */
    public function showOnboardingForm()
    {
        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::firstOrCreate(
            ['company_id' => $company->id],
            ['status' => CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['NOT_STARTED']]
        );

        $countries = Country::all();
        $plans = Plans::where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();
        $payment_gateways = PaymentGateway::where('status', 'Active')->get();
        // Get timezones from PHP
        $timezones = DateTimeZone::listIdentifiers();
        return view('companyonbording.index', compact('company', 'onboarding', 'countries', 'timezones', 'plans', 'payment_gateways'));
    }

           /**
     * save company address
     */
    public function saveAddress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_street_address' => 'nullable|string|max:255',
            'address_city_name' => 'nullable',
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
            $request->address_city_name,
            $request->address_pincode,
            $request->address_country_id
        ]);

        $onboarding->update([
            'address' => $fullAddress,
            'status' => CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['ADDRESS_CAPTURED']
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


           /**
     * create company adress if provided
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

        $cityId =  $this->findOrCreateCity($data['address_city_name'], $data['address_country_id']);
     
        return Address::create([
            'street_address' => $data['address_street_address'],
            'postal_code' => $data['address_pincode'],
            'city_id' => $cityId,
            'country_id' => $data['address_country_id'],
            'address_type' => ADDRESS_TYPES['USER']['SHOW']['HOME'],
        ]);
    }

           /**
     * find or create city
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
     * validate all address fields are there
     */
    private function hasAllAddressFields(array $data, array $requiredFields): bool
    {
        return collect($requiredFields)->every(
            fn($field) =>
            !empty ($data[$field])
        );
    }


           /**
     * save company default currency
     */
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
            'status' => CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['CURRENCY_CAPTURED']
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Currency details saved successfully',
            'nextStep' => 'timezone'
        ]);
    }

           /**
     * save timezone of a company
     */
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
            'status' => CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['TIMEZONE_CAPTURED']
        ]);


        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'nextStep' => 'plan'
        ]);
    }

           /**
     * save company plans
     */
    public function savePlan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required|exists:plans,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $company = auth()->user()->company;
        $company->plan_id = $request->plan_id;
        $company->save();

        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();


        $onboarding->update([
            'plan_id' => $request->plan_id,
            'status' => CRM_STATUS_TYPES['COMPANIES_ONBORDING']['STATUS']['PLAN_CAPTURED']
        ]);

        // company settings generate

        app(CompanyService::class)->generateAllSettings($company->id);

        // if plan is free
        $planOfferPrice = Plans::find($request->plan_id);

        if ($planOfferPrice->offer_price <= 0) {

            $paymentDetails = [
                'payment_gateway' => 'COD',
                'payment_type' => 'OFFLINE',
                'transaction_reference' => json_encode([]),
                'transaction_id' => null,
                'amount' => 00,
                'currency' => 'USD', // tmp
                'company_id' => $company->id,
                'plan_id' => $planOfferPrice->id,
            ];


            // Capture the redirect URL from the method
            $redirectResponse = app(CompanyRegisterController::class)->storeCompnayAfterPaymentOnboading($paymentDetails);
            $redirectUrl = $redirectResponse->getTargetUrl(); // Extract the URL from the redirect response

            return response()->json([
                'success' => true,
                'nextStep' => 'complete',
                'redirectUrl' => $redirectUrl
            ]);
            // return app(CompanyRegisterController::class)->storeCompanyAfterPayment($paymentDetails);
            //return app(CompanyRegisterController::class)->storeCompnayAfterPaymentOnboading($paymentDetails);

        }

        return response()->json([
            'success' => true,
            'message' => 'Plan Captured successfully',
            'nextStep' => 'payment'
        ]);


    }

           /**
     * process the payment of a company plan
     */
    public function processPayment(Request $request, PaymentGatewayFactory $paymentGatewayFactory)
    {

        $validator = Validator::make($request->all(), [
            'gateway' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }


        // Integrate with your payment gateway here
        // This is a placeholder implementation
        $company = auth()->user()->company;
        $onboarding = CompanyOnboarding::where('company_id', $company->id)->first();

        Log::info('Onboarding ', ['onboarding' => $onboarding]);
        $plan = Plans::find($onboarding->plan_id);

        Log::info('Plan Details fetched ', ['plan' => $plan]);
        $validatedData = [
            'plan_id' => $plan->id,
            'plan_name' => $plan->name,
            'plan_price' => $plan->offer_price,
            'gateway' => $request->gateway
        ];


        try {
            // Validate required payment parameters
            $this->validatePaymentParameters($validatedData);

            // Get selected payment gateway
            $gateway = $validatedData['gateway'] ?? 'stripe';

            // Create payment gateway instance
            $paymentGateway = $paymentGatewayFactory->create($gateway);

            // Prepare payment details
            $paymentDetails = [
                'amount' => $validatedData['plan_price'],
                'description' => "Company Registration - {$validatedData['plan_name']} Plan",
                'currency' => getSettingValue('Panel Currency Code'),
                'metadata' => [
                    'plan_id' => $validatedData['plan_id'],
                    'is_company_registration' => true,
                    'company_id' => $company->id
                ]
            ];

            Log::info('Payment Details Passed', ['plan' => $paymentDetails]);
            // Initialize payment
            $paymentUrl = $paymentGateway->initialize($paymentDetails);

            // Redirect to payment gateway
            return response()->json([
                'success' => true,
                'paymentUrl' => $paymentUrl
            ]);

        } catch (\App\Exceptions\PaymentGatewayNotFoundException $e) {
            Log::error('Payment Gateway Error', ['gateway' => $e->getGateway()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    /**
     * Validate payment parameters
     * 
     * @param array $validatedData
     * @throws \Exception
     */
    private function validatePaymentParameters(array $validatedData)
    {
        $requiredFields = [
            'plan_id',
            'plan_name',
            'plan_price',
            'gateway'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($validatedData[$field])) {
                throw new \Exception("Missing required payment parameter: {$field}");
            }
        }
    }

           /**
     * simulate gateway
     */
    private function simulatePaymentGateway($request)
    {
        // Implement actual payment gateway integration
        // This is just a simulation
        return true;
    }

           /**
     * complete onboarding
     */
    public function completeOnboarding(Request $request)
    {
        // update compnay onbording status
        // create permission to compnay
        // redirect to the company dashboard
        prePrintR($request->all());

    }

}
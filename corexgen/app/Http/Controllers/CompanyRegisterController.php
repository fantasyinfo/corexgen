<?php

namespace App\Http\Controllers;

use App\Http\Requests\CRM\CompaniesRequest;
use App\Models\PaymentTransaction;
use App\Models\Plans;
use App\Models\User;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Payment;
use App\Repositories\CompanyRepository;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyRegisterController extends Controller
{
    //

    protected $companyRepository;
    protected $companyService;

    public function __construct(
        CompanyRepository $companyRepository,
        CompanyService $companyService
    ) {
        $this->companyRepository = $companyRepository;
        $this->companyService = $companyService;
    }



    public function register()
    {
        $plans = Plans::where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();
        return view('landing.register', ['plans' => $plans]);
    }

   
    public function initPaymentForCompnayRegistration(
        CompaniesRequest $request,
        PaymentGatewayFactory $paymentGatewayFactory
    ) {
        try {
            // Validate the request
            $validatedData = $request->validated();
            session(['pending_company_registration' => $validatedData]);

            $plan = Plans::find($validatedData['plan_id']);
            $validatedData['plan_name'] = $plan->name;
            $validatedData['plan_price'] = $plan->offer_price;
            $validatedData['currency'] = getSettingValue('Currency Code'); //todo:: change currecny as per tenant setting

            // Initiate payment process
            \Log::info('From REg ' . env('STRIPE_SECRET_KEY'));
            return $this->initiatePayment($validatedData, $paymentGatewayFactory);


        } catch (\Exception $e) {
            // Log the error
            Log::error('Payment Init Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Redirect back with error
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }



    public function storeCompanyAfterPayment(array $paymentData)
    {
        $validatedData = session('pending_company_registration');
    
        $validatedData['payment_details'] = $paymentData;
    
        $companyService = $this->companyService;
    
        try {
            $result = DB::transaction(function () use ($companyService, $validatedData) {
                // Create company
                $company = $companyService->createCompany($validatedData);
    
                // Find and login company owner
                $user = $this->findCompanyOwner($company);
    
                // Login user
                Auth::guard('web')->login($user);
    
                // Log successful registration
                Log::info('Company Registered Successfully', [
                    'company_id' => $company->id,
                    'user_id' => $user->id
                ]);
    
                // Return company and user instead of redirect
                return [
                    'company' => $company,
                    'user' => $user
                ];
            });
    
            // Redirect after successful transaction
            return redirect()->route(
                getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home'
            );
        } catch (\Exception $e) {
            // Log the error
            Log::error('Company Registration Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
    
            // Redirect back with error
            return redirect()->back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }



    /**
     * Initiate payment process
     * 
     * @param array $validatedData
     * @param PaymentGatewayFactory $paymentGatewayFactory
     * @return \Illuminate\Http\RedirectResponse
     */
    private function initiatePayment(array $validatedData, PaymentGatewayFactory $paymentGatewayFactory)
    {
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
                'currency' => getSettingValue('Currency Code'),
                'metadata' => [
                    'plan_id' => $validatedData['plan_id'],
                    'company_registration' => true
                ]
            ];

            // Initialize payment
            $paymentUrl = $paymentGateway->initialize($paymentDetails);

            // Redirect to payment gateway
            return redirect()->away($paymentUrl);

        } catch (\App\Exceptions\PaymentGatewayNotFoundException $e) {
            Log::error('Payment Gateway Error', ['gateway' => $e->getGateway()]);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    /**
     * Find company owner user
     * 
     * @param Company $company
     * @return User
     * @throws \Exception
     */
    private function findCompanyOwner($company)
    {
        $user = User::where('company_id', $company->id)
            ->where('role_id', null)
            ->where('is_tenant', 0)
            ->first();

        if (!$user) {
            throw new \Exception('Company owner user not found');
        }

        return $user;
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


}

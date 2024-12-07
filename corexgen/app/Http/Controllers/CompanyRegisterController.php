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
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyRegisterController extends Controller
{
    //

    public function register()
    {
        $plans = Plans::where('status', CRM_STATUS_TYPES['PLANS']['STATUS']['ACTIVE'])->get();
        return view('landing.register', ['plans' => $plans]);
    }

    // public function registerCompany(CompaniesRequest $request, CompanyService $companyService){

    //     // todo:: please capture the payment now its the plan is paid
    //     if(!$request->has('payment_success') ){
    //         return route('payment.initiate',['gateway' => $request->validated()['gateway']]);
    //     }

    //     // 'currency' => 'USD',
    //     // 'payment_gateway' => $paymentDetails['payment_gateway'] ?? 'COD',
    //     // 'payment_type' => $paymentDetails['payment_type'] ?? 'OFFLINE',
    //     // 'transaction_reference' => $paymentDetails['transaction_reference'] ?? null,

    //     // register the company
    //     $company = $companyService->createCompany($request->validated());

    //     // login user as a compnay owner

    //     $user = User::where('company_id', $company->id)
    //     ->where('role_id', null)
    //     ->where('is_tenant', 0)
    //     ->first();

    //     $guard = Auth::guard('web')->loginUsingId($user->id);


    //     // todo:: after register the compnay redirect to compnay panel
    //     return redirect()->route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home');
    // }

    public function registerCompany(
        CompaniesRequest $request, 
        CompanyService $companyService,
        PaymentGatewayFactory $paymentGatewayFactory
    ) {
        try {
            // Validate the request
            $validatedData = $request->validated();

            $plan = Plans::find($validatedData['plan_id']);
            $validatedData['plan_name'] = $plan->name;
            $validatedData['plan_price'] = $plan->offer_price;
            $validatedData['currency'] = 'USD';


    
            // Check if payment has been processed
            if (!$request->has('payment_success')) {
                // Initiate payment process
                \Log::info('From REg '.env('STRIPE_SECRET_KEY'));
                return $this->initiatePayment($validatedData, $paymentGatewayFactory);
            }
    
            // Verify payment before creating company
            $this->verifyPayment($validatedData, $paymentGatewayFactory);
    
            // Use database transaction for atomic operation
            return DB::transaction(function () use ($companyService, $validatedData) {
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
    
                // Redirect to company panel
                return redirect()->route(
                    getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.home'
                );
            });
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
                'currency' => $validatedData['currency'] ?? 'USD',
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
     * Verify payment before company creation
     * 
     * @param array $validatedData
     * @param PaymentGatewayFactory $paymentGatewayFactory
     * @throws \Exception
     */
    private function verifyPayment(array $validatedData, PaymentGatewayFactory $paymentGatewayFactory)
    {
        try {
            // Get selected payment gateway
            $gateway = $validatedData['gateway'] ?? 'stripe';
    
            // Create payment gateway instance
            $paymentGateway = $paymentGatewayFactory->create($gateway);
    
            // Verify payment details
            $paymentResult = $paymentGateway->processPayment([
                'transaction_id' => $validatedData['transaction_id'],
                'amount' => $validatedData['plan_price'],
                'currency' => $validatedData['currency'] ?? 'USD'
            ]);
    
            // Check payment status
            if ($paymentResult->status !== 'success') {
                throw new \Exception('Payment verification failed');
            }
    
            // Optionally, you can store payment details in database
            $this->storePaymentTransaction($paymentResult, $validatedData);
    
        } catch (\Exception $e) {
            Log::error('Payment Verification Failed', [
                'error' => $e->getMessage(),
                'gateway' => $gateway
            ]);
            throw $e;
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
    
    /**
     * Store payment transaction details
     * 
     * @param PaymentResultDTO $paymentResult
     * @param array $registrationData
     */
    private function storePaymentTransaction($paymentResult, array $registrationData)
    {
        // Implement logic to store payment transaction in database
        // This could be a separate PaymentTransaction model
        PaymentTransaction::create([
            'gateway' => $registrationData['gateway'],
            'transaction_id' => $paymentResult->transactionId,
            'amount' => $paymentResult->amount,
            'currency' => $paymentResult->currency,
            'status' => $paymentResult->status,
            'metadata' => $paymentResult->metadata
        ]);
    }
}

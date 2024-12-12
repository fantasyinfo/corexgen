<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


// payment gateway routes handling for init url, success, cancel
class PaymentGatewayController extends Controller
{
    private PaymentGatewayFactory $gatewayFactory;

    public function __construct(PaymentGatewayFactory $gatewayFactory)
    {
        $this->gatewayFactory = $gatewayFactory;
    }

    /**
     * Initiate payment process
     * 
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function initiate(Request $request, string $gateway = 'stripe')
    {
        try {
            // Validate payment details
            $validatedData = $request->validate([
                'plan_id' => 'required|exists:plans,id',
                'amount' => 'required|numeric|min:0',
                // Add more validation as needed
            ]);

            // Get payment gateway
            $paymentGateway = $this->gatewayFactory->create($gateway);

            // Initialize payment
            $paymentUrl = $paymentGateway->initialize([
                'amount' => $validatedData['amount'],
                'description' => "Plan Subscription",
                // Add more payment details
            ]);

            // Redirect to payment gateway
            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            Log::error('Payment Initiation Failed: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Payment initialization failed');
        }
    }

    /**
     * Handle successful payment
     * 
     * @param Request $request
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleSuccess(Request $request, string $gateway)
    {
        try {
            $paymentGateway = $this->gatewayFactory->create($gateway);

            // Process payment and create user account
            return $paymentGateway->processPayment($request->all());

        } catch (\Exception $e) {
            Log::error('Payment Success Handling Failed: PaymentGatewayController::handleSuccess ' . $e->getMessage());

            return redirect()->route('compnay.landing-register')
                ->with('error', 'Unable to complete your registration');
        }
    }



    /**
     * Handle payment cancellation
     * 
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCancel(string $gateway)
    {
        return redirect()->route('home')->with('warning', 'Payment was cancelled from gateway: ' . $gateway);
    }
    
}
<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        
            $result = $paymentGateway->processPayment($request->all());

            dd($result);

            if ($result->status === 'success') {
                // Create user account
                // Log the transaction
                // Send welcome email

                return redirect()->route('user.dashboard')
                    ->with('success', 'Payment successful');
            }

            throw new \Exception('Payment processing failed');

        } catch (\Exception $e) {
            Log::error('Payment Success Handling Failed: ' . $e->getMessage());

            return redirect()->route('landing')
                ->with('error', 'Unable to complete your registration');
        }
    }


    //     public function handleSuccess(Request $request)
// {
//     // Enable logging for comprehensive debugging
//     Log::channel('stripe_payment')->info('Stripe Success Callback', [
//         'all_request_data' => $request->all(),
//         'query_params' => $request->query(),
//         'request_method' => $request->method(),
//         'headers' => $request->headers->all(),
//     ]);

    //     try {
//         // Stripe requires verification of the session
//         \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

    //         // If you're using Stripe Checkout, retrieve the session
//         $sessionId = $request->query('session_id'); // Or however you're passing the session ID

    //         if (!$sessionId) {
//             Log::channel('stripe_payment')->error('No Stripe Session ID found');
//             return redirect()->route('payment.failed')
//                 ->with('error', 'Invalid payment session');
//         }

    //         try {
//             $session = \Stripe\Checkout\Session::retrieve($sessionId);

    //             // Log the full session details
//             Log::channel('stripe_payment')->info('Stripe Session Details', [
//                 'session' => $session->toArray()
//             ]);

    //             // Verify payment status
//             if ($session->payment_status !== 'paid') {
//                 Log::channel('stripe_payment')->warning('Payment not completed', [
//                     'status' => $session->payment_status
//                 ]);
//                 return redirect()->route('payment.failed')
//                     ->with('error', 'Payment not completed');
//             }

    //             // Extract payment details
//             $paymentDetails = [
//                 'transaction_id' => $session->id,
//                 'amount' => $session->amount_total / 100, // Stripe stores in cents
//                 'currency' => $session->currency,
//                 'customer_email' => $session->customer_details->email ?? null,
//                 'payment_status' => $session->payment_status,
//             ];

    //             // Store or process payment
//             // Call your payment processing method
//             return $this->processSuccessfulPayment($paymentDetails);

    //         } catch (\Stripe\Exception\ApiErrorException $e) {
//             Log::channel('stripe_payment')->error('Stripe API Error', [
//                 'message' => $e->getMessage(),
//                 'trace' => $e->getTraceAsString()
//             ]);

    //             return redirect()->route('payment.failed')
//                 ->with('error', 'Payment verification failed: ' . $e->getMessage());
//         }

    //     } catch (\Exception $e) {
//         Log::channel('stripe_payment')->error('Payment Handling Error', [
//             'message' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

    //         return redirect()->route('payment.failed')
//             ->with('error', 'An unexpected error occurred');
//     }
// }


    /**
     * Handle payment cancellation
     * 
     * @param string $gateway
     * @return \Illuminate\Http\RedirectResponse
     */
    public function handleCancel(string $gateway)
    {
        return redirect()->route('landing')
            ->with('warning', 'Payment was cancelled');
    }
}
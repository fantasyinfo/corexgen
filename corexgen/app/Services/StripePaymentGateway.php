<?php

namespace App\Services;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Http\Controllers\CompanyRegisterController;
use App\Http\Controllers\PaymentGatewayController;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripePaymentGateway implements PaymentGatewayInterface
{


    /**
     *  get stripe secret key
     */
    public function getStripeSecretKey($key = null, $value = null, $mode = null)
    {

        $stripe = PaymentGateway::where('name', 'Stripe')->first();
        if ($stripe) {

            if ($key != null && $value != null && $mode != null) {
                return $value;
            } else {
                return $stripe->config_value;
            }


        }
        return null;
    }



    /**
     *  initilize the gateway
     */
    public function initialize(array $paymentDetails)
    {
        if (isset($paymentDetails['config_key']) && isset($paymentDetails['config_value']) && isset($paymentDetails['mode'])) {
            $accessToken = $this->getStripeSecretKey(
                $paymentDetails['config_key'],
                $paymentDetails['config_value'],
                $paymentDetails['mode']
            );
        } else {
            $accessToken = $this->getStripeSecretKey();
        }

        \Log::info('Stripe API Key from StripePaymentGateway Service: ', [$this->getStripeSecretKey()]);
        Stripe::setApiKey($this->getStripeSecretKey()); // 

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $paymentDetails['currency'] ?? 'usd',
                        'unit_amount' => $paymentDetails['amount'] * 100,
                        'product_data' => [
                            'name' => $paymentDetails['description'] ?? 'Subscription',
                        ],
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('payment.success', ['gateway' => 'stripe']) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('payment.cancel', ['gateway' => 'stripe']),
            'metadata' => $paymentDetails['metadata'],
        ]);

        return $session->url;
    }

    /**
     *  process payment
     */
    public function processPayment($paymentData)
    {

        // \Log::info('payment Data outside.', $paymentData);
        Stripe::setApiKey($this->getStripeSecretKey()); // todo:// get these from tenenat payment settings 

        $sessionId = $paymentData['session_id'];

        if (!$sessionId) {
            \Log::error('Payment failed.', $paymentData);
            return redirect()->route('payment.failed')->with('error', 'Invalid payment session');
        }


        $session = Session::retrieve($sessionId);

        // dd($session);

        // Log the full session details

        // \Log::info('Payment info.', $session->toArray());

        // Verify payment status
        if ($session->payment_status !== 'paid') {
            \Log::error('Payment failed. Payment Status is not paid. ', $paymentData);
            return redirect()->route('payment.failed')
                ->with('error', 'Payment not completed');
        }

        $paymentDetails = [
            'payment_gateway' => 'stripe',
            'payment_type' => 'ONLINE',
            'transaction_reference' => $session->customer_details->toJSON(),
            'transaction_id' => $session->payment_intent,
            'amount' => $session->amount_total / 100,
            'currency' => $session->currency,
            'company_id' => $session?->metadata?->company_id,
            'plan_id' => $session?->metadata?->plan_id,
            'invoice_uuid' => $session?->metadata?->invoice_uuid,
            'response' => $session->toArray(),
            'is_plan_upgrade' => filter_var($session?->metadata?->is_plan_upgrade, FILTER_VALIDATE_BOOLEAN),
            'is_invoice_paying' => filter_var($session?->metadata?->is_invoice_paying, FILTER_VALIDATE_BOOLEAN),
            'is_company_registration' => filter_var($session?->metadata?->is_company_registration, FILTER_VALIDATE_BOOLEAN),
        ];

        return app(PaymentGatewayController::class)->handlePaymentGatewaysSuccessResponse($paymentDetails);



    }

    /**
     *  handle webhooks
     */
    public function handleWebhook(Request $request)
    {
        // Implement Stripe webhook handling
    }

    /**
     *  handle refund
     */
    public function refund(string $transactionId, float $amount)
    {
        // Implement refund logic
    }
}
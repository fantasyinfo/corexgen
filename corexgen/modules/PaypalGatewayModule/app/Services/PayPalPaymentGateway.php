<?php 

namespace Modules\PaypalGatewayModule\App\Services;

use App\Contracts\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\CompanyRegisterController;
use App\Models\PaymentGateway;

class PayPalPaymentGateway implements PaymentGatewayInterface {

    public function getPaypalSecretKey()
    {
        $stripe = PaymentGateway::where('name', 'Stripe')->first();
        if ($stripe) {
            return $stripe->config_value;
        }
        return null;
    }

    public function initialize(array $paymentDetails)
    {
        \Log::info('Initializing PayPal payment with details:', $paymentDetails);
    
        $this->paypal->getAccessToken();
    
        // Prepare metadata
        $metadata = $paymentDetails['metadata'] ?? [];
    
        $order = $this->paypal->createOrder([
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $paymentDetails['currency'] ?? 'USD',
                        'value' => number_format($paymentDetails['amount'], 2, '.', ''),
                    ],
                    'description' => $paymentDetails['description'] ?? 'Subscription',
                    'custom_id' => json_encode([
                        'company_id' => $metadata['company_id'] ?? null,
                        'plan_id' => $metadata['plan_id'] ?? null,
                    ]), // Add company_id and plan_id as custom metadata
                ]
            ],
            'application_context' => [
                'return_url' => route('payment.success', ['gateway' => 'paypal']),
                'cancel_url' => route('payment.cancel', ['gateway' => 'paypal']),
            ],
        ]);
    
        return $order['links'][1]['href']; // URL for PayPal Checkout
    }
    

    public function processPayment($paymentData)
{
    \Log::info('Processing PayPal payment:', $paymentData);

    if (!isset($paymentData['order_id'])) {
        \Log::error('Payment failed. Missing order ID.', $paymentData);
        return redirect()->route('payment.failed')->with('error', 'Invalid payment session');
    }

    $this->paypal->getAccessToken();

    // Capture the order
    $response = $this->paypal->capturePaymentOrder($paymentData['order_id']);

    if ($response['status'] !== 'COMPLETED') {
        \Log::error('Payment failed. Payment status is not completed.', $response);
        return redirect()->route('payment.failed')->with('error', 'Payment not completed');
    }

    // Retrieve metadata
    $customMetadata = json_decode($response['purchase_units'][0]['custom_id'] ?? '{}', true);

    $paymentDetails = [
        'payment_gateway' => 'paypal',
        'payment_type' => 'ONLINE',
        'transaction_reference' => json_encode($response['purchase_units'][0]['payee']),
        'transaction_id' => $response['id'],
        'amount' => $response['purchase_units'][0]['amount']['value'],
        'currency' => $response['purchase_units'][0]['amount']['currency_code'],
        'company_id' => $customMetadata['company_id'] ?? null, // Retrieved company_id
        'plan_id' => $customMetadata['plan_id'] ?? null,       // Retrieved plan_id
    ];

    \Log::info('Payment processed successfully with metadata:', $customMetadata);

    if (isset($session?->metadata?->is_plan_upgrade) && $session?->metadata?->is_plan_upgrade) {
        // upgrade the plan
        return app(CompanyRegisterController::class)->upgradePlanForCompany($paymentDetails);
    }
    return app(CompanyRegisterController::class)->storeCompnayAfterPaymentOnboading($paymentDetails);
}


    public function handleWebhook(Request $request)
    {
        // Implement Stripe webhook handling
    }

    public function refund(string $transactionId, float $amount)
    {
        // Implement refund logic
    }
}
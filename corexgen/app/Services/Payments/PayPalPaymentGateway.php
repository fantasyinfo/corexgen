<?php
namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use Illuminate\Http\Request;

class PayPalPaymentGateway 
//implements PaymentGatewayInterface
{
    public function initialize(array $paymentDetails)
    {
        //
    }

    public function processPayment(array $paymentData)
    {
        dd($paymentData);
        //
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
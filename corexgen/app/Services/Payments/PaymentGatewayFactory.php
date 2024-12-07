<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentGatewayInterface;
use App\Exceptions\PaymentGatewayNotFoundException;

class PaymentGatewayFactory
{
    /**
     * Supported payment gateways
     * 
     * @var array
     */
    private array $gateways = [
        'stripe' => \App\Services\Payments\StripePaymentGateway::class,
        'paypal' => \App\Services\Payments\PayPalPaymentGateway::class,
        // Future gateways can be added here
    ];

    /**
     * Create payment gateway instance
     * 
     * @param string $gateway
     * @return PaymentGatewayInterface
     * @throws PaymentGatewayNotFoundException
     */
    public function create(string $gateway): PaymentGatewayInterface
    {
        if (!isset($this->gateways[$gateway])) {
            throw new PaymentGatewayNotFoundException(
                "Payment gateway '{$gateway}' not found"
            );
        }

        $gatewayClass = $this->gateways[$gateway];
        return app($gatewayClass);
    }
}
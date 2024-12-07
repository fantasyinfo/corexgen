<?php

namespace App\Contracts\Payments;

use App\DTO\Payments\PaymentResultDTO;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initialize payment process
     * 
     * @param array $paymentDetails
     * @return mixed
     */
    public function initialize(array $paymentDetails);

    /**
     * Process payment
     * 
     * @param array $paymentData
     * @return PaymentResultDTO
     */
    public function processPayment(array $paymentData): PaymentResultDTO;

    /**
     * Handle payment webhook
     * 
     * @param Request $request
     * @return mixed
     */
    public function handleWebhook(Request $request);

    /**
     * Refund a transaction
     * 
     * @param string $transactionId
     * @param float $amount
     * @return mixed
     */
    public function refund(string $transactionId, float $amount);
}
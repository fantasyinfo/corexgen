<?php

namespace App\DTO\Payments;

class PaymentResultDTO
{
    public string $status;
    public ?string $transactionId;
    public ?float $amount;
    public ?string $currency;
    public ?array $metadata;

    public static function create(
        string $status, 
        ?string $transactionId = null,
        ?float $amount = null,
        ?string $currency = null,
        ?array $metadata = null
    ): self {
        $dto = new self();
        $dto->status = $status;
        $dto->transactionId = $transactionId;
        $dto->amount = $amount;
        $dto->currency = $currency;
        $dto->metadata = $metadata;
        return $dto;
    }
}
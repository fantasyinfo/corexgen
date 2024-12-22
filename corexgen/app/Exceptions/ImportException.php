<?php
namespace App\Exceptions;

class ImportException extends \Exception
{
    private $errorCode;
    private $technicalMessage;

    public function __construct(string $message, string $errorCode, ?\Throwable $previous = null)
    {
        $this->errorCode = $errorCode;
        $this->technicalMessage = $previous ? $previous->getMessage() : null;
        parent::__construct($message, 0, $previous);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getTechnicalMessage(): ?string
    {
        return $this->technicalMessage;
    }
}
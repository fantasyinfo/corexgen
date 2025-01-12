<?php
namespace App\Exceptions;

/**
 * Import Exceptions ImportException
 */
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

    /**
     * get error codes of import errors
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * get technical msg of import error
     */
    public function getTechnicalMessage(): ?string
    {
        return $this->technicalMessage;
    }
}
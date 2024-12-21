<?php
namespace App\Exceptions;

class ImportException extends \Exception
{
    protected $errorCode;

    public function __construct(string $message, string $errorCode, \Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
        $this->errorCode = $errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
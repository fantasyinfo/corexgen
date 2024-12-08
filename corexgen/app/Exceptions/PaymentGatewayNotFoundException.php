<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class PaymentGatewayNotFoundException extends Exception
{
    /**
     * Constructor for PaymentGatewayNotFoundException
     *
     * @param string $gateway The name of the gateway that was not found
     * @param string $message Custom error message (optional)
     * @param int $code Error code (optional)
     * @param Throwable|null $previous Previous exception (optional)
     */
    public function __construct(
        string $gateway, 
        string $message = '', 
        int $code = 0, 
        ?Throwable $previous = null
    ) {
        // If no custom message is provided, create a default one
        if (empty($message)) {
            $message = "Payment gateway '{$gateway}' is not configured or supported.";
        }

        // Call parent constructor
        parent::__construct($message, $code, $previous);

        // Additional properties can be added if needed
        $this->gateway = $gateway;
    }

    /**
     * Get the gateway that was not found
     *
     * @return string
     */
    public function getGateway(): string
    {
        return $this->gateway;
    }

    /**
     * Custom string representation of the exception
     *
     * @return string
     */
    public function __toString()
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * Report the exception
     * 
     * This method can be used to log the exception
     */
    public function report()
    {
        \Log::error('Payment Gateway Not Found: ' . $this->getMessage(), [
            'gateway' => $this->gateway,
            'trace' => $this->getTraceAsString()
        ]);
    }

    /**
     * Render the exception into an HTTP response
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json([
            'error' => 'Payment Gateway Error',
            'message' => $this->getMessage(),
            'gateway' => $this->gateway
        ], 404);
    }
}
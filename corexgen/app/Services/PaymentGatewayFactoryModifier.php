<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class PaymentGatewayFactoryModifier
{
    /**
     * Adds a new gateway to the PaymentGatewayFactory class file.
     *
     * @param string $key The gateway key.
     * @param string $class The gateway class.
     * @return void
     * @throws \Exception
     */
    public static function addGateway(string $key, string $class): void
    {
        $filePath = app_path('Services/PaymentGatewayFactory.php');

        if (!File::exists($filePath)) {
            throw new \Exception('PaymentGatewayFactory.php not found.');
        }

        // Read file content
        $fileContent = File::get($filePath);

        // Match the gateways property array
        $pattern = '/private array \$gateways = \[(.*?)\];/s';

        if (!preg_match($pattern, $fileContent, $matches)) {
            throw new \Exception('Gateways array not found in PaymentGatewayFactory.');
        }

        $gatewaysArray = $matches[1];

        // Check if the key already exists
        if (strpos($gatewaysArray, "'$key'") !== false) {
            Log::info("Gateway '$key' already exists.");
            return;
        }

        // Append the new gateway
        $newGatewayEntry = "\n        '$key' => \\$class::class,";
        $updatedGatewaysArray = $gatewaysArray . $newGatewayEntry;

        // Replace the old array with the updated one
        $updatedFileContent = preg_replace($pattern, "private array \$gateways = [$updatedGatewaysArray\n    ];", $fileContent);

        // Write the modified content back to the file
        File::put($filePath, $updatedFileContent);

        Log::info("Gateway '$key' added successfully to PaymentGatewayFactory.");
    }

     /**
     * Removes a gateway from the PaymentGatewayFactory class file.
     *
     * @param string $key The gateway key to remove.
     * @return void
     * @throws \Exception
     */
    public static function removeGateway(string $key): void
    {
        $filePath = app_path('Services/PaymentGatewayFactory.php');

        if (!File::exists($filePath)) {
            throw new \Exception('PaymentGatewayFactory.php not found.');
        }

        // Read file content
        $fileContent = File::get($filePath);

        // Match the gateways property array
        $pattern = '/private array \$gateways = \[(.*?)\];/s';

        if (!preg_match($pattern, $fileContent, $matches)) {
            throw new \Exception('Gateways array not found in PaymentGatewayFactory.');
        }

        $gatewaysArray = $matches[1];

        // Construct the pattern to find the specific gateway entry
        $gatewayPattern = "/\s*'$key'\s*=>\s*\\\\.*?::class,?/";
        
        if (!preg_match($gatewayPattern, $gatewaysArray)) {
            Log::info("Gateway '$key' not found. No action needed.");
            return;
        }

        // Remove the gateway entry
        $updatedGatewaysArray = preg_replace($gatewayPattern, '', $gatewaysArray);

        // Clean up extra commas and spaces
        $updatedGatewaysArray = preg_replace('/,\s*,/', ',', $updatedGatewaysArray); // Remove duplicate commas
        $updatedGatewaysArray = preg_replace('/,\s*\]/', ']', $updatedGatewaysArray); // Handle trailing commas before ]

        // Replace the old array with the updated one
        $updatedFileContent = preg_replace($pattern, "private array \$gateways = [$updatedGatewaysArray\n    ];", $fileContent);

        // Write the modified content back to the file
        File::put($filePath, $updatedFileContent);

        Log::info("Gateway '$key' removed successfully from PaymentGatewayFactory.");
    }
}

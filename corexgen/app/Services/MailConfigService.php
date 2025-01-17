<?php

namespace App\Services;

use App\Models\CRM\CRMSettings;
use Illuminate\Support\Facades\File;

class MailConfigService
{
    /**
     * Update the mail configuration directly in the .env file.
     */
    public static function updateMailConfig()
    {
        try {
            // Fetch mail settings from the database
            $mailSettings = CRMSettings::where('is_tenant', '1')
                ->whereIn('name', [
                    'tenant_mail_provider',
                    'tenant_mail_host',
                    'tenant_mail_port',
                    'tenant_mail_username',
                    'tenant_mail_password',
                    'tenant_mail_encryption',
                    'tenant_mail_from_address',
                    'tenant_mail_from_name',
                ])
                ->get()
                ->pluck('value', 'name');

            if ($mailSettings->isNotEmpty()) {
                // Prepare the content to update in .env
                $envUpdates = [
                    'MAIL_MAILER' => $mailSettings['tenant_mail_provider'] ?? 'smtp',
                    'MAIL_HOST' => $mailSettings['tenant_mail_host'] ?? '',
                    'MAIL_PORT' => $mailSettings['tenant_mail_port'] ?? '',
                    'MAIL_USERNAME' => $mailSettings['tenant_mail_username'] ?? '',
                    'MAIL_PASSWORD' => $mailSettings['tenant_mail_password'] ?? '',
                    'MAIL_ENCRYPTION' => $mailSettings['tenant_mail_encryption'] ?? '',
                    'MAIL_FROM_ADDRESS' => $mailSettings['tenant_mail_from_address'] ?? '',
                    'MAIL_FROM_NAME' => $mailSettings['tenant_mail_from_name'] ?? '',
                ];

                // Update the .env file
                self::updateEnvFile($envUpdates);

                // Clear the cached configuration
                self::clearConfigCache();
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to update mail settings: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update the .env file with the given key-value pairs.
     *
     * @param array $data
     */
    private static function updateEnvFile(array $data)
    {
        $envFilePath = base_path('.env');

        if (!File::exists($envFilePath)) {
            throw new \Exception('.env file not found.');
        }

        $envContent = File::get($envFilePath);

        foreach ($data as $key => $value) {
            $escapedValue = preg_replace('/\n|\r\n?/', '', $value); // Remove line breaks
            if (strpos($envContent, "{$key}=") !== false) {
                // Update existing key
                $envContent = preg_replace("/^{$key}=.*/m", "{$key}=\"{$escapedValue}\"", $envContent);
            } else {
                // Add new key
                $envContent .= "\n{$key}=\"{$escapedValue}\"";
            }
        }

        // Write updated content back to .env
        File::put($envFilePath, $envContent);
    }

    /**
     * Clear the cached configuration file.
     */
    private static function clearConfigCache()
    {
        $configPath = base_path('bootstrap/cache/config.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }
}

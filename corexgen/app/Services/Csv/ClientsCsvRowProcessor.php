<?php
namespace App\Services\Csv;

use App\Helpers\PermissionsHelper;
use App\Traits\QueueSubscriptionUsageFilter;
use App\Exceptions\ImportException;

class ClientsCsvRowProcessor
{
    use QueueSubscriptionUsageFilter;

    /**
     * Process a single row of CSV data.
     * 
     * @param array $row
     * @param array $userContext
     * @return bool
     * @throws ImportException
     */
    public function processRow($row, $userContext)
    {
        info('Processing client row', ['data' => $row, 'context' => $userContext]);

        try {
            // Extract context
            $companyId = $userContext['company_id'];
            $userId = $userContext['user_id'];
            $isTenant = $userContext['is_tenant'];

            // Initialize usage filter
            $this->initializeUsageFilter([
                'user_id' => $userId,
                'company_id' => $companyId,
                'is_tenant' => $isTenant
            ]);

            try {
                $this->checkCurrentUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]));
            } catch (\Exception $e) {
                throw new ImportException(
                    'Subscription limit reached for clients. Please upgrade your plan.',
                    'subscription_limit'
                );
            }

            // Validate and parse emails
            $emails = $this->parseAndValidateEmails($row['Emails']);

            // Validate and parse phone numbers
            $phones = $this->parseAndValidatePhones($row['Phones']);

            // Parse social media
            $socialMedia = $this->parseSocialMedia($row['Social Media Links'] ?? '');

            // Build address array
            $address = $this->buildAddressArray($row);

            // Prepare client data
            $clientData = [
                'type' => $row['Type'],
                'title' => $row['Title'],
                'first_name' => $row['First Name'],
                'middle_name' => $row['Middle Name'],
                'last_name' => $row['Last Name'],
                'email' => $emails,
                'phone' => $phones,
                'social_media' => $socialMedia,
                'category' => $row['Category'],
                'addresses' => $address,
                'company_id' => $companyId,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            // Create client
            $client = app('App\Services\ClientService')->createClient($clientData);

            if (!$client) {
                throw new ImportException(
                    "Failed to create client {$row['First Name']} {$row['Last Name']}",
                    'creation_failed'
                );
            }

            // Update usage after successful creation
            $this->updateUsage(
                strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['CLIENTS']]),
                '+',
                '1'
            );

            info('Client created successfully', [
                'name' => $row['First Name'] . ' ' . $row['Last Name']
            ]);

            return true;

        } catch (\Illuminate\Database\QueryException $e) {
            $userFriendlyMessage = $this->handleDatabaseError($e);
            throw new ImportException(
                $userFriendlyMessage,
                'database_conflict'
            );
        } catch (ImportException $e) {
            info('Import error', [
                'name' => $row['First Name'] . ' ' . $row['Last Name'],
                'error' => $e->getMessage(),
                'code' => $e->getErrorCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            info('Unexpected error during import', [
                'name' => $row['First Name'] . ' ' . $row['Last Name'],
                'error' => $e->getMessage()
            ]);
            throw new ImportException(
                "Failed to create client: " . $this->getUserFriendlyError($e->getMessage()),
                'unexpected_error',
                $e
            );
        }
    }

    /**
     * Parse and validate emails
     * 
     * @param string $emailString
     * @return array
     * @throws ImportException
     */
    private function parseAndValidateEmails($emailString)
    {
        $emails = explode(';', $emailString);
        $validatedEmails = [];

        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new ImportException(
                    "Invalid email format: {$email}",
                    'invalid_email'
                );
            }
            if (!empty($email)) {
                $validatedEmails[] = $email;
            }
        }

        return $validatedEmails;
    }

    /**
     * Parse and validate phone numbers
     * 
     * @param string $phoneString
     * @return array
     */
    private function parseAndValidatePhones($phoneString)
    {
        $phones = explode(';', $phoneString);
        return array_map('trim', array_filter($phones));
    }

    /**
     * Parse social media links into key-value JSON
     * 
     * @param string $links
     * @return array
     */
    private function parseSocialMedia($links)
    {
        $result = [];
        if (empty($links)) {
            return $result;
        }

        try {
            $pairs = explode(';', $links);
            foreach ($pairs as $pair) {
                $keyValue = explode("':", $pair);
                if (count($keyValue) === 2) {
                    $key = trim($keyValue[0], " '");
                    $value = trim($keyValue[1]);
                    $result[$key] = $value;
                }
            }
        } catch (\Exception $e) {
            throw new ImportException(
                "Invalid social media format",
                'invalid_social_media'
            );
        }

        return $result;
    }

    /**
     * Build address array from row data
     * 
     * @param array $row
     * @return array
     */
    private function buildAddressArray($row)
    {
        if (
            empty($row['Street Address']) &&
            empty($row['City Name']) &&
            empty($row['Country ID']) &&
            empty($row['Pincode'])
        ) {
            return [];
        }

        return [
            [
                'type' => 'home',
                'city' => $row['City Name'] ?? null,
                'country_id' => $row['Country ID'] ?? null,
                'street_address' => $row['Street Address'] ?? null,
                'pincode' => $row['Pincode'] ?? null,
            ]
        ];
    }

    private function handleDatabaseError(\Exception $e): string
    {
        // Check for duplicate email error
        if (strpos($e->getMessage(), 'clients.clients_primary_email_unique') !== false) {
            preg_match("/Duplicate entry '(.+?)' for key/", $e->getMessage(), $matches);
            $email = $matches[1] ?? 'unknown';
            return "Email address '{$email}' is already in use by another client";
        }

        // Check for duplicate phone error (if you have such constraint)
        if (strpos($e->getMessage(), 'clients.clients_primary_phone_unique') !== false) {
            preg_match("/Duplicate entry '(.+?)' for key/", $e->getMessage(), $matches);
            $phone = $matches[1] ?? 'unknown';
            return "Phone number '{$phone}' is already in use by another client";
        }

        // Generic database error
        return "Unable to create client due to database conflict. Please check for duplicate information.";
    }

    private function getUserFriendlyError(string $message): string
    {
        // Map of technical error messages to user-friendly ones
        $errorMap = [
            'SQLSTATE[23000]' => 'A duplicate record was found',
            'Integrity constraint violation' => 'This information conflicts with an existing client',
            // Add more mappings as needed
        ];

        foreach ($errorMap as $technical => $friendly) {
            if (strpos($message, $technical) !== false) {
                return $friendly;
            }
        }

        return "An unexpected error occurred while creating the client";
    }
}
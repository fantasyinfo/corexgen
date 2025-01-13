<?php
namespace App\Services\Csv;

use App\Helpers\PermissionsHelper;
use App\Traits\CategoryGroupTagsFilter;
use App\Traits\QueueSubscriptionUsageFilter;
use App\Exceptions\ImportException;

class LeadsCsvRowProcessor
{
    use QueueSubscriptionUsageFilter;
    use CategoryGroupTagsFilter;

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
        info('Processing leads row', ['data' => $row, 'context' => $userContext]);

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
                $this->checkCurrentUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]));
            } catch (\Exception $e) {
                throw new ImportException(
                    'Subscription limit reached for leads. Please upgrade your plan.',
                    'subscription_limit'
                );
            }



     
            if (!isset($row['Email']) || empty($row['Email'])) {
                info('Skipping row: Missing email', ['row_data' => $row]);
                throw new ImportException('Email is missing or empty', 'missing_email');
            }
            if (!isset($row['Title']) || empty($row['Title'])) {
                info('Skipping row: Missing Title', ['row_data' => $row]);
                throw new ImportException('Title is missing or empty', 'missing_title');
            }
            if (!isset($row['First Name']) || empty($row['First Name'])) {
                info('Skipping row: Missing First Name', ['row_data' => $row]);
                throw new ImportException('First Name is missing or empty', 'missing_first_name');
            }
            if (!isset($row['Last Name']) || empty($row['Last Name'])) {
                info('Skipping row: Missing Last Name', ['row_data' => $row]);
                throw new ImportException('Last Name is missing or empty', 'missing_last_name');
            }

            $validGroup = $this->checkIsValidCGTID($row['Group ID'], $companyId, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_groups'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validSources = $this->checkIsValidCGTID($row['Source ID'], $companyId, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_sources'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            $validStatus = $this->checkIsValidCGTID($row['Status ID'], $companyId, CATEGORY_GROUP_TAGS_TYPES['KEY']['leads_status'], CATEGORY_GROUP_TAGS_RELATIONS['KEY']['leads']);

            if (!$validGroup) {
                throw new ImportException(
                    "Failed to create lead {$row['First Name']} {$row['Last Name']} beacuse invalid Group ID {$row['Group ID']}",
                    'invalid_group_id'
                );
            }
            if (!$validSources) {
                throw new ImportException(
                    "Failed to create lead {$row['First Name']} {$row['Last Name']} beacuse invalid Source ID {$row['Source ID']}",
                    'invalid_source_id'
                );
            }
            if (!$validStatus) {
                throw new ImportException(
                    "Failed to create lead {$row['First Name']} {$row['Last Name']} beacuse invalid Status ID {$row['Status ID']}",
                    'invalid_status_id'
                );
            }


            // Prepare client data
            $clientData = [
                'type' => $row['Type'],
                'company_name' => $row['Company Name'],
                'title' => $row['Title'],
                'first_name' => $row['First Name'],
                'last_name' => $row['Last Name'],
                'email' => $row['Email'],
                'phone' => $row['Phone'],
                'group_id' => $row['Group ID'],
                'source_id' => $row['Source ID'],
                'status_id' => $row['Status ID'],
                'address_street_address' => $row['Street Address'],
                'address_country_id' => $row['Country ID'],
                'address_city_name' => $row['City Name'],
                'address_pincode' => $row['Pincode'],
                'company_id' => $companyId,
                'created_by' => $userId,
                'updated_by' => $userId,
            ];

            // Create client
            $client = app('App\Services\LeadsService')->createLead($clientData);

            if (!$client) {
                throw new ImportException(
                    "Failed to create lead {$row['First Name']} {$row['Last Name']}",
                    'creation_failed'
                );
            }

            // Update usage after successful creation
            $this->updateUsage(
                strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['LEADS']]),
                '+',
                '1'
            );

            info('Lead created successfully', [
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
                "Failed to create lead: " . $this->getUserFriendlyError($e->getMessage()),
                'unexpected_error',
                $e
            );
        }
    }



    /**
     * handle all database errors if any
     */
    private function handleDatabaseError(\Exception $e): string
    {
        // Check for duplicate email error
        if (strpos($e->getMessage(), 'leads.clients_primary_email_unique') !== false) {
            preg_match("/Duplicate entry '(.+?)' for key/", $e->getMessage(), $matches);
            $email = $matches[1] ?? 'unknown';
            return "Email address '{$email}' is already in use by another client";
        }

        // Check for duplicate phone error (if you have such constraint)
        if (strpos($e->getMessage(), 'leads.clients_primary_phone_unique') !== false) {
            preg_match("/Duplicate entry '(.+?)' for key/", $e->getMessage(), $matches);
            $phone = $matches[1] ?? 'unknown';
            return "Phone number '{$phone}' is already in use by another client";
        }

        // Generic database error
        return "Unable to create client due to database conflict. Please check for duplicate information.";
    }

    /**
     * get user frinedly errors
     */
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

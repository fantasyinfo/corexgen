<?php
namespace App\Services\Csv;

use App\Helpers\PermissionsHelper;
use App\Models\CRM\CRMRole;
use App\Models\User;
use App\Traits\QueueSubscriptionUsageFilter;
use App\Exceptions\ImportException;

class UsersCsvRowProcessor
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
        info('Processing user row', ['data' => $row, 'context' => $userContext]);

        try {
            // Check for existing user
            if (
                User::where('email', $row['Email'])
                    ->where('company_id', $userContext['company_id'])
                    ->exists()
            ) {
                throw new ImportException(
                    "User with email {$row['Email']} already exists",
                    'duplicate_user'
                );
            }

            // Initialize the trait with user data
            $this->initializeUsageFilter([
                'user_id' => $userContext['user_id'],
                'company_id' => $userContext['company_id'],
                'is_tenant' => $userContext['is_tenant']
            ]);

            try {
                $this->checkCurrentUsage(strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]));
            } catch (\Exception $e) {
                throw new ImportException(
                    'Subscription limit reached. Please upgrade your plan.',
                    'subscription_limit'
                );
            }

            // Validate role before creating user
            $roleId = $this->getRoleId($row['Role'], $userContext['company_id']);
            if (!$roleId) {
                throw new ImportException(
                    "Role '{$row['Role']}' not found for company",
                    'invalid_role'
                );
            }

            // Create user with validated data
            $userData = [
                'name' => $row['Name'],
                'email' => $row['Email'],
                'password' => $row['Password'] ?? 'SecretPass@123#',
                'role_id' => $roleId,
                'address_street_address' => $row['Street Address'],
                'address_country_id' => $row['Country ID'],
                'address_city_name' => $row['City Name'],
                'address_pincode' => $row['Pincode'],
                'company_id' => $userContext['company_id'],
                'is_tenant' => $userContext['is_tenant'],
            ];

            $user = app('App\Services\UserService')->createUser($userData);

            if (!$user) {
                throw new ImportException(
                    "Failed to create user with email {$row['Email']}",
                    'creation_failed'
                );
            }

            info('User created successfully', ['email' => $row['Email']]);

            // Update usage after successful creation
            $this->updateUsage(
                strtolower(PLANS_FEATURES[PermissionsHelper::$plansPermissionsKeys['USERS']]),
                '+',
                '1'
            );

            return true;

        } catch (\Illuminate\Database\QueryException $e) {
            $userFriendlyMessage = $this->handleDatabaseError($e, $row);
            throw new ImportException(
                $userFriendlyMessage,
                'database_conflict'
            );
        } catch (ImportException $e) {
            info('Import error', [
                'email' => $row['Email'],
                'error' => $e->getMessage(),
                'code' => $e->getErrorCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            info('Unexpected error during import', [
                'email' => $row['Email'],
                'error' => $e->getMessage()
            ]);
            throw new ImportException(
                "Failed to create user: " . $this->getUserFriendlyError($e->getMessage()),
                'unexpected_error',
                $e
            );
        }
    }


    private function handleDatabaseError(\Exception $e, array $row): string
    {
        if (strpos($e->getMessage(), 'users.users_email_unique') !== false) {
            return "Email address '{$row['Email']}' is already registered to another user";
        }

        if (strpos($e->getMessage(), 'users.users_phone_unique') !== false) {
            return "Phone number '{$row['Phone']}' is already registered to another user";
        }

        if (strpos($e->getMessage(), 'foreign key constraint fails (`crm_roles`)') !== false) {
            return "Invalid role selected. Please check if the role exists in your company.";
        }

        return "Unable to create user due to database conflict. Please check for duplicate information.";
    }

    private function getUserFriendlyError(string $message): string
    {
        $errorMap = [
            'SQLSTATE[23000]' => 'A duplicate record was found',
            'Integrity constraint violation' => 'This information conflicts with an existing user',
            'foreign key constraint fails' => 'Invalid reference to another record',
            'Data too long' => 'One or more fields exceed maximum length',
            'Column cannot be null' => 'Required information is missing',
        ];

        foreach ($errorMap as $technical => $friendly) {
            if (strpos($message, $technical) !== false) {
                return $friendly;
            }
        }

        return "An unexpected error occurred while creating the user";
    }


    /**
     * Get role ID for the given role name and company
     * 
     * @param string $roleName
     * @param int $companyId
     * @return int|null
     */
    protected function getRoleId($roleName, $companyId)
    {
        $role = CRMRole::where('role_name', $roleName)
            ->where('company_id', $companyId)
            ->first();

        return $role ? $role->id : null;
    }
}
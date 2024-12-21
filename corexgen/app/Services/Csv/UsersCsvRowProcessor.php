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
            if (User::where('email', $row['Email'])
                    ->where('company_id', $userContext['company_id'])
                    ->exists()) {
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

        } catch (ImportException $e) {
            info('Import error', [
                'email' => $row['Email'], 
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            info('Unexpected error during import', [
                'email' => $row['Email'], 
                'error' => $e->getMessage()
            ]);
            throw new ImportException(
                "Unexpected error: {$e->getMessage()}",
                'unexpected_error',
                $e
            );
        }
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
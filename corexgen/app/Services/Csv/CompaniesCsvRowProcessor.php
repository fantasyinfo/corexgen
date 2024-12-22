<?php

namespace App\Services\Csv;

use App\Models\Plans;
use App\Models\User;
use App\Exceptions\ImportException;
use App\Models\Company;

class CompaniesCsvRowProcessor
{
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
        info('Starting row processing', ['row_data' => $row, 'context' => $userContext]);

        try {
            // Validate the required fields
            if (!isset($row['Email']) || empty($row['Email'])) {
                info('Skipping row: Missing email', ['row_data' => $row]);
                throw new ImportException('Email is missing or empty', 'missing_email');
            }

            if (!isset($row['Plan ID']) || empty($row['Plan ID'])) {
                info('Skipping row: Missing Plan ID', ['row_data' => $row]);
                throw new ImportException('Plan ID is missing or empty', 'missing_plan_id');
            }

            // Check for existing user
            if (User::where('email', $row['Email'])->exists()) {
                info('Skipping row: Duplicate user found', ['email' => $row['Email']]);
                throw new ImportException(
                    "User with email {$row['Email']} already exists",
                    'duplicate_user'
                );
            }

            // Check for existing company
            if (Company::where('email', $row['Email'])->exists()) {
                info('Skipping row: Duplicate company found', ['email' => $row['Email']]);
                throw new ImportException(
                    "Company with email {$row['Email']} already exists",
                    'duplicate_company'
                );
            }

            // Check for existing plan
            $plan = Plans::find($row['Plan ID']);
            if (!$plan) {
                info('Skipping row: Plan not found', ['plan_id' => $row['Plan ID']]);
                throw new ImportException(
                    "Plan with ID {$row['Plan ID']} does not exist",
                    'plan_not_found'
                );
            }

            // Prepare company data
            $companyData = [
                'cname' => $row['Company Name'] ?? null,
                'name' => $row['Owner Full Name'] ?? null,
                'email' => $row['Email'],
                'phone' => $row['Phone'] ?? null,
                'password' => $row['Password'] ?? 'SecretPass@123#',
                'plan_id' => $row['Plan ID'],
                'address_street_address' => $row['Street Address'] ?? null,
                'address_country_id' => $row['Country ID'] ?? null,
                'address_city_name' => $row['City Name'] ?? null,
                'address_pincode' => $row['Pincode'] ?? null,
                'company_id' => $userContext['company_id'],
                'is_tenant' => $userContext['is_tenant'],
                'from_admin' => true,
            ];

            info('Prepared company data', ['company_data' => $companyData]);

            // Create the company
            $company = app('App\Services\CompanyService')->createCompany($companyData);

            if (!$company) {
                info('Failed to create company', ['email' => $row['Email'], 'data' => $companyData]);
                throw new ImportException(
                    "Failed to create company with email {$row['Email']}",
                    'creation_failed'
                );
            }

            info('Company created successfully', ['email' => $row['Email'], 'company_id' => $company->id]);

            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            $userFriendlyMessage = $this->handleDatabaseError($e, $row);
            throw new ImportException(
                $userFriendlyMessage,
                'database_conflict'
            );
        } catch (ImportException $e) {
            info('Import exception encountered', [
                'row' => $row,
                'error' => $e->getMessage(),
                'code' => $e->getErrorCode()
            ]);
            throw $e;
        } catch (\Exception $e) {
            info('Unexpected error encountered during row processing', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            throw new ImportException(
                "Failed to create company: " . $this->getUserFriendlyError($e->getMessage()),
                'unexpected_error',
                $e
            );
        } finally {
            info('finally row processing', ['row_data' => $row, 'context' => $userContext]);
        }
    }

    private function handleDatabaseError(\Exception $e, array $row): string
    {
        if (strpos($e->getMessage(), 'companies.companies_email_unique') !== false) {
            return "Email address '{$row['Email']}' is already registered to another company";
        }

  

        if (strpos($e->getMessage(), 'companies.companies_name_unique') !== false) {
            return "Company name '{$row['Company Name']}' is already taken";
        }

        return "Unable to create company due to database conflict. Please check for duplicate information.";
    }

    private function getUserFriendlyError(string $message): string
    {
        $errorMap = [
            'SQLSTATE[23000]' => 'A duplicate record was found',
            'Integrity constraint violation' => 'This information conflicts with an existing company',
            'foreign key constraint fails' => 'Invalid reference to another record',
            'Data too long' => 'One or more fields exceed maximum length',
        ];

        foreach ($errorMap as $technical => $friendly) {
            if (strpos($message, $technical) !== false) {
                return $friendly;
            }
        }

        return "An unexpected error occurred while creating the company";
    }
}

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

        } catch (ImportException $e) {
            info('Import exception encountered', [
                'row' => $row,
                'context' => $userContext,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            info('Unexpected error encountered during row processing', [
                'row' => $row,
                'context' => $userContext,
                'error' => $e->getMessage(),
            ]);
            throw new ImportException(
                "Unexpected error: {$e->getMessage()}",
                'unexpected_error',
                $e
            );
        } finally {
            info('finally row processing', ['row_data' => $row, 'context' => $userContext]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Plans;
use App\Models\PlansFeatures;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CompanyService;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Installer for software SystemInstallerController
 */
class SystemInstallerController extends Controller
{

    protected $companyService;
    public function __construct(CompanyService $companyService = null)
    {
        $this->companyService = $companyService;
    }
    /**
     * Method showInstaller
     * showing the view of installer
     *

     */
    public function showInstaller()
    {
        if (File::exists(storage_path('installed.lock'))) {
            return redirect()->route('login');
        }

        $timezones = DateTimeZone::listIdentifiers();
        return view('installer.index', compact('timezones'));
    }

    /**
     * requiredExtensions for software to run
     *
     * @var array
     */
    protected $requiredExtensions = [
        'pdo',
        'mbstring',
        'tokenizer',
        'xml',
        'curl',
        'openssl',
        'json',
        'zip'
    ];

    /**
     * Method checkSystemRequirements
     * checking system requirements
     *
     * @return void
     */
    public function checkSystemRequirements()
    {
        $requirements = [
            'php_version' => version_compare(PHP_VERSION, '8.2', '>='),
            'extensions' => $this->checkExtensions(),
            'storage_permissions' => is_writable(storage_path()),
            'env_writable' => is_writable(base_path('.env')),
        ];

        return response()->json([
            'pass' => !in_array(false, $requirements),
            'details' => $requirements
        ]);
    }


    /**
     * Method verifyPurchaseCodeEndpoint
     *
     * @param Request $request verify purchase code endppint
     *
     * @return void
     */
    public function verifyPurchaseCodeEndpoint(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'purchase_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $isValid = $this->verifyPurchaseCode($request->purchase_code);

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Purchase code verified successfully' : 'Invalid purchase code'
        ]);
    }

    /**
     * Method testSmtpConnection
     *
     * @param Request $request testing the mailing connections
     *
     * @return void
     */
    public function testSmtpConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'smtp_host' => 'required',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required',
            'smtp_password' => 'required',
            'smtp_encryption' => 'required|in:tls,ssl',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            config([
                'mail.mailers.smtp.host' => $request->smtp_host,
                'mail.mailers.smtp.port' => $request->smtp_port,
                'mail.mailers.smtp.username' => $request->smtp_username,
                'mail.mailers.smtp.password' => $request->smtp_password,
                'mail.mailers.smtp.encryption' => $request->smtp_encryption,
                'mail.from.address' => $request->mail_from_address,
                'mail.from.name' => $request->mail_from_name,
            ]);
            session()->put('smtp_details', $request->only([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'mail_from_address',
                'mail_from_name',
            ]));

            // Try to send a test email
            Mail::raw('Test email from installer', function ($message) use ($request) {
                $message->to($request->mail_from_address)
                    ->subject('SMTP Test Email');
            });

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Method testDatabaseConnection
     *
     * @param Request $request testing database connections
     *
     * @return void
     */
    public function testDatabaseConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'db_host' => 'required',
            'db_port' => 'required|integer',
            'db_name' => 'required',
            'db_username' => 'required',
            'db_password' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $config = [
            'driver' => 'mysql',
            'host' => $request->input('db_host'),
            'port' => $request->input('db_port'),
            'database' => $request->input('db_name'),
            'username' => $request->input('db_username'),
            'password' => $request->input('db_password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ];

        try {
            config(['database.connections.installer_test' => $config]);
            DB::connection('installer_test')->getPdo();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Method reConnectDB
     *
     * @param $request $request reconnection of db 
     *
     * @return void
     */
    private function reConnectDB($request)
    {
        config(['database.default' => 'mysql']);


        // Dynamically update database configuration
        Config::set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => $request->db_host,
            'port' => $request->db_port,
            'database' => $request->db_name,
            'username' => $request->db_username,
            'password' => $request->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);

        // Reconnect to apply the new configuration
        DB::purge('mysql');
        DB::reconnect('mysql');

        Log::info('DB is Reconnected.');
    }


    /**
     * Method ensureDatabaseExists
     * checking database is exists
     * @param $host $host
     * @param $port $port 
     * @param $username $username 
     * @param $password $password 
     * @param $database $database 
     *
     * @return void
     */
    private function ensureDatabaseExists($host, $port, $username, $password, $database)
    {
        try {
            // Connect to the server (not the specific database)
            $pdo = new \PDO("mysql:host=$host;port=$port", $username, $password);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            // Check if the database exists
            $result = $pdo->query("SHOW DATABASES LIKE '$database'")->fetch();
            if (!$result) {
                // Create the database
                $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        } catch (\Exception $e) {
            throw new \Exception("Database creation failed: " . $e->getMessage());
        }
    }



    /**
     * Method installApplication
     *
     * @param Request $request installing the application
     *
     * @return void
     */
    public function installApplication(Request $request)
    {
        Log::info('Reached the installation function');
        try {
            Log::info('Installation Begin');

            // Step 1: Ensure DB Connection
            Log::info('Ensuring database connection parameters.');
            $this->ensureDatabaseExists(
                $request->db_host,
                $request->db_port,
                $request->db_username,
                $request->db_password,
                $request->db_name
            );

            Log::info('Reconnecting to the database.');
            $this->reConnectDB($request);

            // Test database connection
            try {
                DB::connection('mysql')->getPdo();
                Log::info('Database connection successful.');
            } catch (\Exception $e) {
                Log::error('Database connection failed', ['message' => $e->getMessage()]);
                throw $e;
            }

            // Step 2: Begin Transaction
            Log::info('DB Transaction Begin');
            DB::beginTransaction();

            // Step 3: Validate Input
            $validator = Validator::make($request->all(), [
                'site_name' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'admin_email' => 'required|email',
                'admin_password' => 'required',
                'db_host' => 'required|string',
                'db_port' => 'required|numeric',
                'db_name' => 'required|string',
                'db_username' => 'required|string',
                'db_password' => 'nullable|string',
                'purchase_code' => 'required|string',
                'smtp_host' => 'nullable',
                'smtp_port' => 'nullable|integer',
                'smtp_username' => 'nullable',
                'smtp_password' => 'nullable',
                'smtp_encryption' => 'nullable|in:tls,ssl',
                'mail_from_address' => 'nullable|email',
                'mail_from_name' => 'nullable',
                'mode' => 'required|in:company,saas'
            ]);

            if ($validator->fails()) {
                Log::info('Validation Failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Step 4: Clear Config Cache
            Log::info('Clearing old cache file if it exists.');
            if (file_exists(base_path('/bootstrap/cache/config.php'))) {
                unlink(base_path('/bootstrap/cache/config.php'));
            }

            // Step 5: Run Migrations
            Log::info('Migration Started.');
            try {
                Artisan::call('migrate:fresh', ['--force' => true]);
                Log::info('Migration Output:', ['output' => Artisan::output()]);
            } catch (\Exception $e) {
                Log::error('Migration failed', ['message' => $e->getMessage()]);
                throw $e;
            }

            // Step 6: Debug Database Tables
            Log::info('Checking database tables after migration.');
            $tables = DB::select('SHOW TABLES');
            Log::info('Current database tables:', ['tables' => $tables]);

            // Step 7: Run Seeders
            Log::info('Seeders Started.');
            try {
                Artisan::call('db:seed', ['--force' => true]); // Ensure it uses the default seeder
                Log::info('Seeders Output:', ['output' => Artisan::output()]);
            } catch (\Exception $e) {
                Log::error('Seeding failed', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

            // Step 8: Create Super Admin
            Log::info('Super Admin Creating...');
            if ($request->input('mode') == 'saas') {

                $user = $this->createSuperAdmin($request);
            } else if ($request->input('mode') == 'company') {
                $user = $this->createCompanyAccount($request);
            }

            // Step 9: Create Installation Lock File
            Log::info('Creating installation lock file.');
            File::put(storage_path('installed.lock'), 'Installation completed on ' . now());

            // Step 10: Update Environment File
            Log::info('Updating the .env file...');
            $this->updateEnvironmentFile($request);

            // Step 11: Create Storage Directory and Set Permissions
            Log::info('Setting up storage directory and permissions...');
            if (!file_exists(public_path('storage'))) {
                try {
                    Log::info('Creating storage symbolic link...');
                    Artisan::call('storage:link');
                    Log::info('Storage link created successfully');

                    // Set proper permissions for storage directory
                    if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
                        Log::info('Setting storage directory permissions...');
                        chmod(storage_path(), 0755);
                        chmod(public_path('storage'), 0755);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to create storage link or set permissions', [
                        'message' => $e->getMessage()
                    ]);
                    // Continue installation even if storage:link fails
                }
            }

            // Step 12: Run Artisan Commands
            Log::info('Running additional Artisan commands.');
            Artisan::call('key:generate');
            Artisan::call('config:clear');
            Artisan::call('config:cache');
            Artisan::call('optimize');

            // Step 13: Commit Transaction
            Log::info('Committing the database transaction.');
            DB::commit();

            Log::info('Installation Successfully Completed');
            return response()->json([
                'status' => 'success',
                'message' => 'Installation Successful',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Installation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Installation Failed: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Method verifyPurchaseCode
     *
     * @param $code $code verification of purchase code ::todo api call
     *
     * @return bool
     */
    private function verifyPurchaseCode($code)
    {
        // Implement your purchase code verification logic here
        // This is a placeholder - replace with actual verification
        $apiUrl = rtrim(env('VERSION_CHECK_API'), '/') . '/validate_license.php';

        try {
            // Fetch the latest version from the API
            $response = Http::post($apiUrl, ['purchase_code' => $code]);
            Log::info('Licence Check API Response ', ['response' => $response]);
            if ($response->successful()) {
                $status = $response->json('status');

                if ($status == 'success') {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            // Log the error and provide a fallback message
            Log::error('Error fetching the latest version', ['error' => $e->getMessage()]);
        }

        return true;
    }
    /**
     * Method updateEnvironmentFile
     *
     * @param Request $request updating the .env file after
     *
     * @return void
     */
    private function updateEnvironmentFile(Request $request)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            throw new \Exception('.env file not found.');
        }

        $replacements = [
            'APP_NAME' => '"' . str_replace('"', '', $request->site_name) . '"',
            'APP_ENV' => 'production',
            'APP_DEBUG' => false,
            'APP_URL' => url('/'),
            'DB_HOST' => $request->db_host ?? '127.0.0.1',
            'DB_PORT' => $request->db_port ?? '3306',
            'DB_DATABASE' => $request->db_name ?? '',
            'DB_USERNAME' => $request->db_username ?? '',
            'DB_PASSWORD' => '"' . addslashes($request->db_password ?? '') . '"', // Add double quotes
            'MAIL_MAILER' => 'smtp',
            'MAIL_HOST' => $request->smtp_host ?? '',
            'MAIL_PORT' => $request->smtp_port ?? '',
            'MAIL_USERNAME' => $request->smtp_username ?? '',
            'MAIL_PASSWORD' => $request->smtp_password ?? '',
            'MAIL_ENCRYPTION' => $request->smtp_encryption ?? 'tls',
            'MAIL_FROM_ADDRESS' => $request->mail_from_address ?? '',
            'MAIL_FROM_NAME' => '"' . str_replace('"', '', $request->mail_from_name ?? '') . '"',
            'SESSION_DRIVER' => 'database'
        ];


        $envContent = File::get($envPath);
        $lines = explode("\n", $envContent);

        foreach ($lines as $index => $line) {
            foreach ($replacements as $key => $value) {
                if (str_contains($line, "{$key}=")) {
                    $lines[$index] = "{$key}={$value}";
                    unset($replacements[$key]);
                }
            }
        }

        // Add any remaining replacements that weren't found
        foreach ($replacements as $key => $value) {
            $lines[] = "{$key}={$value}";
        }

        File::put($envPath, implode("\n", $lines));
    }

    /**
     * Method checkExtensions
     * checking extenstions

     */
    private function checkExtensions()
    {
        return collect($this->requiredExtensions)
            ->mapWithKeys(fn($ext) => [$ext => extension_loaded($ext)]);
    }

    /**
     * Method runSeeders
     * running the seeder to create required data into db
     *
     * @return void
     */
    private function runSeeders()
    {
        Artisan::call('db:seed');

    }

    /**
     * Method createSuperAdmin
     *
     * @param Request $request creating a super admin user to access the admin panel
     *
     * @return array
     */
    private function createSuperAdmin(Request $request)
    {

        $settings = array_merge($request->all(), [
            'smtp_details' => session()->get('smtp_details'),
        ]);

        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->admin_email,
            'currency_code' => $request->currency_code,
            'currency_symbol' => $request->currency_symbol,
            'timezone' => $request->timezone,
            'settings' => json_encode($settings),
            'mode' => $request->mode
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->admin_email,
            'password' => Hash::make($request->admin_password),
            'email_verified_at' => now(),
            'is_tenant' => true,
            'tenant_id' => $tenant->id
        ]);

        $details = [
            'name' => $request->name,
            'email' => $request->admin_email
        ];

        // \Mail::to($user->email)->send(new \App\Mail\WelcomeSuperAdmin($details));

        return $details;
    }

    /**
     * Method createSuperAdmin
     *
     * @param Request $request creating a company admin user to access the admin panel
     *
     * @return array
     */
    private function createCompanyAccount(Request $request)
    {

        $settings = array_merge($request->all(), [
            'smtp_details' => session()->get('smtp_details'),
        ]);

        // create tenant account for id pupose only, do not create its user account for login as saas
        $tenant = Tenant::create([
            'name' => $request->name,
            'domain' => $request->admin_email,
            'currency_code' => $request->currency_code,
            'currency_symbol' => $request->currency_symbol,
            'timezone' => $request->timezone,
            'settings' => json_encode($settings),
            'mode' => $request->mode
        ]);

        // create a unlimited plan for the company
        $plan = Plans::create([
            'name' => 'Direct Company Plan',
            'desc' => 'Company module direct plan',
            'price' => 0,
            'offer_price' => 0,
            'billing_cycle' => 'UNLIMITED',
        ]);
        info('Direct Company Plan Created', [$plan]);

        // add unlimited plan featuers
        foreach (PLANS_FEATURES as $md) {
            PlansFeatures::create([
                'plan_id' => $plan->id,
                'module_name' => strtolower($md),
                'value' => -1
            ]);
        }

        info('Unlimited plans featuers added.', [$plan]);

        $companyArray = [
            'name' => 'CoreXGen CRM',
            'cname' => $request->name,
            'email' => $request->admin_email,
            'password' => $request->admin_password,
            'plan_id' => $plan->id,
            'tenant_id' => $tenant->id,
            'status' => CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE']
        ];

        $company = Company::create(array_merge($companyArray, [
            'name' => $companyArray['cname'],
        ]));

        info('Company Created', [$company]);

        $user = $this->companyService->createCompanyUser($company, $companyArray, $request->name);

        info('Company User Created', [$user]);

        $this->companyService->createPaymentTransaction($plan->id, $company->id, ['currency' => $request->currency_code]);

        info('Company Payment Transaxctions Done');

        $this->companyService->generateAllSettings($company->id);

        info('Company Settings Generated');




        $details = [
            'name' => $user->name,
            'email' => $user->email
        ];

        // \Mail::to($user->email)->send(new \App\Mail\WelcomeSuperAdmin($details));

        return $details;
    }


}
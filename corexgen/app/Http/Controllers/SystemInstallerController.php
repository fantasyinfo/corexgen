<?php

namespace App\Http\Controllers;


use App\Models\Tenant;
use App\Models\User;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;


/**
 * Installer for software SystemInstallerController
 */
class SystemInstallerController extends Controller
{
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
            'php_version' => version_compare(PHP_VERSION, '8.1', '>='),
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

        \Log::info('DB is Reconnected.');
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
        \Log::info('Reached to the installation function');
        try {
            \Log::info('Installation Begin');

            \Log::info('Ensure DB Connect');
            $this->ensureDatabaseExists(
                $request->db_host,
                $request->db_port,
                $request->db_username,
                $request->db_password,
                $request->db_name
            );


            \Log::info('Reconnect the DB');
            $this->reConnectDB($request);

            // Test connection immediately
            DB::connection('mysql')->getPdo();

            \Log::info('DB Transaction Begin');
            DB::beginTransaction();



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
                'mail_from_name' => 'nullable'
            ]);

            if ($validator->fails()) {
                \Log::info('Validation Failed');
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            }

            // remove old config file if exists
            \Log::info('Unlinking the old cache file if exists.');
            if (file_exists(base_path('/bootstrap/cache/config.php'))) {
                unlink(base_path('/bootstrap/cache/config.php'));
            }



            \Log::info('Migration Started.');
            // Run migrations
            Artisan::call('migrate:fresh');

            \Log::info('Seeders Started.');

            $this->runSeeders();

            \Log::info('Super Admin Creating...');
            // Create super admin
            $user = $this->createSuperAdmin($request);



            // Create installation lock file
            File::put(storage_path('installed.lock'), 'Installation completed on ' . now());


            \Log::info('Updating the .env file...');
            // Update environment file
            $this->updateEnvironmentFile($request);

            \Log::info('Artisan Calls...');

            // Generate application key
            Artisan::call('key:generate');

            // Clear and cache configuration
            Artisan::call('config:clear');
            Artisan::call('config:cache');
            Artisan::call('optimize');

            \Log::info('DB Commit...');

            DB::commit();

            session()->put('installation_success', true);




            \Log::info('Installation Succfully...');
            return response()->json([
                'status' => 'success',
                'message' => 'Installation Successful',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Installation failed', [
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
     * @return void
     */
    private function verifyPurchaseCode($code)
    {
        // Implement your purchase code verification logic here
        // This is a placeholder - replace with actual verification
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
            'DB_PASSWORD' => $request->db_password ?? '',
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
}
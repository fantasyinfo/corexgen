<?php

namespace App\Http\Controllers;

use App\Models\Buyer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SystemInstallerController extends Controller
{
    //
    public function showInstaller()
    {
  
        // Check if already installed
        if (File::exists(storage_path('installed.lock'))) {
            return redirect()->route('login');
        }

        return view('installer.index');
    }

    protected $requiredExtensions = [
        'pdo', 'mbstring', 'tokenizer', 'xml', 
        'curl', 'openssl', 'json'
    ];

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
        'driver'    => 'mysql',
        'host'      => $request->input('db_host'),
        'port'      => $request->input('db_port'),
        'database'  => $request->input('db_name'),
        'username'  => $request->input('db_username'),
        'password'  => $request->input('db_password'),
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
    ];

    try {
        // Set the new connection configuration
        config(['database.connections.installer_test' => $config]);
        
        // Attempt to connect using the new configuration
        $connection = DB::connection('installer_test');
        $connection->getPdo();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false, 
            'message' => $e->getMessage()
        ], 500);
    }
}


public function installApplication(Request $request)
{
    try {
        // Step 1: Validate the Request Data
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
            'purchase_code' => 'required|string'
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Step 2: Verify Purchase Code
        if (!$this->verifyPurchaseCode($request->purchase_code)) {
            \Log::error('Purchase code verification failed');
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Purchase Code'
            ], 403);
        }

        // Step 3: Update Configurations in Memory
        config(['app.name' => $request->site_name]);
        config(['database.connections.mysql.host' => $request->db_host]);
        config(['database.connections.mysql.port' => $request->db_port]);
        config(['database.connections.mysql.database' => $request->db_name]);
        config(['database.connections.mysql.username' => $request->db_username]);
        config(['database.connections.mysql.password' => $request->db_password]);

        // Step 4: Run Database Migrations
        \Log::info('Running database migrations');
        Artisan::call('migrate:fresh');

        // Step 5: Run Database Seeders
        \Log::info('Running database seeders');
     
        $this->runSeeders();


        // Step 6: Create Super Admin User
        \Log::info('Creating super admin user');
        $this->createSuperAdmin($request);

        // Step 7: Create Installation Lock File to Prevent Re-installation
        \Log::info('Creating installation lock file');
        File::put(storage_path('installed.lock'), 'Installation completed on ' . now());

        // Step 8: Update the Environment File at the End
        // \Log::info('Updating environment file');
        // $this->updateEnvironmentFile($request);

        // Step 9: Generate Application Key
        \Log::info('Generating application key');
        Artisan::call('key:generate');

        // Step 10: Return Success Response
        return response()->json([
            'status' => 'success',
            'message' => 'Installation Successful',
            'redirect_url' => route('login')
        ]);

    } catch (\Exception $e) {
        // Log the Exception Details
        \Log::error('Installation failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Return Error Response
        return response()->json([
            'status' => 'error',
            'message' => 'Installation Failed',
            'error_details' => $e->getMessage()
        ], 500);
    }
}


    private function verifyPurchaseCode($code)
    {
        // Implement purchase code verification
        // Could be a remote API call or local validation
        return true; // Placeholder
    }

    private function updateEnvironmentFile(Request $request)
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            throw new \Exception('.env file not found.');
        }
    
        // Create an associative array of the key-value pairs to update
        $replacements = [
            'APP_NAME' => preg_replace('/\s+/', '', $request->site_name),
            'APP_URL' => url('/'),
            'DB_HOST' => $request->db_host,
            'DB_PORT' => $request->db_port,
            'DB_DATABASE' => $request->db_name,
            'DB_USERNAME' => $request->db_username,
            'DB_PASSWORD' => $request->db_password,
            'SESSION_DRIVER' => 'database'
        ];
    
        // Read the .env file, modify it line by line to ensure minimal change
        $envContent = File::get($envPath);
        $lines = explode("\n", $envContent);
    
        foreach ($lines as $index => $line) {
            foreach ($replacements as $key => $value) {
                if (str_contains($line, "{$key}=")) {
                    $lines[$index] = "{$key}={$value}";
                }
            }
        }
    
        // Write the modified content back to the .env file at the end
        File::put($envPath, implode("\n", $lines));
    }
    
    public function updateEnvironmentFromConfig()
{
    try {
        // Get the current path of the .env file
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            throw new \Exception('.env file not found.');
        }

        // Create an associative array of the key-value pairs to update based on current configuration
        $replacements = [
            'APP_NAME' => config('app.name'),
            'APP_URL' => config('app.url'),
            'DB_HOST' => config('database.connections.mysql.host'),
            'DB_PORT' => config('database.connections.mysql.port'),
            'DB_DATABASE' => config('database.connections.mysql.database'),
            'DB_USERNAME' => config('database.connections.mysql.username'),
            'DB_PASSWORD' => config('database.connections.mysql.password'),
            'SESSION_DRIVER' => config('session.driver')
        ];

        // Read the .env file and modify it line by line to ensure minimal change
        $envContent = File::get($envPath);
        $lines = explode("\n", $envContent);

        foreach ($lines as $index => $line) {
            foreach ($replacements as $key => $value) {
                if (str_contains($line, "{$key}=")) {
                    $lines[$index] = "{$key}={$value}";
                }
            }
        }

        // Write the modified content back to the .env file
        File::put($envPath, implode("\n", $lines));

        return response()->json([
            'status' => 'success',
            'message' => 'Environment file updated successfully.'
        ]);

    } catch (\Exception $e) {
        // Log the error and return a failure response
        \Log::error('Failed to update .env file', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to update environment file.',
            'error_details' => $e->getMessage()
        ], 500);
    }
}

    private function checkExtensions()
    {
        return collect($this->requiredExtensions)
            ->mapWithKeys(fn($ext) => [$ext => extension_loaded($ext)]);
    }


    private function runSeeders(){

        Artisan::call('db:seed');
        Artisan::call('db:seed', ['--class' => 'CRMPermissionsSeeder']);
        Artisan::call('db:seed', ['--class' => 'CRMMenuSeeder']);
        Artisan::call('db:seed', ['--class' => 'CRMRoleSeeder']);
    }
    private function createSuperAdmin(Request $request)
    {

        try {
            DB::beginTransaction();
   
            // only for buyers/superadmin
                $buyerIdToMaintain = time();
                $buyer = Buyer::create([
                    'name' => $request->name,
                    'email' => $request->admin_email,
                    'buyer_id' => $buyerIdToMaintain,
                    'password' => Hash::make($request->admin_password),
                    
                ]);

                $userArr = [
                    'name' => $request->name,
                    'email' => $request->admin_email,
                    'role_id' => 1, // superadmin 
                    'password' => Hash::make($request->admin_password),
                    'buyer_id' => $buyer->id,
                    'email_verified_at' => now()
                ];
   
                $user = User::create($userArr);
                
            
        
    
            DB::commit();
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Database rollback at the time of super user creationg',[  'message' => $e->getMessage()]);
        }

    }

}

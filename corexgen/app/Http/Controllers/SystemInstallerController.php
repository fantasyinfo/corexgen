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
        $validator = Validator::make($request->all(), [
            'site_name' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required',
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

        // Verify purchase code
        if (!$this->verifyPurchaseCode($request->purchase_code)) {
            \Log::error('Purchase code verification failed');
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Purchase Code'
            ], 403);
        }

        // Update .env file
        \Log::info('Updating environment file');
        $this->updateEnvironmentFile($request);

        // Run database migrations
        \Log::info('Running database migrations');
        Artisan::call('migrate:fresh');

        // Run seeders
        \Log::info('Running database seeders');
        Artisan::call('db:seed');

        // Create super admin
        \Log::info('Creating super admin user');
        $this->createSuperAdmin($request);

        // Create lock file to prevent re-installation
        \Log::info('Creating installation lock file');
        File::put(storage_path('installed.lock'), 'Installation completed on ' . now());

        // Generate application key
        \Log::info('Generating application key');
        Artisan::call('key:generate');

        // Return detailed success response
        return response()->json([
            'status' => 'success',
            'message' => 'Installation Successful',
            'redirect_url' => route('login')
        ]);

    } catch (\Exception $e) {
        // Log the full exception
        \Log::error('Installation failed', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        // Return a comprehensive error response
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
        $env = File::get($envPath);

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

        foreach ($replacements as $key => $value) {
            $env = preg_replace("/{$key}=.*/", "{$key}={$value}", $env);
        }

        File::put($envPath, $env);
    }

    private function checkExtensions()
    {
        return collect($this->requiredExtensions)
            ->mapWithKeys(fn($ext) => [$ext => extension_loaded($ext)]);
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

        }

    }

}

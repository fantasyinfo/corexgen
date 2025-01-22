<?php

namespace App\Core;

use App\Services\PaymentGatewayFactoryModifier;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Summary of ModuleManager
 * Installting, Running Seeder, Database Migrations, Artisans, Composer autoloads, cleanups
 */
class ModuleManager
{
    /**
     * Summary of modules
     * modules array data
     * @var array
     */
    protected $modules = [];

    /**
     * Summary of moduleDirectory
     * dir
     * @var 
     */
    protected $moduleDirectory;

    /**
     * Summary of namespace
     * getting name space
     * @var 
     */
    protected $namespace;

    /**
     * Method __construct
     * getting instaltion dir and namespace config
     *
     * @return void
     */
    public function __construct()
    {
        $this->moduleDirectory = config('modules.module_directory');
        $this->namespace = config('modules.namespace');
    }

    /**
     * Method install
     *
     * @param string $modulePath installing the module
     *
     * @return bool
     */
    public function install(string $modulePath): bool
    {
        try {
            // Validate module package
            if (!$this->validateModule($modulePath)) {
                throw new \Exception('Invalid module package');
            }

            Log::info("Module Loaded $modulePath validation Passeed.");
            // Extract module
            $moduleData = $this->extractModule($modulePath);

            Log::info('Going to install required package');
            $this->installModulePackages($moduleData);

            Log::info("Module Extracted.");
            // Register module in database
            $this->registerModule($moduleData);

            Log::info("Module Registerd.");

            // Run migrations and seeders
            $this->runMigrations($moduleData['id']);

            Log::info("Migrations Ran.");

            Log::info("Running Seeders.");
            $this->runSeeder($moduleData['id']);



            $this->loadModules();

            Log::info(message: "Modules Ran.");

            // Call the updateComposerJson.php script


            Log::info("composer dump-autoload executed.");


            return true;
        } catch (\Exception $e) {
            Log::error('Module installation failed: ' . $e->getMessage());
            $this->cleanup($modulePath);
            return false;
        }
    }



    /**
     * Method validateModule
     *
     * @param string $path validation of module required files check
     *
     * @return bool
     */
    protected function validateModule(string $path): bool
    {
        if (!file_exists($path)) {
            throw new \Exception('Module file not found at: ' . $path);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Unable to open module package: ' . $path);
        }

        try {
            // Debug: List all files in the ZIP
            $fileList = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $fileList[] = $zip->getNameIndex($i);
            }

            // Check if module.json exists (case-insensitive)
            $moduleJsonPath = null;
            foreach ($fileList as $file) {
                if (strtolower($file) === 'module.json') {
                    $moduleJsonPath = $file;
                    break;
                }
            }

            if ($moduleJsonPath === null) {
                throw new \Exception(
                    "module.json not found in the ZIP archive.\n" .
                    "Files found in ZIP:\n" .
                    implode("\n", $fileList)
                );
            }

            // Read and validate module.json
            $moduleJsonContent = $zip->getFromName($moduleJsonPath);
            if ($moduleJsonContent === false) {
                throw new \Exception('Failed to read module.json content');
            }

            if (empty($moduleJsonContent)) {
                throw new \Exception('module.json is empty');
            }

            $moduleJson = json_decode($moduleJsonContent, true);
            if ($moduleJson === null) {
                throw new \Exception('Invalid JSON in module.json: ' . json_last_error_msg());
            }

            if (!$this->validateModuleJson($moduleJson)) {
                throw new \Exception('Invalid module.json structure');
            }

            // Check for other required files
            $requiredFiles = config('modules.required_files');
            foreach ($requiredFiles as $file) {
                $found = false;
                foreach ($fileList as $zipFile) {
                    if (str_ends_with($zipFile, $file)) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    throw new \Exception("Required file {$file} not found in module");
                }
            }

            return true;
        } finally {
            $zip->close();
        }
    }

    /**
     * Method validateModuleJson
     *
     * @param array $moduleJson validation of json file of module
     *
     * @return bool
     */
    protected function validateModuleJson(array $moduleJson): bool
    {
        $requiredFields = [
            'name' => 'string',
            'version' => 'string',
            'description' => 'string',
            'providers' => 'array'
        ];

        foreach ($requiredFields as $field => $type) {
            if (!isset($moduleJson[$field])) {
                throw new \Exception("Missing required field '{$field}' in module.json");
            }

            if (gettype($moduleJson[$field]) !== $type) {
                throw new \Exception("Field '{$field}' must be of type {$type} in module.json");
            }

            if (empty($moduleJson[$field])) {
                throw new \Exception("Field '{$field}' cannot be empty in module.json");
            }
        }

        // Validate version format
        if (!preg_match('/^\d+\.\d+\.\d+$/', $moduleJson['version'])) {
            throw new \Exception('Invalid version format in module.json. Expected format: x.y.z');
        }

        // Validate provider class names
        foreach ($moduleJson['providers'] as $provider) {
            if (!is_string($provider) || empty($provider)) {
                throw new \Exception('Each provider must be a non-empty string');
            }
        }

        if (isset($moduleJson['dependencies'])) {
            if (!$this->checkDependencies($moduleJson['dependencies'])) {
                throw new \Exception('Module dependencies not satisfied');
            }
        }

        return true;
    }



    /**
     * Method checkDependencies
     *
     * @param array $dependencies checking versions
     *
     * @return bool
     */
    protected function checkDependencies(array $dependencies): bool
    {
        foreach ($dependencies as $module => $version) {
            if ($module === 'core') {
                if (!$this->isCompatibleVersion(config('app.version'), $version)) {
                    return false;
                }
                continue;
            }

            $installedModule = $this->getInstalledModule($module);
            if (!$installedModule || !$this->isCompatibleVersion($installedModule->version, $version)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Method isCompatibleVersion
     *
     * @param string $current version compitable check
     * @param string $required [explicite description]
     *
     * @return bool
     */
    protected function isCompatibleVersion(string $current, string $required): bool
    {
        return version_compare($current, trim($required, '>=<~^'), '>=');
    }

    /**
     * Method extractModule
     *
     * @param string $path extracting a module zip file
     *
     * @return array
     */
    protected function extractModule(string $path): array
    {
        $zip = new ZipArchive();
        $zip->open($path);

        // Read module.json
        $moduleJson = json_decode($zip->getFromName('module.json'), true);
        $extractPath = $this->moduleDirectory . '/' . $moduleJson['name'];

        // Clean existing module directory if exists
        if (File::exists($extractPath)) {
            File::deleteDirectory($extractPath);
        }

        // Extract module
        $zip->extractTo($extractPath);
        $zip->close();

        // Add the gateway to PaymentGatewayFactory

        if (isset($moduleJson['payment_gateway'])) {
            Log::info('*** Yes Found the Payment Gateway ', $moduleJson['payment_gateway']);

            PaymentGatewayFactoryModifier::addGateway(
                $moduleJson['payment_gateway']['key'],
                $moduleJson['payment_gateway']['class']
            );
        }

        return [
            'id' => $moduleJson['name'],
            'version' => $moduleJson['version'],
            'description' => $moduleJson['description'],
            'providers' => $moduleJson['providers'],
            'packages' => $moduleJson['packages'],
            'path' => $extractPath,
            'settings' => json_encode($moduleJson)
        ];
    }

    protected function installModulePackages(array $moduleData): void
    {
        // Ensure the "packages" key exists in the module data
        if (!isset($moduleData['packages']) || !is_array($moduleData['packages'])) {
            return;
        }

        foreach ($moduleData['packages'] as $package) {
            $this->requirePackage($package);
        }
    }

protected function requirePackage(string $package): void
{
    try {
        // Check if package already exists in composer.json first
        $composerJsonPath = base_path('composer.json');
        if (!file_exists($composerJsonPath)) {
            throw new \Exception('composer.json not found');
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true);
        if (isset($composerJson['require'][$package])) {
            \Log::info("Package already listed in composer.json: $package");
            return;
        }

        // Find composer executable
        $composerPath = $this->findComposerPath();
        \Log::info("Using composer at: $composerPath");

        // Build and execute command with full error output capture
        $command = sprintf(
            '%s require %s 2>&1',
            escapeshellarg($composerPath),
            escapeshellarg($package)
        );

        $process = Process::fromShellCommandline($command, base_path());
        $process->setTimeout(300); // 5 minutes timeout
        $process->setIdleTimeout(60); // 1 minute idle timeout

        \Log::info("Executing command: " . $command);
        
        $process->run(function ($type, $buffer) {
            \Log::info($buffer);
        });

        if (!$process->isSuccessful()) {
            throw new \Exception(
                sprintf(
                    'Failed to install package: %s. Error: %s',
                    $package,
                    $process->getErrorOutput()
                )
            );
        }

        \Log::info("Package installed successfully: $package");
    } catch (\Exception $e) {
        \Log::error("Package installation failed: " . $e->getMessage());
        throw $e;
    }
}

protected function findComposerPath(): string
{
    // Check common locations
    $paths = [
        '/usr/local/bin/composer',
        '/usr/bin/composer',
        base_path('composer.phar'),
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }

    // Try to find composer in PATH
    $process = new Process(['which', 'composer']);
    $process->run();
    
    if ($process->isSuccessful()) {
        return trim($process->getOutput());
    }

    throw new \Exception('Could not find composer executable');
}



    /**
     * Method registerModule
     *
     * @param array $moduleData register the module into db
     *
     * @return void
     */
    protected function registerModule(array $moduleData): void
    {
        DB::table('modules')->updateOrInsert(
            ['name' => $moduleData['id']],
            [
                'version' => $moduleData['version'],
                'description' => $moduleData['description'],
                'providers' => json_encode($moduleData['providers']),
                'path' => $moduleData['path'],
                'settings' => $moduleData['settings'],
                'status' => 'active',
                'panel_type' => panelAccess(),
                'updated_at' => now()
            ]
        );
    }



    /**
     * Method runMigrations
     *
     * @param string $moduleId running the migrations of module
     *
     * @return void
     */
    protected function runMigrations(string $moduleId): void
    {
        Log::info("Running migrations for module: $moduleId");

        // Set relative migration path based on module
        $migrationPath = 'modules/' . $moduleId . '/database/migrations'; // Relative path to base directory
        Log::info("Relative Migration Path: $migrationPath");

        // Check if the migration directory exists
        if (File::exists(base_path($migrationPath))) {
            Log::info("Migration directory exists.");

            // Log all files in the migration directory
            $files = File::files(base_path($migrationPath));
            foreach ($files as $file) {
                Log::info("Found migration file: " . $file->getFilename());
            }

            try {
                // Run the migrations
                $exitCode = Artisan::call('migrate', [
                    '--path' => $migrationPath,  // Relative path
                    '--force' => true,
                ]);

                Log::info("Artisan migrate output: " . Artisan::output());
                Log::info("Artisan migrate exit code: $exitCode");

                // Check if migration was successful
                if ($exitCode !== 0) {
                    Log::error("Migration command failed with exit code: $exitCode");
                } else {
                    Log::info("Migrations executed successfully for module: $moduleId");
                }
            } catch (\Exception $e) {
                // Catch any exception during the migration process
                Log::error("Error running migration command: " . $e->getMessage());
            }
        } else {
            // Log an error if the migration path doesn't exist
            Log::error('Migration path does not exist: ' . $migrationPath);
        }
    }

protected function runSeeder(string $moduleId): void
{
    Log::info("Running seeder for module: $moduleId");
    
    $seederPath = 'modules/' . $moduleId . '/database/seeders';
    Log::info("Relative Seeder Path: $seederPath");
    
    if (!File::exists(base_path($seederPath))) {
        Log::error('Seeder path does not exist: ' . $seederPath);
        return;
    }

    // First, try to load any module-specific autoload files
    $moduleAutoloadFile = base_path("modules/$moduleId/vendor/autoload.php");
    if (File::exists($moduleAutoloadFile)) {
        require_once $moduleAutoloadFile;
        Log::info("Loaded module autoload file: $moduleAutoloadFile");
    }

    $files = File::files(base_path($seederPath));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            Log::info("Skipping non-PHP file: " . $file->getFilename());
            continue;
        }

        try {
            // Include the file directly first to ensure the class is loaded
            require_once $file->getPathname();
            
            $className = $this->getSeederClassName($file->getPathname());
            Log::info("Attempting to run seeder: $className");
            
            // Verify the class exists before trying to run it
            if (!class_exists($className)) {
                Log::error("Class not found after loading file: $className");
                Log::info("File contents: " . File::get($file->getPathname()));
                continue;
            }

            // Run the seeder with error tracking
            $exitCode = Artisan::call('db:seed', [
                '--class' => $className,
                '--force' => true,
            ]);

            $output = Artisan::output();
            Log::info("Artisan seeder output: " . $output);
            
            if ($exitCode !== 0) {
                Log::error("Seeder failed with exit code $exitCode: $className");
                Log::error("Full output: " . $output);
            } else {
                Log::info("Seeder completed successfully: $className");
            }
        } catch (\Exception $e) {
            Log::error("Error running seeder: $className");
            Log::error("Exception: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}

// Add this helper method if you don't already have it
protected function getSeederClassName(string $filepath): string
{
    // Get the content of the file
    $content = file_get_contents($filepath);
    
    // Extract namespace
    preg_match('/namespace\s+([^;]+);/', $content, $matches);
    $namespace = $matches[1] ?? null;
    
    // Extract class name
    preg_match('/class\s+(\w+)/', $content, $matches);
    $className = $matches[1] ?? null;
    
    if (!$namespace || !$className) {
        throw new \Exception("Could not parse namespace or class name from $filepath");
    }
    
    return $namespace . '\\' . $className;
}

    /**
     * Get the fully qualified class name for a seeder file.
     *
     * @param string $filePath
     * @return string|null
     */
    // protected function getSeederClassName(string $filePath): ?string
    // {
    //     // Extract the namespace and class name from the file
    //     $namespace = null;
    //     $className = null;

    //     $lines = file($filePath);
    //     foreach ($lines as $line) {
    //         if (preg_match('/^namespace\s+(.+);$/', trim($line), $matches)) {
    //             $namespace = $matches[1];
    //         }

    //         if (preg_match('/^class\s+([a-zA-Z0-9_]+)\s/', trim($line), $matches)) {
    //             $className = $matches[1];
    //             break;
    //         }
    //     }

    //     return $namespace && $className ? $namespace . '\\' . $className : null;
    // }


    /**
     * Method loadModules
     *
     * @return void
     */
    public function loadModules(): void
    {
        foreach ($this->getInstalledModules() as $module) {
            $this->bootModule($module);
        }
    }

    /**
     * Method getInstalledModules
     * getting all modules
     *
     * @return array
     */
    protected function getInstalledModules(): array
    {
        if (empty($this->modules)) {
            $this->modules = DB::table('modules')
                ->where('panel_type', '=', panelAccess())
                ->where('status', 'active')
                ->get()
                ->toArray();
        }
        return $this->modules;
    }

    protected function getInstalledModule(string $name)
    {
        return DB::table('modules')
            ->where('name', $name)
            ->where('status', 'active')
            ->first();
    }


    protected function bootModule(object $module): void
    {
        Log::info("Coming from bootModule...");

        // Decode providers from the module and check if it's an array
        $providers = json_decode($module->providers, true);

        // Check if providers are in a valid format
        if (!is_array($providers)) {
            Log::warning("The providers for module {$module->name} are not in a valid format.");
            return;
        }

        // Loop through each provider to register it
        foreach ($providers as $provider) {
            Log::info("Providers Found: $provider");

            // Log the namespace for debugging
            Log::info("Namespace: " . $this->namespace);



            // Build the full provider class name path (PascalCase)
            $providerClass = $this->namespace . '\\' . $module->name . '\\' . $provider;

            // Log the full provider class path being checked
            Log::info("Full Provider Class Path: $providerClass");



            // Build the full file path based on the kebab-case format
            $filePath = base_path("modules/{$module->name}/{$provider}.php");

            // Log the full file path where the class should be located
            Log::info("Checking for provider class at path: $filePath");

            try {
                // Check if the class exists before attempting to register it
                if (class_exists($providerClass)) {


                    // Register the provider if the class exists
                    // Log before registering the provider
                    Log::info("Attempting to register provider: $providerClass");

                    try {
                        // Ensure the provider class exists
                        if (class_exists($providerClass)) {
                            Log::info("Registering provider: {$providerClass}");
                            app()->register(new $providerClass(app()));

                            $this->addProviderToConfig($providerClass);
                            Log::info("Provider registered successfully: {$providerClass}");
                        } else {
                            Log::warning("Provider class does not exist: {$providerClass}");
                            if (file_exists($filePath)) {
                                Log::warning("File exists at {$filePath}, but namespace or autoloading may be incorrect.");
                            }
                        }
                    } catch (\Exception $e) {
                        // Log any exception that occurs during registration
                        Log::error("Failed to register provider $providerClass: " . $e->getMessage());
                    }
                } else {
                    // Log a warning if the provider class does not exist at the given path
                    Log::warning("Provider class $providerClass does not exist at $filePath.");

                    // Try to check if the file itself exists as a fallback
                    if (file_exists($filePath)) {
                        Log::warning("File exists at $filePath, but the class cannot be found. There might be a namespace or autoloading issue.");
                    } else {
                        Log::warning("Provider file does not exist at $filePath.");
                    }
                }
            } catch (\Exception $e) {
                // Log any errors that occur during the provider registration
                Log::error("Error registering provider: " . $e->getMessage());
            }
        }
    }






    protected function addProviderToConfig(string $providerClass): void
    {
        // Path to the config/app.php file
        $configPath = config_path('app.php');

        // Get the content of the config file
        $configContent = file_get_contents($configPath);

        // Check if the provider is already in the config file
        if (strpos($configContent, $providerClass) === false) {
            // Using a more targeted regex pattern that matches the merge structure
            $pattern = '/ServiceProvider::defaultProviders\(\)->merge\(\[(.*?)\]\)->toArray\(\)/s';

            $replacement = function ($matches) use ($providerClass) {
                // Get the existing content within the merge array
                $currentContent = $matches[1];

                // Find the last provider in the array
                $lastCommaPosition = strrpos($currentContent, ',');

                if ($lastCommaPosition !== false) {
                    // Insert the new provider after the last existing provider
                    $newContent = substr_replace(
                        $currentContent,
                        ",\n        {$providerClass}::class",
                        $lastCommaPosition,
                        0
                    );
                } else {
                    // If there are no existing providers, add as first
                    $newContent = $currentContent . "\n        {$providerClass}::class";
                }

                return "ServiceProvider::defaultProviders()->merge([" . $newContent . "])->toArray()";
            };

            // Apply the regex replacement
            $newContent = preg_replace_callback($pattern, $replacement, $configContent);

            if ($newContent !== null && $newContent !== $configContent) {
                // Write the updated content back to the config file
                file_put_contents($configPath, $newContent);
                Log::info("Provider {$providerClass} added to config/app.php");
            } else {
                Log::error("Failed to update config/app.php with provider {$providerClass}");
            }
        } else {
            Log::info("Provider {$providerClass} already exists in config/app.php");
        }
    }

    protected function removeProviderFromConfig(string $providerClass): void
    {
        // Path to the config/app.php file
        $configPath = config_path('app.php');

        // Get the content of the config file
        $configContent = file_get_contents($configPath);

        // Check if the provider is in the config file
        if (strpos($configContent, $providerClass) !== false) {
            // Using a more targeted regex pattern that matches the merge structure
            $pattern = '/ServiceProvider::defaultProviders\(\)->merge\(\[(.*?)\]\)->toArray\(\)/s';

            $replacement = function ($matches) use ($providerClass) {
                // Get the existing content within the merge array
                $currentContent = $matches[1];

                // Remove the provider class from the content
                $newContent = preg_replace(
                    '/,\s*' . preg_quote($providerClass . '::class', '/') . '\s*/',
                    '',
                    $currentContent
                );

                // Rebuild the merge statement with the updated content
                return "ServiceProvider::defaultProviders()->merge([" . $newContent . "])->toArray()";
            };

            // Apply the regex replacement
            $newContent = preg_replace_callback($pattern, $replacement, $configContent);

            if ($newContent !== null && $newContent !== $configContent) {
                // Write the updated content back to the config file
                file_put_contents($configPath, $newContent);
                Log::info("Provider {$providerClass} removed from config/app.php");
            } else {
                Log::error("Failed to update config/app.php, provider {$providerClass} not found for removal");
            }
        } else {
            Log::info("Provider {$providerClass} does not exist in config/app.php");
        }
    }

protected function rollbackSeeder(string $moduleId): void
{
    Log::info("Running rollback for module: $moduleId");
    
    $seederPath = 'modules/' . $moduleId . '/database/seeders';
    Log::info("Relative Seeder Path: $seederPath");
    
    if (!File::exists(base_path($seederPath))) {
        Log::error('Seeder path does not exist: ' . $seederPath);
        return;
    }

    // Load module-specific autoload file if it exists
    $moduleAutoloadFile = base_path("modules/$moduleId/vendor/autoload.php");
    if (File::exists($moduleAutoloadFile)) {
        require_once $moduleAutoloadFile;
        Log::info("Loaded module autoload file: $moduleAutoloadFile");
    }

    $files = File::files(base_path($seederPath));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            Log::info("Skipping non-PHP file: " . $file->getFilename());
            continue;
        }

        try {
            // Include the file directly to ensure the class is loaded
            require_once $file->getPathname();
            
            $className = $this->getSeederClassName($file->getPathname());
            Log::info("Attempting to rollback seeder: $className");
            
            // Verify the class exists after loading
            if (!class_exists($className)) {
                Log::error("Class not found after loading file: $className");
                Log::info("File contents: " . File::get($file->getPathname()));
                continue;
            }

            // Create an instance of the seeder with Laravel's service container
            $seederInstance = app($className);
            
            if (!method_exists($seederInstance, 'rollback')) {
                Log::warning("Rollback method does not exist in class: $className");
                continue;
            }

            Log::info("Running rollback method for: $className");
            
            // Wrap the rollback in a database transaction
            DB::beginTransaction();
            try {
                $seederInstance->rollback();
                DB::commit();
                Log::info("Successfully rolled back seeder: $className");
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error("Error rolling back seeder: $className");
            Log::error("Exception: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
        }
    }
}


    public function uninstall(string $moduleId, $moduleSettings): bool
    {
        try {
            // Disable module first
            $this->disable($moduleId);

            // Run uninstall migrations if they exist
            $this->runUninstallMigrations($moduleId);
            $this->rollbackSeeder($moduleId);


            // Remove module files
            File::deleteDirectory($this->moduleDirectory . '/' . $moduleId);




            $providerClassName = "Modules\\$moduleId\\$moduleId" . "ServiceProvider";


            $this->removeProviderFromConfig($providerClassName);

            $settingsData = json_decode($moduleSettings->settings, true);
            \Log::info('Settings', $settingsData);
            // remove payment gateway class from factory if its a gateway module
            if (isset($settingsData) && isset($settingsData['payment_gateway']) && isset($settingsData['payment_gateway']['key'])) {
                PaymentGatewayFactoryModifier::removeGateway($settingsData['payment_gateway']['key']);
            }

            // Remove from database
            DB::table('modules')->where('name', $moduleId)->delete();

            return true;
        } catch (\Exception $e) {
            Log::error('Module uninstallation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function enable(string $moduleId): bool
    {
        try {
            DB::table('modules')
                ->where('name', $moduleId)
                ->update(['status' => 'active']);
            return true;
        } catch (\Exception $e) {
            Log::error('Module activation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function disable(string $moduleId): bool
    {
        try {
            DB::table('modules')
                ->where('name', $moduleId)
                ->update(['status' => 'inactive']);
            return true;
        } catch (\Exception $e) {
            Log::error('Module deactivation failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function cleanup(string $path): void
    {
        if (File::exists($path)) {
            File::delete($path);
        }
    }

    protected function runUninstallMigrations(string $moduleId): void
    {
        $migrationPath = $this->moduleDirectory . '/' . $moduleId . '/database/migrations';
        \Log::info('Rolling back the module migrations from ' . $migrationPath);

        if (File::exists($migrationPath)) {
            \Log::info('Migrations directory exists: ' . $migrationPath);

            try {
                // Get all migration files in the module directory
                $migrationFiles = File::files($migrationPath);

                \Log::info('Found migration files:', array_map(fn($file) => $file->getFilename(), $migrationFiles));

                foreach ($migrationFiles as $file) {
                    $migrationClassName = $this->getMigrationClassName($file->getPathname());

                    if (class_exists($migrationClassName)) {
                        \Log::info('Rolling back migration: ' . $migrationClassName);

                        // Run the down method for the migration class
                        (new $migrationClassName)->down();

                        // Remove the migration record from the migrations table
                        DB::table('migrations')
                            ->where('migration', basename($file->getFilename(), '.php'))
                            ->delete();
                    } else {
                        \Log::warning('Migration class not found for file: ' . $file->getFilename());
                    }
                }

                \Log::info('Module migrations rolled back successfully.');
            } catch (\Exception $e) {
                \Log::error('Error rolling back module migrations: ' . $e->getMessage());
            }
        } else {
            \Log::warning('Migration path does not exist: ' . $migrationPath);
        }
    }

    /**
     * Extract the migration class name from a migration file.
     */
    protected function getMigrationClassName(string $migrationFilePath): string
    {
        $content = file_get_contents($migrationFilePath);
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }

        throw new \RuntimeException('Unable to determine class name for migration file: ' . $migrationFilePath);
    }

}

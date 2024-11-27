<?php

namespace App\Core;

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

            Log::info("Module Extracted.");
            // Register module in database
            $this->registerModule($moduleData);

            Log::info("Module Registerd.");

            // Run migrations and seeders
            $this->runMigrations($moduleData['id']);

            Log::info("Migrations Ran.");



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

        return [
            'id' => $moduleJson['name'],
            'version' => $moduleJson['version'],
            'description' => $moduleJson['description'],
            'providers' => $moduleJson['providers'],
            'path' => $extractPath
        ];
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
                'status' => 'active',
                'panel_type' => panelAccess(),
                'updated_at' => now()
            ]
        );
    }

    // protected function runMigrations(string $moduleId): void
    // {
    //     Log::info("Running migrations for module: $moduleId");
    //     $migrationPath = $this->moduleDirectory . '/' . $moduleId . '/database/migrations';

    //     Log::info("Migration path: $migrationPath");

    //     if (File::exists($migrationPath)) {
    //         Log::info("Migration directory exists.");

    //         // Make the migration path relative to base_path
    //         $relativePath = str_replace(base_path() . '/', '', $migrationPath);

    //         Log::info("Relative path for migration: $relativePath");

    //         // Log all files in the directory
    //         $files = File::files($migrationPath);
    //         foreach ($files as $file) {
    //             Log::info("File Name: " . $file->getFilename());
    //         }

    //         try {
    //             // Call the Artisan migrate command with the correct relative path
    //             $exitCode = Artisan::call('migrate', [
    //                 '--path' => $relativePath,
    //                 '--force' => true,
    //             ]);

    //             Log::info("Artisan migrate output: " . Artisan::output());
    //             Log::info("Artisan migrate exit code: $exitCode");

    //             if ($exitCode !== 0) {
    //                 Log::error("Migration command failed with exit code: $exitCode");
    //             } else {
    //                 Log::info("Migrations executed successfully for module: $moduleId");
    //             }
    //         } catch (\Exception $e) {
    //             Log::error("Error running migration command: " . $e->getMessage());
    //         }
    //     } else {
    //         Log::error('Migration path does not exist: ' . $migrationPath);
    //     }
    // }



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



    // protected function addProviderToConfig(string $providerClass): void
    // {
    //     // Path to the config/app.php file
    //     $configPath = config_path('app.php');

    //     // Get the content of the config file
    //     $configContent = file_get_contents($configPath);

    //     // Check if the provider is already in the config file
    //     if (strpos($configContent, $providerClass) === false) {
    //         // Using a more targeted regex approach to insert the provider within the 'providers' array
    //         $pattern = '/\'providers\' => \[.*?\]/s'; // This will match the entire providers array
    //         $replacement = function ($matches) use ($providerClass) {
    //             // Append the new provider before the closing bracket of the array
    //             return preg_replace(
    //                 '/\]$/',
    //                 "\n        {$providerClass}::class,\n    ]",
    //                 $matches[0]
    //             );
    //         };

    //         // Apply the regex replacement
    //         $configContent = preg_replace_callback($pattern, $replacement, $configContent);

    //         // Write the updated content back to the config file
    //         file_put_contents($configPath, $configContent);

    //         Log::info("Provider {$providerClass} added to config/app.php");
    //     } else {
    //         Log::info("Provider {$providerClass} already exists in config/app.php");
    //     }
    // }



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

    public function uninstall(string $moduleId): bool
    {
        try {
            // Disable module first
            $this->disable($moduleId);

            // Run uninstall migrations if they exist
            $this->runUninstallMigrations($moduleId);

            // Remove module files
            File::deleteDirectory($this->moduleDirectory . '/' . $moduleId);


            // create provider class
            // $moduleId = 'BlogModule';
            //Modules\BlogModule\BlogModuleServiceProvider

            $providerClassName = "Modules\\$moduleId\\$moduleId" . "ServiceProvider";


            $this->removeProviderFromConfig($providerClassName);

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
        if (File::exists($migrationPath)) {
            Artisan::call('migrate:rollback', [
                '--path' => str_replace(base_path(), '', $migrationPath),
                '--force' => true
            ]);
        }
    }
}

<?php

namespace App\Core;

use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;


class ModuleManager
{
    protected $modules = [];
    protected $moduleDirectory;
    protected $namespace;

    public function __construct()
    {
        $this->moduleDirectory = config('modules.module_directory');
        $this->namespace = config('modules.namespace');
    }

    public function install(string $modulePath): bool
    {
        try {
            // Validate module package
            if (!$this->validateModule($modulePath)) {
                throw new \Exception('Invalid module package');
            }

            // Extract module
            $moduleData = $this->extractModule($modulePath);

            // Register module in database
            $this->registerModule($moduleData);

            // Run migrations and seeders
            $this->runMigrations($moduleData['id']);

            return true;
        } catch (\Exception $e) {
            Log::error('Module installation failed: ' . $e->getMessage());
            $this->cleanup($modulePath);
            return false;
        }
    }

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

    protected function isCompatibleVersion(string $current, string $required): bool
    {
        return version_compare($current, trim($required, '>=<~^'), '>=');
    }

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
                'updated_at' => now()
            ]
        );
    }

    protected function runMigrations(string $moduleId): void
    {
        $migrationPath = $this->moduleDirectory . '/' . $moduleId . '/database/migrations';

        // Ensure the module path is correctly set and the migrations folder exists
        if (File::exists($migrationPath)) {
            $relativePath = str_replace(base_path('database/migrations'), '', $migrationPath);

            Artisan::call('migrate', [
                '--path' => $relativePath, // Use relative path from base migration directory
                '--force' => true
            ]);
        } else {
            Log::error('Migration path does not exist: ' . $migrationPath);
        }
    }


    public function loadModules(): void
    {
        foreach ($this->getInstalledModules() as $module) {
            $this->bootModule($module);
        }
    }

    protected function getInstalledModules(): array
    {
        if (empty($this->modules)) {
            $this->modules = DB::table('modules')
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
        $providers = json_decode($module->providers, true);
        foreach ($providers as $provider) {
            $providerClass = $this->namespace . '\\' . $module->name . '\\' . $provider;
            if (class_exists($providerClass)) {
                app()->register(new $providerClass());
            }
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

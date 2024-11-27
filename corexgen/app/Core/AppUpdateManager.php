<?php

namespace App\Core;

use Illuminate\Support\Facades\Artisan;
use ZipArchive;

class AppUpdateManager
{

    /**
     * Method install
     *
     * @param string $updateFilePath installing the module
     *
     * @return bool
     */
    public function install(string $updateFilePath): bool
    {
        try {
            // Validate module package
            $newVersion = $this->validateUpdateAndExtractVersionNumber($updateFilePath);

            // 
            $currentVersion = config('app.version') ?? env('APP_VERSION', '1.0.0');
            if ($this->isNewerVersion($currentVersion, $newVersion)) {
                throw new \Exception("Update version {$newVersion} is not newer than current version {$currentVersion}");
            }


            // override the files

            $this->overrideProjectFiles($updateFilePath, $newVersion);

            // udpate the current version on config and .env
            $this->updateVersionConfig($newVersion);

            \Log::info("App Update Loaded $updateFilePath validation Passeed.");
            // Extract module



            return true;
        } catch (\Exception $e) {
            \Log::error('update installation failed: ' . $e->getMessage());

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
    protected function validateUpdateAndExtractVersionNumber(string $path)
    {
        if (!file_exists($path)) {
            throw new \Exception('Update file not found at: ' . $path);
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \Exception('Unable to open update file: ' . $path);
        }

        try {

            $requiredFiles = [
                'version.txt',
                'composer.json',
                // Add other critical files specific to your app
            ];

            foreach ($requiredFiles as $requiredFile) {
                if ($zip->locateName($requiredFile) === false) {
                    $zip->close();
                    return false;
                }
            }


            // Debug: List all files in the ZIP
            $versionFile = $zip->getFromName('version.txt') ?: $zip->getFromName('.version');
            if ($versionFile) {
                return trim($versionFile);
            }

            return pathinfo($path, PATHINFO_FILENAME);

        } finally {
            $zip->close();
        }
    }


    /**
     * Compare if new version is newer
     *
     * @param string $currentVersion
     * @param string $newVersion
     * @return bool
     */
    protected function isNewerVersion(string $currentVersion, string $newVersion): bool
    {
        return $newVersion > $currentVersion;
    }

    /**
     * Override project files
     *
     * @param string $extractPath
     */
    protected function overrideProjectFiles(string $zipFilePath, string $newVersion)
    {
        \Log::info('Starting the update process');

        $zip = new ZipArchive();

        // Open the zip file
        if ($zip->open($zipFilePath) === true) {
            \Log::info('Zip file opened successfully');

            // Temporary extraction path within storage to avoid direct overwrites
            $tempExtractPath = storage_path('app/update_temp');

            // Ensure the temporary extraction directory is clean
            if (is_dir($tempExtractPath)) {
                $this->deleteDirectory($tempExtractPath);
            }
            mkdir($tempExtractPath, 0755, true);

            // Extract to temporary path
            $zip->extractTo($tempExtractPath);
            $zip->close();
            \Log::info('Zip extracted to temporary path: ' . $tempExtractPath);

            // Perform file replacement from the temp path to base_path
            \Log::info('Replacing files in base path...');
            $this->recursiveCopy($tempExtractPath, base_path(), ['.env', 'storage', 'public/uploads']);

            // Clean up temporary extraction directory
            $this->deleteDirectory($tempExtractPath);

            \Log::info("App successfully updated to version {$newVersion}");
            return true;
        } else {
            \Log::error('Could not open update package');
            throw new \Exception('Could not open update package');
        }
    }


    /**
     * Recursively copy files
     *
     * @param string $source
     * @param string $destination
     */
    protected function recursiveCopy(string $source, string $destination, array $skip = [])
    {
        $directory = opendir($source);
        @mkdir($destination, 0755, true);

        while (false !== ($file = readdir($directory))) {
            if ($file !== '.' && $file !== '..') {
                $srcFile = $source . DIRECTORY_SEPARATOR . $file;
                $destFile = $destination . DIRECTORY_SEPARATOR . $file;

                // Skip specified files or directories
                if (in_array($file, $skip)) {
                    \Log::info("Skipping {$srcFile}");
                    continue;
                }

                if (is_dir($srcFile)) {
                    $this->recursiveCopy($srcFile, $destFile, $skip);
                } else {
                    copy($srcFile, $destFile);
                }
            }
        }
        closedir($directory);
    }


    protected function deleteDirectory(string $dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }



    /**
     * Update version in configuration
     *
     * @param string $newVersion
     */
    protected function updateVersionConfig(string $newVersion)
    {
        // Update .env file
        $this->updateEnvFile('APP_VERSION', $newVersion);

        // Reload configuration
        Artisan::call('config:clear');
    }

    /**
     * Update .env file
     *
     * @param string $key
     * @param string $value
     */
    protected function updateEnvFile(string $key, string $value)
    {
        $path = base_path('.env');

        // Read the current .env contents
        $contents = file_get_contents($path);

        // Replace or add the version
        if (strpos($contents, $key) !== false) {
            $contents = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $contents);
        } else {
            $contents .= "\n{$key}={$value}";
        }

        // Write back to .env
        file_put_contents($path, $contents);
    }

    /**
     * Recursively remove directory
     *
     * @param string $directory
     */
    protected function recursiveRemoveDirectory(string $directory)
    {
        $files = glob($directory . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->recursiveRemoveDirectory($file) : unlink($file);
        }
        rmdir($directory);
    }



}
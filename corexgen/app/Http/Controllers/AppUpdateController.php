<?php

namespace App\Http\Controllers;

use App\Core\AppUpdateManager;
use Illuminate\Http\Request;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class AppUpdateController extends Controller
{
    //
    use TenantFilter;


    /**
     * Tenant-specific route prefix
     * @var string
     */
    private $tenantRoute;

    /**
     * Base directory for view files
     * @var string
     */
    private $viewDir = 'appupdates.';

    /**
     * Generate full view file path
     * 
     * @param string $filename
     * @return string
     */
    private function getViewFilePath($filename)
    {
        return $this->viewDir . $filename;
    }

    /**
     *  updateManager object var
     * @var 
     */
    protected $updateManager;



    /**
     * Method index returning exiting modules
     *

     */
    public function index()
    {

        return view($this->getViewFilePath('index'), ['module' => PANEL_MODULES[$this->getPanelModule()]['appupdates'], 'title' => 'App Updates Management']);
    }

    /**
     * Method create
     *
     * @param Request $request uploding the module
     *

     */
    public function create(Request $request)
    {

        $this->tenantRoute = $this->getTenantRoute();

        $this->updateManager = new AppUpdateManager();

        $request->validate([
            'appupdate' => 'required|file|mimes:zip' // max 10MB
        ]);


        $file = $request->file('appupdate');
        $path = Storage::disk('local')->putFile('temp/appupdate', $file);
        $fullPath = Storage::disk('local')->path($path);

        try {

            $this->performPreUpdateChecks();

            if ($this->updateManager->install($fullPath)) {

                $this->runPostUpdateTasks();

                $this->cleanupTempFiles('temp/appupdate');
                // Run the "php artisan optimize" command
                Artisan::call('optimize');

                return redirect()->route($this->tenantRoute . 'appupdates.index')
                    ->with('success', 'Update installed successfully');
            }

            return redirect()->route($this->tenantRoute . 'appupdates.index')
                ->with('error', 'Failed to install update');
        } finally {
            // Cleanup temp file
            Storage::disk('local')->delete($path);
        }
    }



    /**
     * Method runComposerDumpAutoload
     * running compoer dump
     *
     * @return void
     */
    private function runComposerDumpAutoload()
    {
        // Get the base path (root directory of your Laravel project)
        $basePath = base_path();

        // Run composer dump-autoload from the base path
        $output = shell_exec("cd {$basePath} && composer dump-autoload 2>&1");

        if ($output === null) {
            Log::error('Failed to execute composer dump-autoload. No output returned.');
        } else {
            // Log::info('composer dump-autoload executed. Output: ' . $output);
            Log::info('composer dump-autoload executed. Output: want to logs the output change this log');
        }
    }

    /**
     * Run post-update tasks
     */
    private function runPostUpdateTasks()
    {
        // Run composer dump-autoload with improved error handling
        $this->runComposerDumpAutoload();

        // Clear various caches
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        // Optional: Run database migrations
        Artisan::call('migrate', ['--force' => true]);


    }

    /**
     * Perform pre-update system checks
     * 
     * @throws \Exception If system is not ready for update
     */
    private function performPreUpdateChecks()
    {
        // Check available disk space
        $minimumRequiredSpace = 500 * 1024 * 1024; // 500MB
        $freeSpace = disk_free_space(base_path());

        if ($freeSpace < $minimumRequiredSpace) {
            throw new \Exception('Insufficient disk space for update');
        }

        // Check PHP version compatibility
        $currentPhpVersion = PHP_VERSION;
        $requiredPhpVersion = '8.0.1'; // Example version requirement

        if (version_compare($currentPhpVersion, $requiredPhpVersion, '<')) {
            throw new \Exception("PHP version {$requiredPhpVersion} or higher required");
        }
    }

    /**
     * Clean up temporary files
     *
     * @param string $path Path to temporary file
     */
    private function cleanupTempFiles(string $path)
    {
        try {
            Storage::disk('local')->delete($path);
        } catch (\Exception $e) {
            Log::warning('Failed to delete temporary update file: ' . $e->getMessage());
        }
    }

}

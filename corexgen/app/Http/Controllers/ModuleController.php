<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ModuleManager;
use App\Models\Module;
use App\Traits\TenantFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller
{
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
    private $viewDir = 'modules.';

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
     *  moduleManager object var
     * @var 
     */
    protected $moduleManager;

  

    /**
     * Method index returning exiting modules
     *

     */
    public function index()
    {
        if (getModule() == 'saas' && panelAccess() == PANEL_TYPES['COMPANY_PANEL']) {
            abort(403);
        }

        $this->tenantRoute = $this->getTenantRoute();

        $panel_type = panelAccess();
        $modules = Module::where('panel_type', '=', $panel_type)->get();
        return view($this->getViewFilePath('index'), ['modules' => $modules, 'module' => PANEL_MODULES[$this->getPanelModule()]['modules'], 'title' => 'Modules Management']);
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

        $this->moduleManager = new ModuleManager();

        $request->validate([
            'module' => 'required|file|mimes:zip|max:10240' // max 10MB
        ]);

        $file = $request->file('module');
        $path = Storage::disk('local')->putFile('temp/modules', $file);
        $fullPath = Storage::disk('local')->path($path);

        try {
            if ($this->moduleManager->install($fullPath)) {

                $this->updateComposerJson('addAutoloadModuleComposerJson.php');
                $this->runComposerDumpAutoload();

                // Run the "php artisan optimize" command
                Artisan::call('optimize');

                return redirect()->route($this->tenantRoute . 'modules.index')
                    ->with('success', 'Module installed successfully');
            }

            return redirect()->route($this->tenantRoute . 'modules.index')
                ->with('error', 'Failed to install module');
        } finally {
            // Cleanup temp file
            Storage::disk('local')->delete($path);
        }
    }


    /**
     * Method updateComposerJson
     *
     * @param $filename $filename updating the composer file
     *
     * @return void
     */
    private function updateComposerJson($filename)
    {
        // Run the updateComposerJson.php script
        require_once base_path('composers_task/' . $filename);
        Log::info('COmposer Update file called.');
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



    // public function enable(string $module)
    // {
    //     if ($this->moduleManager->enable($module)) {
    //         return redirect()->route('modules.index')
    //             ->with('success', 'Module enabled successfully');
    //     }

    //     return redirect()->route('modules.index')
    //         ->with('error', 'Failed to enable module');
    // }

    // public function disable(string $module)
    // {
    //     if ($this->moduleManager->disable($module)) {
    //         return redirect()->route('modules.index')
    //             ->with('success', 'Module disabled successfully');
    //     }

    //     return redirect()->route('modules.index')
    //         ->with('error', 'Failed to disable module');
    // }

    /**
     * Method destroy
     *
     * @param string $module deleting the module after
     *
   
     */
    public function destroy(string $module)
    {
        $this->tenantRoute = $this->getTenantRoute();
        $this->moduleManager = new ModuleManager();
        $moduleSettings = DB::table('modules')->where('name' , $module)->first();
        if ($this->moduleManager->uninstall($module,$moduleSettings)) {

            $this->updateComposerJson('addAutoloadModuleComposerJson.php');
            $this->runComposerDumpAutoload();

            // Run the "php artisan optimize" command
            Artisan::call('optimize');


            return redirect()->route($this->tenantRoute . 'modules.index')
                ->with('success', 'Module uninstalled successfully');
        }

        return redirect()->route($this->tenantRoute . 'modules.index')
            ->with('error', 'Failed to uninstall module');
    }
}

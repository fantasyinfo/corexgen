<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class ModuleController extends Controller
{
    protected $moduleManager;

    public function __construct(ModuleManager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    public function index()
    {
        $modules = DB::table('modules')->get();
        return view('modules.index', compact('modules'));
    }

    public function upload(Request $request)
    {
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

                return redirect()->route('admin.modules.index')
                    ->with('success', 'Module installed successfully');
            }

            return redirect()->route('admin.modules.index')
                ->with('error', 'Failed to install module');
        } finally {
            // Cleanup temp file
            Storage::disk('local')->delete($path);
        }
    }

 
    private function updateComposerJson($filename)
    {
        // Run the updateComposerJson.php script
        require_once base_path($filename);
        Log::info('COmposer Update file called.');
    }

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
            Log::info('composer dump-autoload executed. Output: want to logs the output change this log' );
        }
    }
    
    

    public function enable(string $module)
    {
        if ($this->moduleManager->enable($module)) {
            return redirect()->route('admin.modules.index')
                ->with('success', 'Module enabled successfully');
        }

        return redirect()->route('admin.modules.index')
            ->with('error', 'Failed to enable module');
    }

    public function disable(string $module)
    {
        if ($this->moduleManager->disable($module)) {
            return redirect()->route('admin.modules.index')
                ->with('success', 'Module disabled successfully');
        }

        return redirect()->route('admin.modules.index')
            ->with('error', 'Failed to disable module');
    }

    public function destroy(string $module)
    {
        if ($this->moduleManager->uninstall($module)) {

            $this->updateComposerJson('addAutoloadModuleComposerJson.php');
            $this->runComposerDumpAutoload();

            // Run the "php artisan optimize" command
            Artisan::call('optimize');

            
            return redirect()->route('admin.modules.index')
                ->with('success', 'Module uninstalled successfully');
        }

        return redirect()->route('admin.modules.index')
            ->with('error', 'Failed to uninstall module');
    }
}

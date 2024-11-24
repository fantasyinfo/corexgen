<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Core\ModuleManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            return redirect()->route('admin.modules.index')
                ->with('success', 'Module uninstalled successfully');
        }

        return redirect()->route('admin.modules.index')
            ->with('error', 'Failed to uninstall module');
    }
}

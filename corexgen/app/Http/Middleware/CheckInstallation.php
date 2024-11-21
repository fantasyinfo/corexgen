<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\File;

class CheckInstallation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if installation is complete
        $installationFile = storage_path('installed.lock');
        
        // If no installation file exists, redirect to installer
        if (!File::exists($installationFile)) {
         
            return redirect()->route('installer.index');
        }

   
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\CRM\CRMSettings;
use App\Traits\TenantFilter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;

class ApplyTheme
{

    use TenantFilter;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $userId = Auth::id(); // Get the authenticated user ID
    
            // Cache key based on user and company (if applicable)
            $cacheKey = 'theme_settings_user_' . $userId . '_company_' . (Auth::user()->company_id ?? 'global');
    
            // Fetch from cache or query the database and store the result
            $themeSettings = Cache::remember($cacheKey, now()->addMinutes(60), function () {
                return $this->applyTenantFilter(CRMSettings::where('type', 'Theme'))->get()->toArray();
            });
    
            // Share the settings with views
            view()->share('themeSettings', $themeSettings);
        }
    
        return $next($request);
    }
}

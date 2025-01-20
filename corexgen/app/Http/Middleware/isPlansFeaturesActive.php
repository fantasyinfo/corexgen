<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class isPlansFeaturesActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $module): Response
    {

   
        // info('Checking ' . $module . ' Featuers Permissions');
        if (!isFeatureEnabled($module)) {
            return redirect()->route(getPanelRoutes('planupgrade.index'))->with('error', 'This feature is not enabled on your current plan, please upgrade your plan.');
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {

        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                $panelAccess = panelAccess();

                if ($panelAccess === PANEL_TYPES['SUPER_PANEL']) {
                    return redirect(route(getPanelUrl(PANEL_TYPES['SUPER_PANEL']) . '.role.index'));
                } else if ($panelAccess === PANEL_TYPES['COMPANY_PANEL']) {
                    return redirect(route(getPanelUrl(PANEL_TYPES['COMPANY_PANEL']) . '.role.index'));
                }
            }
        }

        return $next($request);
    }
}

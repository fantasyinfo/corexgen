<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\CRM\CRMRole;
use Illuminate\Support\Facades\Auth;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$requiredRole = null): Response
    {
          // Check if the user is authenticated
          if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $userRoleId = $user->role_id;


        // return true for superadmin or role id 1
        if ($user->is_tenant && $userRoleId === null) {
            return $next($request);
        }

        // Fetch the role_name from the CRMRole model
        $role = CRMRole::find($user->role_id);

        if (!$role) {
            abort(403, 'Unauthorized action. Role not found.');
        }

        // Check if a specific role is required and matches the user's role
        if ($requiredRole && $role->role_name !== $requiredRole) {
            abort(403, 'Unauthorized action. Insufficient role permissions.');
        }

        // Attach role_name to the request for later use
        $request->merge(['user_role_name' => $role->role_name]);

        return $next($request);
    }
}

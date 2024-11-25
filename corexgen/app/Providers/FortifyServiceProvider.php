<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });


        // Custom Authentication Logic
        Fortify::authenticateUsing(function (Request $request) {
            $isTenant = filter_var($request->input('is_tenant'), FILTER_VALIDATE_BOOLEAN); // Ensure boolean value
            $email = $request->input('email');
            $password = $request->input('password');
        
            // Fetch active user by email
            $user = User::where('email', $email)
                ->where('status', CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE'])
                ->first();
        
            if ($user && Hash::check($password, $user->password)) {
                // Super admin / tenant check
                if ($isTenant && $user->is_tenant && $user->tenant_id !== null) {
                    $tenant = Tenant::where('id', $user->tenant_id)
                        ->where('status', CRM_STATUS_TYPES['TENANTS']['STATUS']['ACTIVE'])
                        ->first();
                    if ($tenant) {
                        $user->tenant_id = $tenant->id;
                        session(['panelAccess' => PANEL_TYPES['SUPER_PANEL']]);
                        return $user;
                    }
                    return null; // Tenant is inactive
                }
        
                // Normal user
    
                session(['panelAccess' => PANEL_TYPES['COMPANY_PANEL']]);
                return $user;
            }
        
            return null; // Invalid credentials or inactive user
        });
        
        
    }
}

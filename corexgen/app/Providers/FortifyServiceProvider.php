<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\Company;
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
use Illuminate\Validation\ValidationException;

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
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });


        // Instead of silently returning null, provide more informative error handling
        Fortify::authenticateUsing(function (Request $request) {
            $guard = Auth::guard('web');

    

            // Logout the current user if authenticated
            if ($guard->check()) {
                $guard->logout();
            }

            $path = $request->input('path', '');
            $isTenant = filter_var($request->input('is_tenant'), FILTER_VALIDATE_BOOLEAN);
            $email = $request->input('email');
            $password = $request->input('password');

            // Fetch active user by email
            $user = User::where('email', $email)
                ->where('status', CRM_STATUS_TYPES['USERS']['STATUS']['ACTIVE'])
                ->first();

            if (!$user) {
                throw ValidationException::withMessages([
                    'email' => ['Login Details Not Matched.'],
                ]);
            }

            // Password check
            if (!Hash::check($password, $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Login Details Not Matched.'],
                ]);
            }

            // Company status check
            if (!$isTenant && $user->company_id !== null) {
                $company = Company::find($user->company_id);

                if (
                    !$company ||
                    ($company->status !== CRM_STATUS_TYPES['COMPANIES']['STATUS']['ACTIVE'] &&
                        $company->status !== CRM_STATUS_TYPES['COMPANIES']['STATUS']['ONBOARDING'])
                ) {
                    throw ValidationException::withMessages([
                        'email' => ['Your company account is currently inactive.'],
                    ]);
                }
            }

            // Tenant status check
            if ($isTenant && $path === 'super-admin-login') {
                $tenant = Tenant::find($user->tenant_id);

                if (!$tenant || $tenant->status !== CRM_STATUS_TYPES['TENANTS']['STATUS']['ACTIVE']) {
                    throw ValidationException::withMessages([
                        'email' => ['Your tenant account is currently inactive.'],
                    ]);
                }
            }

            // Set panel access based on user type
            $panelAccess = match (true) {
                $isTenant && $user->role_id === null => PANEL_TYPES['SUPER_PANEL'],
                $isTenant && $user->role_id !== null => PANEL_TYPES['SUPER_PANEL'],
                !$isTenant && $user->role_id === null => PANEL_TYPES['COMPANY_PANEL'],
                !$isTenant && $user->role_id !== null => PANEL_TYPES['COMPANY_PANEL'],
                default => null
            };

            if (!$panelAccess) {
                throw ValidationException::withMessages([
                    'email' => ['You are not authorized to access this panel.'],
                ]);
            }

            session(['panelAccess' => $panelAccess]);
            return $user;
        });


    }
}

<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
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
            $buyerId = $request->input('buyer_id');
            $email = $request->input('email');
            $password = $request->input('password');
        
            // Query the `users` table and join with the `buyers` table directly
            $user = DB::table('users')
                ->join('buyers', 'buyers.id', '=', 'users.buyer_id')
                ->where('users.email', $email)
                ->where('users.status', 'active')
                ->where('buyers.buyer_id', $buyerId)
                ->select('users.*') // Select user fields
                ->first();
        
            // Verify password
            if ($user && Hash::check($password, $user->password)) {
                return \App\Models\User::find($user->id);
            }
        
            return null;
        });
        
    }
}

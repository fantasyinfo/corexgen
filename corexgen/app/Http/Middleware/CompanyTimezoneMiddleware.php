<?php
namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CompanyTimezoneMiddleware
{
    public function handle($request, Closure $next)
    {


        try {
            if (auth()->check()) {
                $company = auth()->user()->company;

                if ($company) {
                    $timezone = getSettingValue('Time Zone') ?: config('app.timezone');
                    $dateFormat = getSettingValue('Date Format') ?: 'd M Y, h:i A';

                    // Validate timezone before setting
                    if ($timezone && in_array($timezone, timezone_identifiers_list())) {
                        date_default_timezone_set($timezone);

                        config(['app.timezone' => $timezone]);
                    } else {
                        Log::warning("Invalid timezone setting for company ID: {$company->id}");
                        // Fallback to default timezone
                        date_default_timezone_set(config('app.timezone'));
                    }

                    // Set date format if valid
                    if ($dateFormat) {
                        try {
                            // Test if the date format is valid by attempting to format current date
                            Carbon::now()->format($dateFormat);

                            Carbon::setToStringFormat($dateFormat);
                        } catch (\Exception $e) {
                            Log::warning("Invalid date format setting for company ID: {$company->id}. Error: {$e->getMessage()}");
                        }
                    }
                }

                // for tenant
                if (auth()->user()->is_tenant) {
                    $timezone = getSettingValue('Panel Time Zone') ?: config('app.timezone');
                    $dateFormat = getSettingValue('Panel Date Format') ?: 'd M Y, h:i A';

                    // Validate timezone before setting
                    if ($timezone && in_array($timezone, timezone_identifiers_list())) {
                        date_default_timezone_set($timezone);

                        config(['app.timezone' => $timezone]);
                    } else {
                        Log::warning("Invalid timezone setting for tenant: " . auth()->user()->id);
                        // Fallback to default timezone
                        date_default_timezone_set(config('app.timezone'));
                    }

                    // Set date format if valid
                    if ($dateFormat) {
                        try {
                            // Test if the date format is valid by attempting to format current date
                            Carbon::now()->format($dateFormat);

                            Carbon::setToStringFormat($dateFormat);
                        } catch (\Exception $e) {
                            Log::warning("Invalid date format setting for company ID: " . auth()->user()->id . ". Error: {$e->getMessage()}");
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error("Error in CompanyTimezoneMiddleware: {$e->getMessage()}");
            // Ensure we have a valid timezone even if something fails
            date_default_timezone_set(config('app.timezone'));
        }

        return $next($request);
    }
}
<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        require_once app_path('Helpers/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginator::defaultView('layout.components.pagination');
   

        Builder::macro('whereLower', function ($column, $operator, $value) {
            return $this->whereRaw("LOWER({$column}) {$operator} ?", [$value]);
        });
    
        Builder::macro('orWhereLower', function ($column, $operator, $value) {
            return $this->orWhereRaw("LOWER({$column}) {$operator} ?", [$value]);
        });
    }
}

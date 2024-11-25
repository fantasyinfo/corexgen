<?php

namespace Modules\BlogModule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;

use Modules\BlogModule\Repositories\PostRepository;
use Modules\BlogModule\Repositories\EloquentPostRepository;

class BlogModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

        $routesPath = __DIR__ . '/routes/web.php';
        $viewsDir = __DIR__ . '/resources/views';
        $translationDir = __DIR__ . '/resources/lang';
        $migrationsDir = __DIR__ . '/database/migrations';

        // Check if routes file exists
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
            Log::info("Loaded routes from: $routesPath");
        } else {
            Log::error("Routes file not found: $routesPath");
        }

        // Check if views directory exists
        if (is_dir($viewsDir)) {
            $this->loadViewsFrom($viewsDir, 'BlogModule');
            Log::info("Loaded views from: $viewsDir");
        } else {
            Log::error("Views directory not found: $viewsDir");
        }

        // Check if translations directory exists
        if (is_dir($translationDir)) {
            $this->loadTranslationsFrom($translationDir, 'BlogModule');
            Log::info("Loaded translations from: $translationDir");
        } else {
            Log::error("Translations directory not found: $translationDir");
        }

        // Check if migrations directory exists
        if (is_dir($migrationsDir)) {
            $this->loadMigrationsFrom($migrationsDir); // Correct method for migrations
            Log::info("Loaded migrations from: $migrationsDir");
        } else {
            Log::error("Migrations directory not found: $migrationsDir");
        }

        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }
}

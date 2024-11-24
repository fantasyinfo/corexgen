<?php

namespace Modules\BlogModule;

use App\Core\BaseModule;
use Illuminate\Support\Facades\Gate;

class BlogModuleServiceProvider extends BaseModule
{
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'blog-module');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Register permissions
        Gate::define('manage-blog', function ($user) {
            return $user->hasPermission('blog.manage');
        });
    }

    public function register(): void
    {
        $this->app->bind(PostRepository::class, EloquentPostRepository::class);
    }
}

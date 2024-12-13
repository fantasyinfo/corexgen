<?php

namespace Modules\PaypalGatewayModule;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;
use ReflectionClass;

class PaypalGatewayModuleServiceProvider extends ServiceProvider
{
    protected $moduleName = 'PaypalGatewayModule';
    protected $moduleNamespace = 'Modules\PaypalGatewayModule';

    public function boot()
    {
        $this->registerModule();
    }

    protected function registerModule()
    {
        $modulePath = __DIR__;

        // Register routes
        $this->registerRoutes($modulePath);

        // Register views
        $this->registerViews($modulePath);

        // Register translations
        $this->registerTranslations($modulePath);

        // Register migrations
        $this->registerMigrations($modulePath);

        // Register module components
        $this->registerModuleComponents($modulePath);
    }

    protected function registerRoutes($modulePath)
    {
        $routesPath = $modulePath . '/routes/web.php';
        
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
            // Log::info("Loaded routes from: $routesPath");
        } else {
            // Log::warning("Routes file not found: $routesPath");
        }
    }

    protected function registerViews($modulePath)
    {
        $viewsPath = $modulePath . '/resources/views';
        
        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, $this->moduleName);
            // Log::info("Loaded views from: $viewsPath");
        } else {
            // Log::warning("Views directory not found: $viewsPath");
        }
    }

    protected function registerTranslations($modulePath)
    {
        $translationsPath = $modulePath . '/resources/lang';
        
        if (is_dir($translationsPath)) {
            $this->loadTranslationsFrom($translationsPath, $this->moduleName);
            // Log::info("Loaded translations from: $translationsPath");
        } else {
            // Log::warning("Translations directory not found: $translationsPath");
        }
    }

    protected function registerMigrations($modulePath)
    {
        $migrationsPath = $modulePath . '/database/migrations';
        
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
            // Log::info("Loaded migrations from: $migrationsPath");
        } else {
            // Log::warning("Migrations directory not found: $migrationsPath");
        }
    }

    protected function registerModuleComponents($modulePath)
    {
        $appPath = $modulePath . '/app';
        $componentDirectories = [
            'Controllers' => ['namespace' => 'Controllers', 'method' => 'registerControllers'],
            'Services' => ['namespace' => 'Services', 'method' => 'registerServices'],
            'Middleware' => ['namespace' => 'Middleware', 'method' => 'registerMiddleware'],
            'Jobs' => ['namespace' => 'Jobs', 'method' => 'registerJobs'],
            'Actions' => ['namespace' => 'Actions', 'method' => 'registerActions'],
            'Repositories' => ['namespace' => 'Repositories', 'method' => 'registerRepositories']
        ];

        foreach ($componentDirectories as $directory => $config) {
            $path = $appPath . '/' . $directory;
            
            if (is_dir($path) && method_exists($this, $config['method'])) {
                $this->{$config['method']}($path, $config['namespace']);
            }
        }
    }

    protected function registerControllers($path, $namespace)
    {
        $this->app->make('Illuminate\Routing\Router')
            ->namespace($this->moduleNamespace . '\\' . $namespace)
            ->group(function ($router) use ($path) {
                // Potential route group configurations
            });
    }

    protected function registerServices($path, $namespace)
    {
        $this->bindClassesInNamespace($path, $namespace, function($class) {
            $this->app->bind($class, $class);
        });
    }

    protected function registerMiddleware($path, $namespace)
    {
        $this->bindClassesInNamespace($path, $namespace, function($class) {
            $this->app['router']->aliasMiddleware(
                strtolower(class_basename($class)), 
                $class
            );
        });
    }

    protected function bindClassesInNamespace($path, $namespace, $bindingCallback)
    {
        $finder = new Finder();
        $finder->files()->in($path)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = str_replace('/', '\\', substr($file->getRelativePathname(), 0, -4));
            $fullClassName = $this->moduleNamespace . '\\app\\' . $namespace . '\\' . $relativePath;

            if (class_exists($fullClassName)) {
                $reflectionClass = new ReflectionClass($fullClassName);
                
                // Skip abstract classes and interfaces
                if (!$reflectionClass->isAbstract() && !$reflectionClass->isInterface()) {
                    $bindingCallback($fullClassName);
                    Log::info("Registered {$fullClassName}");
                }
            }
        }
    }

    public function register()
    {
        // Optional: Register configuration, singleton bindings, etc.
        // $this->mergeConfigFrom(
        //     __DIR__ . '/config/paypal.php', 
        //     'paypal'
        // );
    }
}
<?php

namespace LaravelShopper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelShopper\Console\Commands\OptimizeCommand;
use LaravelShopper\Contracts\ProductRepositoryInterface;
use LaravelShopper\Repositories\ProductRepository;
use LaravelShopper\Services\CacheService;
use LaravelShopper\Services\InventoryService;
use LaravelShopper\Services\NotificationService;
use LaravelShopper\Services\WebhookService;

class ShopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shopper.php', 'shopper');
        $this->mergeConfigFrom(__DIR__.'/../config/shopper-performance.php', 'shopper-performance');

        // Register OAuth services configuration
        if (file_exists(__DIR__.'/../config/services.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
        }

        // Register services
        $this->app->singleton(CacheService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(WebhookService::class);

        // Register repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                OptimizeCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->bootRoutes();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'shopper');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shopper.php' => config_path('shopper.php'),
            ], 'shopper-config');

            // Publish OAuth services configuration
            $this->publishes([
                __DIR__.'/../config/services.php' => config_path('services-oauth.php'),
            ], 'shopper-oauth-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/shopper'),
            ], 'shopper-views');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/shopper'),
            ], 'shopper-assets');

            // Publish Vue components
            $this->publishes([
                __DIR__.'/../resources/js/Components' => resource_path('js/Components/Shopper'),
            ], 'shopper-components');

            // Publish translations
            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path(),
            ], 'shopper-lang');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'shopper-migrations');

            // Publish performance configuration
            $this->publishes([
                __DIR__.'/../config/shopper-performance.php' => config_path('shopper-performance.php'),
            ], 'shopper-performance-config');

            // Publish OpenAPI documentation
            $this->publishes([
                __DIR__.'/../openapi.yaml' => base_path('openapi.yaml'),
            ], 'shopper-docs');

            // Register commands
            $this->commands([
                \LaravelShopper\Console\Commands\InstallShopperCommand::class,
                OptimizeCommand::class,
            ]);

            // Register middleware aliases
            $router = $this->app['router'];
            $router->aliasMiddleware('cp', \LaravelShopper\Http\Middleware\ControlPanelMiddleware::class);
            $router->aliasMiddleware('shopper.inertia', \LaravelShopper\Http\Middleware\HandleInertiaRequests::class);
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shopper');
    }

    protected function bootRoutes(): void
    {
        Route::group([
            'middleware' => ['web'],
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/cp.php');
        });

        // Load OAuth routes if they exist
        if (file_exists(__DIR__.'/../routes/auth.php')) {
            Route::group([
                'middleware' => ['web'],
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/auth.php');
            });
        }

        // Load API OAuth routes if they exist
        if (file_exists(__DIR__.'/../routes/api-auth.php')) {
            Route::group([
                'middleware' => ['api'],
                'prefix' => 'api',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../routes/api-auth.php');
            });
        }
    }
}

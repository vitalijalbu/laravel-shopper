<?php

namespace LaravelShopper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ShopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shopper.php', 'shopper');
        
        // Register OAuth services configuration
        if (file_exists(__DIR__ . '/../config/services.php')) {
            $this->mergeConfigFrom(__DIR__ . '/../config/services.php', 'services');
        }
    }

    public function boot(): void
    {
        $this->bootRoutes();
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        // Load translations
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'shopper');
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/shopper.php' => config_path('shopper.php'),
            ], 'shopper-config');

            // Publish OAuth services configuration
            $this->publishes([
                __DIR__ . '/../config/services.php' => config_path('services-oauth.php'),
            ], 'shopper-oauth-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/shopper'),
            ], 'shopper-views');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/shopper'),
            ], 'shopper-assets');

            // Publish Vue components
            $this->publishes([
                __DIR__ . '/../resources/js/Components' => resource_path('js/Components/Shopper'),
            ], 'shopper-components');

            // Publish translations
            $this->publishes([
                __DIR__ . '/../lang' => lang_path(),
            ], 'shopper-lang');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'shopper-migrations');

            // Register commands
            $this->commands([
                \LaravelShopper\Console\Commands\InstallShopperCommand::class,
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

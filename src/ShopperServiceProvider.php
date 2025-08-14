<?php

namespace LaravelShopper;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ShopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/shopper.php', 'shopper');
    }

    public function boot(): void
    {
        $this->bootRoutes();
        
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/shopper.php' => config_path('shopper.php'),
            ], 'shopper-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/shopper'),
            ], 'shopper-views');

            $this->publishes([
                __DIR__ . '/../resources/js' => resource_path('js/vendor/shopper'),
            ], 'shopper-assets');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'shopper-migrations');
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
    }
}

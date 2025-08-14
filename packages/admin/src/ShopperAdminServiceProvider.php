<?php

namespace VitaliJalbu\LaravelShopper\Admin;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ShopperAdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register admin-specific services
    }

    public function boot(): void
    {
        if (config('shopper.admin.enabled', true)) {
            $this->registerRoutes();
            $this->registerViews();
            $this->registerTranslations();
            $this->registerAssets();
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('shopper.admin.route_prefix', 'admin'),
            'middleware' => config('shopper.admin.middleware', ['web', 'auth:sanctum']),
            'namespace' => 'VitaliJalbu\\LaravelShopper\\Admin\\Http\\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        });

        Route::group([
            'prefix' => 'api/' . config('shopper.admin.route_prefix', 'admin'),
            'middleware' => ['api', 'auth:sanctum'],
            'namespace' => 'VitaliJalbu\\LaravelShopper\\Admin\\Http\\Controllers\\Api',
        ], function () {
            $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        });
    }

    protected function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'shopper-admin');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/resources/views' => resource_path('views/vendor/shopper-admin'),
            ], 'shopper-admin-views');
        }
    }

    protected function registerTranslations(): void
    {
        $this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'shopper-admin');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/resources/lang' => lang_path('vendor/shopper-admin'),
            ], 'shopper-admin-lang');
        }
    }

    protected function registerAssets(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/resources/js' => resource_path('js/vendor/shopper-admin'),
                __DIR__ . '/resources/css' => resource_path('css/vendor/shopper-admin'),
            ], 'shopper-admin-assets');
        }
    }
}

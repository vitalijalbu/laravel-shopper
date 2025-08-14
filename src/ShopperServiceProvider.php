<?php

namespace VitaliJalbu\LaravelShopper;

use Illuminate\Support\ServiceProvider;
use VitaliJalbu\LaravelShopper\Core\ShopperCoreServiceProvider;
use VitaliJalbu\LaravelShopper\Admin\ShopperAdminServiceProvider;

class ShopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ShopperCoreServiceProvider::class);
        $this->app->register(ShopperAdminServiceProvider::class);

        $this->mergeConfigFrom(__DIR__ . '/../config/shopper.php', 'shopper');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                \VitaliJalbu\LaravelShopper\Console\InstallCommand::class,
            ]);

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
    }
}

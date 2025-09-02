<?php

namespace Shopper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Shopper\Console\Commands\OptimizeCommand;
use Shopper\Console\Commands\ShowAdminUsersCommand;
use Shopper\Console\CreateAdminUserCommand;
use Shopper\Contracts\ProductRepositoryInterface;
use Shopper\Providers\InertiaServiceProvider;
use Shopper\Repositories\CustomerRepository;
use Shopper\Repositories\OrderRepository;
use Shopper\Repositories\PaymentGatewayRepository;
use Shopper\Repositories\ProductRepository;
use Shopper\Repositories\SettingRepository;
use Shopper\Repositories\ShippingMethodRepository;
use Shopper\Repositories\TaxRateRepository;
use Shopper\Services\CacheService;
use Shopper\Services\InventoryService;
use Shopper\Services\NotificationService;
use Shopper\Services\WebhookService;

class ShopperServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/shopper.php', 'shopper');
        $this->mergeConfigFrom(__DIR__.'/../config/shopper-performance.php', 'shopper-performance');
        $this->mergeConfigFrom(__DIR__.'/../config/permission.php', 'permission');

        // Register OAuth services configuration
        if (file_exists(__DIR__.'/../config/services.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
        }

        // Register Inertia Service Provider
        $this->app->register(InertiaServiceProvider::class);

        // Register services
        $this->app->singleton(CacheService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(WebhookService::class);

        // Register repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->singleton(CustomerRepository::class);
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(SettingRepository::class);
        $this->app->singleton(PaymentGatewayRepository::class);
        $this->app->singleton(TaxRateRepository::class);
        $this->app->singleton(ShippingMethodRepository::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                OptimizeCommand::class,
                CreateAdminUserCommand::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->bootRoutes();
        $this->configureAuthentication();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'shopper');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/shopper.php' => config_path('shopper.php'),
            ], 'shopper-config');

            // Publish permission configuration
            $this->publishes([
                __DIR__.'/../config/permission.php' => config_path('permission.php'),
            ], 'shopper-permission-config');

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
                \Shopper\Console\Commands\InstallShopperCommand::class,
                ShowAdminUsersCommand::class,
                OptimizeCommand::class,
            ]);
        }

        // Register middleware aliases (always, not just in console)
        $router = $this->app['router'];
        $router->aliasMiddleware('cp', \Shopper\Http\Middleware\ControlPanelMiddleware::class);
        $router->aliasMiddleware('shopper.inertia', \Shopper\Http\Middleware\HandleInertiaRequests::class);
        $router->aliasMiddleware('shopper.auth', \Shopper\Http\Middleware\Authenticate::class);

        // Register policies
        $this->registerPolicies();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shopper');
    }

    protected function configureAuthentication(): void
    {
        // Configure the default login route for redirects
        config(['auth.defaults.login_route' => 'cp.login']);

        // Configure Inertia root view
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::setRootView('shopper::app');
        }

        // Set the login path for unauthenticated redirects
        if (method_exists($this->app['auth'], 'setDefaultDriver')) {
            $this->app['auth']->viaRequest('api', function ($request) {
                // Custom auth logic if needed
                return null;
            });
        }
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

    /**
     * Register the package policies.
     */
    protected function registerPolicies(): void
    {
        $gate = $this->app[\Illuminate\Contracts\Auth\Access\Gate::class];

        // Register policies for Shopper models
        $policies = [
            \Shopper\Models\Product::class => \Shopper\Policies\ProductPolicy::class,
            // Add more model-policy mappings here as needed
        ];

        foreach ($policies as $model => $policy) {
            $gate->policy($model, $policy);
        }

        // Register control panel gates
        $gate->define('access-cp', \Shopper\Policies\ControlPanelPolicy::class.'@access');
        $gate->define('view-dashboard', \Shopper\Policies\ControlPanelPolicy::class.'@viewDashboard');
        $gate->define('view-analytics', \Shopper\Policies\ControlPanelPolicy::class.'@viewAnalytics');
        $gate->define('view-reports', \Shopper\Policies\ControlPanelPolicy::class.'@viewReports');
        $gate->define('manage-settings', \Shopper\Policies\ControlPanelPolicy::class.'@manageSettings');
        $gate->define('edit-settings', \Shopper\Policies\ControlPanelPolicy::class.'@editSettings');
        $gate->define('manage-users', \Shopper\Policies\ControlPanelPolicy::class.'@manageUsers');
        $gate->define('manage-roles', \Shopper\Policies\ControlPanelPolicy::class.'@manageRoles');
    }
}

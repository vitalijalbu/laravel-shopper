<?php

namespace LaravelShopper;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use LaravelShopper\Console\Commands\OptimizeCommand;
use LaravelShopper\Console\CreateAdminUserCommand;
use LaravelShopper\Contracts\ProductRepositoryInterface;
use LaravelShopper\Providers\InertiaServiceProvider;
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
                \LaravelShopper\Console\Commands\InstallShopperCommand::class,
                OptimizeCommand::class,
            ]);
        }

        // Register middleware aliases (always, not just in console)
        $router = $this->app['router'];
        $router->aliasMiddleware('cp', \LaravelShopper\Http\Middleware\ControlPanelMiddleware::class);
        $router->aliasMiddleware('shopper.inertia', \LaravelShopper\Http\Middleware\HandleInertiaRequests::class);
        $router->aliasMiddleware('shopper.auth', \LaravelShopper\Http\Middleware\Authenticate::class);

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
            \LaravelShopper\Models\Product::class => \LaravelShopper\Policies\ProductPolicy::class,
            // Add more model-policy mappings here as needed
        ];

        foreach ($policies as $model => $policy) {
            $gate->policy($model, $policy);
        }

        // Register control panel gates
        $gate->define('access-cp', \LaravelShopper\Policies\ControlPanelPolicy::class.'@access');
        $gate->define('view-dashboard', \LaravelShopper\Policies\ControlPanelPolicy::class.'@viewDashboard');
        $gate->define('view-analytics', \LaravelShopper\Policies\ControlPanelPolicy::class.'@viewAnalytics');
        $gate->define('view-reports', \LaravelShopper\Policies\ControlPanelPolicy::class.'@viewReports');
        $gate->define('manage-settings', \LaravelShopper\Policies\ControlPanelPolicy::class.'@manageSettings');
        $gate->define('edit-settings', \LaravelShopper\Policies\ControlPanelPolicy::class.'@editSettings');
        $gate->define('manage-users', \LaravelShopper\Policies\ControlPanelPolicy::class.'@manageUsers');
        $gate->define('manage-roles', \LaravelShopper\Policies\ControlPanelPolicy::class.'@manageRoles');
    }
}

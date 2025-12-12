<?php

namespace Cartino;

use Cartino\Console\Commands\ExpireFidelityPoints;
use Cartino\Console\Commands\OptimizeCommand;
use Cartino\Console\Commands\ShowAdminUsersCommand;
use Cartino\Console\CreateAdminUserCommand;
use Cartino\Contracts\ProductRepositoryInterface;
use Cartino\Providers\InertiaServiceProvider;
use Cartino\Repositories\CustomerRepository;
use Cartino\Repositories\OrderRepository;
use Cartino\Repositories\PaymentGatewayRepository;
use Cartino\Repositories\ProductRepository;
use Cartino\Repositories\SettingRepository;
use Cartino\Repositories\ShippingMethodRepository;
use Cartino\Repositories\TaxRateRepository;
use Cartino\Services\CacheService;
use Cartino\Services\FidelityService;
use Cartino\Services\InventoryService;
use Cartino\Services\NotificationService;
use Cartino\Services\WebhookService;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CartinoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cartino.php', 'cartino');
        $this->mergeConfigFrom(__DIR__.'/../config/permission.php', 'permission');

        // Register OAuth services configuration
        if (file_exists(__DIR__.'/../config/services.php')) {
            $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');
        }

        // Register Inertia Service Provider
        $this->app->register(InertiaServiceProvider::class);

        // Register services
        $this->app->singleton(CacheService::class);
        $this->app->singleton(FidelityService::class);
        $this->app->singleton(InventoryService::class);
        $this->app->singleton(NotificationService::class);
        $this->app->singleton(WebhookService::class);

        // Register repositories
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(\Cartino\Contracts\SupplierRepositoryInterface::class, \Cartino\Repositories\SupplierRepository::class);
        $this->app->singleton(\Cartino\Repositories\BrandRepository::class);
        $this->app->singleton(\Cartino\Repositories\ChannelRepository::class);
        $this->app->singleton(\Cartino\Repositories\CountryRepository::class);
        $this->app->singleton(\Cartino\Repositories\CurrencyRepository::class);
        $this->app->singleton(\Cartino\Repositories\CategoryRepository::class);
        $this->app->singleton(CustomerRepository::class);
        $this->app->singleton(OrderRepository::class);
        $this->app->singleton(SettingRepository::class);
        $this->app->singleton(PaymentGatewayRepository::class);
        $this->app->singleton(TaxRateRepository::class);
        $this->app->singleton(ShippingMethodRepository::class);
        $this->app->singleton(\Cartino\Repositories\BrandRepository::class);

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                ExpireFidelityPoints::class,
            ]);
        }
    }

    public function boot(): void
    {
        $this->bootRoutes();
        $this->configureAuthentication();
        $this->registerMiddleware();

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'cartino');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cartino.php' => config_path('cartino.php'),
            ], 'cartino-config');

            // Publish permission configuration
            $this->publishes([
                __DIR__.'/../config/permission.php' => config_path('permission.php'),
            ], 'cartino-permission-config');

            // Publish OAuth services configuration
            $this->publishes([
                __DIR__.'/../config/services.php' => config_path('services-oauth.php'),
            ], 'cartino-oauth-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/cartino'),
            ], 'cartino-views');

            $this->publishes([
                __DIR__.'/../resources/js' => resource_path('js/vendor/cartino'),
            ], 'cartino-assets');

            // Publish built assets to public/vendor/cartino
            $this->publishes([
                __DIR__.'/../public/vendor/cartino' => public_path('vendor/cartino'),
            ], 'cartino-assets-built');

            // Publish Vue components
            $this->publishes([
                __DIR__.'/../resources/js/Components' => resource_path('js/Components/Cartino'),
            ], 'cartino-components');

            // Publish translations
            $this->publishes([
                __DIR__.'/../resources/lang' => lang_path(),
            ], 'cartino-lang');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'cartino-migrations');

            // Publish OpenAPI documentation
            $this->publishes([
                __DIR__.'/../openapi.yaml' => base_path('openapi.yaml'),
            ], 'cartino-docs');

            // Register commands
            $this->commands([
                \Cartino\Console\Commands\InstallShopperCommand::class,
                \Cartino\Console\Commands\BuildAssetsCommand::class,
                CreateAdminUserCommand::class,
                ShowAdminUsersCommand::class,
                OptimizeCommand::class,
            ]);
        }

        // Register middleware aliases (always, not just in console)
        $router = $this->app['router'];
        $router->aliasMiddleware('cp', \Cartino\Http\Middleware\ControlPanelMiddleware::class);
        $router->aliasMiddleware('cartino.inertia', \Cartino\Http\Middleware\HandleInertiaRequests::class);
        $router->aliasMiddleware('cartino.auth', \Cartino\Http\Middleware\Authenticate::class);

        // Register policies
        $this->registerPolicies();

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'cartino');
    }

    protected function configureAuthentication(): void
    {
        // Configure the default login route for redirects
        config(['auth.defaults.login_route' => 'cp.login']);

        // Configure Inertia root view
        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::setRootView('cartino::app');
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

        // Register policies for Cartino models
        $policies = [
            \Cartino\Models\Address::class => \Cartino\Policies\AddressPolicy::class,
            \Cartino\Models\AnalyticsEvent::class => \Cartino\Policies\AnalyticsEventPolicy::class,
            \Cartino\Models\App::class => \Cartino\Policies\AppPolicy::class,
            \Cartino\Models\AppApiToken::class => \Cartino\Policies\AppApiTokenPolicy::class,
            \Cartino\Models\AppInstallation::class => \Cartino\Policies\AppInstallationPolicy::class,
            \Cartino\Models\AppReview::class => \Cartino\Policies\AppReviewPolicy::class,
            \Cartino\Models\AppWebhook::class => \Cartino\Policies\AppWebhookPolicy::class,
            \Cartino\Models\Asset::class => \Cartino\Policies\AssetPolicy::class,
            \Cartino\Models\AssetContainer::class => \Cartino\Policies\AssetContainerPolicy::class,
            \Cartino\Models\AssetFolder::class => \Cartino\Policies\AssetFolderPolicy::class,
            \Cartino\Models\AssetTransformation::class => \Cartino\Policies\AssetTransformationPolicy::class,
            \Cartino\Models\Brand::class => \Cartino\Policies\BrandPolicy::class,
            \Cartino\Models\Cart::class => \Cartino\Policies\CartPolicy::class,
            \Cartino\Models\CartLine::class => \Cartino\Policies\CartLinePolicy::class,
            \Cartino\Models\Catalog::class => \Cartino\Policies\CatalogPolicy::class,
            \Cartino\Models\Category::class => \Cartino\Policies\CategoryPolicy::class,
            \Cartino\Models\Channel::class => \Cartino\Policies\ChannelPolicy::class,
            \Cartino\Models\Country::class => \Cartino\Policies\CountryPolicy::class,
            \Cartino\Models\Currency::class => \Cartino\Policies\CurrencyPolicy::class,
            \Cartino\Models\Customer::class => \Cartino\Policies\CustomerPolicy::class,
            \Cartino\Models\CustomerAddress::class => \Cartino\Policies\CustomerAddressPolicy::class,
            \Cartino\Models\CustomerGroup::class => \Cartino\Policies\CustomerGroupPolicy::class,
            \Cartino\Models\Discount::class => \Cartino\Policies\DiscountPolicy::class,
            \Cartino\Models\DiscountApplication::class => \Cartino\Policies\DiscountApplicationPolicy::class,
            \Cartino\Models\Entry::class => \Cartino\Policies\EntryPolicy::class,
            \Cartino\Models\Favorite::class => \Cartino\Policies\FavoritePolicy::class,
            \Cartino\Models\FidelityCard::class => \Cartino\Policies\FidelityCardPolicy::class,
            \Cartino\Models\FidelityTransaction::class => \Cartino\Policies\FidelityTransactionPolicy::class,
            \Cartino\Models\GlobalSet::class => \Cartino\Policies\GlobalPolicy::class,
            \Cartino\Models\Menu::class => \Cartino\Policies\MenuPolicy::class,
            \Cartino\Models\MenuItem::class => \Cartino\Policies\MenuItemPolicy::class,
            \Cartino\Models\Order::class => \Cartino\Policies\OrderPolicy::class,
            \Cartino\Models\OrderLine::class => \Cartino\Policies\OrderLinePolicy::class,
            \Cartino\Models\Page::class => \Cartino\Policies\PagePolicy::class,
            \Cartino\Models\PaymentGateway::class => \Cartino\Policies\PaymentGatewayPolicy::class,
            \Cartino\Models\Product::class => \Cartino\Policies\ProductPolicy::class,
            \Cartino\Models\ProductOption::class => \Cartino\Policies\ProductOptionPolicy::class,
            \Cartino\Models\ProductReview::class => \Cartino\Policies\ProductReviewPolicy::class,
            \Cartino\Models\ProductSupplier::class => \Cartino\Policies\ProductSupplierPolicy::class,
            \Cartino\Models\ProductType::class => \Cartino\Policies\ProductTypePolicy::class,
            \Cartino\Models\ProductVariant::class => \Cartino\Policies\ProductVariantPolicy::class,
            \Cartino\Models\PurchaseOrder::class => \Cartino\Policies\PurchaseOrderPolicy::class,
            \Cartino\Models\PurchaseOrderItem::class => \Cartino\Policies\PurchaseOrderItemPolicy::class,
            \Cartino\Models\ReviewMedia::class => \Cartino\Policies\ReviewMediaPolicy::class,
            \Cartino\Models\ReviewVote::class => \Cartino\Policies\ReviewVotePolicy::class,
            \Cartino\Models\Setting::class => \Cartino\Policies\SettingPolicy::class,
            \Cartino\Models\ShippingMethod::class => \Cartino\Policies\ShippingMethodPolicy::class,
            \Cartino\Models\ShippingRate::class => \Cartino\Policies\ShippingRatePolicy::class,
            \Cartino\Models\ShippingZone::class => \Cartino\Policies\ShippingZonePolicy::class,
            \Cartino\Models\ShopperPage::class => \Cartino\Policies\ShopperPagePolicy::class,
            \Cartino\Models\Site::class => \Cartino\Policies\SitePolicy::class,
            \Cartino\Models\SocialAccount::class => \Cartino\Policies\SocialAccountPolicy::class,
            \Cartino\Models\StockNotification::class => \Cartino\Policies\StockNotificationPolicy::class,
            \Cartino\Models\StorefrontSection::class => \Cartino\Policies\StorefrontSectionPolicy::class,
            \Cartino\Models\StorefrontTemplate::class => \Cartino\Policies\StorefrontTemplatePolicy::class,
            \Cartino\Models\StorefrontTemplateSection::class => \Cartino\Policies\StorefrontTemplateSectionPolicy::class,
            \Cartino\Models\Supplier::class => \Cartino\Policies\SupplierPolicy::class,
            \Cartino\Models\TaxRate::class => \Cartino\Policies\TaxRatePolicy::class,
            \Cartino\Models\Transaction::class => \Cartino\Policies\TransactionPolicy::class,
            \Cartino\Models\User::class => \Cartino\Policies\UserPolicy::class,
            \Cartino\Models\UserGroup::class => \Cartino\Policies\UserGroupPolicy::class,
            \Cartino\Models\UserPreference::class => \Cartino\Policies\UserPreferencePolicy::class,
            \Cartino\Models\VariantPrice::class => \Cartino\Policies\VariantPricePolicy::class,
            \Cartino\Models\Wishlist::class => \Cartino\Policies\WishlistPolicy::class,
            \Cartino\Models\WishlistItem::class => \Cartino\Policies\WishlistItemPolicy::class,
        ];

        foreach ($policies as $model => $policy) {
            $gate->policy($model, $policy);
        }

        // Register control panel gates
        $gate->define('access-cp', \Cartino\Policies\ControlPanelPolicy::class.'@access');
        $gate->define('view-dashboard', \Cartino\Policies\ControlPanelPolicy::class.'@viewDashboard');
        $gate->define('view-analytics', \Cartino\Policies\ControlPanelPolicy::class.'@viewAnalytics');
        $gate->define('view-reports', \Cartino\Policies\ControlPanelPolicy::class.'@viewReports');
        $gate->define('manage-settings', \Cartino\Policies\ControlPanelPolicy::class.'@manageSettings');
        $gate->define('edit-settings', \Cartino\Policies\ControlPanelPolicy::class.'@editSettings');
        $gate->define('manage-users', \Cartino\Policies\ControlPanelPolicy::class.'@manageUsers');
        $gate->define('manage-roles', \Cartino\Policies\ControlPanelPolicy::class.'@manageRoles');
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('force.json', \Cartino\Http\Middleware\ForceJsonResponse::class);
    }
}

<?php

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Cp\AppsController;
use Shopper\Http\Controllers\Cp\Auth\AuthenticatedSessionController;
use Shopper\Http\Controllers\Cp\Auth\NewPasswordController;
use Shopper\Http\Controllers\Cp\Auth\PasswordResetLinkController;
use Shopper\Http\Controllers\Cp\CollectionsController;
use Shopper\Http\Controllers\Cp\CustomersController;
use Shopper\Http\Controllers\Cp\DashboardController;
use Shopper\Http\Controllers\Cp\DiscountController;
use Shopper\Http\Controllers\Cp\EntriesController;
use Shopper\Http\Controllers\Cp\NavigationController;
use Shopper\Http\Controllers\Cp\NavigationItemController;
use Shopper\Http\Controllers\Cp\OrdersController;
use Shopper\Http\Controllers\Cp\PaymentGatewaysController;
use Shopper\Http\Controllers\Cp\SettingsController;
use Shopper\Http\Controllers\Cp\ShippingMethodsController;
use Shopper\Http\Controllers\Cp\TaxRatesController;

/*
|--------------------------------------------------------------------------
| Control Panel Routes
|--------------------------------------------------------------------------
|
| These routes handle the Shopper Control Panel interface, similar to
| Statamic CMS. All routes are prefixed with the CP route prefix.
|
*/

// CP prefix from config (default: 'cp')
$cpPrefix = config('cp.route_prefix', 'cp');

Route::prefix($cpPrefix)->name('cp.')->middleware(['web', 'shopper.inertia'])->group(function () {

    // Authentication Routes (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    // Authenticated Routes (CP access required)
    Route::middleware(['shopper.auth', 'cp', 'shopper.inertia'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');

        // Collections Management
        Route::get('/collections', [CollectionsController::class, 'index'])->name('collections.index');
        Route::get('/collections/create', [CollectionsController::class, 'create'])->name('collections.create');
        Route::get('/collections/{collection}', [CollectionsController::class, 'show'])->name('collections.show');
        Route::get('/collections/{collection}/edit', [CollectionsController::class, 'edit'])->name('collections.edit');

        // Collection Entries Management
        Route::prefix('collections/{collection}')->name('collections.')->group(function () {
            Route::get('/entries', [EntriesController::class, 'index'])->name('entries.index');
            Route::get('/entries/create', [EntriesController::class, 'create'])->name('entries.create');
            Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('entries.show');
            Route::get('/entries/{entry}/edit', [EntriesController::class, 'edit'])->name('entries.edit');
        });

        // Navigation Management
        Route::prefix('navigations')->name('navigations.')->group(function () {
            Route::get('/', [NavigationController::class, 'index'])->name('index');
            Route::get('/create', [NavigationController::class, 'create'])->name('create');
            Route::post('/', [NavigationController::class, 'store'])->name('store');
            Route::get('/{navigation}', [NavigationController::class, 'show'])->name('show');
            Route::get('/{navigation}/edit', [NavigationController::class, 'edit'])->name('edit');
            Route::put('/{navigation}', [NavigationController::class, 'update'])->name('update');
            Route::delete('/{navigation}', [NavigationController::class, 'destroy'])->name('destroy');
            Route::post('/{navigation}/duplicate', [NavigationController::class, 'duplicate'])->name('duplicate');

            // Navigation Items Management
            Route::prefix('{navigation}')->name('items.')->group(function () {
                Route::post('/items', [NavigationItemController::class, 'store'])->name('store');
                Route::put('/items/{item}', [NavigationItemController::class, 'update'])->name('update');
                Route::delete('/items/{item}', [NavigationItemController::class, 'destroy'])->name('destroy');
                Route::post('/items/reorder', [NavigationItemController::class, 'reorder'])->name('reorder');
                Route::post('/items/{item}/move', [NavigationItemController::class, 'move'])->name('move');
            });
        });

        // Utilities
        Route::get('/utilities', function () {
            return inertia('Utilities/Index');
        })->name('utilities.index');

        Route::get('/utilities/import', function () {
            return inertia('Utilities/Import');
        })->name('utilities.import');

        Route::get('/utilities/export', function () {
            return inertia('Utilities/Export');
        })->name('utilities.export');

        // Apps Management
        Route::prefix('apps')->name('apps.')->group(function () {
            Route::get('/', [AppsController::class, 'store'])->name('store');
            Route::get('/installed', [AppsController::class, 'installed'])->name('installed');
            Route::get('/submit', [AppsController::class, 'submit'])->name('submit');
            Route::get('/{app}/configure', [AppsController::class, 'configure'])->name('configure');
            Route::post('/install', [AppsController::class, 'install'])->name('install');
            Route::delete('/{app}/uninstall', [AppsController::class, 'uninstall'])->name('uninstall');
            Route::post('/{installation}/activate', [AppsController::class, 'activate'])->name('activate');
            Route::post('/{installation}/deactivate', [AppsController::class, 'deactivate'])->name('deactivate');
            Route::post('/bulk-activate', [AppsController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [AppsController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/bulk-uninstall', [AppsController::class, 'bulkUninstall'])->name('bulk-uninstall');
            Route::get('/{installation}/analytics', [AppsController::class, 'analytics'])->name('analytics');
            Route::post('/{app}/reviews', [AppsController::class, 'storeReview'])->name('reviews.store');
        });

        // Customers Management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomersController::class, 'index'])->name('index');
            Route::post('/', [CustomersController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomersController::class, 'show'])->name('show');
            Route::put('/{customer}', [CustomersController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomersController::class, 'destroy'])->name('destroy');

            // Customer Addresses
            Route::prefix('{customer}/addresses')->name('addresses.')->group(function () {
                Route::get('/', [CustomerAddressController::class, 'index'])->name('index');
                Route::get('/create', [CustomerAddressController::class, 'create'])->name('create');
                Route::post('/', [CustomerAddressController::class, 'store'])->name('store');
                Route::get('/{address}', [CustomerAddressController::class, 'show'])->name('show');
                Route::get('/{address}/edit', [CustomerAddressController::class, 'edit'])->name('edit');
                Route::put('/{address}', [CustomerAddressController::class, 'update'])->name('update');
                Route::delete('/{address}', [CustomerAddressController::class, 'destroy'])->name('destroy');
                Route::post('/{address}/set-default', [CustomerAddressController::class, 'setDefault'])->name('set-default');
            });

            // Customer Wishlists
            Route::prefix('{customer}/wishlists')->name('wishlists.')->group(function () {
                Route::get('/', [WishlistController::class, 'index'])->name('index');
                Route::post('/', [WishlistController::class, 'store'])->name('store');
                Route::get('/{wishlist}', [WishlistController::class, 'show'])->name('show');
                Route::put('/{wishlist}', [WishlistController::class, 'update'])->name('update');
                Route::delete('/{wishlist}', [WishlistController::class, 'destroy'])->name('destroy');
                Route::post('/{wishlist}/items', [WishlistController::class, 'addItem'])->name('items.store');
                Route::delete('/{wishlist}/items/{item}', [WishlistController::class, 'removeItem'])->name('items.destroy');
            });

            // Customer Favorites
            Route::prefix('{customer}/favorites')->name('favorites.')->group(function () {
                Route::get('/', [FavoriteController::class, 'index'])->name('index');
                Route::post('/toggle', [FavoriteController::class, 'toggle'])->name('toggle');
                Route::delete('/{favorite}', [FavoriteController::class, 'destroy'])->name('destroy');
            });
        });

        // Abandoned Carts Management
        Route::prefix('abandoned-carts')->name('abandoned-carts.')->group(function () {
            Route::get('/', [AbandonedCartController::class, 'index'])->name('index');
            Route::get('/{abandonedCart}', [AbandonedCartController::class, 'show'])->name('show');
            Route::post('/{abandonedCart}/send-recovery-email', [AbandonedCartController::class, 'sendRecoveryEmail'])->name('send-recovery-email');
            Route::post('/bulk-send-recovery-emails', [AbandonedCartController::class, 'bulkSendRecoveryEmails'])->name('bulk-send-recovery-emails');
            Route::post('/{abandonedCart}/mark-recovered', [AbandonedCartController::class, 'markAsRecovered'])->name('mark-recovered');
            Route::delete('/{abandonedCart}', [AbandonedCartController::class, 'destroy'])->name('destroy');
            Route::delete('/bulk-delete', [AbandonedCartController::class, 'bulkDelete'])->name('bulk-delete');
        });

        // Stock Notifications Management
        Route::prefix('stock-notifications')->name('stock-notifications.')->group(function () {
            Route::get('/', [StockNotificationController::class, 'index'])->name('index');
            Route::get('/{stockNotification}', [StockNotificationController::class, 'show'])->name('show');
            Route::post('/notify-for-product', [StockNotificationController::class, 'notifyForProduct'])->name('notify-for-product');
            Route::post('/{stockNotification}/send-notification', [StockNotificationController::class, 'sendNotification'])->name('send-notification');
            Route::delete('/{stockNotification}', [StockNotificationController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-notify', [StockNotificationController::class, 'bulkNotify'])->name('bulk-notify');
        });

        // Bulk Product Edit
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/bulk-edit', [ProductBulkEditController::class, 'index'])->name('bulk-edit');
            Route::post('/bulk-update', [ProductBulkEditController::class, 'bulkUpdate'])->name('bulk-update');
        });

        // Orders Management
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrdersController::class, 'index'])->name('index');
            Route::post('/', [OrdersController::class, 'store'])->name('store');
            Route::get('/{order}', [OrdersController::class, 'show'])->name('show');
            Route::put('/{order}', [OrdersController::class, 'update'])->name('update');
            Route::patch('/{order}/status', [OrdersController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{order}', [OrdersController::class, 'destroy'])->name('destroy');
        });

        // Discounts Management
        Route::prefix('discounts')->name('discounts.')->group(function () {
            Route::get('/', [DiscountController::class, 'index'])->name('index');
            Route::get('/create', [DiscountController::class, 'create'])->name('create');
            Route::post('/', [DiscountController::class, 'store'])->name('store');
            Route::get('/{discount}', [DiscountController::class, 'show'])->name('show');
            Route::get('/{discount}/edit', [DiscountController::class, 'edit'])->name('edit');
            Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
            Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
        });

        // Settings Management
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingsController::class, 'index'])->name('index');
            Route::get('/general', [SettingsController::class, 'general'])->name('general');
            Route::put('/general', [SettingsController::class, 'updateGeneral'])->name('general.update');
            Route::get('/checkout', [SettingsController::class, 'checkout'])->name('checkout');
            Route::put('/checkout', [SettingsController::class, 'updateCheckout'])->name('checkout.update');
            Route::get('/email', [SettingsController::class, 'email'])->name('email');
            Route::put('/email', [SettingsController::class, 'updateEmail'])->name('email.update');

            // Payment Gateways
            Route::prefix('payment-gateways')->name('payment-gateways.')->group(function () {
                Route::get('/', [PaymentGatewaysController::class, 'index'])->name('index');
                Route::post('/', [PaymentGatewaysController::class, 'store'])->name('store');
                Route::get('/{paymentGateway}', [PaymentGatewaysController::class, 'show'])->name('show');
                Route::put('/{paymentGateway}', [PaymentGatewaysController::class, 'update'])->name('update');
                Route::patch('/{paymentGateway}/toggle-status', [PaymentGatewaysController::class, 'toggleStatus'])->name('toggle-status');
                Route::patch('/{paymentGateway}/set-default', [PaymentGatewaysController::class, 'setDefault'])->name('set-default');
                Route::patch('/{paymentGateway}/config', [PaymentGatewaysController::class, 'updateConfig'])->name('update-config');
                Route::post('/sort-order', [PaymentGatewaysController::class, 'updateSortOrder'])->name('sort-order');
                Route::delete('/{paymentGateway}', [PaymentGatewaysController::class, 'destroy'])->name('destroy');
            });

            // Tax Rates
            Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
                Route::get('/', [TaxRatesController::class, 'index'])->name('index');
                Route::post('/', [TaxRatesController::class, 'store'])->name('store');
                Route::get('/{taxRate}', [TaxRatesController::class, 'show'])->name('show');
                Route::put('/{taxRate}', [TaxRatesController::class, 'update'])->name('update');
                Route::patch('/{taxRate}/toggle-status', [TaxRatesController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{taxRate}/duplicate', [TaxRatesController::class, 'duplicate'])->name('duplicate');
                Route::post('/priorities', [TaxRatesController::class, 'updatePriorities'])->name('priorities');
                Route::post('/calculate', [TaxRatesController::class, 'calculateTax'])->name('calculate');
                Route::delete('/{taxRate}', [TaxRatesController::class, 'destroy'])->name('destroy');
            });

            // Shipping Methods
            Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
                Route::get('/', [ShippingMethodsController::class, 'index'])->name('index');
                Route::post('/', [ShippingMethodsController::class, 'store'])->name('store');
                Route::get('/{shippingMethod}', [ShippingMethodsController::class, 'show'])->name('show');
                Route::put('/{shippingMethod}', [ShippingMethodsController::class, 'update'])->name('update');
                Route::patch('/{shippingMethod}/toggle-status', [ShippingMethodsController::class, 'toggleStatus'])->name('toggle-status');
                Route::post('/{shippingMethod}/duplicate', [ShippingMethodsController::class, 'duplicate'])->name('duplicate');
                Route::post('/sort-order', [ShippingMethodsController::class, 'updateSortOrder'])->name('sort-order');
                Route::post('/calculate', [ShippingMethodsController::class, 'calculateShipping'])->name('calculate');
                Route::delete('/{shippingMethod}', [ShippingMethodsController::class, 'destroy'])->name('destroy');
            });
        });
    });
});

// Control Panel API Routes
Route::prefix('cp/api')->name('cp.api.')->middleware(['web', 'shopper.auth'])->group(function () {

    // Dashboard API
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Collections API
    Route::apiResource('collections', CollectionsController::class);

    // Entries API
    Route::prefix('collections/{collection}')->group(function () {
        Route::get('/entries', [EntriesController::class, 'index'])->name('entries.index');
        Route::post('/entries', [EntriesController::class, 'store'])->name('entries.store');
        Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('entries.show');
        Route::put('/entries/{entry}', [EntriesController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{entry}', [EntriesController::class, 'destroy'])->name('entries.destroy');
        Route::post('/entries/bulk', [EntriesController::class, 'bulk'])->name('entries.bulk');
    });

    // Import/Export API
    Route::post('/import', function () {
        return response()->json(['message' => 'Import started']);
    })->name('import');

    Route::post('/export', function () {
        return response()->json(['download_url' => '/cp/api/download/export.csv']);
    })->name('export');

    // Global Search API
    Route::get('/search', function () {
        $query = request('q');

        return response()->json([
            'results' => [
                [
                    'type' => 'collection',
                    'title' => 'Products',
                    'url' => '/cp/collections/products',
                ],
                [
                    'type' => 'entry',
                    'title' => 'Premium Headphones',
                    'url' => '/cp/collections/products/entries/1',
                ],
            ],
        ]);
    })->name('search');
});

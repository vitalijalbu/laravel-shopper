<?php

use Illuminate\Support\Facades\Route;
use LaravelShopper\Http\Controllers\Cp\CollectionsController;
use LaravelShopper\Http\Controllers\Cp\DashboardController;
use LaravelShopper\Http\Controllers\Cp\PagesController;
use LaravelShopper\Http\Controllers\Cp\ProductsController;

// CP Routes - Protected by auth middleware
Route::middleware(['web', 'auth'])->prefix('cp')->name('cp.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'index'])->name('index');
        Route::get('/create', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'create'])->name('create');
        Route::post('/', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'store'])->name('store');
        Route::get('/drafts', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'drafts'])->name('drafts');
        Route::get('/abandoned', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'abandoned'])->name('abandoned');
        Route::get('/{order}', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'show'])->name('show');
        Route::put('/{order}', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'update'])->name('update');
        Route::delete('/{order}', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'destroy'])->name('destroy');
        Route::post('/{order}/fulfill', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'fulfill'])->name('fulfill');
        Route::post('/{order}/refund', [\LaravelShopper\Http\Controllers\Cp\OrdersController::class, 'refund'])->name('refund');
    });

    // Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [ProductsController::class, 'index'])->name('index');
        Route::get('/create', [ProductsController::class, 'create'])->name('create');
        Route::post('/', [ProductsController::class, 'store'])->name('store');
        Route::get('/{product}', [ProductsController::class, 'show'])->name('show');
        Route::put('/{product}', [ProductsController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductsController::class, 'destroy'])->name('destroy');
        Route::post('/{product}/duplicate', [ProductsController::class, 'duplicate'])->name('duplicate');
        Route::get('/import', [ProductsController::class, 'import'])->name('import');
        Route::post('/import', [ProductsController::class, 'processImport'])->name('process-import');
        Route::get('/export', [ProductsController::class, 'export'])->name('export');
    });

    // Collections (Categories)
    Route::prefix('collections')->name('collections.')->group(function () {
        Route::get('/', [CollectionsController::class, 'index'])->name('index');
        Route::get('/create', [CollectionsController::class, 'create'])->name('create');
        Route::post('/', [CollectionsController::class, 'store'])->name('store');
        Route::get('/{collection}', [CollectionsController::class, 'show'])->name('show');
        Route::put('/{collection}', [CollectionsController::class, 'update'])->name('update');
        Route::delete('/{collection}', [CollectionsController::class, 'destroy'])->name('destroy');
    });

    // Inventory
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\InventoryController::class, 'index'])->name('index');
        Route::get('/adjustments', [\LaravelShopper\Http\Controllers\Cp\InventoryController::class, 'adjustments'])->name('adjustments');
        Route::post('/adjust', [\LaravelShopper\Http\Controllers\Cp\InventoryController::class, 'adjust'])->name('adjust');
        Route::get('/transfers', [\LaravelShopper\Http\Controllers\Cp\InventoryController::class, 'transfers'])->name('transfers');
    });

    // Customers
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'index'])->name('index');
        Route::get('/create', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'create'])->name('create');
        Route::post('/', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'store'])->name('store');
        Route::get('/segments', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'segments'])->name('segments');
        Route::get('/{customer}', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'show'])->name('show');
        Route::put('/{customer}', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'update'])->name('update');
        Route::delete('/{customer}', [\LaravelShopper\Http\Controllers\Cp\CustomersController::class, 'destroy'])->name('destroy');
    });

    // Content Section
    Route::prefix('pages')->name('pages.')->group(function () {
        Route::get('/', [PagesController::class, 'index'])->name('index');
        Route::get('/create', [PagesController::class, 'create'])->name('create');
        Route::post('/', [PagesController::class, 'store'])->name('store');
        Route::get('/builder/{page?}', [PagesController::class, 'builder'])->name('builder');
        Route::get('/{page}', [PagesController::class, 'show'])->name('show');
        Route::put('/{page}', [PagesController::class, 'update'])->name('update');
        Route::delete('/{page}', [PagesController::class, 'destroy'])->name('destroy');
        Route::post('/{page}/duplicate', [PagesController::class, 'duplicate'])->name('duplicate');
    });

    // Blog
    Route::prefix('blog')->name('blog.')->group(function () {
        Route::get('/posts', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'create'])->name('posts.create');
        Route::post('/posts', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'show'])->name('posts.show');
        Route::put('/posts/{post}', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [\LaravelShopper\Http\Controllers\Cp\BlogController::class, 'destroy'])->name('posts.destroy');
    });

    // Navigation
    Route::prefix('navigation')->name('navigation.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\NavigationController::class, 'index'])->name('index');
        Route::post('/', [\LaravelShopper\Http\Controllers\Cp\NavigationController::class, 'store'])->name('store');
        Route::put('/{menu}', [\LaravelShopper\Http\Controllers\Cp\NavigationController::class, 'update'])->name('update');
        Route::delete('/{menu}', [\LaravelShopper\Http\Controllers\Cp\NavigationController::class, 'destroy'])->name('destroy');
    });

    // Analytics
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\AnalyticsController::class, 'overview'])->name('overview');
        Route::get('/reports', [\LaravelShopper\Http\Controllers\Cp\AnalyticsController::class, 'reports'])->name('reports');
        Route::get('/api/dashboard', [\LaravelShopper\Http\Controllers\Cp\AnalyticsController::class, 'dashboardData'])->name('api.dashboard');
        Route::get('/api/sales', [\LaravelShopper\Http\Controllers\Cp\AnalyticsController::class, 'salesData'])->name('api.sales');
        Route::get('/api/customers', [\LaravelShopper\Http\Controllers\Cp\AnalyticsController::class, 'customersData'])->name('api.customers');
    });

    // Marketing
    Route::prefix('marketing')->name('marketing.')->group(function () {
        Route::get('/discounts', [\LaravelShopper\Http\Controllers\Cp\DiscountsController::class, 'index'])->name('discounts.index');
        Route::get('/discounts/create', [\LaravelShopper\Http\Controllers\Cp\DiscountsController::class, 'create'])->name('discounts.create');
        Route::post('/discounts', [\LaravelShopper\Http\Controllers\Cp\DiscountsController::class, 'store'])->name('discounts.store');
        Route::get('/campaigns', [\LaravelShopper\Http\Controllers\Cp\MarketingController::class, 'campaigns'])->name('campaigns');
        Route::get('/automation', [\LaravelShopper\Http\Controllers\Cp\MarketingController::class, 'automation'])->name('automation');
    });

    // Templates & Themes
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'index'])->name('index');
        Route::get('/create', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'create'])->name('create');
        Route::post('/', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'store'])->name('store');
        Route::get('/{template}', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'show'])->name('show');
        Route::put('/{template}', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'update'])->name('update');
        Route::delete('/{template}', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'destroy'])->name('destroy');
        Route::get('/{template}/preview', [\LaravelShopper\Http\Controllers\Cp\TemplatesController::class, 'preview'])->name('preview');
    });

    // Settings
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'general'])->name('general');
        Route::put('/', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'updateGeneral'])->name('general.update');

        Route::get('/payments', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'payments'])->name('payments');
        Route::put('/payments', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'updatePayments'])->name('payments.update');

        Route::get('/shipping', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'shipping'])->name('shipping');
        Route::put('/shipping', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'updateShipping'])->name('shipping.update');

        Route::get('/taxes', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'taxes'])->name('taxes');
        Route::put('/taxes', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'updateTaxes'])->name('taxes.update');

        Route::get('/notifications', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'notifications'])->name('notifications');
        Route::put('/notifications', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'updateNotifications'])->name('notifications.update');

        Route::get('/users', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'users'])->name('users');
        Route::get('/legal', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'legal'])->name('legal');
        Route::get('/domains', [\LaravelShopper\Http\Controllers\Cp\SettingsController::class, 'domains'])->name('domains');
    });

    // Apps
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::get('/', [\LaravelShopper\Http\Controllers\Cp\AppsController::class, 'index'])->name('index');
        Route::get('/store', [\LaravelShopper\Http\Controllers\Cp\AppsController::class, 'store'])->name('store');
        Route::post('/install', [\LaravelShopper\Http\Controllers\Cp\AppsController::class, 'install'])->name('install');
        Route::delete('/uninstall/{app}', [\LaravelShopper\Http\Controllers\Cp\AppsController::class, 'uninstall'])->name('uninstall');
    });

    // API Routes for CP
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/navigation', function () {
            return \LaravelShopper\CP\Navigation::tree();
        })->name('navigation');

        Route::get('/dashboard', function () {
            return \LaravelShopper\CP\Dashboard::data();
        })->name('dashboard');

        Route::post('/media/upload', [\LaravelShopper\Http\Controllers\Cp\MediaController::class, 'upload'])->name('media.upload');
        Route::delete('/media/{media}', [\LaravelShopper\Http\Controllers\Cp\MediaController::class, 'destroy'])->name('media.destroy');
    });
});

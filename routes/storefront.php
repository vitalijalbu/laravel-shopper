<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\StorefrontController;
use Shopper\Http\Middleware\StorefrontTemplateMiddleware;

// Storefront Routes with Template Support
Route::middleware([
    'web',
    StorefrontTemplateMiddleware::class,
])->group(function () {

    // Home page
    Route::get('/', [StorefrontController::class, 'home'])
        ->name('storefront.home');

    // Product routes with handle support
    Route::prefix('products')->name('storefront.products.')->group(function () {
        Route::get('/', [StorefrontController::class, 'productIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'productShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Collection routes
    Route::prefix('collections')->name('storefront.collections.')->group(function () {
        Route::get('/', [StorefrontController::class, 'collectionIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'collectionShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Pages
    Route::prefix('pages')->name('storefront.pages.')->group(function () {
        Route::get('/{handle}', [StorefrontController::class, 'pageShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Blog
    Route::prefix('blog')->name('storefront.blog.')->group(function () {
        Route::get('/', [StorefrontController::class, 'blogIndex'])
            ->name('index');

        Route::get('/{handle}', [StorefrontController::class, 'blogShow'])
            ->name('show')
            ->where('handle', '[a-zA-Z0-9\-_]+');
    });

    // Search
    Route::get('/search', [StorefrontController::class, 'search'])
        ->name('storefront.search');

    // Cart
    Route::prefix('cart')->name('storefront.cart.')->group(function () {
        Route::get('/', [StorefrontController::class, 'cartShow'])
            ->name('show');

        Route::post('/add', [StorefrontController::class, 'cartAdd'])
            ->name('add');

        Route::put('/update/{line}', [StorefrontController::class, 'cartUpdate'])
            ->name('update');

        Route::delete('/remove/{line}', [StorefrontController::class, 'cartRemove'])
            ->name('remove');
    });

    // Customer routes
    Route::prefix('account')->middleware('auth:customers')->name('storefront.account.')->group(function () {
        Route::get('/', [StorefrontController::class, 'accountDashboard'])
            ->name('dashboard');

        Route::get('/orders', [StorefrontController::class, 'accountOrders'])
            ->name('orders');

        Route::get('/orders/{order}', [StorefrontController::class, 'accountOrderShow'])
            ->name('orders.show');
    });

    // Template Preview Routes (Admin only)
    Route::prefix('admin/template-preview')->middleware('auth')->name('admin.template.preview.')->group(function () {
        Route::get('/{template}', [StorefrontController::class, 'templatePreview'])
            ->name('show');
    });

});

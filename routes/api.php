<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use LaravelShopper\Http\Controllers\Api\AuthController;
use LaravelShopper\Http\Controllers\Api\BrandController;
use LaravelShopper\Http\Controllers\Api\CartController;
use LaravelShopper\Http\Controllers\Api\CategoryController;
use LaravelShopper\Http\Controllers\Api\ProductController;
use LaravelShopper\Http\Middleware\HandleSiteContext;

/*
|--------------------------------------------------------------------------
| API Routes with Handle Support
|--------------------------------------------------------------------------
|
| Routes support both ID and handle/slug for all resources:
| /api/products/123 or /api/products/awesome-t-shirt
| /api/sites/main/products/123 or /api/sites/main/products/awesome-t-shirt
|
*/

Route::group([
    'prefix' => 'shopper/api',
    'middleware' => ['api'],
], function () {

    // Authentication routes (public)
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout'])
        ->name('api.logout');

    // Product routes (public)
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.categories.show');

    Route::get('/brands', [BrandController::class, 'index'])->name('api.brands.index');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('api.brands.show');

    // Cart routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cart', [CartController::class, 'show'])->name('api.cart.show');
        Route::post('/cart/add', [CartController::class, 'add'])->name('api.cart.add');
        Route::put('/cart/{item}', [CartController::class, 'update'])->name('api.cart.update');
        Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('api.cart.remove');
        Route::delete('/cart', [CartController::class, 'clear'])->name('api.cart.clear');
    });

    // User routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

/*
|--------------------------------------------------------------------------
| Enhanced API Routes with Handle Support
|--------------------------------------------------------------------------
*/

Route::middleware([HandleSiteContext::class])->group(function () {

    // Enhanced routes supporting handles (slug or ID)
    Route::prefix('api/v2')->group(function () {

        // Products API with handle support
        Route::get('products', [ProductController::class, 'index']);
        Route::post('products', [ProductController::class, 'store']);
        Route::get('products/{product:handle}', [ProductController::class, 'show']);
        Route::put('products/{product:handle}', [ProductController::class, 'update']);
        Route::delete('products/{product:handle}', [ProductController::class, 'destroy']);
        Route::post('products/{product:handle}/duplicate', [ProductController::class, 'duplicate']);

        // Categories API with handle support
        Route::get('categories/{category:handle}', [CategoryController::class, 'show']);
        Route::put('categories/{category:handle}', [CategoryController::class, 'update']);
        Route::delete('categories/{category:handle}', [CategoryController::class, 'destroy']);
        Route::get('categories/{category:handle}/products', [CategoryController::class, 'products']);

        // Site-specific routes
        Route::prefix('sites/{site:handle}')->group(function () {
            Route::get('products', [ProductController::class, 'index']);
            Route::get('products/{product:handle}', [ProductController::class, 'show']);
            Route::get('categories/{category:handle}/products', [CategoryController::class, 'products']);
        });
    });
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use VitaliJalbu\LaravelShopper\Http\Controllers\Api\ProductController;
use VitaliJalbu\LaravelShopper\Http\Controllers\Api\CategoryController;
use VitaliJalbu\LaravelShopper\Http\Controllers\Api\BrandController;
use VitaliJalbu\LaravelShopper\Http\Controllers\Api\CartController;
use VitaliJalbu\LaravelShopper\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'shopper/api',
    'middleware' => ['api'],
], function () {

    // Authentication routes
    Route::post('/login', [AuthController::class, 'login'])->name('shopper.api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('shopper.api.register');
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum')
        ->name('shopper.api.logout');

    // Public routes
    Route::get('/products', [ProductController::class, 'index'])->name('shopper.api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('shopper.api.products.show');
    
    Route::get('/categories', [CategoryController::class, 'index'])->name('shopper.api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('shopper.api.categories.show');
    
    Route::get('/brands', [BrandController::class, 'index'])->name('shopper.api.brands.index');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('shopper.api.brands.show');

    // Cart routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/cart', [CartController::class, 'show'])->name('shopper.api.cart.show');
        Route::post('/cart/add', [CartController::class, 'add'])->name('shopper.api.cart.add');
        Route::put('/cart/{item}', [CartController::class, 'update'])->name('shopper.api.cart.update');
        Route::delete('/cart/{item}', [CartController::class, 'remove'])->name('shopper.api.cart.remove');
        Route::delete('/cart', [CartController::class, 'clear'])->name('shopper.api.cart.clear');
    });

    // User routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
    });
});

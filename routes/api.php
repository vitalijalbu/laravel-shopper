<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Api\AuthController;
use Shopper\Http\Controllers\Api\BrandController;
use Shopper\Http\Controllers\Api\CartController;
use Shopper\Http\Controllers\Api\DiscountController;
use Shopper\Http\Controllers\Api\FidelityController;
use Shopper\Http\Controllers\Api\ProductController;
use Shopper\Http\Middleware\HandleSiteContext;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Laravel Shopper with support for:
| - Public product/collection browsing
| - Authentication (Sanctum)
| - Cart management
| - Admin operations with permissions
| - Multi-site support
|
*/

Route::group([
    'prefix' => 'shopper/api',
    'middleware' => ['api'],
], function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    // Public Product Browsing
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

    // Public Brand Browsing
    Route::get('/brands', [BrandController::class, 'index'])->name('api.brands.index');

    // Fidelity System Configuration (Public)
    Route::get('/fidelity/configuration', [FidelityController::class, 'configuration'])->name('api.fidelity.configuration');
    Route::post('/fidelity/calculate-points', [FidelityController::class, 'calculatePoints'])->name('api.fidelity.calculate-points');
    Route::post('/fidelity/find-card', [FidelityController::class, 'findByCardNumber'])->name('api.fidelity.find-card');
    Route::get('/brands/{brand}', [BrandController::class, 'show'])->name('api.brands.show');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (Authentication Required)
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        // Authentication
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

        // User Profile
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->name('api.user');

        // Cart Management
        Route::prefix('cart')->name('api.cart.')->group(function () {
            Route::get('/', [CartController::class, 'show'])->name('show');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::put('/{item}', [CartController::class, 'update'])->name('update');
            Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
        });

        // Fidelity System
        Route::prefix('fidelity')->name('api.fidelity.')->group(function () {
            Route::get('/', [FidelityController::class, 'show'])->name('show');
            Route::post('/', [FidelityController::class, 'store'])->name('store');
            Route::get('/transactions', [FidelityController::class, 'transactions'])->name('transactions');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin Routes (Permissions Required)
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')->name('api.admin.')->group(function () {

            // Permission Management
            Route::prefix('permissions')->name('permissions.')->group(function () {
                Route::get('/', [\Shopper\Http\Controllers\Api\PermissionController::class, 'index'])->name('index');
                Route::get('/roles/{role}/permissions', [\Shopper\Http\Controllers\Api\PermissionController::class, 'rolePermissions'])->name('role.permissions');
                Route::put('/roles/{role}/permissions', [\Shopper\Http\Controllers\Api\PermissionController::class, 'updateRolePermissions'])->name('role.update');
                Route::post('/generate', [\Shopper\Http\Controllers\Api\PermissionController::class, 'generatePermissions'])->name('generate');
                Route::post('/super-role', [\Shopper\Http\Controllers\Api\PermissionController::class, 'createSuperRole'])->name('super.create');
                Route::get('/tree', [\Shopper\Http\Controllers\Api\PermissionController::class, 'permissionTree'])->name('tree');
            });

            // Role Management
            Route::prefix('roles')->name('roles.')->group(function () {
                Route::get('/', [\Shopper\Http\Controllers\Api\RoleController::class, 'index'])->name('index');
                Route::post('/', [\Shopper\Http\Controllers\Api\RoleController::class, 'store'])->name('store');
                Route::get('/{role}', [\Shopper\Http\Controllers\Api\RoleController::class, 'show'])->name('show');
                Route::put('/{role}', [\Shopper\Http\Controllers\Api\RoleController::class, 'update'])->name('update');
                Route::delete('/{role}', [\Shopper\Http\Controllers\Api\RoleController::class, 'destroy'])->name('destroy');
                Route::post('/{role}/assign-users', [\Shopper\Http\Controllers\Api\RoleController::class, 'assignUsers'])->name('assign.users');
                Route::post('/{role}/remove-users', [\Shopper\Http\Controllers\Api\RoleController::class, 'removeUsers'])->name('remove.users');
                Route::post('/{role}/clone', [\Shopper\Http\Controllers\Api\RoleController::class, 'clone'])->name('clone');
                Route::get('/statistics', [\Shopper\Http\Controllers\Api\RoleController::class, 'statistics'])->name('statistics');
            });

            // Customer Management
            Route::prefix('customers')->name('customers.')->group(function () {
                Route::get('/', [\Shopper\Http\Controllers\Api\CustomerController::class, 'index'])->name('index');
                Route::get('/with-fidelity', [\Shopper\Http\Controllers\Api\CustomerController::class, 'indexWithFidelity'])->name('index-with-fidelity');
                Route::post('/', [\Shopper\Http\Controllers\Api\CustomerController::class, 'store'])->name('store');
                Route::get('/{customer}', [\Shopper\Http\Controllers\Api\CustomerController::class, 'show'])->name('show');
                Route::put('/{customer}', [\Shopper\Http\Controllers\Api\CustomerController::class, 'update'])->name('update');
                Route::delete('/{customer}', [\Shopper\Http\Controllers\Api\CustomerController::class, 'destroy'])->name('destroy');
                Route::get('/{customer}/fidelity', [\Shopper\Http\Controllers\Api\CustomerController::class, 'fidelityCard'])->name('fidelity');
                Route::post('/{customer}/fidelity', [\Shopper\Http\Controllers\Api\CustomerController::class, 'createFidelityCard'])->name('fidelity.create');
                Route::get('/{customer}/orders', [\Shopper\Http\Controllers\Api\CustomerController::class, 'orders'])->name('orders');
                Route::get('/{customer}/addresses', [\Shopper\Http\Controllers\Api\CustomerController::class, 'addresses'])->name('addresses');
                Route::post('/{customer}/addresses', [\Shopper\Http\Controllers\Api\CustomerController::class, 'addAddress'])->name('addresses.add');
                Route::get('/{customer}/statistics', [\Shopper\Http\Controllers\Api\CustomerController::class, 'statistics'])->name('statistics');
                Route::post('/bulk', [\Shopper\Http\Controllers\Api\CustomerController::class, 'bulk'])->name('bulk');
            });

            // Fidelity System Management
            Route::prefix('fidelity')->name('fidelity.')->group(function () {
                Route::post('/redeem-points', [FidelityController::class, 'redeemPoints'])->name('redeem-points');
                Route::get('/cards', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'index'])->name('cards.index');
                Route::get('/cards/{card}', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'show'])->name('cards.show');
                Route::put('/cards/{card}', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'update'])->name('cards.update');
                Route::post('/cards/{card}/add-points', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'addPoints'])->name('cards.add-points');
                Route::get('/statistics', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'statistics'])->name('statistics');
                Route::post('/expire-points', [\Shopper\Http\Controllers\Api\Admin\FidelityAdminController::class, 'expirePoints'])->name('expire-points');
            });

            // Discount Management
            Route::prefix('discounts')->name('discounts.')->group(function () {
                Route::get('/', [DiscountController::class, 'index'])->name('index');
                Route::post('/', [DiscountController::class, 'store'])->name('store');
                Route::get('/{discount}', [DiscountController::class, 'show'])->name('show');
                Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
                Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
                Route::post('/{discount}/toggle', [DiscountController::class, 'toggle'])->name('toggle');
                Route::post('/{discount}/duplicate', [DiscountController::class, 'duplicate'])->name('duplicate');
                Route::post('/validate-code', [DiscountController::class, 'validateCode'])->name('validate-code');
                Route::get('/statistics/overview', [DiscountController::class, 'statistics'])->name('statistics');
            });

            // Permission Builder

            // Discount Management
            Route::prefix('discounts')->name('discounts.')->group(function () {
                Route::get('/', [\Shopper\Http\Controllers\Api\DiscountController::class, 'index'])->name('index');
                Route::post('/', [\Shopper\Http\Controllers\Api\DiscountController::class, 'store'])->name('store');
                Route::get('/statistics', [\Shopper\Http\Controllers\Api\DiscountController::class, 'statistics'])->name('statistics');
                Route::get('/{discount}', [\Shopper\Http\Controllers\Api\DiscountController::class, 'show'])->name('show');
                Route::put('/{discount}', [\Shopper\Http\Controllers\Api\DiscountController::class, 'update'])->name('update');
                Route::delete('/{discount}', [\Shopper\Http\Controllers\Api\DiscountController::class, 'destroy'])->name('destroy');
                Route::post('/{discount}/toggle', [\Shopper\Http\Controllers\Api\DiscountController::class, 'toggle'])->name('toggle');
                Route::post('/{discount}/duplicate', [\Shopper\Http\Controllers\Api\DiscountController::class, 'duplicate'])->name('duplicate');
                Route::post('/validate-code', [\Shopper\Http\Controllers\Api\DiscountController::class, 'validateCode'])->name('validate-code');
            });

            // Permission Builder
            Route::prefix('permission-builder')->name('builder.')->group(function () {
                Route::get('/', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'builder'])->name('index');
                Route::put('/matrix', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'updateMatrix'])->name('matrix.update');
                Route::post('/apply-template', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'applyTemplate'])->name('template.apply');
                Route::post('/generate-resource', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'generateResourcePermissions'])->name('resource.generate');
                Route::get('/export', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'export'])->name('export');
                Route::post('/import', [\Shopper\Http\Controllers\Api\PermissionBuilderController::class, 'import'])->name('import');
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Multi-Site Routes (Site Context)
    |--------------------------------------------------------------------------
    */

    Route::prefix('sites/{site}')->middleware([HandleSiteContext::class])->name('api.sites.')->group(function () {

        // Site-specific Products
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        });

        // Site-specific Brands
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::get('/{brand}', [BrandController::class, 'show'])->name('show');
        });
    });
});

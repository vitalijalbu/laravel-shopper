<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Api\AuthController;
use Shopper\Http\Controllers\Api\BrandController;
use Shopper\Http\Controllers\Api\CartController;
use Shopper\Http\Controllers\Api\CategoryController;
use Shopper\Http\Controllers\Api\ProductController;
use Shopper\Http\Middleware\HandleSiteContext;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Laravel Shopper with support for:
| - Public product/category browsing
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

    // Public Category Browsing
    Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('api.categories.show');

    // Public Brand Browsing
    Route::get('/brands', [BrandController::class, 'index'])->name('api.brands.index');
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

        // Site-specific Categories
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        });

        // Site-specific Brands
        Route::prefix('brands')->name('brands.')->group(function () {
            Route::get('/', [BrandController::class, 'index'])->name('index');
            Route::get('/{brand}', [BrandController::class, 'show'])->name('show');
        });
    });
});

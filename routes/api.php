<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Api\AuthController;
use Shopper\Http\Controllers\Api\BrandController;
use Shopper\Http\Controllers\Api\CartController;
use Shopper\Http\Controllers\Api\ChannelController;
use Shopper\Http\Controllers\Api\CollectionController;
use Shopper\Http\Controllers\Api\CountryController;
use Shopper\Http\Controllers\Api\CurrencyController;
use Shopper\Http\Controllers\Api\CustomerController;
use Shopper\Http\Controllers\Api\Data\StatusController;
use Shopper\Http\Controllers\Api\DiscountController;
use Shopper\Http\Controllers\Api\FidelityController;
use Shopper\Http\Controllers\Api\OrderController;
use Shopper\Http\Controllers\Api\ProductController;
use Shopper\Http\Controllers\Api\ShippingMethodController;
use Shopper\Http\Controllers\Api\TaxRateController;
use Shopper\Http\Controllers\Api\UserController;
use Shopper\Http\Controllers\Api\UserGroupController;
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
    'prefix' => 'api',
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

    // Data Endpoints (Statuses, etc.)
    Route::prefix('data')->name('api.data.')->group(function () {
        Route::get('/statuses', [StatusController::class, 'index'])->name('statuses.index');
        Route::get('/statuses/{type}', [StatusController::class, 'show'])->name('statuses.show');
    });

    // Public Product Browsing
    Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('api.products.show');

    // Public Brand Browsing
    Route::prefix('brands')->name('api.brands.')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->name('index');
        Route::get('/{brand}', [BrandController::class, 'show'])->name('show');
    });

    // Public Collections Browsing
    Route::prefix('collections')->name('api.collections.')->group(function () {
        Route::get('/', [\Shopper\Http\Controllers\Api\CollectionController::class, 'index'])->name('index');
        Route::get('/{collection}', [\Shopper\Http\Controllers\Api\CollectionController::class, 'show'])->name('show');
    });

    // Public Countries/Currencies
    Route::prefix('countries')->name('api.countries.')->group(function () {
        Route::get('/', [\Shopper\Http\Controllers\Api\CountryController::class, 'index'])->name('index');
        Route::get('/{country}', [\Shopper\Http\Controllers\Api\CountryController::class, 'show'])->name('show');
        Route::get('/{country}/states', [\Shopper\Http\Controllers\Api\CountryController::class, 'states'])->name('states');
        Route::get('/{country}/cities', [\Shopper\Http\Controllers\Api\CountryController::class, 'cities'])->name('cities');
    });

    Route::prefix('currencies')->name('api.currencies.')->group(function () {
        Route::get('/', [\Shopper\Http\Controllers\Api\CurrencyController::class, 'index'])->name('index');
        Route::get('/{currency}', [\Shopper\Http\Controllers\Api\CurrencyController::class, 'show'])->name('show');
        Route::post('/convert', [\Shopper\Http\Controllers\Api\CurrencyController::class, 'convert'])->name('convert');
    });

    // Public Shipping Methods
    Route::prefix('shipping-methods')->name('api.shipping-methods.')->group(function () {
        Route::get('/', [\Shopper\Http\Controllers\Api\ShippingMethodController::class, 'index'])->name('index');
        Route::post('/calculate', [\Shopper\Http\Controllers\Api\ShippingMethodController::class, 'calculate'])->name('calculate');
    });

    // Public Tax Rates
    Route::prefix('tax-rates')->name('api.tax-rates.')->group(function () {
        Route::get('/', [\Shopper\Http\Controllers\Api\TaxRateController::class, 'index'])->name('index');
        Route::post('/calculate', [\Shopper\Http\Controllers\Api\TaxRateController::class, 'calculate'])->name('calculate');
    });

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

        // Customer Authentication and Management
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::post('/register', [CustomerController::class, 'register'])->name('register');
            Route::post('/login', [CustomerController::class, 'login'])->name('login');
            Route::post('/logout', [CustomerController::class, 'logout'])->name('logout')->middleware('auth:customer');
            Route::post('/forgot-password', [CustomerController::class, 'forgotPassword'])->name('forgot-password');
            Route::post('/reset-password', [CustomerController::class, 'resetPassword'])->name('reset-password');
            Route::get('/verify/{token}', [CustomerController::class, 'verify'])->name('verify');

            // Authenticated Customer Routes
            Route::middleware(['auth:customer'])->group(function () {
                Route::get('/profile', [CustomerController::class, 'profile'])->name('profile');
                Route::put('/profile', [CustomerController::class, 'updateProfile'])->name('update-profile');
                Route::get('/addresses', [CustomerController::class, 'addresses'])->name('addresses');
                Route::post('/addresses', [CustomerController::class, 'storeAddress'])->name('store-address');
                Route::put('/addresses/{address}', [CustomerController::class, 'updateAddress'])->name('update-address');
                Route::delete('/addresses/{address}', [CustomerController::class, 'destroyAddress'])->name('destroy-address');
                Route::get('/orders', [CustomerController::class, 'customerOrders'])->name('orders');
                Route::get('/orders/{order}', [CustomerController::class, 'customerOrder'])->name('order');
            });
        });

        // Order Management for Customers
        Route::prefix('orders')->name('orders.')->middleware(['auth:customer'])->group(function () {
            Route::get('/', [OrderController::class, 'customerIndex'])->name('customer.index');
            Route::post('/', [OrderController::class, 'customerStore'])->name('customer.store');
            Route::get('/{order}', [OrderController::class, 'customerShow'])->name('customer.show');
            Route::post('/{order}/cancel', [OrderController::class, 'customerCancel'])->name('customer.cancel');
            Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('customer.invoice');
            Route::get('/{order}/track', [OrderController::class, 'track'])->name('customer.track');
        });

        // Enhanced Cart Management
        Route::prefix('cart')->name('cart.')->group(function () {
            Route::get('/', [CartController::class, 'show'])->name('show');
            Route::post('/add', [CartController::class, 'add'])->name('add');
            Route::put('/{item}', [CartController::class, 'update'])->name('update');
            Route::delete('/{item}', [CartController::class, 'remove'])->name('remove');
            Route::delete('/', [CartController::class, 'clear'])->name('clear');
            Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('apply-discount');
            Route::delete('/remove-discount', [CartController::class, 'removeDiscount'])->name('remove-discount');
            Route::get('/totals', [CartController::class, 'totals'])->name('totals');
            Route::post('/estimate-shipping', [CartController::class, 'estimateShipping'])->name('estimate-shipping');
        });

        // Enhanced Product Browsing with Reviews
        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('public.index');
            Route::get('/{product}', [ProductController::class, 'show'])->name('public.show');
            Route::get('/{product}/variants', [ProductController::class, 'variants'])->name('public.variants');
            Route::get('/{product}/reviews', [ProductController::class, 'reviews'])->name('public.reviews');
            Route::post('/{product}/reviews', [ProductController::class, 'storeReview'])->name('public.store-review')->middleware('auth:customer');
            Route::get('/search', [ProductController::class, 'search'])->name('public.search');
            Route::get('/featured', [ProductController::class, 'featured'])->name('public.featured');
            Route::get('/bestsellers', [ProductController::class, 'bestsellers'])->name('public.bestsellers');
        });

        // Enhanced Fidelity System
        Route::prefix('fidelity')->name('fidelity.')->group(function () {
            // Public endpoints
            Route::get('/configuration', [FidelityController::class, 'configuration'])->name('configuration');
            Route::post('/calculate-points', [FidelityController::class, 'calculatePoints'])->name('calculate-points');
            Route::post('/find-card', [FidelityController::class, 'findByCardNumber'])->name('find-card');

            // Customer authenticated endpoints
            Route::middleware(['auth:customer'])->group(function () {
                Route::get('/card', [FidelityController::class, 'card'])->name('card');
                Route::get('/transactions', [FidelityController::class, 'transactions'])->name('transactions');
                Route::get('/balance', [FidelityController::class, 'balance'])->name('balance');
                Route::post('/redeem', [FidelityController::class, 'redeem'])->name('redeem');
                Route::get('/offers', [FidelityController::class, 'offers'])->name('offers');
            });
        });

        // Discount/Coupon Validation
        Route::prefix('discounts')->name('discounts.')->group(function () {
            Route::post('/validate', [DiscountController::class, 'validate'])->name('validate');
            Route::get('/public', [DiscountController::class, 'public'])->name('public');
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

            // User Group Management
            Route::prefix('user-groups')->name('user-groups.')->group(function () {
                Route::get('/', [UserGroupController::class, 'index'])->name('index');
                Route::post('/', [UserGroupController::class, 'store'])->name('store');
                Route::get('/{group}', [UserGroupController::class, 'show'])->name('show');
                Route::put('/{group}', [UserGroupController::class, 'update'])->name('update');
                Route::delete('/{group}', [UserGroupController::class, 'destroy'])->name('destroy');
                Route::post('/{group}/assign-users', [UserGroupController::class, 'assignUsers'])->name('assign.users');
                Route::post('/{group}/remove-users', [UserGroupController::class, 'removeUsers'])->name('remove.users');
                Route::get('/{group}/permissions', [UserGroupController::class, 'permissions'])->name('permissions');
            });

            // User Management
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::get('/{user}', [UserController::class, 'show'])->name('show');
                Route::put('/{user}', [UserController::class, 'update'])->name('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
                Route::post('/{user}/deactivate', [UserController::class, 'deactivate'])->name('deactivate');
                Route::post('/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('assign.roles');
                Route::post('/{user}/assign-permissions', [UserController::class, 'assignPermissions'])->name('assign.permissions');
                Route::get('/{user}/activity', [UserController::class, 'activity'])->name('activity');
                Route::post('/bulk', [UserController::class, 'bulk'])->name('bulk');
            });

            // Brand Management (Admin)
            Route::prefix('brands')->name('brands.')->group(function () {
                Route::get('/', [BrandController::class, 'adminIndex'])->name('index');
                Route::post('/', [BrandController::class, 'store'])->name('store');
                Route::get('/{brand}', [BrandController::class, 'adminShow'])->name('show');
                Route::put('/{brand}', [BrandController::class, 'update'])->name('update');
                Route::delete('/{brand}', [BrandController::class, 'destroy'])->name('destroy');
                Route::post('/{brand}/toggle-status', [BrandController::class, 'toggleStatus'])->name('toggle.status');
                Route::get('/{brand}/products', [BrandController::class, 'products'])->name('products');
                Route::post('/bulk', [BrandController::class, 'bulk'])->name('bulk');
            });

            // Supplier Management (Admin)
            Route::prefix('suppliers')->name('suppliers.')->group(function () {
                Route::get('/select', [\Shopper\Http\Controllers\Api\SupplierController::class, 'select'])->name('select');
                Route::get('/top-performers', [\Shopper\Http\Controllers\Api\SupplierController::class, 'topPerformers'])->name('top-performers');
                Route::post('/bulk/activate', [\Shopper\Http\Controllers\Api\SupplierController::class, 'bulkActivate'])->name('bulk.activate');
                Route::post('/bulk/deactivate', [\Shopper\Http\Controllers\Api\SupplierController::class, 'bulkDeactivate'])->name('bulk.deactivate');
                Route::post('/bulk/delete', [\Shopper\Http\Controllers\Api\SupplierController::class, 'bulkDelete'])->name('bulk.delete');
                Route::post('/bulk/export', [\Shopper\Http\Controllers\Api\SupplierController::class, 'bulkExport'])->name('bulk.export');
                Route::get('/', [\Shopper\Http\Controllers\Api\SupplierController::class, 'index'])->name('index');
                Route::post('/', [\Shopper\Http\Controllers\Api\SupplierController::class, 'store'])->name('store');
                Route::get('/{supplier}', [\Shopper\Http\Controllers\Api\SupplierController::class, 'show'])->name('show');
                Route::put('/{supplier}', [\Shopper\Http\Controllers\Api\SupplierController::class, 'update'])->name('update');
                Route::delete('/{supplier}', [\Shopper\Http\Controllers\Api\SupplierController::class, 'destroy'])->name('destroy');
                Route::put('/{supplier}/toggle-status', [\Shopper\Http\Controllers\Api\SupplierController::class, 'toggleStatus'])->name('toggle.status');
                Route::get('/{supplier}/products', [\Shopper\Http\Controllers\Api\SupplierController::class, 'products'])->name('products');
                Route::get('/{supplier}/purchase-orders', [\Shopper\Http\Controllers\Api\SupplierController::class, 'purchaseOrders'])->name('purchase-orders');
                Route::get('/{supplier}/performance', [\Shopper\Http\Controllers\Api\SupplierController::class, 'performance'])->name('performance');
            });

            // Collection Management (Admin)
            Route::prefix('collections')->name('collections.')->group(function () {
                Route::get('/', [CollectionController::class, 'adminIndex'])->name('index');
                Route::post('/', [CollectionController::class, 'store'])->name('store');
                Route::get('/{collection}', [CollectionController::class, 'adminShow'])->name('show');
                Route::put('/{collection}', [CollectionController::class, 'update'])->name('update');
                Route::delete('/{collection}', [CollectionController::class, 'destroy'])->name('destroy');
                Route::post('/{collection}/products/attach', [CollectionController::class, 'attachProducts'])->name('products.attach');
                Route::post('/{collection}/products/detach', [CollectionController::class, 'detachProducts'])->name('products.detach');
                Route::post('/{collection}/toggle-status', [CollectionController::class, 'toggleStatus'])->name('toggle.status');
                Route::post('/bulk', [CollectionController::class, 'bulk'])->name('bulk');
            });

            // Product Management (Admin)
            Route::prefix('products')->name('products.')->group(function () {
                Route::get('/', [ProductController::class, 'adminIndex'])->name('index');
                Route::post('/', [ProductController::class, 'store'])->name('store');
                Route::get('/{product}', [ProductController::class, 'adminShow'])->name('show');
                Route::put('/{product}', [ProductController::class, 'update'])->name('update');
                Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
                Route::post('/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('toggle.status');
                Route::post('/{product}/variants', [ProductController::class, 'createVariant'])->name('variants.create');
                Route::put('/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
                Route::delete('/{product}/variants/{variant}', [ProductController::class, 'deleteVariant'])->name('variants.delete');
                Route::post('/{product}/inventory', [ProductController::class, 'updateInventory'])->name('inventory.update');
                Route::post('/bulk', [ProductController::class, 'bulk'])->name('bulk');
            });

            // Order Management (Admin)
            Route::prefix('orders')->name('orders.')->group(function () {
                Route::get('/', [OrderController::class, 'adminIndex'])->name('index');
                Route::post('/', [OrderController::class, 'store'])->name('store');
                Route::get('/{order}', [OrderController::class, 'adminShow'])->name('show');
                Route::put('/{order}', [OrderController::class, 'update'])->name('update');
                Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
                Route::post('/{order}/fulfill', [OrderController::class, 'fulfill'])->name('fulfill');
                Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
                Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
                Route::post('/{order}/archive', [OrderController::class, 'archive'])->name('archive');
                Route::get('/{order}/timeline', [OrderController::class, 'timeline'])->name('timeline');
                Route::get('/statistics', [OrderController::class, 'statistics'])->name('statistics');
                Route::post('/bulk', [OrderController::class, 'bulk'])->name('bulk');
            });

            // Channel Management
            Route::prefix('channels')->name('channels.')->group(function () {
                Route::get('/', [ChannelController::class, 'adminIndex'])->name('index');
                Route::post('/', [ChannelController::class, 'store'])->name('store');
                Route::get('/{channel}', [ChannelController::class, 'adminShow'])->name('show');
                Route::put('/{channel}', [ChannelController::class, 'update'])->name('update');
                Route::delete('/{channel}', [ChannelController::class, 'destroy'])->name('destroy');
                Route::post('/{channel}/toggle-status', [ChannelController::class, 'toggleStatus'])->name('toggle.status');
                Route::get('/{channel}/products', [ChannelController::class, 'products'])->name('products');
                Route::post('/bulk', [ChannelController::class, 'bulk'])->name('bulk');
            });

            // Country Management
            Route::prefix('countries')->name('countries.')->group(function () {
                Route::get('/', [CountryController::class, 'adminIndex'])->name('index');
                Route::post('/', [CountryController::class, 'store'])->name('store');
                Route::get('/{country}', [CountryController::class, 'adminShow'])->name('show');
                Route::put('/{country}', [CountryController::class, 'update'])->name('update');
                Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
                Route::post('/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->name('toggle.status');
                Route::post('/bulk', [CountryController::class, 'bulk'])->name('bulk');
            });

            // Currency Management
            Route::prefix('currencies')->name('currencies.')->group(function () {
                Route::get('/', [CurrencyController::class, 'adminIndex'])->name('index');
                Route::post('/', [CurrencyController::class, 'store'])->name('store');
                Route::get('/{currency}', [CurrencyController::class, 'adminShow'])->name('show');
                Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
                Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
                Route::post('/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('set.default');
                Route::post('/bulk', [CurrencyController::class, 'bulk'])->name('bulk');
            });

            // Shipping Method Management
            Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
                Route::get('/', [ShippingMethodController::class, 'adminIndex'])->name('index');
                Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
                Route::get('/{method}', [ShippingMethodController::class, 'adminShow'])->name('show');
                Route::put('/{method}', [ShippingMethodController::class, 'update'])->name('update');
                Route::delete('/{method}', [ShippingMethodController::class, 'destroy'])->name('destroy');
                Route::post('/{method}/toggle-status', [ShippingMethodController::class, 'toggleStatus'])->name('toggle.status');
                Route::post('/bulk', [ShippingMethodController::class, 'bulk'])->name('bulk');
            });

            // Tax Rate Management
            Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
                Route::get('/', [TaxRateController::class, 'adminIndex'])->name('index');
                Route::post('/', [TaxRateController::class, 'store'])->name('store');
                Route::get('/{rate}', [TaxRateController::class, 'adminShow'])->name('show');
                Route::put('/{rate}', [TaxRateController::class, 'update'])->name('update');
                Route::delete('/{rate}', [TaxRateController::class, 'destroy'])->name('destroy');
                Route::post('/{rate}/toggle-status', [TaxRateController::class, 'toggleStatus'])->name('toggle.status');
                Route::post('/bulk', [TaxRateController::class, 'bulk'])->name('bulk');

            });

            // Discount Management (Admin)
            Route::prefix('discounts')->name('discounts.')->group(function () {
                Route::get('/', [DiscountController::class, 'adminIndex'])->name('index');
                Route::post('/', [DiscountController::class, 'store'])->name('store');
                Route::get('/{discount}', [DiscountController::class, 'adminShow'])->name('show');
                Route::put('/{discount}', [DiscountController::class, 'update'])->name('update');
                Route::delete('/{discount}', [DiscountController::class, 'destroy'])->name('destroy');
                Route::post('/{discount}/toggle', [DiscountController::class, 'toggle'])->name('toggle');
                Route::post('/{discount}/duplicate', [DiscountController::class, 'duplicate'])->name('duplicate');
                Route::post('/validate-code', [DiscountController::class, 'validateCode'])->name('validate-code');
                Route::get('/statistics', [DiscountController::class, 'statistics'])->name('statistics');
                Route::post('/bulk', [DiscountController::class, 'bulk'])->name('bulk');
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

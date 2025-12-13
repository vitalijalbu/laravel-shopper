<?php

declare(strict_types=1);

use Cartino\Http\Controllers\Api\AddressesController;
use Cartino\Http\Controllers\Api\AuthController;
use Cartino\Http\Controllers\Api\BrandsController;
use Cartino\Http\Controllers\Api\CartController;
use Cartino\Http\Controllers\Api\CategoriesController;
use Cartino\Http\Controllers\Api\ChannelController;
use Cartino\Http\Controllers\Api\CountryController;
use Cartino\Http\Controllers\Api\CouriersController;
use Cartino\Http\Controllers\Api\CurrencyController;
use Cartino\Http\Controllers\Api\CustomerController;
use Cartino\Http\Controllers\Api\CustomerGroupsController;
use Cartino\Http\Controllers\Api\Data\StatusController;
use Cartino\Http\Controllers\Api\DiscountController;
use Cartino\Http\Controllers\Api\DiscountsController;
use Cartino\Http\Controllers\Api\FidelityController;
use Cartino\Http\Controllers\Api\OrderController;
use Cartino\Http\Controllers\Api\OrdersController;
use Cartino\Http\Controllers\Api\PaymentMethodsController;
use Cartino\Http\Controllers\Api\ProductController;
use Cartino\Http\Controllers\Api\ProductTypesController;
use Cartino\Http\Controllers\Api\ShippingMethodController;
use Cartino\Http\Controllers\Api\SitesController;
use Cartino\Http\Controllers\Api\SuppliersController;
use Cartino\Http\Controllers\Api\TaxRateController;
use Cartino\Http\Controllers\Api\UserController;
use Cartino\Http\Controllers\Api\UserGroupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes for Cartino with support for:
| - Public product/collection browsing
| - Authentication (Sanctum)
| - Cart management
| - Admin operations with permissions
| - Multi-site support
|
*/

Route::group([
    'prefix' => 'api',
    'middleware' => ['api', 'force.json'],
], function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    // Brands resource with additional custom methods
    Route::apiResource('brands', BrandsController::class, [
        'names' => 'api.brands',
    ]);
    Route::apiResource('orders', OrdersController::class, [
        'names' => 'api.orders',
    ]);
    Route::apiResource('addresses', AddressesController::class, [
        'names' => 'api.addresses',
    ]);
    Route::apiResource('channels', ChannelController::class, [
        'names' => 'api.channels',
    ]);
    Route::apiResource('products', ProductController::class, [
        'names' => 'api.products',
    ]);
    Route::apiResource('product-types', ProductTypesController::class, [
        'names' => 'api.product-types',
    ]);
    Route::apiResource('sites', SitesController::class, [
        'names' => 'api.sites',
    ]);
    Route::apiResource('categories', CategoriesController::class, [
        'names' => 'api.categories',
    ]);
    Route::apiResource('payment-methods', PaymentMethodsController::class, [
        'names' => 'api.payment-methods',
    ]);
    Route::apiResource('customers', CustomerController::class, [
        'names' => 'api.customers',
    ]);
    Route::apiResource('customer-groups', CustomerGroupsController::class, [
        'names' => 'api.customer-groups',
    ]);
    Route::apiResource('suppliers', SuppliersController::class, [
        'names' => 'api.suppliers',
    ]);
    Route::apiResource('discounts', DiscountsController::class, [
        'names' => 'api.discounts',
    ]);

    // Couriers resource
    Route::apiResource('couriers', CouriersController::class, [
        'names' => 'api.couriers',
    ]);

    // Authentication
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');

    // Data Endpoints (Statuses, etc.)
    Route::prefix('data')->name('api.data.')->group(function () {
        Route::get('/statuses', [StatusController::class, 'index'])->name('statuses.index');
        Route::get('/statuses/{type}', [StatusController::class, 'show'])->name('statuses.show');
    });

    // Public Countries/Currencies
    Route::prefix('countries')->name('api.countries.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\CountryController::class, 'index'])->name('index');
        Route::get('/{country}', [\Cartino\Http\Controllers\Api\CountryController::class, 'show'])->name('show');
        Route::get('/{country}/states', [\Cartino\Http\Controllers\Api\CountryController::class, 'states'])->name('states');
        Route::get('/{country}/cities', [\Cartino\Http\Controllers\Api\CountryController::class, 'cities'])->name('cities');
    });

    Route::prefix('currencies')->name('api.currencies.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\CurrencyController::class, 'index'])->name('index');
        Route::get('/{currency}', [\Cartino\Http\Controllers\Api\CurrencyController::class, 'show'])->name('show');
        Route::post('/convert', [\Cartino\Http\Controllers\Api\CurrencyController::class, 'convert'])->name('convert');
    });

    // Public Shipping Methods
    Route::prefix('shipping-methods')->name('api.shipping-methods.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\ShippingMethodController::class, 'index'])->name('index');
        Route::post('/calculate', [\Cartino\Http\Controllers\Api\ShippingMethodController::class, 'calculate'])->name('calculate');
    });

    // Public Tax Rates
    Route::prefix('tax-rates')->name('api.tax-rates.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\TaxRateController::class, 'index'])->name('index');
        Route::post('/calculate', [\Cartino\Http\Controllers\Api\TaxRateController::class, 'calculate'])->name('calculate');
    });

    // Fidelity System Configuration (Public)
    Route::get('/fidelity/configuration', [FidelityController::class, 'configuration'])->name('api.fidelity.configuration');
    Route::post('/fidelity/calculate-points', [FidelityController::class, 'calculatePoints'])->name('api.fidelity.calculate-points');
    Route::post('/fidelity/find-card', [FidelityController::class, 'findByCardNumber'])->name('api.fidelity.find-card');

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

    // Permission Management
    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\PermissionController::class, 'index'])->name('index');
        Route::get('/roles/{role}/permissions', [\Cartino\Http\Controllers\Api\PermissionController::class, 'rolePermissions'])->name('role.permissions');
        Route::put('/roles/{role}/permissions', [\Cartino\Http\Controllers\Api\PermissionController::class, 'updateRolePermissions'])->name('role.update');
        Route::post('/generate', [\Cartino\Http\Controllers\Api\PermissionController::class, 'generatePermissions'])->name('generate');
        Route::post('/super-role', [\Cartino\Http\Controllers\Api\PermissionController::class, 'createSuperRole'])->name('super.create');
        Route::get('/tree', [\Cartino\Http\Controllers\Api\PermissionController::class, 'permissionTree'])->name('tree');
    });

    // Custom role actions
    Route::post('roles/{role}/assign-users', [\Cartino\Http\Controllers\Api\RoleController::class, 'assignUsers'])->name('api.roles.assign.users');
    Route::post('roles/{role}/remove-users', [\Cartino\Http\Controllers\Api\RoleController::class, 'removeUsers'])->name('api.roles.remove.users');
    Route::post('roles/{role}/clone', [\Cartino\Http\Controllers\Api\RoleController::class, 'clone'])->name('api.roles.clone');
    Route::get('roles/statistics', [\Cartino\Http\Controllers\Api\RoleController::class, 'statistics'])->name('api.roles.statistics');

    // Customer Management (Standardized with apiResource)
    Route::apiResource('customers', \Cartino\Http\Controllers\Api\CustomerController::class, [
        'names' => 'api.customers',
    ]);

    // Custom customer actions
    Route::get('customers/with-fidelity', [\Cartino\Http\Controllers\Api\CustomerController::class, 'indexWithFidelity'])->name('api.customers.index-with-fidelity');
    Route::get('customers/{customer}/fidelity', [\Cartino\Http\Controllers\Api\CustomerController::class, 'fidelityCard'])->name('api.customers.fidelity');
    Route::post('customers/{customer}/fidelity', [\Cartino\Http\Controllers\Api\CustomerController::class, 'createFidelityCard'])->name('api.customers.fidelity.create');
    Route::get('customers/{customer}/orders', [\Cartino\Http\Controllers\Api\CustomerController::class, 'orders'])->name('api.customers.orders');
    Route::get('customers/{customer}/addresses', [\Cartino\Http\Controllers\Api\CustomerController::class, 'addresses'])->name('api.customers.addresses');
    Route::post('customers/{customer}/addresses', [\Cartino\Http\Controllers\Api\CustomerController::class, 'addAddress'])->name('api.customers.addresses.add');
    Route::get('customers/{customer}/statistics', [\Cartino\Http\Controllers\Api\CustomerController::class, 'statistics'])->name('api.customers.statistics');
    Route::post('customers/bulk', [\Cartino\Http\Controllers\Api\CustomerController::class, 'bulk'])->name('api.customers.bulk');

    // Fidelity System Management
    Route::prefix('fidelity')->name('fidelity.')->group(function () {
        Route::post('/redeem-points', [FidelityController::class, 'redeemPoints'])->name('redeem-points');
        Route::get('/cards', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'index'])->name('cards.index');
        Route::get('/cards/{card}', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'show'])->name('cards.show');
        Route::put('/cards/{card}', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'update'])->name('cards.update');
        Route::post('/cards/{card}/add-points', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'addPoints'])->name('cards.add-points');
        Route::get('/statistics', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'statistics'])->name('statistics');
        Route::post('/expire-points', [\Cartino\Http\Controllers\Api\Admin\FidelityAdminController::class, 'expirePoints'])->name('expire-points');
    });

    // User Group Management (Standardized with apiResource)
    Route::apiResource('user-groups', UserGroupController::class, [
        'names' => 'api.user-groups',
    ]);

    // Custom user-group actions
    Route::post('user-groups/{group}/assign-users', [UserGroupController::class, 'assignUsers'])->name('api.user-groups.assign.users');
    Route::post('user-groups/{group}/remove-users', [UserGroupController::class, 'removeUsers'])->name('api.user-groups.remove.users');
    Route::get('user-groups/{group}/permissions', [UserGroupController::class, 'permissions'])->name('api.user-groups.permissions');

    // User Management (Standardized with apiResource)
    Route::apiResource('users', UserController::class, [
        'names' => 'api.users',
    ]);

    // Custom user actions
    Route::post('users/{user}/activate', [UserController::class, 'activate'])->name('api.users.activate');
    Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('api.users.deactivate');
    Route::post('users/{user}/assign-roles', [UserController::class, 'assignRoles'])->name('api.users.assign.roles');
    Route::post('users/{user}/assign-permissions', [UserController::class, 'assignPermissions'])->name('api.users.assign.permissions');
    Route::get('users/{user}/activity', [UserController::class, 'activity'])->name('api.users.activity');
    Route::post('users/bulk', [UserController::class, 'bulk'])->name('api.users.bulk');

    // Brand Management (Admin) resource
    Route::apiResource('brands', BrandsController::class, [
        'names' => 'api.brands',
    ]);

    // Additional admin brand operations
    Route::post('brands/{brand}/toggle-status', [BrandsController::class, 'toggleStatus'])->name('api.brands.toggleStatus');
    Route::get('brands/{brand}/products', [BrandsController::class, 'products'])->name('api.brands.products');

    // Courier Management (Admin) resource
    Route::apiResource('couriers', CouriersController::class, [
        'names' => 'api.couriers',
    ]);

    // Additional admin courier operations
    Route::post('couriers/{courier}/toggle-status', [CouriersController::class, 'toggleStatus'])->name('api.couriers.toggleStatus');
    Route::post('couriers/{courier}/toggle-enabled', [CouriersController::class, 'toggleEnabled'])->name('api.couriers.toggleEnabled');
    Route::get('couriers/{courier}/orders', [CouriersController::class, 'orders'])->name('api.couriers.orders');
    Route::get('couriers/enabled', [CouriersController::class, 'enabled'])->name('api.couriers.enabled');

    // Order Management (Admin)
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
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
    // Country Management
    Route::prefix('countries')->name('countries.')->group(function () {
        Route::get('/', [CountryController::class, 'index'])->name('index');
        Route::post('/', [CountryController::class, 'store'])->name('store');
        Route::get('/{country}', [CountryController::class, 'adminShow'])->name('show');
        Route::put('/{country}', [CountryController::class, 'update'])->name('update');
        Route::delete('/{country}', [CountryController::class, 'destroy'])->name('destroy');
        Route::post('/{country}/toggle-status', [CountryController::class, 'toggleStatus'])->name('toggle.status');
        Route::post('/bulk', [CountryController::class, 'bulk'])->name('bulk');
    });

    // Currency Management
    Route::prefix('currencies')->name('currencies.')->group(function () {
        Route::get('/', [CurrencyController::class, 'index'])->name('index');
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::get('/{currency}', [CurrencyController::class, 'adminShow'])->name('show');
        Route::put('/{currency}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{currency}', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/{currency}/set-default', [CurrencyController::class, 'setDefault'])->name('set.default');
        Route::post('/bulk', [CurrencyController::class, 'bulk'])->name('bulk');
    });

    // Shipping Method Management
    Route::prefix('shipping-methods')->name('shipping-methods.')->group(function () {
        Route::get('/', [ShippingMethodController::class, 'index'])->name('index');
        Route::post('/', [ShippingMethodController::class, 'store'])->name('store');
        Route::get('/{method}', [ShippingMethodController::class, 'adminShow'])->name('show');
        Route::put('/{method}', [ShippingMethodController::class, 'update'])->name('update');
        Route::delete('/{method}', [ShippingMethodController::class, 'destroy'])->name('destroy');
        Route::post('/{method}/toggle-status', [ShippingMethodController::class, 'toggleStatus'])->name('toggle.status');
        Route::post('/bulk', [ShippingMethodController::class, 'bulk'])->name('bulk');
    });

    // Tax Rate Management
    Route::prefix('tax-rates')->name('tax-rates.')->group(function () {
        Route::get('/', [TaxRateController::class, 'index'])->name('index');
        Route::post('/', [TaxRateController::class, 'store'])->name('store');
        Route::get('/{rate}', [TaxRateController::class, 'adminShow'])->name('show');
        Route::put('/{rate}', [TaxRateController::class, 'update'])->name('update');
        Route::delete('/{rate}', [TaxRateController::class, 'destroy'])->name('destroy');
        Route::post('/{rate}/toggle-status', [TaxRateController::class, 'toggleStatus'])->name('toggle.status');
        Route::post('/bulk', [TaxRateController::class, 'bulk'])->name('bulk');
    });

    // Discount Management (Admin)
    Route::prefix('discounts')->name('discounts.')->group(function () {
        Route::get('/', [DiscountController::class, 'index'])->name('index');
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
        Route::get('/', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'builder'])->name('index');
        Route::put('/matrix', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'updateMatrix'])->name('matrix.update');
        Route::post('/apply-template', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'applyTemplate'])->name('template.apply');
        Route::post('/generate-resource', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'generateResourcePermissions'])->name('resource.generate');
        Route::get('/export', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'export'])->name('export');
        Route::post('/import', [\Cartino\Http\Controllers\Api\PermissionBuilderController::class, 'import'])->name('import');
    });

    // Asset Container Management
    Route::prefix('asset-containers')->name('asset-containers.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\AssetContainerController::class, 'index'])->name('index');
        Route::post('/', [\Cartino\Http\Controllers\Api\AssetContainerController::class, 'store'])->name('store');
        Route::get('/{container}', [\Cartino\Http\Controllers\Api\AssetContainerController::class, 'show'])->name('show');
        Route::put('/{container}', [\Cartino\Http\Controllers\Api\AssetContainerController::class, 'update'])->name('update');
        Route::delete('/{container}', [\Cartino\Http\Controllers\Api\AssetContainerController::class, 'destroy'])->name('destroy');
    });

    // Asset Management
    Route::prefix('assets')->name('assets.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\AssetController::class, 'index'])->name('index');
        Route::post('/upload', [\Cartino\Http\Controllers\Api\AssetController::class, 'upload'])->name('upload');
        Route::post('/upload-multiple', [\Cartino\Http\Controllers\Api\AssetController::class, 'uploadMultiple'])->name('upload.multiple');
        Route::get('/{asset}', [\Cartino\Http\Controllers\Api\AssetController::class, 'show'])->name('show');
        Route::put('/{asset}', [\Cartino\Http\Controllers\Api\AssetController::class, 'update'])->name('update');
        Route::delete('/{asset}', [\Cartino\Http\Controllers\Api\AssetController::class, 'destroy'])->name('destroy');
        Route::post('/{asset}/move', [\Cartino\Http\Controllers\Api\AssetController::class, 'move'])->name('move');
        Route::post('/{asset}/rename', [\Cartino\Http\Controllers\Api\AssetController::class, 'rename'])->name('rename');
        Route::get('/{asset}/download', [\Cartino\Http\Controllers\Api\AssetController::class, 'download'])->name('download');
        Route::post('/bulk-delete', [\Cartino\Http\Controllers\Api\AssetController::class, 'bulkDelete'])->name('bulk.delete');
    });

    // Globals Management (like Statamic)
    Route::prefix('globals')->name('globals.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'index'])->name('index');
        Route::post('/', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'store'])->name('store');
        Route::get('/handle/{handle}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'byHandle'])->name('by-handle');
        Route::put('/handle/{handle}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'updateByHandle'])->name('update-by-handle');
        Route::post('/handle/{handle}/set-value', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'setValue'])->name('set-value');
        Route::get('/handle/{handle}/get-value/{key}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'getValue'])->name('get-value');
        Route::get('/{global}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'show'])->name('show');
        Route::put('/{global}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'update'])->name('update');
        Route::delete('/{global}', [\Cartino\Http\Controllers\Api\GlobalsController::class, 'destroy'])->name('destroy');
    });

    // Entries Management (like Statamic Collections)
    Route::prefix('entries')->name('entries.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\EntriesController::class, 'index'])->name('index');
        Route::post('/', [\Cartino\Http\Controllers\Api\EntriesController::class, 'store'])->name('store');
        Route::post('/reorder', [\Cartino\Http\Controllers\Api\EntriesController::class, 'reorder'])->name('reorder');
        Route::get('/collection/{collection}', [\Cartino\Http\Controllers\Api\EntriesController::class, 'byCollection'])->name('by-collection');
        Route::get('/collection/{collection}/tree', [\Cartino\Http\Controllers\Api\EntriesController::class, 'tree'])->name('tree');
        Route::get('/collection/{collection}/{slug}', [\Cartino\Http\Controllers\Api\EntriesController::class, 'bySlug'])->name('by-slug');
        Route::get('/{entry}', [\Cartino\Http\Controllers\Api\EntriesController::class, 'show'])->name('show');
        Route::put('/{entry}', [\Cartino\Http\Controllers\Api\EntriesController::class, 'update'])->name('update');
        Route::delete('/{entry}', [\Cartino\Http\Controllers\Api\EntriesController::class, 'destroy'])->name('destroy');
        Route::post('/{entry}/publish', [\Cartino\Http\Controllers\Api\EntriesController::class, 'publish'])->name('publish');
        Route::post('/{entry}/unpublish', [\Cartino\Http\Controllers\Api\EntriesController::class, 'unpublish'])->name('unpublish');
        Route::post('/{entry}/schedule', [\Cartino\Http\Controllers\Api\EntriesController::class, 'schedule'])->name('schedule');
        Route::post('/{entry}/duplicate', [\Cartino\Http\Controllers\Api\EntriesController::class, 'duplicate'])->name('duplicate');
    });

    // API Keys Management
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
        Route::get('/', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'index'])->name('index');
        Route::post('/', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'store'])->name('store');
        Route::get('/{apiKey}', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'show'])->name('show');
        Route::put('/{apiKey}', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'update'])->name('update');
        Route::delete('/{apiKey}', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'destroy'])->name('destroy');
        Route::post('/{apiKey}/revoke', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'revoke'])->name('revoke');
        Route::post('/{apiKey}/activate', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'activate'])->name('activate');
        Route::post('/{apiKey}/regenerate', [\Cartino\Http\Controllers\Api\ApiKeysController::class, 'regenerate'])->name('regenerate');
    });

    // Reports & Analytics
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/dashboard', [\Cartino\Http\Controllers\Api\ReportsController::class, 'dashboard'])->name('dashboard');
        Route::get('/sales', [\Cartino\Http\Controllers\Api\ReportsController::class, 'sales'])->name('sales');
        Route::get('/customers', [\Cartino\Http\Controllers\Api\ReportsController::class, 'customers'])->name('customers');
        Route::get('/products', [\Cartino\Http\Controllers\Api\ReportsController::class, 'products'])->name('products');
        Route::get('/revenue', [\Cartino\Http\Controllers\Api\ReportsController::class, 'revenue'])->name('revenue');
        Route::get('/inventory', [\Cartino\Http\Controllers\Api\ReportsController::class, 'inventory'])->name('inventory');
        Route::get('/orders-by-status', [\Cartino\Http\Controllers\Api\ReportsController::class, 'ordersByStatus'])->name('orders-by-status');
        Route::get('/export', [\Cartino\Http\Controllers\Api\ReportsController::class, 'export'])->name('export');
    });
});

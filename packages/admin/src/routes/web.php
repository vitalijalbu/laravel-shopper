<?php

use Illuminate\Support\Facades\Route;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\DashboardController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\ProductController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\OrderController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\CustomerController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\CategoryController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\BrandController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\CollectionController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\DiscountController;
use VitaliJalbu\LaravelShopper\Admin\Http\Controllers\SettingController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Products Management
Route::resource('products', ProductController::class)->names([
    'index' => 'products.index',
    'create' => 'products.create',
    'store' => 'products.store',
    'show' => 'products.show',
    'edit' => 'products.edit',
    'update' => 'products.update',
    'destroy' => 'products.destroy',
]);

// Product bulk actions
Route::post('products/bulk-update', [ProductController::class, 'bulkUpdate'])->name('products.bulk-update');
Route::post('products/bulk-delete', [ProductController::class, 'bulkDelete'])->name('products.bulk-delete');

// Orders Management  
Route::resource('orders', OrderController::class)->names([
    'index' => 'orders.index',
    'show' => 'orders.show',
    'edit' => 'orders.edit',
    'update' => 'orders.update',
]);

// Order status updates
Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
Route::patch('orders/{order}/fulfill', [OrderController::class, 'fulfill'])->name('orders.fulfill');

// Customers Management
Route::resource('customers', CustomerController::class)->names([
    'index' => 'customers.index',
    'create' => 'customers.create',
    'store' => 'customers.store',
    'show' => 'customers.show',
    'edit' => 'customers.edit',
    'update' => 'customers.update',
    'destroy' => 'customers.destroy',
]);

// Categories Management
Route::resource('categories', CategoryController::class)->names([
    'index' => 'categories.index',
    'create' => 'categories.create',
    'store' => 'categories.store',
    'show' => 'categories.show',
    'edit' => 'categories.edit',
    'update' => 'categories.update',
    'destroy' => 'categories.destroy',
]);

// Brands Management
Route::resource('brands', BrandController::class)->names([
    'index' => 'brands.index',
    'create' => 'brands.create',
    'store' => 'brands.store',
    'show' => 'brands.show',
    'edit' => 'brands.edit',
    'update' => 'brands.update',
    'destroy' => 'brands.destroy',
]);

// Collections Management
Route::resource('collections', CollectionController::class)->names([
    'index' => 'collections.index',
    'create' => 'collections.create',
    'store' => 'collections.store',
    'show' => 'collections.show',
    'edit' => 'collections.edit',
    'update' => 'collections.update',
    'destroy' => 'collections.destroy',
]);

// Discounts Management
Route::resource('discounts', DiscountController::class)->names([
    'index' => 'discounts.index',
    'create' => 'discounts.create',
    'store' => 'discounts.store',
    'show' => 'discounts.show',
    'edit' => 'discounts.edit',
    'update' => 'discounts.update',
    'destroy' => 'discounts.destroy',
]);

// Settings
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/', [SettingController::class, 'index'])->name('index');
    Route::get('general', [SettingController::class, 'general'])->name('general');
    Route::post('general', [SettingController::class, 'updateGeneral'])->name('general.update');
    Route::get('shipping', [SettingController::class, 'shipping'])->name('shipping');
    Route::post('shipping', [SettingController::class, 'updateShipping'])->name('shipping.update');
    Route::get('taxes', [SettingController::class, 'taxes'])->name('taxes');
    Route::post('taxes', [SettingController::class, 'updateTaxes'])->name('taxes.update');
    Route::get('payments', [SettingController::class, 'payments'])->name('payments');
    Route::post('payments', [SettingController::class, 'updatePayments'])->name('payments.update');
});

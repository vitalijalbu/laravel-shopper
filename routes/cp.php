<?php

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Cp\CollectionsController;
use Shopper\Http\Controllers\Cp\EntriesController;
use Shopper\Http\Controllers\Cp\DashboardController;
use Shopper\Http\Controllers\Cp\NavigationController;
use Shopper\Http\Controllers\Cp\SitesController;

/*
|--------------------------------------------------------------------------
| Control Panel API Routes
|--------------------------------------------------------------------------
|
| Here are the API routes for the Shopper control panel. These routes
| are loaded by the ShopperServiceProvider within a group which
| contains the "web" middleware group and "cp" prefix.
|
*/

Route::prefix('api/cp')->middleware(['web', 'auth:web'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('shopper.cp.dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('shopper.cp.dashboard.stats');
    
    // Navigation
    Route::get('/navigation', [NavigationController::class, 'index'])->name('shopper.cp.navigation');
    Route::get('/navigation/breadcrumbs', [NavigationController::class, 'breadcrumbs'])->name('shopper.cp.navigation.breadcrumbs');
    
    // Sites
    Route::get('/sites', [SitesController::class, 'index'])->name('shopper.cp.sites.index');
    Route::get('/sites/current', [SitesController::class, 'current'])->name('shopper.cp.sites.current');
    Route::post('/sites/switch', [SitesController::class, 'switch'])->name('shopper.cp.sites.switch');
    
    // Collections
    Route::apiResource('collections', CollectionsController::class)->parameters([
        'collections' => 'collection'
    ]);
    
    // Collection Entries
    Route::prefix('collections/{collection}')->group(function () {
        Route::get('/entries', [EntriesController::class, 'index'])->name('shopper.cp.entries.index');
        Route::post('/entries', [EntriesController::class, 'store'])->name('shopper.cp.entries.store');
        Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('shopper.cp.entries.show');
        Route::put('/entries/{entry}', [EntriesController::class, 'update'])->name('shopper.cp.entries.update');
        Route::delete('/entries/{entry}', [EntriesController::class, 'destroy'])->name('shopper.cp.entries.destroy');
        Route::post('/entries/bulk-action', [EntriesController::class, 'bulkAction'])->name('shopper.cp.entries.bulk-action');
    });
    
    // Search
    Route::get('/search', function () {
        $query = request('q');
        
        return response()->json([
            'results' => [
                [
                    'type' => 'collection',
                    'title' => 'Products',
                    'handle' => 'products',
                    'url' => '/cp/collections/products',
                    'description' => 'Product catalog'
                ],
                [
                    'type' => 'entry',
                    'title' => 'Premium Wireless Headphones',
                    'handle' => 'premium-wireless-headphones',
                    'url' => '/cp/collections/products/entries/1',
                    'description' => 'Product entry'
                ]
            ]
        ]);
    })->name('shopper.cp.search');
    
    // User Preferences
    Route::get('/preferences', function () {
        return response()->json([
            'preferences' => [
                'sidebar_collapsed' => false,
                'theme' => 'light',
                'entries_per_page' => 15,
                'default_view' => 'table'
            ]
        ]);
    })->name('shopper.cp.preferences');
    
    Route::put('/preferences', function () {
        return response()->json([
            'message' => 'Preferences updated successfully'
        ]);
    })->name('shopper.cp.preferences.update');
    
    // Live Preview
    Route::get('/live-preview/{collection}/{entry}', function ($collection, $entry) {
        return response()->json([
            'preview_url' => "/preview/{$collection}/{$entry}",
            'is_enabled' => true
        ]);
    })->name('shopper.cp.live-preview');
});

// Auth Routes for CP
Route::prefix('cp')->group(function () {
    Route::get('/login', function () {
        if (request()->wantsJson()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        return view('shopper::auth.login');
    })->name('shopper.cp.login');
    
    Route::post('/login', function () {
        // Handle login logic
        return response()->json(['message' => 'Login successful']);
    })->name('shopper.cp.login.post');
    
    Route::post('/logout', function () {
        // Handle logout logic
        return response()->json(['message' => 'Logout successful']);
    })->name('shopper.cp.logout');
});

// SPA Route - Catch all routes for Vue.js app
Route::get('/cp/{any?}', function () {
    return view('shopper::cp.app');
})->where('any', '.*')->name('shopper.cp.spa');

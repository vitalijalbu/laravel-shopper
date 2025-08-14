<?php

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Cp\CollectionsController;
use Shopper\Http\Controllers\Cp\EntriesController;
use Shopper\Http\Controllers\Cp\DashboardController;

/*
|--------------------------------------------------------------------------
| Shopper Control Panel Routes
|--------------------------------------------------------------------------
|
| Here are the routes for the Shopper control panel. These routes mirror
| the Statamic CMS structure with both admin interface and API endpoints.
|
*/

// Control Panel Admin Interface Routes (Inertia.js)
Route::prefix('cp')->name('shopper.cp.')->middleware(['web', 'auth'])->group(function () {
    
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Collections Management
    Route::get('/collections', [CollectionsController::class, 'index'])->name('collections.index');
    Route::get('/collections/create', [CollectionsController::class, 'create'])->name('collections.create');
    Route::get('/collections/{collection}', [CollectionsController::class, 'show'])->name('collections.show');
    Route::get('/collections/{collection}/edit', [CollectionsController::class, 'edit'])->name('collections.edit');
    
    // Collection Entries Management
    Route::prefix('collections/{collection}')->name('collections.')->group(function () {
        Route::get('/entries', [EntriesController::class, 'index'])->name('entries.index');
        Route::get('/entries/create', [EntriesController::class, 'create'])->name('entries.create');
        Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('entries.show');
        Route::get('/entries/{entry}/edit', [EntriesController::class, 'edit'])->name('entries.edit');
    });
    
    // Utilities
    Route::get('/utilities', function () {
        return inertia('Utilities/Index');
    })->name('utilities.index');
    
    Route::get('/utilities/import', function () {
        return inertia('Utilities/Import');
    })->name('utilities.import');
    
    Route::get('/utilities/export', function () {
        return inertia('Utilities/Export');  
    })->name('utilities.export');
});

// Control Panel API Routes
Route::prefix('cp/api')->name('shopper.cp.api.')->middleware(['web', 'auth:sanctum'])->group(function () {
    
    // Dashboard API
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');
    
    // Collections API
    Route::apiResource('collections', CollectionsController::class);
    
    // Entries API
    Route::prefix('collections/{collection}')->group(function () {
        Route::get('/entries', [EntriesController::class, 'index'])->name('entries.index');
        Route::post('/entries', [EntriesController::class, 'store'])->name('entries.store');
        Route::get('/entries/{entry}', [EntriesController::class, 'show'])->name('entries.show');
        Route::put('/entries/{entry}', [EntriesController::class, 'update'])->name('entries.update');
        Route::delete('/entries/{entry}', [EntriesController::class, 'destroy'])->name('entries.destroy');
        Route::post('/entries/bulk', [EntriesController::class, 'bulk'])->name('entries.bulk');
    });
    
    // Import/Export API
    Route::post('/import', function () {
        return response()->json(['message' => 'Import started']);
    })->name('import');
    
    Route::post('/export', function () {
        return response()->json(['download_url' => '/cp/api/download/export.csv']);
    })->name('export');
    
    // Global Search API
    Route::get('/search', function () {
        $query = request('q');
        return response()->json([
            'results' => [
                [
                    'type' => 'collection',
                    'title' => 'Products',
                    'url' => '/cp/collections/products',
                ],
                [
                    'type' => 'entry', 
                    'title' => 'Premium Headphones',
                    'url' => '/cp/collections/products/entries/1',
                ]
            ]
        ]);
    })->name('search');
});

// Auth Routes
Route::prefix('cp')->group(function () {
    Route::get('/auth/login', function () {
        return inertia('Auth/Login');
    })->name('shopper.cp.auth.login')->middleware('guest');
    
    Route::post('/auth/login', function () {
        // Handle login
        return redirect()->route('shopper.cp.dashboard');
    })->name('shopper.cp.auth.login.post');
    
    Route::post('/auth/logout', function () {
        auth()->logout();
        return redirect()->route('shopper.cp.auth.login');
    })->name('shopper.cp.auth.logout');
});

<?php

use Illuminate\Support\Facades\Route;
use LaravelShopper\Http\Controllers\Cp\AppsController;
use LaravelShopper\Http\Controllers\Cp\Auth\AuthenticatedSessionController;
use LaravelShopper\Http\Controllers\Cp\Auth\NewPasswordController;
use LaravelShopper\Http\Controllers\Cp\Auth\PasswordResetLinkController;
use LaravelShopper\Http\Controllers\Cp\CollectionsController;
use LaravelShopper\Http\Controllers\Cp\DashboardController;
use LaravelShopper\Http\Controllers\Cp\EntriesController;

/*
|--------------------------------------------------------------------------
| Control Panel Routes
|--------------------------------------------------------------------------
|
| These routes handle the Shopper Control Panel interface, similar to
| Statamic CMS. All routes are prefixed with the CP route prefix.
|
*/

// CP prefix from config (default: 'cp')
$cpPrefix = config('cp.route_prefix', 'cp');

Route::prefix($cpPrefix)->name('cp.')->middleware(['web', 'shopper.inertia'])->group(function () {

    // Authentication Routes (Guest only)
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('login');

        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->name('login.store');

        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
            ->name('password.request');

        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('password.email');

        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
            ->name('password.reset');

        Route::post('reset-password', [NewPasswordController::class, 'store'])
            ->name('password.store');
    });

    // Authenticated Routes (CP access required)
    Route::middleware(['auth', 'cp'])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('logout');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('dashboard');

        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');

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

        // Apps Management
        Route::prefix('apps')->name('apps.')->group(function () {
            Route::get('/', [AppsController::class, 'store'])->name('store');
            Route::get('/installed', [AppsController::class, 'installed'])->name('installed');
            Route::get('/submit', [AppsController::class, 'submit'])->name('submit');
            Route::get('/{app}/configure', [AppsController::class, 'configure'])->name('configure');
            Route::post('/install', [AppsController::class, 'install'])->name('install');
            Route::delete('/{app}/uninstall', [AppsController::class, 'uninstall'])->name('uninstall');
            Route::post('/{installation}/activate', [AppsController::class, 'activate'])->name('activate');
            Route::post('/{installation}/deactivate', [AppsController::class, 'deactivate'])->name('deactivate');
            Route::post('/bulk-activate', [AppsController::class, 'bulkActivate'])->name('bulk-activate');
            Route::post('/bulk-deactivate', [AppsController::class, 'bulkDeactivate'])->name('bulk-deactivate');
            Route::delete('/bulk-uninstall', [AppsController::class, 'bulkUninstall'])->name('bulk-uninstall');
            Route::get('/{installation}/analytics', [AppsController::class, 'analytics'])->name('analytics');
            Route::post('/{app}/reviews', [AppsController::class, 'storeReview'])->name('reviews.store');
        });
    });
});

// Control Panel API Routes
Route::prefix('cp/api')->name('cp.api.')->middleware(['web', 'auth:sanctum'])->group(function () {

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
                ],
            ],
        ]);
    })->name('search');
});

<?php

use Illuminate\Support\Facades\Route;
use Shopper\Http\Controllers\Api\Auth\SocialAuthApiController;

/*
|--------------------------------------------------------------------------
| API Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle OAuth authentication for SPA/API clients.
| They return JSON responses and API tokens instead of redirecting.
|
*/

Route::prefix('auth/social')->name('api.auth.social.')->group(function () {

    // Public routes (no authentication required)
    Route::get('providers', [SocialAuthApiController::class, 'providers'])
        ->name('providers');

    Route::get('{provider}/redirect', [SocialAuthApiController::class, 'getRedirectUrl'])
        ->name('redirect')
        ->where('provider', '[a-z]+');

    Route::post('{provider}/callback', [SocialAuthApiController::class, 'callback'])
        ->name('callback')
        ->where('provider', '[a-z]+');

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('connected', [SocialAuthApiController::class, 'connectedProviders'])
            ->name('connected');

        Route::post('{provider}/link', [SocialAuthApiController::class, 'link'])
            ->name('link')
            ->where('provider', '[a-z]+');

        Route::delete('{provider}/unlink', [SocialAuthApiController::class, 'unlink'])
            ->name('unlink')
            ->where('provider', '[a-z]+');

        Route::post('{provider}/refresh-token', [SocialAuthApiController::class, 'refreshToken'])
            ->name('refresh-token')
            ->where('provider', '[a-z]+');
    });
});

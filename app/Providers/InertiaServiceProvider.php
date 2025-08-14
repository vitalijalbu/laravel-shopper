<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class InertiaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Inertia::share([
            // Flash messages
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                    'warning' => Session::get('warning'),
                    'info' => Session::get('info'),
                ];
            },

            // Authentication
            'auth' => function () {
                return [
                    'user' => auth()->user(),
                ];
            },

            // Locale and translations
            'locale' => function () {
                return [
                    'current' => App::getLocale(),
                    'available' => config('app.available_locales', ['en', 'it']),
                ];
            },

            // Translations - Statamic CMS style
            'translations' => function () {
                $locale = App::getLocale();
                
                return [
                    // Core admin translations
                    'admin' => Lang::get('admin', [], $locale),
                    
                    // Module-specific translations
                    'products' => Lang::get('products', [], $locale),
                    'categories' => Lang::get('categories', [], $locale),
                    'brands' => Lang::get('brands', [], $locale),
                    'pages' => Lang::get('pages', [], $locale),
                    
                    // Laravel validation messages
                    'validation' => Lang::get('validation', [], $locale),
                    
                    // Auth messages
                    'auth' => Lang::get('auth', [], $locale),
                    
                    // Pagination
                    'pagination' => Lang::get('pagination', [], $locale),
                ];
            },

            // Application config
            'config' => function () {
                return [
                    'app_name' => config('app.name'),
                    'app_url' => config('app.url'),
                    'currency' => config('shopper.currency', 'EUR'),
                    'timezone' => config('app.timezone'),
                ];
            },
        ]);
    }
}

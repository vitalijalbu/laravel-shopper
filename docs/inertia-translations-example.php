<?php

/**
 * Esempio di come configurare le traduzioni per Inertia.js
 * Questo dovrebbe essere nel tuo InertiaServiceProvider o nel controller
 */

// Nel tuo InertiaServiceProvider::boot()
Inertia::share([
    'translations' => function () {
        return [
            'shopper' => [
                'auth' => [
                    'headings' => [
                        'login' => __('shopper::auth.headings.login'),
                    ],
                    'descriptions' => [
                        'login' => __('shopper::auth.descriptions.login'),
                    ],
                    'labels' => [
                        'email' => __('shopper::auth.labels.email'),
                        'password' => __('shopper::auth.labels.password'),
                        'remember_me' => __('shopper::auth.labels.remember_me'),
                        'forgot_password' => __('shopper::auth.labels.forgot_password'),
                        'login' => __('shopper::auth.labels.login'),
                    ],
                    'placeholders' => [
                        'email' => __('shopper::auth.placeholders.email'),
                        'password' => __('shopper::auth.placeholders.password'),
                    ],
                    'actions' => [
                        'signing_in' => __('shopper::auth.actions.signing_in'),
                    ],
                ],
            ],
        ];
    },
    'locale' => function () {
        return app()->getLocale();
    },
    'locales' => function () {
        return config('app.available_locales', ['en', 'it']);
    },
]);

// Oppure nel controller specifico
class AuthController extends Controller
{
    public function showLoginForm()
    {
        return Inertia::render('Cp/Auth/Login', [
            'translations' => [
                'shopper' => [
                    'auth' => __('shopper::auth'),
                ],
            ],
        ]);
    }
}

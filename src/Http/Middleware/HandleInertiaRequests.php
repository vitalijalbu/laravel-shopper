<?php

namespace LaravelShopper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use LaravelShopper\Http\Resources\UserResource;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaRequests
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Share auth data globally with Inertia
        Inertia::share([
            'csrf_token' => csrf_token(),
            // Authentication
            'auth' => function () {
                $user = Auth::user();
                Log::info('HandleInertiaRequests - Auth data', [
                    'auth_check' => Auth::check(),
                    'user_id' => $user ? $user->id : null,
                    'user_email' => $user ? $user->email : null,
                ]);

                if (!$user) {
                    return ['user' => null];
                }

                // Simple user data to avoid timeout
                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name ?? ($user->first_name . ' ' . $user->last_name),
                        'email' => $user->email,
                        'can_access_cp' => true, // Simplified for now
                    ],
                ];
            },
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'errors' => function () use ($request) {
                return $request->session()->get('errors') 
                    ? $request->session()->get('errors')->getBag('default')->getMessages() 
                    : [];
            },
            'locale' => app()->getLocale(),
            'locales' => config('shopper.locales', ['en', 'it']),
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'debug' => config('app.debug'),
            ],
            'cp' => [
                'name' => config('shopper.cp.name', 'Control Panel'),
                'url' => config('shopper.cp.url', '/cp'),
                'branding' => [
                    'logo' => config('shopper.cp.branding.logo'),
                    'logo_dark' => config('shopper.cp.branding.logo_dark'),
                    'favicon' => config('shopper.cp.branding.favicon'),
                ],
            ],
        ]);

        return $next($request);
    }

    /**
     * Check if user can access control panel.
     */
    protected function canUserAccessCP($user): bool
    {
        if (! $user) {
            return false;
        }

        // Check if user has CP access permission or role
        if (method_exists($user, 'can')) {
            if ($user->can('access-cp') || $user->hasRole('admin') || $user->hasRole('super-admin')) {
                return true;
            }
        }

        // Check if user has specific field
        if (isset($user->can_access_cp)) {
            return (bool) $user->can_access_cp;
        }

        // Default: allow if user exists
        return true;
    }

    /**
     * Generate Gravatar URL.
     */
    protected function getGravatar(string $email): string
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower(trim($email))).'?s=64&d=mp';
    }
}

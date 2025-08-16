<?php

namespace LaravelShopper\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
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
            'auth' => [
                'user' => $request->user() ? [
                    'id' => $request->user()->id,
                    'name' => $request->user()->name,
                    'email' => $request->user()->email,
                    'avatar_url' => $request->user()->avatar_url ?? $this->getGravatar($request->user()->email),
                    'roles' => method_exists($request->user(), 'getRoleNames')
                        ? $request->user()->getRoleNames()->toArray()
                        : [],
                    'permissions' => method_exists($request->user(), 'getAllPermissions')
                        ? $request->user()->getAllPermissions()->pluck('name')->toArray()
                        : [],
                    'can_access_cp' => $this->canUserAccessCP($request->user()),
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'status' => fn () => $request->session()->get('status'),
            ],
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

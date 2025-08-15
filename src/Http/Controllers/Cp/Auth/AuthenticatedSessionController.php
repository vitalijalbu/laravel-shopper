<?php

namespace LaravelShopper\Http\Controllers\Cp\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): Response
    {
        // Check if already authenticated and trying to access CP
        if (Auth::check() && $this->canAccessControlPanel(Auth::user())) {
            return redirect()->intended(route('shopper.cp.dashboard'));
        }

        return Inertia::render('Cp/Auth/Login', [
            'status' => session('status'),
            'canResetPassword' => true,
            'locale' => app()->getLocale(),
            'locales' => config('shopper.locales', ['en', 'it']),
            'app_name' => config('app.name'),
            'cp_name' => config('shopper.cp.name', 'Control Panel'),
            'branding' => [
                'logo' => config('shopper.cp.branding.logo'),
                'logo_dark' => config('shopper.cp.branding.logo_dark'),
                'favicon' => config('shopper.cp.branding.favicon'),
            ],
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Rate limiting
        $this->ensureIsNotRateLimited($request);

        // Validate credentials
        $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => __('shopper::auth.validation.email_required'),
            'email.email' => __('shopper::auth.validation.email_invalid'),
            'password.required' => __('shopper::auth.validation.password_required'),
        ]);

        // Attempt authentication
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($this->throttleKey($request));

            throw ValidationException::withMessages([
                'email' => __('shopper::auth.failed'),
            ]);
        }

        // Check if user can access control panel
        $user = Auth::user();
        if (!$this->canAccessControlPanel($user)) {
            Auth::logout();
            
            throw ValidationException::withMessages([
                'email' => __('shopper::auth.cp_access_denied'),
            ]);
        }

        // Clear rate limiting
        RateLimiter::clear($this->throttleKey($request));

        // Regenerate session
        $request->session()->regenerate();

        // Log successful login
        Log::info('Control Panel login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Redirect to intended location
        return redirect()->intended(route('shopper.cp.dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Log logout
        if ($user) {
            Log::info('Control Panel logout', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);
        }

        // Logout user
        Auth::guard('web')->logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Clear Inertia user data
        return redirect()->route('shopper.cp.login')->with('status', __('shopper::auth.logged_out'));
    }

    /**
     * Check if user can access the control panel.
     */
    protected function canAccessControlPanel($user): bool
    {
        if (!$user) {
            return false;
        }

        // Check if user has CP access permission or is admin
        if (method_exists($user, 'can')) {
            return $user->can('access-cp') || $user->hasRole('admin') || $user->hasRole('super-admin');
        }

        // Fallback: check if user has specific field
        if (isset($user->can_access_cp)) {
            return (bool) $user->can_access_cp;
        }

        // Default: allow if user exists (configure based on your needs)
        return true;
    }

    /**
     * Ensure the login request is not rate limited.
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => __('shopper::auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email')) . '|' . $request->ip();
    }
}
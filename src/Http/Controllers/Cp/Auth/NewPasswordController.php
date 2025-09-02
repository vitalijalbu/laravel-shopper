<?php

namespace Shopper\Http\Controllers\Cp\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Http\Controllers\Controller;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Cp/Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
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
     * Handle an incoming new password request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate the request
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => __('shopper::auth.validation.email_required'),
            'email.email' => __('shopper::auth.validation.email_invalid'),
            'password.required' => __('shopper::auth.validation.password_required'),
            'password.confirmed' => __('shopper::auth.validation.password_confirmed'),
            'password.min' => __('shopper::auth.validation.password_min'),
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Clear all user's sessions
                if (method_exists($user, 'clearUserSessions')) {
                    $user->clearUserSessions();
                }
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('shopper.cp.login')->with('status', __('shopper::auth.password_reset_success'))
            : back()->withInput($request->only('email'))
                ->withErrors(['email' => __('shopper::auth.password_reset_invalid')]);
    }
}

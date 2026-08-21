<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Show the application's login form.
     */
    public function showLoginForm(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ]),
            ]);
        }

        $remember = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // Verify account is active
        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Your account is inactive. Please contact an administrator.',
            ]);
        }

        RateLimiter::clear($throttleKey);

        // Regenerate session for security
        $request->session()->regenerate();

        // Update last login timestamp
        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        ActivityLog::record('logged_in', "User '{$user->name}' logged in successfully", $user);

        $targetRoute = $user->getHomeRoute();
        
        // Verify user has authorization for intended URL, otherwise fallback to home route
        $intendedUrl = session()->get('url.intended');
        if ($intendedUrl) {
            try {
                $intendedRequest = Request::create($intendedUrl, 'GET');
                $route = \Illuminate\Support\Facades\Route::getRoutes()->match($intendedRequest);
                $middlewares = $route->gatherMiddleware();

                foreach ($middlewares as $middleware) {
                    if (is_string($middleware) && str_starts_with($middleware, 'permission:')) {
                        $permission = explode(':', $middleware, 2)[1];
                        if (!$user->can($permission)) {
                            session()->forget('url.intended');
                            break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                session()->forget('url.intended');
            }
        }

        return redirect()->intended($targetRoute)
            ->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        if ($user) {
            ActivityLog::record('logged_out', "User '{$user->name}' logged out", $user);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'You have been logged out successfully.');
    }
}

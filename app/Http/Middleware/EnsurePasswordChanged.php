<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordChanged
{
    private const ALLOWED_ROUTE_PATTERNS = [
        'profile.edit',
        'profile.update',
        'password.update',
        'logout',
        'verification.*',
        'password.confirm',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->with('status', 'Your account is inactive. Please contact an administrator or club officer for assistance.');
        }

        if (! $user->must_change_password) {
            return $next($request);
        }

        foreach (self::ALLOWED_ROUTE_PATTERNS as $pattern) {
            if ($request->routeIs($pattern)) {
                return $next($request);
            }
        }

        return redirect()
            ->route('profile.edit')
            ->with('force_password_change', 'Please change your temporary password before continuing.');
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        if (! $user || ! $user->must_change_password) {
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

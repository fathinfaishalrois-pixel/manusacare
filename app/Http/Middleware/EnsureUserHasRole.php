<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $guard = Auth::getDefaultDriver();
        $user = $request->user();

        abort_unless($user, 403);

        if (! $user->is_active) {
            // Clear only the inactive guard. A different role may still be
            // authenticated in another tab of this browser session.
            Auth::guard($guard)->logout();
            return redirect()->route($guard === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['login' => 'Akun ini sedang dinonaktifkan. Hubungi administrator.']);
        }

        abort_unless(collect($roles)->contains(fn ($role) => $user->hasRole($role)), 403);

        return $next($request);
    }
}

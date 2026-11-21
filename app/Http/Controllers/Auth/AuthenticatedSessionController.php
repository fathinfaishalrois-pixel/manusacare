<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        if ($this->moveLegacyAdminToAdminGuard(request())) {
            return redirect()->route('admin.dashboard');
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if (! $user->is_active) {
                Auth::guard('web')->logout();
            } else {
                return redirect()->route($user->hasRole('officer') ? 'officer.dashboard' : 'reporter.dashboard');
            }
        }

        return view('auth.login', ['admin' => false]);
    }

    public function adminCreate()
    {
        $this->moveLegacyAdminToAdminGuard(request());

        if (Auth::guard('admin')->check()) {
            if (! Auth::guard('admin')->user()->is_active) {
                Auth::guard('admin')->logout();
            } else {
                return redirect()->route('admin.dashboard');
            }
        }

        return view('auth.login', ['admin' => true]);
    }

    public function store(Request $request)
    {
        $this->moveLegacyAdminToAdminGuard($request);
        return $this->authenticate($request, 'web');
    }

    public function adminStore(Request $request)
    {
        return $this->authenticate($request, 'admin');
    }

    private function moveLegacyAdminToAdminGuard(Request $request): bool
    {
        $legacyUser = Auth::guard('web')->user();
        if (! $legacyUser || ! $legacyUser->hasRole('admin')) {
            return false;
        }

        Auth::guard('web')->logout();
        Auth::guard('admin')->login($legacyUser);
        $request->session()->migrate(true);

        return true;
    }

    private function authenticate(Request $request, string $guard)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($credentials['login']);
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'identity_number';

        if (! Auth::guard($guard)->attempt([
            $field => $identifier,
            'password' => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'login' => 'Email/NIS/NIP/NIK/ID atau kata sandi tidak sesuai.',
            ]);
        }

        // Rotate the session ID without invalidating the other guard's login.
        // Laravel's session migration carries the existing session data forward.
        $request->session()->migrate(true);
        $user = Auth::guard($guard)->user();

        $allowed = $guard === 'admin'
            ? $user->hasRole('admin')
            : ! $user->hasRole('admin');

        if (! $allowed) {
            // Clear only the guard that was just used. Do not destroy the
            // reporter/admin session in the other tab.
            Auth::guard($guard)->logout();

            throw ValidationException::withMessages([
                'login' => 'Akun tidak memiliki akses ke halaman ini.',
            ]);
        }

        if ($guard === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route($user->hasRole('officer') ? 'officer.dashboard' : 'reporter.dashboard');
    }

    public function destroy(Request $request, string $guard = 'web')
    {
        abort_unless(in_array($guard, ['web', 'admin'], true), 404);

        Auth::guard($guard)->logout();

        // Do not invalidate the whole shared browser session: the other guard
        // may still be active in a separate tab.
        return redirect()->route('home');
    }
}

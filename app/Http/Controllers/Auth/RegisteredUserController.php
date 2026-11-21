<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\IdentityInput;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View|RedirectResponse
    {
        $this->moveLegacyAdminToAdminGuard(request());

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if (! $user->is_active) {
                Auth::guard('web')->logout();
            } else {
                return redirect()->route($user->hasRole('officer') ? 'officer.dashboard' : 'reporter.dashboard');
            }
        }

        return view('auth.register');
    }

    private function moveLegacyAdminToAdminGuard(Request $request): void
    {
        $legacyUser = Auth::guard('web')->user();
        if ($legacyUser && $legacyUser->hasRole('admin')) {
            Auth::guard('web')->logout();
            Auth::guard('admin')->login($legacyUser);
            $request->session()->migrate(true);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $this->moveLegacyAdminToAdminGuard($request);
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->is_active) {
                return redirect()->route($user->hasRole('officer') ? 'officer.dashboard' : 'reporter.dashboard');
            }
            Auth::guard('web')->logout();
        }

        $type = (string) $request->input('identity_type');
        $phone = IdentityInput::normalizePhone($request->input('phone'));
        $request->merge(['phone' => $phone]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'identity_type' => ['required', Rule::in(IdentityInput::TYPES)],
            'identity_number' => [
                'required', 'string', 'max:20', 'regex:'.IdentityInput::identityPattern($type),
                'unique:users,identity_number',
            ],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:16', 'regex:/^08[1-9][0-9]{7,10}$/'],
            'class_or_position' => ['nullable', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'identity_number.regex' => IdentityInput::identityHint($type),
            'identity_number.unique' => 'Nomor identitas sudah terdaftar.',
            'phone.regex' => 'Nomor HP harus berupa nomor seluler Indonesia yang valid, misalnya 081234567890 atau +6281234567890.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi belum cocok.',
            'terms.accepted' => 'Kamu perlu menyetujui ketentuan penggunaan.',
        ]);

        $user = DB::transaction(function () use ($validated) {
            $reporterRole = Role::firstOrCreate(
                ['slug' => 'reporter'],
                ['name' => 'Pelapor', 'description' => 'Pengguna yang membuat dan memantau laporan fasilitas.']
            );

            $user = User::create([
                'name' => trim($validated['name']),
                'identity_type' => $validated['identity_type'],
                'identity_number' => trim($validated['identity_number']),
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'class_or_position' => $validated['class_or_position'] ?? null,
                'password' => $validated['password'],
                'is_active' => true,
            ]);

            $user->roles()->attach($reporterRole->id);

            return $user;
        });

        Auth::guard('web')->login($user);
        $request->session()->migrate(true);

        return redirect()->route('reporter.dashboard')
            ->with('success', 'Pendaftaran berhasil. Akun pelapor sudah aktif dan kamu otomatis masuk ke MANUSACARE.');
    }
}

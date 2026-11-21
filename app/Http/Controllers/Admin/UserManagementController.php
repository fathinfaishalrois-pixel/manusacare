<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\IdentityInput;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    private function ensureAssignableRoles(): void
    {
        Role::firstOrCreate(['slug' => 'reporter'], ['name' => 'Pelapor', 'description' => 'Pengguna yang membuat laporan.']);
        Role::firstOrCreate(['slug' => 'officer'], ['name' => 'Petugas', 'description' => 'Petugas penanganan laporan MANUSACARE.']);
    }

    private function assignableRoles()
    {
        $this->ensureAssignableRoles();
        return Role::whereIn('slug', ['reporter', 'officer'])->orderBy('name')->get();
    }

    private function normalizeRequest(Request $request): string
    {
        $type = (string) $request->input('identity_type');
        $request->merge([
            'name' => trim((string) preg_replace('/\s+/', ' ', (string) $request->input('name', ''))),
            'identity_number' => trim((string) $request->input('identity_number', '')),
            'phone' => IdentityInput::normalizePhone($request->input('phone')),
        ]);
        return $type;
    }

    private function identityRules(string $type, ?int $ignoreUserId = null): array
    {
        $unique = Rule::unique('users', 'identity_number');
        if ($ignoreUserId !== null) {
            $unique->ignore($ignoreUserId);
        }

        return [
            'required', 'string', 'max:20', 'regex:'.IdentityInput::identityPattern($type), $unique,
        ];
    }

    private function phoneRules(): array
    {
        return ['nullable', 'string', 'max:16', 'regex:/^08[1-9][0-9]{7,10}$/'];
    }

    public function index(Request $request)
    {
        $users = User::with('roles')
            ->withCount([
                'assignments as active_assignments_count' => fn ($query) => $query
                    ->where('is_active', true)
                    ->whereHas('report', fn ($reports) => $reports->whereNotIn('status', ['selesai', 'ditolak'])),
            ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $term = trim((string) $request->search);
                $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                    ->orWhere('identity_number', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(), 'roles' => $this->assignableRoles()]);
    }

    public function store(Request $request)
    {
        $type = $this->normalizeRequest($request);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'identity_type' => ['required', Rule::in(IdentityInput::TYPES)],
            'identity_number' => $this->identityRules($type),
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'phone' => $this->phoneRules(),
            'class_or_position' => ['nullable', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['officer', 'reporter'])],
        ], [
            'identity_number.regex' => IdentityInput::identityHint($type),
            'phone.regex' => 'Nomor HP harus berupa nomor seluler Indonesia yang valid, misalnya 081234567890.',
        ]);

        DB::transaction(function () use ($data) {
            $role = Role::where('slug', $data['role'])->firstOrFail();
            unset($data['role']);
            $user = User::create($data + ['is_active' => true]);
            $user->roles()->sync([$role->id]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function edit(User $user)
    {
        $roles = $this->assignableRoles();
        if ($user->hasRole('admin')) {
            $adminRole = Role::where('slug', 'admin')->first();
            if ($adminRole) $roles->push($adminRole);
        }

        return view('admin.users.form', ['user' => $user, 'roles' => $roles]);
    }

    public function update(Request $request, User $user)
    {
        $type = $this->normalizeRequest($request);
        $allowedRoles = $user->hasRole('admin') ? ['admin', 'reporter', 'officer'] : ['reporter', 'officer'];
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'identity_type' => ['required', Rule::in(IdentityInput::TYPES)],
            'identity_number' => $this->identityRules($type, (int) $user->id),
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => $this->phoneRules(),
            'class_or_position' => ['nullable', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', Rule::in($allowedRoles)],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'identity_number.regex' => IdentityInput::identityHint($type),
            'phone.regex' => 'Nomor HP harus berupa nomor seluler Indonesia yang valid, misalnya 081234567890.',
        ]);

        if ((int) $user->id === (int) $request->user()->id
            && (($data['role'] ?? '') !== 'admin' || ! (bool) ($data['is_active'] ?? true))) {
            return back()->withErrors(['user' => 'Admin yang sedang digunakan tidak boleh menurunkan role atau menonaktifkan akunnya sendiri.'])->withInput();
        }

        if ($user->hasRole('admin') && ($data['role'] !== 'admin' || ! (bool) ($data['is_active'] ?? true))
            && User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('slug', 'admin'))->count() <= 1) {
            return back()->withErrors(['user' => 'Admin aktif terakhir tidak boleh diubah menjadi nonaktif atau role lain.'])->withInput();
        }

        if ($user->hasRole('officer') && (($data['role'] ?? 'officer') !== 'officer' || ! (bool) ($data['is_active'] ?? true))) {
            $hasOpenAssignments = $user->assignments()->where('is_active', true)
                ->whereHas('report', fn ($query) => $query->whereNotIn('status', ['selesai', 'ditolak']))->exists();
            if ($hasOpenAssignments) {
                return back()->withErrors(['user' => 'Petugas masih memiliki laporan aktif. Pindahkan atau selesaikan penugasannya terlebih dahulu.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $user) {
            $role = Role::where('slug', $data['role'])->firstOrFail();
            unset($data['role']);
            if (blank($data['password'] ?? null)) unset($data['password']);
            $user->update($data);
            $user->roles()->sync([$role->id]);
        });

        return redirect()->route('admin.users.index')->with('success', 'Data akun diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if((int) $user->id === (int) $request->user()->id, 422, 'Akun yang sedang digunakan tidak dapat dihapus.');
        $request->validate(['confirmation' => ['required', 'in:HAPUS']]);

        if ($user->hasRole('admin') && User::where('is_active', true)->whereHas('roles', fn ($q) => $q->where('slug', 'admin'))->count() <= 1) {
            return back()->withErrors(['user' => 'Admin aktif terakhir tidak boleh dihapus atau dinonaktifkan.']);
        }

        $hasHistory = $user->reports()->exists() || $user->assignments()->exists()
            || DB::table('report_updates')->where('user_id', $user->id)->exists()
            || DB::table('reviews')->where('user_id', $user->id)->exists();

        if ($hasHistory) {
            if ($user->hasRole('officer') && $user->assignments()->where('is_active', true)->exists()) {
                return back()->withErrors(['user' => 'Petugas masih memiliki penugasan aktif. Pindahkan penugasannya terlebih dahulu.']);
            }
            $user->update(['is_active' => false]);
            return back()->with('success', 'Akun memiliki riwayat transaksi; akun dinonaktifkan agar riwayat tetap utuh.');
        }

        $user->delete();
        return back()->with('success', 'Akun dihapus.');
    }
}

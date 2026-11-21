@extends('layouts.app')
@section('title', $user->exists ? 'Edit Pengguna' : 'Tambah Pengguna')
@php($currentRole = old('role', $user->exists ? $user->roles()->first()?->slug : 'reporter'))
@section('content')
<div class="form-page-head">
    <span class="eyebrow"><span class="eyebrow-index">03</span>Admin / User management</span>
    <h1>{{ $user->exists ? 'Perbarui' : 'Buat' }} <em>akun pengguna.</em></h1>
    <p>{{ $user->exists ? 'Ubah data akun, peran, atau status akses pengguna.' : 'Lengkapi data akun baru dan tentukan perannya dalam sistem.' }}</p>
</div>

<form class="report-form" method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($user->exists)
        @method('PUT')
    @endif

    <div class="form-section">
        <div class="form-section-head">
            <span class="form-section-index">01</span>
            <div>
                <h2>Identitas pengguna</h2>
                <p>Data ini digunakan untuk masuk dan dikenali dalam sistem.</p>
            </div>
        </div>
        <div class="form-grid">
            <div class="field @error('name') has-error @enderror">
                <label for="name">Nama lengkap <span>*</span></label>
                <input id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="150" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
                @error('name')<small class="field-error" id="name-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('identity_type') has-error @enderror">
                <label for="identity_type">Jenis identitas <span>*</span></label>
                <select id="identity_type" name="identity_type" required @error('identity_type') aria-invalid="true" aria-describedby="identity-type-error" @enderror>
                    <option value="">Pilih jenis identitas</option>
                    <option value="nis" @selected(old('identity_type', $user->identity_type) === 'nis')>NIS</option>
                    <option value="nip" @selected(old('identity_type', $user->identity_type) === 'nip')>NIP</option>
                    <option value="nik" @selected(old('identity_type', $user->identity_type) === 'nik')>NIK</option>
                    <option value="lainnya" @selected(old('identity_type', $user->identity_type ?: 'lainnya') === 'lainnya')>ID lainnya</option>
                </select>
                @error('identity_type')<small class="field-error" id="identity-type-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('identity_number') has-error @enderror">
                <label for="identity_number">Nomor identitas <span>*</span></label>
                <input id="identity_number" name="identity_number" value="{{ old('identity_number', $user->identity_number) }}" maxlength="20" inputmode="numeric" required autocomplete="username" @error('identity_number') aria-invalid="true" aria-describedby="identity-number-error" @enderror>
                @error('identity_number')<small class="field-error" id="identity-number-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('email') has-error @enderror">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" maxlength="150" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                @error('email')<small class="field-error" id="email-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('phone') has-error @enderror">
                <label for="phone">Nomor telepon</label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" maxlength="18" inputmode="tel" autocomplete="tel" placeholder="08xxxxxxxxxx" pattern="(\+62|62|0|8)[0-9]{8,13}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                @error('phone')<small class="field-error" id="phone-error">{{ $message }}</small>@enderror
            </div>
            <div class="field span-2 @error('class_or_position') has-error @enderror">
                <label for="class_or_position">Kelas / Jabatan</label>
                <input id="class_or_position" name="class_or_position" value="{{ old('class_or_position', $user->class_or_position) }}" maxlength="120" list="class-position-options" placeholder="Contoh: XI IPA 1 / Guru" @error('class_or_position') aria-invalid="true" aria-describedby="class-position-error" @enderror>
                <datalist id="class-position-options"><option value="VII"><option value="VIII"><option value="IX"><option value="X"><option value="XI"><option value="XII"><option value="Guru"><option value="Tenaga Kependidikan"><option value="Petugas Sarpras"><option value="Wali Kelas"></datalist>
                @error('class_or_position')<small class="field-error" id="class-position-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-head">
            <span class="form-section-index">02</span>
            <div>
                <h2>Peran dan akses</h2>
                <p>Tentukan area yang dapat diakses oleh akun ini.</p>
            </div>
        </div>
        <div class="form-grid">
            <div class="field @error('role') has-error @enderror">
                <label for="role">Role <span>*</span></label>
                <select id="role" name="role" required @disabled($roles->isEmpty()) @error('role') aria-invalid="true" aria-describedby="role-error" @enderror>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}" @selected($currentRole === $role->slug)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @if($roles->isEmpty())
                    <small class="field-error" role="status">Belum ada role di database. Jalankan seeder resmi sebelum mengelola akun.</small>
                @endif
                @error('role')<small class="field-error" id="role-error">{{ $message }}</small>@enderror
            </div>

            @if($user->exists)
                <div class="field @error('is_active') has-error @enderror">
                    <label for="is_active">Status akun</label>
                    <select id="is_active" name="is_active" @error('is_active') aria-invalid="true" aria-describedby="is-active-error" @enderror>
                        <option value="1" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '1')>Aktif</option>
                        <option value="0" @selected((string) old('is_active', $user->is_active ? '1' : '0') === '0')>Nonaktif</option>
                    </select>
                    <small>Akun nonaktif tidak dapat masuk ke sistem.</small>
                    @error('is_active')<small class="field-error" id="is-active-error">{{ $message }}</small>@enderror
                </div>
            @endif
        </div>
    </div>

    <div class="form-section">
        <div class="form-section-head">
            <span class="form-section-index">03</span>
            <div>
                <h2>Keamanan akun</h2>
                <p>{{ $user->exists ? 'Biarkan kosong jika kata sandi tidak ingin diubah.' : 'Gunakan kata sandi minimal 8 karakter.' }}</p>
            </div>
        </div>
        <div class="form-grid">
            <div class="field span-2 @error('password') has-error @enderror">
                <label for="password">Kata sandi {{ $user->exists ? '(opsional)' : '*' }}</label>
                <div class="password-field">
                    <input id="password" type="password" name="password" minlength="8" autocomplete="new-password" {{ $user->exists ? '' : 'required' }} @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                    <button class="password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi">
                        <x-icon name="eye" />
                    </button>
                </div>
                <small>Minimal 8 karakter.</small>
                @error('password')<small class="field-error" id="password-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="button button-ghost" href="{{ route('admin.users.index') }}">Batal</a>
        <button class="button button-primary button-large" type="submit" @disabled($roles->isEmpty())>{{ $user->exists ? 'Simpan perubahan' : 'Buat akun' }} <x-icon name="check" /></button>
    </div>
</form>
@endsection

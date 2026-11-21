@extends('layouts.app')
@section('title', 'Daftar Akun')
@section('content')
<section class="auth-wrap auth-wrap-register">
    <div class="auth-intro" data-reveal>
        <span class="eyebrow"><span class="eyebrow-index">01</span>Create your account</span>
        <h1>Mulai berkontribusi untuk <em>sekolah lebih baik.</em></h1>
        <p>Buat akun pelapor MANUSACARE. Setelah pendaftaran berhasil, kamu akan langsung masuk dan dapat mengirim laporan fasilitas sekolah.</p>
        <div class="register-points">
            <div><span>01</span><p><b>Daftar sekali</b><small>Pilih jenis identitas agar panjang nomor otomatis tervalidasi.</small></p></div>
            <div><span>02</span><p><b>Laporkan dengan mudah</b><small>Sampaikan masalah fasilitas beserta detail lokasinya.</small></p></div>
            <div><span>03</span><p><b>Pantau perkembangannya</b><small>Lihat status laporan dari dashboard akunmu.</small></p></div>
        </div>
    </div>

    <form class="glass-form register-form auth-card" method="POST" action="{{ route('register.store') }}" data-reveal>
        @csrf
        <div class="form-heading">
            <span class="eyebrow">Registrasi pelapor</span>
            <h2>Buat akun baru</h2>
            <p>Isi data di bawah ini dengan benar.</p>
        </div>

        <div class="field @error('name') has-error @enderror">
            <label for="name">Nama lengkap <span>*</span></label>
            <input id="name" name="name" value="{{ old('name') }}" autocomplete="name" maxlength="120" required @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
            @error('name')<small class="field-error" id="name-error">{{ $message }}</small>@enderror
        </div>

        <div class="register-grid">
            <div class="field @error('identity_type') has-error @enderror">
                <label for="identity_type">Jenis identitas <span>*</span></label>
                <select id="identity_type" name="identity_type" required @error('identity_type') aria-invalid="true" aria-describedby="identity-type-error" @enderror>
                    <option value="">Pilih jenis identitas</option>
                    <option value="nis" @selected(old('identity_type') === 'nis')>NIS</option>
                    <option value="nip" @selected(old('identity_type') === 'nip')>NIP</option>
                    <option value="nik" @selected(old('identity_type') === 'nik')>NIK</option>
                    <option value="lainnya" @selected(old('identity_type') === 'lainnya')>ID lainnya</option>
                </select>
                @error('identity_type')<small class="field-error" id="identity-type-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('identity_number') has-error @enderror">
                <label for="identity_number">Nomor identitas <span>*</span></label>
                <input id="identity_number" name="identity_number" value="{{ old('identity_number') }}" autocomplete="username" maxlength="20" inputmode="numeric" required placeholder="Masukkan nomor identitas" @error('identity_number') aria-invalid="true" aria-describedby="identity-number-error identity-number-help" @enderror>
                <small id="identity-number-help">NIS 4–12 angka · NIP 18 angka · NIK 16 angka.</small>
                @error('identity_number')<small class="field-error" id="identity-number-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="field @error('email') has-error @enderror">
            <label for="email">Email <small>(opsional)</small></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" maxlength="150" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')<small class="field-error" id="email-error">{{ $message }}</small>@enderror
        </div>

        <div class="register-grid">
            <div class="field @error('phone') has-error @enderror">
                <label for="phone">Nomor HP <small>(opsional)</small></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" maxlength="18" placeholder="08xxxxxxxxxx" pattern="(\+62|62|0|8)[0-9]{8,13}" @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror>
                @error('phone')<small class="field-error" id="phone-error">{{ $message }}</small>@enderror
            </div>
            <div class="field @error('class_or_position') has-error @enderror">
                <label for="class_or_position">Kelas / Jabatan <small>(opsional)</small></label>
                <input id="class_or_position" name="class_or_position" value="{{ old('class_or_position') }}" maxlength="120" list="class-position-options" placeholder="Contoh: XI IPA 1 / Guru" @error('class_or_position') aria-invalid="true" aria-describedby="class-position-error" @enderror>
                <datalist id="class-position-options"><option value="VII"><option value="VIII"><option value="IX"><option value="X"><option value="XI"><option value="XII"><option value="Guru"><option value="Tenaga Kependidikan"><option value="Petugas Sarpras"><option value="Wali Kelas"></datalist>
                @error('class_or_position')<small class="field-error" id="class-position-error">{{ $message }}</small>@enderror
            </div>
        </div>

        <div class="field @error('password') has-error @enderror">
            <label for="password">Kata sandi <span>*</span></label>
            <div class="password-field">
                <input id="password" type="password" name="password" autocomplete="new-password" minlength="8" required @error('password') aria-invalid="true" aria-describedby="password-error password-help" @enderror>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi">
                    <x-icon name="eye" />
                </button>
            </div>
            <small id="password-help">Minimal 8 karakter.</small>
            @error('password')<small class="field-error" id="password-error">{{ $message }}</small>@enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Ulangi kata sandi <span>*</span></label>
            <div class="password-field">
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" minlength="8" required>
                <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-pressed="false" aria-label="Tampilkan ulangi kata sandi">
                    <x-icon name="eye" />
                </button>
            </div>
        </div>

        <label class="terms-check @error('terms') has-error @enderror">
            <input type="checkbox" name="terms" value="1" @checked(old('terms')) required @error('terms') aria-invalid="true" aria-describedby="terms-error" @enderror>
            <span>Saya menyatakan data yang diberikan benar dan akan menggunakan MANUSACARE secara bertanggung jawab.</span>
        </label>
        @error('terms')<small class="field-error" id="terms-error">{{ $message }}</small>@enderror

        <button class="button button-primary button-large w-full" type="submit">Daftar &amp; Masuk <x-icon name="arrow-right" /></button>
        <p class="form-foot">Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
        <p class="form-foot admin-note">Pendaftaran umum hanya membuat akun pelapor. Akun petugas dan admin dikelola oleh administrator.</p>
    </form>
</section>
@endsection

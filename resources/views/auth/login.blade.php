@extends('layouts.app')
@section('title', $admin ? 'Admin Login' : 'Login')
@section('content')
<section class="auth-wrap">
    <div class="auth-intro" data-reveal>
        <span class="eyebrow"><span class="eyebrow-index">01</span>{{ $admin ? 'Restricted access' : 'Welcome back' }}</span>
        <h1>{{ $admin ? 'Admin control' : 'Satu langkah untuk' }} <em>{{ $admin ? 'center.' : 'sekolah lebih baik.' }}</em></h1>
        <p>{{ $admin ? 'Halaman khusus pengelola sistem MANUSACARE. Gunakan akun administrator yang terdaftar.' : 'Masuk menggunakan NIS/NIP atau email yang terhubung dengan akunmu.' }}</p>
        <div class="register-points">
            <div>
                <span>01</span>
                <p><b>Akses terverifikasi</b><small>Setiap peran hanya membuka area yang menjadi tanggung jawabnya.</small></p>
            </div>
            <div>
                <span>02</span>
                <p><b>Riwayat tersimpan</b><small>Status dan catatan penanganan tetap dapat dipantau setelah masuk.</small></p>
            </div>
        </div>
    </div>

    <form class="glass-form auth-card" method="POST" action="{{ $admin ? route('admin.login.store') : route('login.store') }}" data-reveal>
        @csrf
        <div class="form-heading">
            <span class="eyebrow">{{ $admin ? 'Administrator' : 'Akun pelapor' }}</span>
            <h2>{{ $admin ? 'Administrator login' : 'Masuk ke akun' }}</h2>
            <p>{{ $admin ? 'Akses terbatas untuk pengelola sistem.' : 'Gunakan data akun yang sudah terdaftar.' }}</p>
        </div>

        <div class="field @error('login') has-error @enderror">
            <label for="login">{{ $admin ? 'Email atau ID Admin' : 'NIS / NIP / NIK / Email' }} <span>*</span></label>
            <input id="login" name="login" value="{{ old('login') }}" required autofocus autocomplete="username" maxlength="150" @if($errors->has('login')) aria-describedby="login-error" aria-invalid="true" @endif>
            @error('login')<small class="field-error" id="login-error">{{ $message }}</small>@enderror
        </div>

        <div class="field @error('password') has-error @enderror">
            <label for="password">Kata sandi <span>*</span></label>
            <div class="password-field">
                <input id="password" type="password" name="password" required autocomplete="current-password" @if($errors->has('password')) aria-describedby="password-error" aria-invalid="true" @endif>
                <button class="password-toggle" type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false" aria-label="Tampilkan kata sandi">
                    <x-icon name="eye" />
                </button>
            </div>
            @error('password')<small class="field-error" id="password-error">{{ $message }}</small>@enderror
        </div>

        <label class="remember"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> Ingat saya</label>

        <button class="button button-primary button-large w-full" type="submit">Masuk <x-icon name="arrow-right" /></button>

        @unless($admin)
            <p class="form-foot">Belum punya akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
            <p class="form-foot admin-note">Akun petugas dan admin dibuat oleh administrator sekolah.</p>
        @else
            <p class="form-foot admin-note">Akses khusus administrator sekolah. Hubungi pengelola sistem jika akun tidak dapat digunakan.</p>
        @endunless
    </form>
</section>
@endsection

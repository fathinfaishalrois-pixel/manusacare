@extends('layouts.app')
@section('title', 'Kontak')
@section('content')
<section class="simple-page">
    <span class="eyebrow" data-reveal><span class="eyebrow-index">01</span>Contact</span>
    <h1 data-reveal>Terhubung dengan <em>pengelola sekolah.</em></h1>
    <p data-reveal>Untuk bantuan akun atau pertanyaan mengenai fasilitas, hubungi admin sekolah melalui kanal komunikasi resmi yang ditetapkan sekolah.</p>

    <div class="manifesto" data-reveal-group>
        <div data-reveal>
            <b>01 / AKUN</b>
            <h2>Akses dan pendaftaran</h2>
            <p>Akun pelapor dapat dibuat melalui halaman daftar. Akun petugas dan admin dikelola oleh administrator sekolah.</p>
        </div>
        <div data-reveal>
            <b>02 / FASILITAS</b>
            <h2>Pertanyaan teknis</h2>
            <p>Sampaikan detail kerusakan melalui laporan agar tercatat lengkap, termasuk lokasi dan dokumentasi pendukung.</p>
        </div>
        <div data-reveal>
            <b>03 / TINDAK LANJUT</b>
            <h2>Status laporan</h2>
            <p>Pantau perkembangan laporan dari dashboard akun. Catatan penanganan akan tampil pada setiap perubahan status.</p>
        </div>
    </div>

    <blockquote class="page-quote" data-reveal>“Kanal yang jelas membuat setiap laporan sampai ke orang yang tepat.”</blockquote>

    <div class="access-panel" data-reveal>
        <div>
            <h2>Mulai dari akunmu.</h2>
            <p>Masuk untuk memantau laporan, atau buat akun baru jika belum memiliki akses.</p>
        </div>
        <div class="access-panel-actions">
            @auth
                <a class="button button-primary" href="{{ route('reporter.dashboard') }}">Buka dashboard <x-icon name="arrow-right" /></a>
            @else
                <a class="button button-primary" href="{{ route('login') }}">Masuk <x-icon name="arrow-right" /></a>
                <a class="button button-ghost" href="{{ route('register') }}">Daftar akun</a>
            @endauth
        </div>
    </div>
</section>
@endsection

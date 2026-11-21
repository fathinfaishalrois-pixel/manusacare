@extends('layouts.app')
@section('title', 'Beranda')
@section('content')
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow"><span class="eyebrow-index">01</span><span class="pulse-dot" aria-hidden="true"></span>School facility care system</span>
        <h1>Ruang belajar yang lebih baik, <em>dimulai dari kepedulian.</em></h1>
        <p class="hero-lede">Laporkan kerusakan fasilitas sekolah, ikuti proses penanganannya, dan ikut menjaga lingkungan belajar tetap aman serta nyaman.</p>
        <div class="hero-actions">
            @auth
                <a class="button button-primary button-large" href="{{ route('reports.create') }}">Buat laporan <x-icon name="arrow-up-right" /></a>
                <a class="button button-ghost button-large" href="{{ route('reporter.dashboard') }}">Buka dashboard</a>
            @else
                <a class="button button-primary button-large" href="{{ route('register') }}">Daftar &amp; mulai melapor <x-icon name="arrow-up-right" /></a>
                <a class="button button-ghost button-large" href="{{ route('login') }}">Sudah punya akun? Masuk</a>
            @endauth
        </div>
        <div class="hero-proof">
            <span><b>01</b>Laporkan</span>
            <span><b>02</b>Tangani</span>
            <span><b>03</b>Selesai</span>
        </div>
    </div>

    <div class="hero-stage" aria-hidden="true">
        <div class="stage-frame">
            <div class="stage-top">
                <span>MANUSACARE / LIVE SYSTEM</span>
                <span class="stage-status"><span class="stage-live-dot"></span>System ready</span>
            </div>
            <p class="stage-title">Lampu koridor sayap timur padam sejak pagi.</p>
            <div class="stage-loc"><x-icon name="pin" />Gedung A · Lantai 2 · Koridor</div>
            <div class="stage-track">
                <span class="stage-step is-done">Dikirim</span>
                <span class="stage-step is-done">Diverifikasi</span>
                <span class="stage-step is-active">Ditangani</span>
                <span class="stage-step">Selesai</span>
            </div>
            <div class="stage-info">
                <div class="stage-media">
                    <x-icon name="image" />
                    <span>2 foto · 1 video</span>
                </div>
                <div class="stage-note">
                    <b>CATATAN PETUGAS</b>
                    Penggantian ballast dan lampu dijadwalkan hari ini.
                </div>
            </div>
            <div class="stage-caption">
                <span>Laporan #MC-2026-0148</span>
                <span>Diperbarui 08:42</span>
            </div>
        </div>
        <div class="stage-legend">
            <span><i></i>Transparan</span>
            <span><i></i>Terukur</span>
            <span><i></i>Dapat dipantau</span>
        </div>
    </div>
</section>

<section class="section-block" id="tentang" data-reveal>
    <span class="section-kicker"><span class="eyebrow-index">02</span>About</span>
    <div class="section-heading">
        <h2>Perbaikan dimulai dari <em>satu laporan.</em></h2>
        <p>MANUSACARE menghubungkan warga sekolah dengan proses pemeliharaan fasilitas yang tertata dan dapat dipantau dari awal hingga tuntas.</p>
    </div>
</section>

<section class="feature-grid" aria-label="Kemampuan utama MANUSACARE" data-reveal-group>
    <article class="feature-card" data-reveal>
        <span class="feature-index">01 / PELAPORAN</span>
        <span class="feature-icon"><x-icon name="report" /></span>
        <h3>Pelaporan terarah</h3>
        <p>Lengkapi kategori, lokasi, urgensi, dan dokumentasi agar masalah mudah dipahami oleh pihak yang menangani.</p>
        <span class="feature-rule" aria-hidden="true"></span>
    </article>
    <article class="feature-card" data-reveal>
        <span class="feature-index">02 / PEMANTAUAN</span>
        <span class="feature-icon"><x-icon name="clock" /></span>
        <h3>Progres transparan</h3>
        <p>Pantau perubahan status dan catatan penanganan melalui akun masing-masing tanpa perlu menebak.</p>
        <span class="feature-rule" aria-hidden="true"></span>
    </article>
    <article class="feature-card" data-reveal>
        <span class="feature-index">03 / EVALUASI</span>
        <span class="feature-icon"><x-icon name="layers" /></span>
        <h3>Evaluasi berkelanjutan</h3>
        <p>Riwayat laporan dan penilaian membantu sekolah menentukan prioritas perbaikan berikutnya.</p>
        <span class="feature-rule" aria-hidden="true"></span>
    </article>
</section>

<section class="section-block process-section" id="alur" data-reveal-group>
    <span class="section-kicker"><span class="eyebrow-index">03</span>How it works</span>
    <div class="section-heading">
        <h2>Alur yang jelas, dari awal <em>hingga tuntas.</em></h2>
        <p>Setiap laporan melewati tahap yang sama, sehingga pelapor, admin, dan petugas memiliki pemahaman yang selaras.</p>
    </div>
    <div class="process-grid">
        <div class="process-step" data-reveal>
            <b>01</b>
            <h3>Kirim laporan</h3>
            <p>Pelapor mengisi detail kerusakan dan mengunggah dokumentasi pendukung.</p>
        </div>
        <div class="process-step" data-reveal>
            <b>02</b>
            <h3>Verifikasi admin</h3>
            <p>Admin memeriksa laporan dan mencatat identitas petugas yang menangani.</p>
        </div>
        <div class="process-step" data-reveal>
            <b>03</b>
            <h3>Penanganan</h3>
            <p>Petugas memperbarui progres dan melampirkan bukti perbaikan.</p>
        </div>
        <div class="process-step" data-reveal>
            <b>04</b>
            <h3>Evaluasi</h3>
            <p>Pelapor melihat hasil dan memberikan penilaian setelah pekerjaan selesai.</p>
        </div>
    </div>
</section>

<section class="closing-cta" data-reveal>
    <div>
        <span class="eyebrow">Your school. Our shared responsibility.</span>
        <h2>Melihat masalah adalah awal. <em>Melaporkannya adalah langkah.</em></h2>
    </div>
    @auth
        <a class="button button-primary button-large" href="{{ route('reports.create') }}">Buat laporan baru <x-icon name="arrow-up-right" /></a>
    @else
        <a class="button button-primary button-large" href="{{ route('register') }}">Buat akun pelapor <x-icon name="arrow-up-right" /></a>
    @endauth
</section>
@endsection

@extends('layouts.app')
@section('title', 'FAQ')
@section('content')
<section class="simple-page">
    <span class="eyebrow" data-reveal><span class="eyebrow-index">01</span>Help center</span>
    <h1 data-reveal>Pertanyaan <em>umum.</em></h1>
    <p data-reveal>Jawaban singkat mengenai akun, pelaporan, dan cara memantau penanganan fasilitas sekolah.</p>

    <div class="faq-list" data-reveal-group>
        <details class="faq-item" data-reveal>
            <summary>Siapa yang dapat membuat laporan? <x-icon name="plus" /></summary>
            <div class="faq-answer"><p>Siswa, guru, dan tenaga kependidikan yang memiliki akun aktif dapat membuat laporan. Akun pelapor dibuat melalui pendaftaran atau oleh administrator sekolah.</p></div>
        </details>
        <details class="faq-item" data-reveal>
            <summary>Bagaimana cara memantau laporan? <x-icon name="plus" /></summary>
            <div class="faq-answer"><p>Masuk ke akun dan buka dashboard. Di sana tersedia riwayat laporan beserta status terbaru dan catatan penanganan dari petugas.</p></div>
        </details>
        <details class="faq-item" data-reveal>
            <summary>Apakah akun dapat dibuat sendiri? <x-icon name="plus" /></summary>
            <div class="faq-answer"><p>Akun pelapor dapat dibuat melalui halaman pendaftaran. Akun petugas dan admin hanya dapat dibuat atau diaktifkan oleh administrator sekolah.</p></div>
        </details>
        <details class="faq-item" data-reveal>
            <summary>Dokumen apa yang sebaiknya dilampirkan? <x-icon name="plus" /></summary>
            <div class="faq-answer"><p>Lampirkan foto atau video yang memperlihatkan kondisi kerusakan dengan jelas. Sertakan juga lokasi yang spesifik agar petugas dapat menemukan titik masalah tanpa kebingungan.</p></div>
        </details>
        <details class="faq-item" data-reveal>
            <summary>Mengapa laporan saya ditolak? <x-icon name="plus" /></summary>
            <div class="faq-answer"><p>Laporan dapat ditolak jika informasi tidak lengkap, duplikat, atau bukan merupakan kerusakan fasilitas sekolah. Alasan penolakan akan dicantumkan pada detail laporan.</p></div>
        </details>
    </div>

    <div class="access-panel" data-reveal>
        <div>
            <h2>Belum menemukan jawaban?</h2>
            <p>Hubungi pengelola sekolah melalui kanal komunikasi resmi, atau masuk untuk melihat status laporanmu.</p>
        </div>
        <div class="access-panel-actions">
            <a class="button button-ghost" href="{{ route('contact') }}">Halaman kontak</a>
            <a class="button button-primary" href="{{ auth()->check() ? route('reporter.dashboard') : route('login') }}">Masuk ke akun <x-icon name="arrow-right" /></a>
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')
@section('title', 'Tentang')
@section('content')
<section class="simple-page">
    <span class="eyebrow" data-reveal><span class="eyebrow-index">01</span>About MANUSACARE</span>
    <h1 data-reveal>Building a Better School <em>Together.</em></h1>
    <p data-reveal>MANUSACARE adalah platform pelaporan dan pengelolaan kerusakan fasilitas sekolah yang membantu siswa, guru, tenaga kependidikan, petugas, dan admin bekerja dalam satu alur yang terstruktur.</p>

    <div class="manifesto" data-reveal-group>
        <div data-reveal>
            <b>01 / TERARAH</b>
            <h2>Satu saluran resmi</h2>
            <p>Laporan fasilitas tidak lagi tersebar di percakapan sehari-hari. Semua masuk ke satu tempat yang dapat ditelusuri.</p>
        </div>
        <div data-reveal>
            <b>02 / TERBUKA</b>
            <h2>Progres yang terlihat</h2>
            <p>Pelapor dapat melihat status, catatan penanganan, dan dokumentasi sebelum maupun sesudah perbaikan.</p>
        </div>
        <div data-reveal>
            <b>03 / BERKELANJUTAN</b>
            <h2>Dasar pengambilan keputusan</h2>
            <p>Riwayat kerusakan membantu sekolah memprioritaskan perawatan pada area yang paling sering bermasalah.</p>
        </div>
    </div>

    <blockquote class="page-quote" data-reveal>“Fasilitas yang terawat bukan sekadar bangunan. Ia adalah bagian dari cara sekolah menghargai proses belajar.”</blockquote>

    <div class="access-panel" data-reveal>
        <div>
            <h2>Ikut menjaga fasilitas sekolah.</h2>
            <p>Masuk ke akunmu untuk mengirim laporan baru atau memantau laporan yang sudah ada.</p>
        </div>
        <div class="access-panel-actions">
            @auth
                <a class="button button-primary" href="{{ route('reports.create') }}">Buat laporan <x-icon name="arrow-up-right" /></a>
                <a class="button button-ghost" href="{{ route('reporter.dashboard') }}">Dashboard</a>
            @else
                <a class="button button-primary" href="{{ route('register') }}">Daftar akun <x-icon name="arrow-up-right" /></a>
                <a class="button button-ghost" href="{{ route('login') }}">Masuk</a>
            @endauth
        </div>
    </div>
</section>
@endsection

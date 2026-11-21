@extends('layouts.app')
@section('title', 'Dashboard Pelapor')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">01</span>Personal dashboard</span>
        <h1>Halo, <em>{{ auth()->user()->name }}.</em></h1>
        <p>Pantau laporan dan perkembangan penanganan fasilitas sekolah.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-primary" href="{{ route('reports.create') }}"><x-icon name="plus" /> Buat laporan</a>
    </div>
</section>

<div class="stats-grid" data-reveal-group>
    <div class="stat-card" data-reveal>
        <div class="stat-top"><span class="stat-label">Total laporan</span><span class="stat-index">01</span></div>
        <strong>{{ $stats['total'] }}</strong>
        <span class="stat-foot">Laporanmu</span>
    </div>
    <div class="stat-card is-warm" data-reveal>
        <div class="stat-top"><span class="stat-label">Dikirim</span><span class="stat-index">02</span></div>
        <strong>{{ $stats['submitted'] }}</strong>
        <span class="stat-foot">Menunggu verifikasi</span>
    </div>
    <div class="stat-card is-cool" data-reveal>
        <div class="stat-top"><span class="stat-label">Diproses</span><span class="stat-index">03</span></div>
        <strong>{{ $stats['progress'] }}</strong>
        <span class="stat-foot">Dalam penanganan</span>
    </div>
    <div class="stat-card is-soft" data-reveal>
        <div class="stat-top"><span class="stat-label">Selesai</span><span class="stat-index">04</span></div>
        <strong>{{ $stats['completed'] }}</strong>
        <span class="stat-foot">Sudah dituntaskan</span>
    </div>
</div>

<section class="content-card" data-reveal>
    <div class="card-heading">
        <h2>Aktivitas laporan</h2>
        <a class="text-link" href="{{ route('reports.index') }}">Riwayat lengkap <x-icon name="arrow-right" /></a>
    </div>
    @forelse($reports as $report)
        <a class="report-row report-row-link" href="{{ route('reports.show', $report) }}">
            <span class="report-icon"><x-icon name="report" /></span>
            <span class="report-main">
                <b>{{ $report->title }}</b>
                <small>
                    {{ $report->category->name }} · {{ $report->created_at->format('d M Y') }}
                    @if($report->activeAssignment?->officer)
                        · <span class="report-petugas"><x-icon name="id-card" />{{ $report->activeAssignment->officer->name }}</span>
                    @endif
                </small>
            </span>
            <x-status-pill :status="$report->status" />
            <x-icon name="arrow-up-right" class="row-arrow" />
        </a>
    @empty
        <div class="empty-state">
            <x-icon name="report" />
            <h3>Belum ada laporan</h3>
            <p>Mulai dengan mengirim laporan kerusakan fasilitas sekolah.</p>
            <a class="button button-primary" href="{{ route('reports.create') }}">Buat laporan pertama</a>
        </div>
    @endforelse
</section>
@endsection

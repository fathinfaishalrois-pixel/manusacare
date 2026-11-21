@extends('layouts.app')
@section('title', 'Dashboard Petugas')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">01</span>Field operations</span>
        <h1>Dashboard <em>petugas.</em></h1>
        <p>Daftar laporan yang sedang ditugaskan kepadamu, terbaru di atas.</p>
    </div>
    @if($reports->total())
        <div class="dashboard-head-actions">
            <span class="status-pill status-progress"><span class="status-dot" aria-hidden="true"></span>{{ $stats['progress'] }} dalam proses</span>
        </div>
    @endif
</section>

<div class="stats-grid" data-reveal-group>
    <div class="stat-card" data-reveal>
        <div class="stat-top"><span class="stat-label">Total tugas aktif</span><span class="stat-index">01</span></div>
        <strong>{{ $stats['total'] }}</strong>
        <span class="stat-foot">Ditugaskan kepadamu</span>
    </div>
    <div class="stat-card is-cool" data-reveal>
        <div class="stat-top"><span class="stat-label">Sedang diproses</span><span class="stat-index">02</span></div>
        <strong>{{ $stats['progress'] }}</strong>
        <span class="stat-foot">Dalam penanganan</span>
    </div>
    <div class="stat-card is-soft" data-reveal>
        <div class="stat-top"><span class="stat-label">Selesai</span><span class="stat-index">03</span></div>
        <strong>{{ $stats['done'] }}</strong>
        <span class="stat-foot">Sudah dituntaskan</span>
    </div>
</div>

<section class="content-card" data-reveal>
    <div class="card-heading">
        <h2>Penugasan aktif</h2>
    </div>
    @forelse($reports as $report)
        <a class="report-row report-row-link" href="{{ route('officer.reports.show', $report) }}">
            <span class="report-icon"><x-icon name="wrench" /></span>
            <span class="report-main">
                <b>{{ $report->title }}</b>
                <small>{{ $report->building }}{{ $report->room ? ' · '.$report->room : '' }} · Pelapor: {{ $report->reporter->name }}</small>
            </span>
            <x-status-pill :status="$report->status" />
            <x-icon name="arrow-up-right" class="row-arrow" />
        </a>
    @empty
        <div class="empty-state">
            <x-icon name="wrench" />
            <h3>Belum ada tugas aktif</h3>
            <p>Laporan yang ditugaskan kepadamu akan muncul di halaman ini.</p>
        </div>
    @endforelse

    @if($reports->hasPages())
        <div class="pagination">{{ $reports->links() }}</div>
    @endif
</section>
@endsection

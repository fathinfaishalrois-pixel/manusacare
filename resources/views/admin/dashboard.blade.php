@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">01</span>Control center / Admin</span>
        <h1>Ringkasan <em>sistem.</em></h1>
        <p>Pantau laporan, pengguna, dan aktivitas pemeliharaan fasilitas sekolah dalam satu tampilan.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-primary" href="{{ route('admin.reports.index') }}">Kelola laporan <x-icon name="arrow-up-right" /></a>
    </div>
</section>

<div class="stats-grid" data-reveal-group>
    <div class="stat-card" data-reveal>
        <div class="stat-top"><span class="stat-label">Total laporan</span><span class="stat-index">01</span></div>
        <strong>{{ $stats['total'] }}</strong>
        <span class="stat-foot">Seluruh laporan</span>
    </div>
    <div class="stat-card is-warm" data-reveal>
        <div class="stat-top"><span class="stat-label">Menunggu verifikasi</span><span class="stat-index">02</span></div>
        <strong>{{ $stats['submitted'] }}</strong>
        <span class="stat-foot">Perlu keputusan</span>
    </div>
    <div class="stat-card is-cool" data-reveal>
        <div class="stat-top"><span class="stat-label">Sedang diproses</span><span class="stat-index">03</span></div>
        <strong>{{ $stats['progress'] }}</strong>
        <span class="stat-foot">Dalam penanganan</span>
    </div>
    <div class="stat-card is-soft" data-reveal>
        <div class="stat-top"><span class="stat-label">Selesai</span><span class="stat-index">04</span></div>
        <strong>{{ $stats['completed'] }}</strong>
        <span class="stat-foot">Sudah dituntaskan</span>
    </div>
</div>

<div class="admin-shortcuts" data-reveal-group>
    <a class="shortcut" href="{{ route('admin.reports.index') }}" data-reveal>
        <span class="shortcut-icon"><x-icon name="report" /></span>
        <span class="shortcut-copy"><small>Reports</small><b>Semua laporan</b></span>
        <x-icon name="arrow-up-right" />
    </a>
    <a class="shortcut" href="{{ route('admin.users.index') }}" data-reveal>
        <span class="shortcut-icon"><x-icon name="users" /></span>
        <span class="shortcut-copy"><small>Accounts</small><b>Manajemen pengguna</b></span>
        <x-icon name="arrow-up-right" />
    </a>
    <a class="shortcut" href="{{ route('admin.categories.index') }}" data-reveal>
        <span class="shortcut-icon"><x-icon name="tag" /></span>
        <span class="shortcut-copy"><small>Configuration</small><b>Kategori laporan</b></span>
        <x-icon name="arrow-up-right" />
    </a>
</div>

<section class="content-card" data-reveal>
    <div class="card-heading">
        <h2>Laporan terbaru</h2>
        <a class="text-link" href="{{ route('admin.reports.index') }}">Lihat semua <x-icon name="arrow-right" /></a>
    </div>
    @forelse($recent as $report)
        <a class="report-row report-row-link" href="{{ route('admin.reports.show', $report) }}">
            <span class="report-icon"><x-icon name="report" /></span>
            <span class="report-main">
                <b>{{ $report->title }}</b>
                <small>
                    {{ $report->reporter->name }} · {{ $report->category->name }} · {{ $report->created_at->format('d M Y') }}
                    @if($report->activeAssignment?->officer) · Petugas: {{ $report->activeAssignment->officer->name }} @endif
                </small>
            </span>
            <x-status-pill :status="$report->status" />
            <x-icon name="arrow-up-right" class="row-arrow" />
        </a>
    @empty
        <div class="empty-state">
            <x-icon name="report" />
            <h3>Belum ada laporan masuk</h3>
            <p>Laporan dari pelapor akan muncul di sini setelah dikirim.</p>
        </div>
    @endforelse
</section>
@endsection

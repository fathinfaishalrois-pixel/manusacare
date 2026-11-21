@extends('layouts.app')
@section('title', 'Riwayat Laporan')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Your reports</span>
        <h1>Riwayat <em>laporan.</em></h1>
        <p>Semua laporan fasilitas yang pernah Anda kirim, terbaru di atas.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-primary" href="{{ route('reports.create') }}"><x-icon name="plus" /> Buat laporan</a>
        <a class="button button-ghost" href="{{ route('reporter.dashboard') }}"><x-icon name="arrow-left" /> Dashboard</a>
    </div>
</section>

<section class="content-card" data-reveal>
    @if($reports->total())
        <div class="filter-meta">
            <span>{{ $reports->total() }} laporan</span>
            <span>{{ $reports->firstItem() }}–{{ $reports->lastItem() }}</span>
        </div>
    @endif
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
            <p>Laporan yang kamu kirim akan tersimpan dan dapat dipantau dari halaman ini.</p>
            <a class="button button-primary" href="{{ route('reports.create') }}">Buat laporan pertama</a>
        </div>
    @endforelse

    @if($reports->hasPages())
        <div class="pagination">{{ $reports->links() }}</div>
    @endif
</section>
@endsection

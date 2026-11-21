@extends('layouts.app')
@section('title', 'Manajemen Laporan')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Admin / Reports</span>
        <h1>Manajemen <em>laporan.</em></h1>
        <p>Verifikasi, pantau, dan atur penanganan laporan fasilitas sekolah.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-ghost" href="{{ route('admin.dashboard') }}"><x-icon name="arrow-left" /> Dashboard</a>
    </div>
</section>

<section class="content-card" data-reveal>
    <form class="filter-row" method="GET" role="search">
        <label class="search-field">
            <span class="sr-only">Cari judul laporan</span>
            <x-icon name="search" />
            <input name="search" placeholder="Cari judul laporan…" value="{{ request('search') }}" maxlength="150">
        </label>
        <label>
            <span class="sr-only">Filter status laporan</span>
            <select name="status">
                <option value="">Semua status</option>
                @foreach(['dikirim', 'diproses', 'selesai', 'ditolak'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </label>
        <button class="button button-primary" type="submit">Filter</button>
        @if(request()->filled('search') || request()->filled('status'))
            <a class="button button-quiet" href="{{ route('admin.reports.index') }}">Reset</a>
        @endif
    </form>

    <div class="filter-meta">
        <span>{{ $reports->total() }} laporan ditemukan</span>
        <span>{{ $reports->firstItem() ?? 0 }}–{{ $reports->lastItem() ?? 0 }}</span>
    </div>

    @forelse($reports as $report)
        <a class="report-row report-row-link" href="{{ route('admin.reports.show', $report) }}">
            <span class="report-icon"><x-icon name="report" /></span>
            <span class="report-main">
                <b>{{ $report->title }}</b>
                <small>
                    {{ $report->reporter->name }} · {{ $report->category->name }} · {{ $report->created_at->format('d M Y') }}
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
            <x-icon name="search" />
            <h3>Tidak ada laporan yang cocok</h3>
            <p>Ubah kata kunci atau filter status untuk melihat laporan lain.</p>
            <a class="button button-ghost" href="{{ route('admin.reports.index') }}">Tampilkan semua</a>
        </div>
    @endforelse

    @if($reports->hasPages())
        <div class="pagination">{{ $reports->links() }}</div>
    @endif
</section>
@endsection

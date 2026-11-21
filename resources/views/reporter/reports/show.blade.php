@extends('layouts.app')
@section('title', 'Detail Laporan')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Report detail / {{ strtoupper(substr($report->id, 0, 8)) }}</span>
        <h1>{{ $report->title }}</h1>
        <p>Dikirim {{ $report->created_at->format('d M Y, H:i') }} · {{ $report->category->name }}</p>
    </div>
    <div class="dashboard-head-actions">
        <x-status-pill :status="$report->status" />
        <a class="button button-ghost" href="{{ route('reports.index') }}"><x-icon name="arrow-left" /> Riwayat</a>
    </div>
</section>

<div class="detail-grid">
    <div class="detail-main">
        <section class="content-card" data-reveal>
            <h2>Detail laporan</h2>

            <div class="meta-grid">
                <div class="meta-item">
                    <small>Kategori</small>
                    <b>{{ $report->category->name }}</b>
                </div>
                <div class="meta-item">
                    <small>Urgensi</small>
                    <b><span class="urgency urgency-{{ $report->urgency }}">{{ ucfirst($report->urgency) }}</span></b>
                </div>
                <div class="meta-item">
                    <small>Lokasi</small>
                    <b>{{ $report->building }}{{ $report->floor ? ' · Lantai '.$report->floor : '' }}{{ $report->room ? ' · '.$report->room : '' }}</b>
                </div>
                <div class="meta-item">
                    <small>Waktu ditemukan</small>
                    <b>{{ $report->found_at?->format('d M Y H:i') ?? 'Tidak dicatat' }}</b>
                </div>
            </div>

            <div class="detail-section">
                <h3><x-icon name="report" /> Deskripsi</h3>
                <p>{{ $report->description }}</p>
            </div>

            @if($report->chronology)
                <div class="detail-section">
                    <h3><x-icon name="clock" /> Kronologi</h3>
                    <p>{{ $report->chronology }}</p>
                </div>
            @endif

            @if($report->additional_notes)
                <div class="detail-section">
                    <h3><x-icon name="info" /> Catatan tambahan</h3>
                    <p>{{ $report->additional_notes }}</p>
                </div>
            @endif

            <div class="detail-section">
                <h3><x-icon name="image" /> Dokumentasi laporan</h3>
                <x-report-media-gallery :items="$report->media" />
            </div>
        </section>

        @php($latestRepairUpdate = $report->updates->firstWhere('repair_stage', '!=', null))
        <section class="content-card repair-progress-card" data-reveal>
            <div class="repair-progress-heading">
                <div>
                    <span class="eyebrow">TRANSPARANSI PENANGANAN</span>
                    <h2>Proses perbaikan</h2>
                </div>
                <span class="repair-live-indicator"><span></span> Diperbarui admin</span>
            </div>
            @if($latestRepairUpdate)
                <div class="repair-current-stage">
                    <small>Tahap terkini</small>
                    <strong>{{ ucwords(str_replace('_', ' ', $latestRepairUpdate->repair_stage)) }}</strong>
                    <p>{{ $latestRepairUpdate->note }}</p>
                    <time datetime="{{ $latestRepairUpdate->created_at->toIso8601String() }}">{{ $latestRepairUpdate->created_at->format('d M Y · H:i') }}</time>
                </div>
            @else
                <div class="repair-not-started">
                    <x-icon name="clock" />
                    <div><strong>Belum ada pembaruan proses perbaikan</strong><p>Admin akan memperbarui tahapan di sini. Kamu dapat melihat setiap perkembangan dari halaman ini.</p></div>
                </div>
            @endif
        </section>

        <section class="content-card" data-reveal>
            <h2>Perkembangan laporan</h2>
            @if($report->updates->isEmpty())
                <div class="empty-state">
                    <x-icon name="clock" />
                    <h3>Laporan sedang menunggu</h3>
                    <p>Catatan verifikasi dan penanganan akan muncul di sini setelah admin atau petugas memperbarui status.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($report->updates as $update)
                        <div class="timeline-item">
                            <time class="timeline-time" datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->format('d M Y · H:i') }}</time>
                            <b>{{ $update->repair_stage ? 'Proses perbaikan · '.ucwords(str_replace('_', ' ', $update->repair_stage)) : ($update->status ? ucfirst($update->status) : 'Pembaruan') }}</b>
                            <p>{{ $update->note ?: 'Tidak ada catatan.' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <aside class="detail-side">
        <section class="content-card" data-reveal>
            <h2>Informasi</h2>
            <div class="meta-grid">
                <div class="meta-item">
                    <small>ID laporan</small>
                    <b>{{ $report->id }}</b>
                </div>
                <div class="meta-item">
                    <small>Dokumentasi</small>
                    <b>{{ $report->media->count() }} file</b>
                </div>
                <div class="meta-item">
                    <small>Terakhir diperbarui</small>
                    <b>{{ $report->updated_at->format('d M Y H:i') }}</b>
                </div>
                <div class="meta-item">
                    <small>Status</small>
                    <b>{{ ucfirst($report->status) }}</b>
                </div>
            </div>
            @if($report->rejection_reason)
                <p class="muted-note">Alasan penolakan: {{ $report->rejection_reason }}</p>
            @endif
            <p class="muted-note">Dokumentasi hanya dapat diakses oleh pihak yang memiliki izin.</p>
        </section>

        <section class="content-card officer-card" data-reveal>
            <h2>Petugas penanggung jawab</h2>
            @if($report->activeAssignment?->officer)
                <div class="officer-current">
                    <span class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($report->activeAssignment->officer->name, 0, 1)) }}</span>
                    <span class="officer-current-copy">
                        <small>Ditugaskan oleh admin</small>
                        <b>{{ $report->activeAssignment->officer->name }}</b>
                        @if($report->activeAssignment->officer->phone)
                            <a href="tel:{{ $report->activeAssignment->officer->phone }}"><x-icon name="phone" />{{ $report->activeAssignment->officer->phone }}</a>
                        @endif
                    </span>
                </div>
                <p class="muted-note">Petugas ini merupakan akun aktif yang ditugaskan untuk menangani laporan.</p>
            @else
                <div class="officer-empty"><x-icon name="user" /><span>Admin belum menugaskan petugas untuk laporan ini.</span></div>
            @endif
        </section>
    </aside>
</div>
@endsection

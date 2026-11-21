@extends('layouts.app')
@section('title', 'Penanganan Laporan')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Field operations / Task</span>
        <h1>{{ $report->title }}</h1>
        <p>{{ $report->building }}{{ $report->room ? ' · '.$report->room : '' }} · Pelapor: {{ $report->reporter->name }}</p>
    </div>
    <div class="dashboard-head-actions">
        <x-status-pill :status="$report->status" />
        <a class="button button-ghost" href="{{ route('officer.dashboard') }}"><x-icon name="arrow-left" /> Dashboard</a>
    </div>
</section>

<div class="detail-grid">
    <div class="detail-main">
        <section class="content-card" data-reveal>
            <h2>Detail kerusakan</h2>
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

            @if($report->location_detail)
                <div class="detail-section">
                    <h3><x-icon name="pin" /> Detail lokasi</h3>
                    <p>{{ $report->location_detail }}</p>
                </div>
            @endif

            <div class="detail-section">
                <h3><x-icon name="image" /> Dokumentasi</h3>
                <x-report-media-gallery :items="$report->media" />
            </div>
        </section>

        <section class="content-card" data-reveal>
            <h2>Catatan penanganan</h2>
            @if($report->updates->isEmpty())
                <div class="empty-state">
                    <x-icon name="clock" />
                    <h3>Belum ada catatan</h3>
                    <p>Catatan akan tersimpan setiap kali progres diperbarui.</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($report->updates as $update)
                        <div class="timeline-item">
                            <time class="timeline-time" datetime="{{ $update->created_at->toIso8601String() }}">{{ $update->created_at->format('d M Y · H:i') }}</time>
                            <b>{{ $update->user?->name ?? 'Sistem' }}{{ $update->status ? ' · '.ucfirst($update->status) : '' }}</b>
                            <p>{{ $update->note ?: 'Tidak ada catatan.' }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <aside class="detail-side">
        @if($report->activeAssignment?->officer)
            <section class="content-card officer-card" data-reveal>
                <h2>Petugas penanggung jawab</h2>
                <div class="officer-current">
                    <span class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($report->activeAssignment->officer->name, 0, 1)) }}</span>
                    <span class="officer-current-copy"><small>Petugas yang ditugaskan</small><b>{{ $report->activeAssignment->officer->name }}</b></span>
                </div>
            </section>
        @endif
                    </span>
                </div>
            </section>
        @endif

        <section class="content-card" data-reveal>
            <h2>Perbarui progres</h2>
            <form method="POST" enctype="multipart/form-data" action="{{ route('officer.reports.update', $report) }}">
                @csrf

                <div class="field @error('status') has-error @enderror">
                    <label for="status">Status <span>*</span></label>
                    <select id="status" name="status" required @error('status') aria-invalid="true" aria-describedby="status-error" @enderror>
                        <option value="diproses" @selected(old('status', 'diproses') === 'diproses')>Sedang diproses</option>
                        <option value="selesai" @selected(old('status') === 'selesai')>Selesai</option>
                    </select>
                    @error('status')<small class="field-error" id="status-error">{{ $message }}</small>@enderror
                </div>

                <div class="field">
                    <label for="after_photos">Foto bukti sesudah perbaikan</label>
                    <div class="file-field">
                        <x-icon name="image" />
                        <b>Pilih foto bukti</b>
                        <small>JPG, JPEG, PNG · maks. 5 foto · 10 MB per foto</small>
                        <input id="after_photos" type="file" name="after_photos[]" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple data-file-preview="after-photos" aria-describedby="after-photos-help @error('after_photos.*') after-photos-error @enderror" @error('after_photos.*') aria-invalid="true" @enderror>
                    </div>
                    <small id="after-photos-help">Wajib minimal satu foto atau video jika status diubah menjadi selesai.</small>
                    <div class="upload-preview" data-preview-output="after-photos" aria-live="polite"></div>
                    @error('after_photos.*')<small class="field-error" id="after-photos-error">{{ $message }}</small>@enderror
                </div>

                <div class="field">
                    <label for="after_videos">Video bukti sesudah perbaikan</label>
                    <div class="file-field">
                        <x-icon name="video" />
                        <b>Pilih video bukti</b>
                        <small>MP4, MOV · maks. 2 video · 100 MB per video</small>
                        <input id="after_videos" type="file" name="after_videos[]" accept=".mp4,.mov,video/mp4,video/quicktime" multiple data-file-preview="after-videos" aria-describedby="after-videos-help @error('after_videos.*') after-videos-error @enderror" @error('after_videos.*') aria-invalid="true" @enderror>
                    </div>
                    <small id="after-videos-help">Video singkat memperlihatkan hasil perbaikan secara lebih jelas.</small>
                    <div class="upload-preview" data-preview-output="after-videos" aria-live="polite"></div>
                    @error('after_videos.*')<small class="field-error" id="after-videos-error">{{ $message }}</small>@enderror
                </div>

                <div class="field @error('note') has-error @enderror">
                    <label for="note">Catatan pekerjaan <span>*</span></label>
                    <textarea id="note" name="note" rows="5" maxlength="3000" required placeholder="Jelaskan pekerjaan yang dilakukan..." @error('note') aria-invalid="true" aria-describedby="note-error" @enderror>{{ old('note') }}</textarea>
                    @error('note')<small class="field-error" id="note-error">{{ $message }}</small>@enderror
                </div>

                <button class="button button-primary w-full" type="submit">Simpan progres</button>
            </form>
        </section>
    </aside>
</div>
@endsection

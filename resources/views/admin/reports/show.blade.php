@extends('layouts.app')
@section('title', 'Detail Laporan')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Report detail / {{ strtoupper(substr($report->id, 0, 8)) }}</span>
        <h1>{{ $report->title }}</h1>
        <p>{{ $report->reporter->name }} · {{ $report->category->name }} · dikirim {{ $report->created_at->format('d M Y H:i') }}</p>
    </div>
    <div class="dashboard-head-actions">
        <x-status-pill :status="$report->status" />
        <a class="button button-ghost" href="{{ route('admin.reports.index') }}"><x-icon name="arrow-left" /> Kembali</a>
    </div>
</section>

<div class="detail-grid">
    <div class="detail-main">
        <section class="content-card" data-reveal>
            <h2>Informasi kerusakan</h2>
            <div class="meta-grid">
                <div class="meta-item"><small>Kategori</small><b>{{ $report->category->name }}</b></div>
                <div class="meta-item"><small>Urgensi</small><b><span class="urgency urgency-{{ $report->urgency }}">{{ ucfirst($report->urgency) }}</span></b></div>
                <div class="meta-item"><small>Lokasi</small><b>{{ $report->building }}{{ $report->floor ? ' · Lantai '.$report->floor : '' }}{{ $report->room ? ' · '.$report->room : '' }}</b></div>
                <div class="meta-item"><small>Waktu ditemukan</small><b>{{ $report->found_at?->format('d M Y H:i') ?? 'Tidak dicatat' }}</b></div>
                @if($report->location_detail)<div class="meta-item"><small>Detail lokasi</small><b>{{ $report->location_detail }}</b></div>@endif
                <div class="meta-item"><small>Dokumentasi</small><b>{{ $report->media->count() }} file</b></div>
            </div>
            <div class="detail-section"><h3><x-icon name="report" /> Deskripsi</h3><p>{{ $report->description }}</p></div>
            <div class="detail-section"><h3><x-icon name="clock" /> Kronologi</h3><p>{{ $report->chronology ?: 'Tidak ada kronologi tambahan.' }}</p></div>
            @if($report->additional_notes)<div class="detail-section"><h3><x-icon name="info" /> Catatan tambahan</h3><p>{{ $report->additional_notes }}</p></div>@endif
            <div class="detail-section"><h3><x-icon name="image" /> Dokumentasi</h3><x-report-media-gallery :items="$report->media" /></div>
        </section>

        <section class="content-card" data-reveal>
            <h2>Riwayat verifikasi dan penanganan</h2>
            @if($report->updates->isEmpty())
                <div class="empty-state"><x-icon name="clock" /><h3>Belum ada catatan</h3><p>Riwayat verifikasi dan penanganan akan muncul di sini.</p></div>
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
        <section class="content-card" data-reveal>
            <h2>Verifikasi laporan</h2>
            @if($report->status === 'dikirim' && ! $report->verified_at)
                <form method="POST" action="{{ route('admin.reports.verify', $report) }}">
                    @csrf
                    <div class="field @error('decision') has-error @enderror">
                        <label for="decision">Keputusan <span>*</span></label>
                        <select id="decision" name="decision" required>
                            <option value="accept" @selected(old('decision', 'accept') === 'accept')>Terima laporan</option>
                            <option value="reject" @selected(old('decision') === 'reject')>Tolak laporan</option>
                        </select>
                        @error('decision')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field @error('reason') has-error @enderror">
                        <label for="reason">Alasan penolakan <small>(wajib jika ditolak)</small></label>
                        <textarea id="reason" name="reason" rows="3" maxlength="2000">{{ old('reason') }}</textarea>
                        @error('reason')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <button class="button button-primary w-full" type="submit">Simpan keputusan</button>
                </form>
            @elseif($report->status === 'ditolak')
                <p>Laporan ini ditolak.</p>
                @if($report->rejection_reason)<p class="muted-note">Alasan: {{ $report->rejection_reason }}</p>@endif
            @else
                <p><x-status-pill :status="$report->status" /></p>
                <p class="muted-note">{{ $report->verified_at ? 'Laporan sudah diverifikasi pada '.$report->verified_at->format('d M Y H:i').'.' : 'Laporan sudah diverifikasi.' }}</p>
            @endif
        </section>

        <section class="content-card repair-control-card" data-reveal>
            <h2>Proses perbaikan</h2>
            <p class="muted-note">Tahap ini tampil pada halaman pelapor sebagai informasi perkembangan. Status utama laporan tetap mengikuti penugasan dan penyelesaian petugas.</p>
            @if($report->verified_at && $report->status !== 'ditolak')
                @php($latestRepairUpdate = $report->updates->firstWhere('repair_stage', '!=', null))
                <form method="POST" action="{{ route('admin.reports.repair-progress', $report) }}">
                    @csrf
                    <div class="field @error('repair_stage') has-error @enderror">
                        <label for="repair_stage">Tahap perbaikan <span>*</span></label>
                        <select id="repair_stage" name="repair_stage" required>
                            @foreach(['menunggu_perbaikan' => 'Menunggu perbaikan', 'pemeriksaan' => 'Pemeriksaan', 'menunggu_material' => 'Menunggu material', 'dalam_perbaikan' => 'Dalam perbaikan', 'pengujian' => 'Pengujian', 'selesai' => 'Selesai', 'tertunda' => 'Tertunda'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('repair_stage', $latestRepairUpdate?->repair_stage ?? 'menunggu_perbaikan') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('repair_stage')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="field @error('note') has-error @enderror">
                        <label for="repair_note">Keterangan untuk pelapor <span>*</span></label>
                        <textarea id="repair_note" name="note" rows="3" maxlength="2000" required placeholder="Jelaskan perkembangan perbaikan secara singkat...">{{ old('note') }}</textarea>
                        @error('note')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <button class="button button-primary w-full" type="submit">Perbarui proses perbaikan</button>
                </form>
            @else
                <p class="muted-note">Verifikasi laporan terlebih dahulu untuk memperbarui tahapan perbaikan.</p>
            @endif
        </section>

        <section class="content-card officer-card" data-reveal>
            <h2>Penugasan petugas</h2>
            @if($report->activeAssignment?->officer)
                <div class="officer-current">
                    <span class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($report->activeAssignment->officer->name, 0, 1)) }}</span>
                    <span class="officer-current-copy">
                        <small>Petugas aktif</small>
                        <b>{{ $report->activeAssignment->officer->name }}</b>
                        @if($report->activeAssignment->officer->phone)<a href="tel:{{ $report->activeAssignment->officer->phone }}"><x-icon name="phone" />{{ $report->activeAssignment->officer->phone }}</a>@endif
                        <small>{{ (int) ($officers->firstWhere('id', $report->activeAssignment->officer_id)?->active_workload ?? 0) }} laporan aktif</small>
                    </span>
                </div>
            @else
                <div class="officer-empty"><x-icon name="user" /><span>Belum ada petugas yang ditugaskan.</span></div>
            @endif

            @if($report->verified_at && ! in_array($report->status, ['selesai', 'ditolak'], true))
                @if($officerWorkloadWarning)<p class="muted-note">{{ $officerWorkloadWarning }}</p>@endif
                <form method="POST" action="{{ route('admin.reports.assign', $report) }}">
                    @csrf
                    <div class="field @error('officer_id') has-error @enderror">
                        <label for="officer_id">Pilih petugas <span>*</span></label>
                        <select id="officer_id" name="officer_id" required @disabled($officers->isEmpty())>
                            <option value="">Pilih petugas aktif</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" @selected((string) old('officer_id', $report->activeAssignment?->officer_id ?? $recommendedOfficer?->id) === (string) $officer->id)>
                                    {{ $officer->name }} · {{ (int) $officer->active_workload }} tugas aktif{{ $recommendedOfficer?->id === $officer->id ? ' · Rekomendasi sistem' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @if($officers->isEmpty())<small class="field-error">Belum ada akun petugas aktif. Buat akun petugas melalui menu Pengguna.</small>@endif
                        @error('officer_id')<small class="field-error">{{ $message }}</small>@enderror
                        @if($recommendedOfficer)<small>Rekomendasi: {{ $recommendedOfficer->name }} karena memiliki beban tugas aktif paling sedikit.</small>@endif
                    </div>
                    <div class="field @error('note') has-error @enderror">
                        <label for="note">Catatan penugasan</label>
                        <textarea id="note" name="note" rows="3" maxlength="1000" placeholder="Catatan untuk petugas (opsional)">{{ old('note') }}</textarea>
                        @error('note')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <button class="button button-primary w-full" type="submit" @disabled($officers->isEmpty())>{{ $report->activeAssignment ? 'Ganti petugas' : 'Tetapkan petugas' }}</button>
                    <p class="muted-note">Peringatan beban kerja hanya sebagai informasi. Admin tetap dapat menetapkan petugas yang dipilih.</p>
                </form>
            @elseif(! $report->verified_at && $report->status === 'dikirim')
                <p class="muted-note">Verifikasi laporan terlebih dahulu sebelum penugasan.</p>
            @endif
        </section>
    </aside>
</div>
@endsection

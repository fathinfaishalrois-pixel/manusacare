@extends('layouts.app')
@section('title', 'Buat Laporan')
@section('content')
<div class="form-page-head">
    <span class="eyebrow"><span class="eyebrow-index">01</span>New facility report</span>
    <h1>Ceritakan apa yang <em>perlu diperbaiki.</em></h1>
    <p>Berikan informasi yang jelas agar laporan dapat diverifikasi dan ditangani dengan tepat.</p>
</div>

<form class="form-layout" method="POST" action="{{ route('reports.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="report-form">
        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-index">01</span>
                <div>
                    <h2>Informasi laporan</h2>
                    <p>Ringkas masalah dan tentukan tingkat urgensinya.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field span-2 @error('title') has-error @enderror">
                    <label for="title">Judul laporan <span>*</span></label>
                    <input id="title" name="title" value="{{ old('title') }}" maxlength="150" required placeholder="Contoh: Lampu ruang kelas tidak menyala" @error('title') aria-invalid="true" aria-describedby="title-error" @enderror>
                    @error('title')<small class="field-error" id="title-error">{{ $message }}</small>@enderror
                </div>

                <div class="field @error('category_id') has-error @enderror">
                    <label for="category_id">Kategori <span>*</span></label>
                    <select id="category_id" name="category_id" required @disabled($categories->isEmpty()) @if($errors->has('category_id')) aria-invalid="true" aria-describedby="category-help category-error" @else aria-describedby="category-help" @endif>
                        <option value="">Pilih kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <small id="category-help">@if($categories->isEmpty())Belum ada kategori aktif. Silakan hubungi administrator agar kategori tersedia.@elsePilih kategori yang paling sesuai dengan kerusakan.@endif</small>
                    @error('category_id')<small class="field-error" id="category-error">{{ $message }}</small>@enderror
                </div>

                <div class="field @error('urgency') has-error @enderror">
                    <label for="urgency">Tingkat urgensi <span>*</span></label>
                    <select id="urgency" name="urgency" required @error('urgency') aria-invalid="true" aria-describedby="urgency-error" @enderror>
                        <option value="rendah" @selected(old('urgency', 'rendah') === 'rendah')>Rendah</option>
                        <option value="sedang" @selected(old('urgency') === 'sedang')>Sedang</option>
                        <option value="tinggi" @selected(old('urgency') === 'tinggi')>Tinggi</option>
                    </select>
                    @error('urgency')<small class="field-error" id="urgency-error">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-index">02</span>
                <div>
                    <h2>Lokasi fasilitas</h2>
                    <p>Cantumkan lokasi sedetail mungkin agar petugas mudah menemukannya.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field @error('building') has-error @enderror">
                    <label for="building">Gedung <span>*</span></label>
                    <select id="building" name="building" required @error('building') aria-invalid="true" aria-describedby="building-error" @enderror>
                        <option value="">Pilih gedung</option>
                        @foreach($buildings as $buildingOption)
                            <option value="{{ $buildingOption }}" @selected(old('building') === $buildingOption)>{{ $buildingOption }}</option>
                        @endforeach
                        <option value="__other" @selected(old('building') === '__other')>Gedung lainnya…</option>
                    </select>
                    <div class="conditional-field" data-conditional-field="building" @if(old('building') !== '__other' && !$errors->has('building_custom')) hidden @endif>
                        <label for="building_custom">Nama gedung lainnya <span>*</span></label>
                        <input id="building_custom" name="building_custom" value="{{ old('building_custom') }}" maxlength="100" placeholder="Contoh: Gedung E" aria-label="Nama gedung lainnya" @if(old('building') === '__other') required @endif>
                        @error('building_custom')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    @error('building')<small class="field-error" id="building-error">{{ $message }}</small>@enderror
                </div>
                <div class="field @error('floor') has-error @enderror">
                    <label for="floor">Lantai</label>
                    <select id="floor" name="floor" @error('floor') aria-invalid="true" aria-describedby="floor-error" @enderror>
                        <option value="">Pilih lantai (opsional)</option>
                        @foreach($floors as $floorOption)
                            <option value="{{ $floorOption }}" @selected(old('floor') === $floorOption)>{{ $floorOption }}</option>
                        @endforeach
                        <option value="__other" @selected(old('floor') === '__other')>Lantai lainnya…</option>
                    </select>
                    <div class="conditional-field" data-conditional-field="floor" @if(old('floor') !== '__other' && !$errors->has('floor_custom')) hidden @endif>
                        <label for="floor_custom">Nama/nomor lantai lainnya <span>*</span></label>
                        <input id="floor_custom" name="floor_custom" value="{{ old('floor_custom') }}" maxlength="50" placeholder="Contoh: Lantai Mezzanine" aria-label="Lantai lainnya" @if(old('floor') === '__other') required @endif>
                        @error('floor_custom')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    @error('floor')<small class="field-error" id="floor-error">{{ $message }}</small>@enderror
                </div>
                <div class="field @error('room') has-error @enderror">
                    <label for="room">Ruangan</label>
                    <select id="room" name="room" @error('room') aria-invalid="true" aria-describedby="room-error" @enderror>
                        <option value="">Pilih ruangan (opsional)</option>
                        @foreach($rooms as $roomOption)
                            <option value="{{ $roomOption }}" @selected(old('room') === $roomOption)>{{ $roomOption }}</option>
                        @endforeach
                        <option value="__other" @selected(old('room') === '__other')>Ruangan lainnya…</option>
                    </select>
                    <div class="conditional-field" data-conditional-field="room" @if(old('room') !== '__other' && !$errors->has('room_custom')) hidden @endif>
                        <label for="room_custom">Nama ruangan lainnya <span>*</span></label>
                        <input id="room_custom" name="room_custom" value="{{ old('room_custom') }}" maxlength="100" placeholder="Contoh: Ruang Multimedia" aria-label="Nama ruangan lainnya" @if(old('room') === '__other') required @endif>
                        @error('room_custom')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    @error('room')<small class="field-error" id="room-error">{{ $message }}</small>@enderror
                </div>
                <div class="field @error('location_detail') has-error @enderror">
                    <label for="location_detail">Detail lokasi</label>
                    <input id="location_detail" name="location_detail" value="{{ old('location_detail') }}" maxlength="500" placeholder="Contoh: dekat jendela sisi timur" @error('location_detail') aria-invalid="true" aria-describedby="location-detail-error" @enderror>
                    @error('location_detail')<small class="field-error" id="location-detail-error">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-index">03</span>
                <div>
                    <h2>Detail kerusakan</h2>
                    <p>Jelaskan kondisi dan dampaknya terhadap kegiatan belajar.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field span-2 @error('description') has-error @enderror">
                    <label for="description">Deskripsi kerusakan <span>*</span></label>
                    <textarea id="description" name="description" rows="4" maxlength="5000" required placeholder="Jelaskan kondisi fasilitas dan dampaknya..." @error('description') aria-invalid="true" aria-describedby="description-error" @enderror>{{ old('description') }}</textarea>
                    @error('description')<small class="field-error" id="description-error">{{ $message }}</small>@enderror
                </div>
                <div class="field span-2 @error('chronology') has-error @enderror">
                    <label for="chronology">Kronologi <small>(opsional)</small></label>
                    <textarea id="chronology" name="chronology" rows="3" maxlength="5000" placeholder="Kapan dan bagaimana kerusakan diketahui?" @error('chronology') aria-invalid="true" aria-describedby="chronology-error" @enderror>{{ old('chronology') }}</textarea>
                    @error('chronology')<small class="field-error" id="chronology-error">{{ $message }}</small>@enderror
                </div>
                <div class="field @error('found_at') has-error @enderror">
                    <label for="found_at">Waktu ditemukan</label>
                    <input id="found_at" type="datetime-local" name="found_at" value="{{ old('found_at') }}" @error('found_at') aria-invalid="true" aria-describedby="found-at-error" @enderror>
                    @error('found_at')<small class="field-error" id="found-at-error">{{ $message }}</small>@enderror
                </div>
                <div class="field @error('additional_notes') has-error @enderror">
                    <label for="additional_notes">Catatan tambahan</label>
                    <input id="additional_notes" name="additional_notes" value="{{ old('additional_notes') }}" maxlength="2000" placeholder="Informasi lain yang relevan" @error('additional_notes') aria-invalid="true" aria-describedby="additional-notes-error" @enderror>
                    @error('additional_notes')<small class="field-error" id="additional-notes-error">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <div class="form-section-head">
                <span class="form-section-index">04</span>
                <div>
                    <h2>Dokumentasi</h2>
                    <p>Foto dan video membantu petugas memahami kondisi sebenarnya.</p>
                </div>
            </div>
            <div class="form-grid">
                <div class="field">
                    <label for="photos">Foto dokumentasi</label>
                    <div class="file-field">
                        <x-icon name="image" />
                        <b>Pilih foto dokumentasi</b>
                        <small>JPG, JPEG, PNG · maks. 5 file · 10 MB per file</small>
                        <input id="photos" type="file" name="photos[]" accept=".jpg,.jpeg,.png,image/jpeg,image/png" multiple data-file-preview="photos" aria-describedby="photos-help @error('photos.*') photos-error @enderror" @error('photos.*') aria-invalid="true" @enderror>
                    </div>
                    <small id="photos-help">Lampirkan foto yang memperlihatkan kerusakan dengan jelas.</small>
                    <div class="upload-preview" data-preview-output="photos" aria-live="polite"></div>
                    @error('photos.*')<small class="field-error" id="photos-error">{{ $message }}</small>@enderror
                </div>
                <div class="field">
                    <label for="videos">Video dokumentasi</label>
                    <div class="file-field">
                        <x-icon name="video" />
                        <b>Pilih video dokumentasi</b>
                        <small>MP4, MOV · maks. 2 file · 100 MB per file</small>
                        <input id="videos" type="file" name="videos[]" accept=".mp4,.mov,video/mp4,video/quicktime" multiple data-file-preview="videos" aria-describedby="videos-help @error('videos.*') videos-error @enderror" @error('videos.*') aria-invalid="true" @enderror>
                    </div>
                    <small id="videos-help">Video singkat lebih mudah ditinjau daripada rekaman panjang.</small>
                    <div class="upload-preview" data-preview-output="videos" aria-live="polite"></div>
                    @error('videos.*')<small class="field-error" id="videos-error">{{ $message }}</small>@enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="button button-ghost" href="{{ route('reporter.dashboard') }}">Batal</a>
            <button class="button button-primary button-large" type="submit" @disabled($categories->isEmpty())>Kirim laporan <x-icon name="arrow-up-right" /></button>
        </div>
    </div>

    <aside class="form-aside">
        <div class="guide-card">
            <h2><x-icon name="check-circle" /> Sebelum mengirim</h2>
            <ul>
                <li><x-icon name="check" /><span>Gunakan judul yang spesifik dan mudah dicari.</span></li>
                <li><x-icon name="check" /><span>Cantumkan gedung, lantai, dan ruangan yang tepat.</span></li>
                <li><x-icon name="check" /><span>Pilih urgensi sesuai dampak terhadap kegiatan belajar.</span></li>
                <li><x-icon name="check" /><span>Lampirkan dokumentasi yang cukup jelas.</span></li>
            </ul>
        </div>
        <div class="guide-card">
            <h2><x-icon name="lock" /> Privasi dokumentasi</h2>
            <p>Dokumentasi hanya dapat diakses oleh pelapor, petugas yang ditugaskan, dan administrator sekolah.</p>
        </div>
    </aside>
</form>
@endsection

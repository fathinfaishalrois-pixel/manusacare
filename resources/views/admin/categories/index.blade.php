@extends('layouts.app')
@section('title', 'Kategori')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Admin / Configuration</span>
        <h1>Kategori <em>laporan.</em></h1>
        <p>Atur klasifikasi kerusakan fasilitas sekolah agar laporan lebih mudah ditelusuri.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-ghost" href="{{ route('admin.dashboard') }}"><x-icon name="arrow-left" /> Dashboard</a>
    </div>
</section>

<section class="content-card" data-reveal>
    <h2>Tambah kategori</h2>
    <form class="filter-row" method="POST" action="{{ route('admin.categories.store') }}">
        @csrf
        <label class="search-field">
            <span class="sr-only">Nama kategori baru</span>
            <input name="name" placeholder="Nama kategori" value="{{ old('name') }}" maxlength="100" required>
        </label>
        <label>
            <span class="sr-only">Deskripsi kategori baru</span>
            <input name="description" placeholder="Deskripsi singkat" value="{{ old('description') }}" maxlength="500">
        </label>
        <button class="button button-primary" type="submit"><x-icon name="plus" /> Tambah</button>
    </form>

    <div class="filter-meta">
        <span>{{ $categories->count() }} kategori</span>
        <span>Urut berdasarkan nama</span>
    </div>

    @forelse($categories as $cat)
        <form class="category-row" method="POST" action="{{ route('admin.categories.update', $cat) }}">
            @csrf
            @method('PUT')
            <label>
                <span class="sr-only">Nama kategori {{ $cat->name }}</span>
                <input name="name" value="{{ $cat->name }}" maxlength="100" required>
            </label>
            <label>
                <span class="sr-only">Deskripsi kategori {{ $cat->name }}</span>
                <input name="description" value="{{ $cat->description }}" maxlength="500" placeholder="Deskripsi">
            </label>
            <label>
                <span class="sr-only">Status kategori {{ $cat->name }}</span>
                <select name="is_active">
                    <option value="1" @selected($cat->is_active)>Aktif</option>
                    <option value="0" @selected(! $cat->is_active)>Nonaktif</option>
                </select>
            </label>
            <small>{{ $cat->reports_count }} laporan</small>
            <button class="button button-ghost" type="submit">Simpan</button>
        </form>
    @empty
        <div class="empty-state">
            <x-icon name="tag" />
            <h3>Belum ada kategori</h3>
            <p>Tambahkan kategori pertama agar pelapor dapat mengklasifikasikan laporan.</p>
        </div>
    @endforelse
</section>
@endsection

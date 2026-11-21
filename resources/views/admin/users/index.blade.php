@extends('layouts.app')
@section('title', 'Pengguna')
@section('content')
<section class="dashboard-head">
    <div>
        <span class="eyebrow"><span class="eyebrow-index">02</span>Admin / Accounts</span>
        <h1>Manajemen <em>pengguna.</em></h1>
        <p>Buat dan kelola akun pelapor, petugas, dan admin.</p>
    </div>
    <div class="dashboard-head-actions">
        <a class="button button-primary" href="{{ route('admin.users.create') }}"><x-icon name="plus" /> Tambah akun</a>
    </div>
</section>

<section class="content-card" data-reveal>
    <form class="filter-row" method="GET" role="search">
        <label class="search-field">
            <span class="sr-only">Cari pengguna</span>
            <x-icon name="search" />
            <input name="search" value="{{ request('search') }}" placeholder="Cari nama atau NIS/NIP" maxlength="150">
        </label>
        <button class="button button-primary" type="submit">Cari</button>
        @if(request()->filled('search'))
            <a class="button button-quiet" href="{{ route('admin.users.index') }}">Reset</a>
        @endif
    </form>

    <div class="filter-meta">
        <span>{{ $users->total() }} akun</span>
        <span>{{ $users->firstItem() ?? 0 }}–{{ $users->lastItem() ?? 0 }}</span>
    </div>

    @forelse($users as $u)
        <div class="report-row">
            <span class="report-icon"><span class="avatar" aria-hidden="true">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($u->name, 0, 1)) }}</span></span>
            <span class="report-main">
                <b>{{ $u->name }}</b>
                <small class="user-identity">
                    <span>{{ $u->identity_number }}</span>
                    @foreach($u->roles as $role)
                        <span class="role-badge">{{ $role->name }}</span>
                    @endforeach
                    <span class="user-identity-status {{ $u->is_active ? 'is-active' : 'is-inactive' }}">{{ $u->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                </small>
            </span>
            <a class="text-link" href="{{ route('admin.users.edit', $u) }}">Edit <x-icon name="arrow-up-right" /></a>
        </div>
    @empty
        <div class="empty-state">
            <x-icon name="users" />
            <h3>Tidak ada akun ditemukan</h3>
            <p>Ubah kata kunci pencarian atau tambahkan akun baru.</p>
        </div>
    @endforelse

    @if($users->hasPages())
        <div class="pagination">{{ $users->links() }}</div>
    @endif
</section>
@endsection

@props(['items'])
<div class="media-preview-grid">
    @forelse($items as $media)
        <article class="media-preview-card">
            @if(str_starts_with($media->mime_type, 'image/'))
                <a href="{{ route('media.show', $media) }}" data-lightbox target="_blank" rel="noopener" aria-label="Buka pratinjau {{ $media->original_name }}">
                    <img src="{{ route('media.show', $media) }}" alt="{{ $media->original_name }}" loading="lazy">
                </a>
            @elseif(str_starts_with($media->mime_type, 'video/'))
                <video controls preload="metadata" playsinline>
                    <source src="{{ route('media.show', $media) }}" type="{{ $media->mime_type }}">
                    Browser tidak mendukung pratinjau video ini.
                </video>
            @else
                <div class="media-empty">
                    <x-icon name="report" />
                    <span>Pratinjau tidak tersedia untuk tipe file ini.</span>
                </div>
            @endif
            <div>
                <b>{{ $media->original_name }}</b>
                <small>{{ strtoupper($media->kind) }} · {{ number_format($media->size / 1048576, 1) }} MB</small>
                <a class="text-link" href="{{ route('media.show', ['media' => $media, 'download' => 1]) }}">
                    Unduh file <x-icon name="download" />
                </a>
            </div>
        </article>
    @empty
        <div class="media-empty">
            <x-icon name="image" />
            <span>Belum ada dokumentasi yang dilampirkan.</span>
        </div>
    @endforelse
</div>

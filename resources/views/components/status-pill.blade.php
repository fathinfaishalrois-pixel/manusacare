@props(['status'])

@php
    $states = [
        'dikirim' => ['label' => 'Dikirim', 'tone' => 'submitted'],
        'diproses' => ['label' => 'Diproses', 'tone' => 'progress'],
        'selesai' => ['label' => 'Selesai', 'tone' => 'completed'],
        'ditolak' => ['label' => 'Ditolak', 'tone' => 'rejected'],
    ];
    $state = $states[$status] ?? ['label' => ucfirst((string) $status), 'tone' => 'neutral'];
@endphp

<span {{ $attributes->merge(['class' => 'status-pill status-'.$state['tone']]) }}>
    <span class="status-dot" aria-hidden="true"></span>{{ $state['label'] }}
</span>

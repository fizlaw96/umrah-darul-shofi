@props(['status'])

@php
    $styles = [
        'published' => 'bg-[#dcf7ed] text-[#006b57]',
        'draft' => 'bg-[#fff0cf] text-[#9b6910]',
        'archived' => 'bg-[#e8ece9] text-[#69736f]',
    ];

    $labels = [
        'published' => 'Diterbitkan',
        'draft' => 'Draf',
        'archived' => 'Diarkibkan',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-bold tracking-[0.08em] '.$styles[$status]]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>{{ $labels[$status] ?? ucfirst($status) }}
</span>

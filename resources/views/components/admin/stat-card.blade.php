@props(['label', 'value', 'detail', 'icon', 'tone' => 'emerald'])

@php
    $tones = [
        'emerald' => 'border-l-[#006b57] bg-[#e9f7ed] text-[#006b57]',
        'gold' => 'border-l-[#e9c171] bg-[#fff8e8] text-[#9b6910]',
        'ink' => 'border-l-[#00251e] bg-[#edf5f0] text-[#00251e]',
    ];
@endphp

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-[#edf0ec] border-l-4 bg-white p-5 shadow-[0_12px_30px_-24px_rgba(0,37,30,.35)] '.$tones[$tone]]) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.13em] text-[#66716c]">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold leading-none text-[#00251e]">{{ $value }}</p>
        </div>
        <span class="grid size-11 place-items-center rounded-xl bg-white/80"><span class="material-symbols-outlined text-[22px]">{{ $icon }}</span></span>
    </div>
    <p class="mt-8 text-xs font-medium text-[#64706a]">{{ $detail }}</p>
</article>

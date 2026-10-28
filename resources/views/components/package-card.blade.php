@props([
    'package',
])

@php($nextDeparture = $package->departures->first())

<article class="group flex h-60 w-[82vw] shrink-0 snap-start overflow-hidden rounded-2xl border border-[#e9e4d9] bg-white shadow-[0_10px_26px_-20px_rgba(0,39,31,0.36)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_16px_30px_-20px_rgba(0,39,31,0.42)] sm:h-64 sm:w-auto">
    @if ($package->posterImageUrl())
        <button type="button" class="grid w-28 shrink-0 cursor-zoom-in place-items-center bg-[#f2f0e9] p-1.5 sm:w-32" data-package-poster data-package-poster-src="{{ $package->posterImageUrl() }}" data-package-poster-alt="{{ $package->title }}" aria-label="Lihat poster {{ $package->title }}">
            <img src="{{ $package->posterImageUrl() }}" alt="{{ $package->title }}" class="max-h-full max-w-full object-contain" loading="lazy">
        </button>
    @else
        <div class="grid w-28 shrink-0 place-items-center bg-gradient-to-br from-[#073e34] to-[#00271f] text-[#ffdf9a] sm:w-32">
            <span class="material-symbols-outlined text-4xl">mosque</span>
        </div>
    @endif

    <div class="flex min-w-0 flex-1 flex-col p-3.5 sm:p-4">
        <div>
            <div class="flex items-center justify-between gap-2 text-[8px] font-bold uppercase tracking-[0.1em]">
                <span class="text-[#95742c]">{{ $package->category->name }}</span>
                <span class="shrink-0 text-[#68736d]">{{ $package->duration_days }}H {{ $package->duration_nights }}M</span>
            </div>
            <h3 class="mt-1.5 line-clamp-2 font-sans text-[15px] font-bold leading-[1.25] tracking-[-.025em] text-[#1a2421] sm:text-base">
                <a href="{{ route('packages.show', $package) }}">{{ $package->title }}</a>
            </h3>
        </div>

        @if ($package->is_date_flexible)
            <p class="mt-3 flex items-center gap-1.5 text-[10px] font-medium text-[#59635f]"><span class="material-symbols-outlined text-[15px] text-[#9a762e]">info</span><span>Tarikh akan disahkan</span></p>
        @elseif ($nextDeparture)
            <p class="mt-3 flex items-center gap-1.5 text-[10px] font-medium text-[#59635f]"><span class="material-symbols-outlined text-[15px] text-[#9a762e]">calendar_month</span><span>Berlepas {{ $nextDeparture->departure_date->translatedFormat('d M Y') }}</span>@if ($nextDeparture->status === 'closed')<span class="font-bold text-[#a14b3e]">· Ditutup</span>@endif</p>
        @endif

        <div class="mt-auto flex items-end justify-between gap-2 border-t border-[#f0ece4] pt-3">
            <div>
                <p class="text-[8px] font-medium uppercase tracking-[0.12em] text-[#7d8581]">Bermula</p>
                <p class="mt-0.5 font-sans text-lg font-extrabold leading-[1.2] tracking-[-.035em] text-[#0f382c] tabular-nums">{{ $package->formattedStartingPrice() }}</p>
            </div>

            <a href="{{ route('packages.show', $package) }}" class="grid size-9 shrink-0 place-items-center rounded-lg bg-dark-green text-[#ffdf9a] transition hover:bg-[#104f43]" aria-label="Lihat pakej {{ $package->title }}">
                <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            </a>
        </div>
    </div>
</article>

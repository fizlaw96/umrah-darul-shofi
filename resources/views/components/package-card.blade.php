@props([
    'package',
])

@php
    $hotelSummary = filled($package->hotel_information)
        ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags($package->hotel_information)) ?? '', 58)
        : 'Butiran hotel akan dikemas kini oleh agensi.';
    $nextDeparture = $package->departures->first();

@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[#e9e4d9] bg-white shadow-[0_12px_30px_-22px_rgba(0,39,31,0.36)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_36px_-20px_rgba(0,39,31,0.42)]">
    <a href="{{ route('packages.show', $package) }}" class="package-card-image relative block aspect-[3/4] overflow-hidden bg-[radial-gradient(circle_at_center,rgba(255,224,128,.18),transparent_52%),linear-gradient(135deg,#0b4c3f,#04271f)]" data-package-image-frame>
        @if ($package->imageUrl())
            <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover transition duration-500" loading="lazy" data-package-image>
        @else
            <div class="grid size-full place-items-center bg-gradient-to-br from-[#073e34] to-[#00271f] text-[#ffdf9a]">
                <span class="material-symbols-outlined text-5xl">mosque</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div>
            <div class="flex items-center justify-between gap-3 text-[9px] font-bold uppercase tracking-[0.1em]">
                <span class="text-[#95742c]">{{ $package->category->name }}</span>
                <span class="shrink-0 text-[#68736d]">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</span>
            </div>
            <h3 class="mt-2 font-sans text-lg font-bold leading-[1.3] tracking-[-.025em] text-[#1a2421]">
                <a href="{{ route('packages.show', $package) }}">{{ $package->title }}</a>
            </h3>
            <p class="mt-2 line-clamp-2 text-[11px] leading-5 text-[#66706c]">{{ $package->short_description }}</p>
        </div>

        <div class="mt-5 space-y-2.5 border-y border-[#f0ece4] py-4 text-[10px] leading-4 text-[#59635f]">
            @if ($nextDeparture)
                <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">calendar_month</span><span>Berlepas {{ $nextDeparture->departure_date->translatedFormat('d M Y') }} @if ($nextDeparture->status === 'closed')<span class="font-bold text-[#a14b3e]">· Ditutup</span>@endif</span></p>
            @endif
            <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">hotel</span><span>{{ $hotelSummary }}</span></p>
            <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">info</span><span>Harga dan ketersediaan tertakluk kepada pengesahan agensi.</span></p>
        </div>

        <div class="mt-auto flex items-end justify-between gap-4 pt-5">
            <div>
                <p class="text-[9px] font-medium uppercase tracking-[0.12em] text-[#7d8581]">Bermula dari</p>
                <p class="mt-1 font-sans text-xl font-extrabold leading-[1.2] tracking-[-.035em] text-[#0f382c] tabular-nums">{{ $package->formattedStartingPrice() }}</p>
                <p class="mt-1 text-[9px] text-[#7d8581]">seorang</p>
            </div>

            <a href="{{ route('packages.show', $package) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-dark-green px-3 py-2.5 text-[10px] font-bold text-[#ffdf9a] transition hover:bg-[#104f43]">
                Lihat pakej <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
            </a>
        </div>
    </div>
</article>

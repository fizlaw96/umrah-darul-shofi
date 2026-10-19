@props([
    'package',
    'whatsappNumber' => null,
])

@php
    $hotelSummary = filled($package->hotel_information)
        ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags($package->hotel_information)) ?? '', 58)
        : 'Butiran hotel akan dikemas kini oleh agensi.';

    $whatsAppMessage = 'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.';
@endphp

<article class="group flex h-full flex-col overflow-hidden rounded-2xl border border-[#e9e4d9] bg-white shadow-[0_12px_30px_-22px_rgba(0,39,31,0.36)] transition duration-300 hover:-translate-y-1 hover:shadow-[0_18px_36px_-20px_rgba(0,39,31,0.42)]">
    <a href="{{ route('packages.show', $package) }}" class="relative block h-52 overflow-hidden bg-[#e4e2de] sm:h-56">
        @if ($package->imageUrl())
            <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @else
            <div class="grid size-full place-items-center bg-gradient-to-br from-[#073e34] to-[#00271f] text-[#ffdf9a]">
                <span class="material-symbols-outlined text-5xl">mosque</span>
            </div>
        @endif
        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#00271f]/65 to-transparent"></div>
        <span class="absolute left-4 top-4 rounded-full bg-[#003d32] px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.1em] text-[#ffdf9a]">{{ $package->category->name }}</span>
        <span class="absolute bottom-4 right-4 rounded-full bg-white/95 px-3 py-1.5 text-[10px] font-bold text-dark-green shadow-sm">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</span>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <div>
            <h3 class="font-display text-[25px] leading-[1.12] text-dark-green">
                <a href="{{ route('packages.show', $package) }}">{{ $package->title }}</a>
            </h3>
            <p class="mt-2 line-clamp-2 text-[11px] leading-5 text-[#66706c]">{{ $package->short_description }}</p>
        </div>

        <div class="mt-5 space-y-2.5 border-y border-[#f0ece4] py-4 text-[10px] leading-4 text-[#59635f]">
            @if ($package->departures->first())
                <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">calendar_month</span><span>Berlepas {{ $package->departures->first()->departure_date->translatedFormat('d M Y') }}</span></p>
            @endif
            <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">hotel</span><span>{{ $hotelSummary }}</span></p>
            <p class="flex items-start gap-2"><span class="material-symbols-outlined mt-px text-[15px] text-[#9a762e]">info</span><span>Harga dan ketersediaan tertakluk kepada pengesahan agensi.</span></p>
        </div>

        <div class="mt-auto flex items-end justify-between gap-4 pt-5">
            <div>
                <p class="text-[9px] font-medium uppercase tracking-[0.12em] text-[#7d8581]">Bermula dari</p>
                <p class="mt-1 font-display text-[27px] leading-none text-dark-green">{{ $package->formattedStartingPrice() }}</p>
                <p class="mt-1 text-[9px] text-[#7d8581]">seorang</p>
            </div>

            @if (filled($whatsappNumber))
                <x-whats-app-link
                    :number="$whatsappNumber"
                    :message="$whatsAppMessage"
                    :fallback="route('contact.create', ['package_id' => $package->id])"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-dark-green px-3 py-2.5 text-[10px] font-bold text-[#ffdf9a] transition hover:bg-[#104f43]"
                    aria-label="Tanya tentang {{ $package->title }} melalui WhatsApp"
                >
                    Tanya <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </x-whats-app-link>
            @else
                <a href="{{ route('packages.show', $package) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-dark-green px-3 py-2.5 text-[10px] font-bold text-[#ffdf9a] transition hover:bg-[#104f43]">
                    Lihat pakej <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                </a>
            @endif
        </div>
    </div>
</article>

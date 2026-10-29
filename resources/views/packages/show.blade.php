@php
    $packageUrl = route('packages.show', $package);
    $packageMessage = 'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.'."\n\n".$packageUrl;
    $packageImages = $package->images->unique('path')->values();
    $heroImagePath = $package->cover_image ?: $packageImages->first()?->path;
    $heroGalleryImages = $packageImages
        ->reject(fn (\App\Models\PackageImage $image): bool => $image->path === $heroImagePath)
        ->take(2)
        ->values();
    $heroImageCount = collect([$heroImagePath])
        ->merge($heroGalleryImages->pluck('path'))
        ->filter()
        ->count();
    $firstDeparture = $package->departures->first();
    $hotelOptions = collect($package->hotel_options ?? [])
        ->filter(fn (mixed $hotel): bool => is_array($hotel) && filled($hotel['name'] ?? null))
        ->values();
    $roomImagesByHotel = collect($package->room_prices ?? [])
        ->filter(fn (mixed $roomPrice): bool => is_array($roomPrice) && filled($roomPrice['hotel_key'] ?? null) && filled($roomPrice['image'] ?? null))
        ->groupBy('hotel_key')
        ->map(fn ($roomPrices) => $roomPrices
            ->pluck('image')
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path))
            ->values());
    $flightOptions = collect($package->flight_options ?? [])
        ->filter(fn (mixed $flight): bool => is_array($flight) && filled($flight['airline'] ?? null))
        ->values();
    $ziarahPlaces = collect($package->ziarah_places ?? [])
        ->filter(fn (mixed $place): bool => is_array($place) && filled($place['name'] ?? null))
        ->values();
    $gifts = collect($package->gifts ?? [])
        ->filter(fn (mixed $gift): bool => is_array($gift) && filled($gift['title'] ?? null))
        ->values();

    if ($hotelOptions->isEmpty()) {
        $hotelOptions = collect(array_filter([
            filled($package->makkah_hotel) ? [
                'location' => 'Makkah',
                'name' => $package->makkah_hotel,
                'rating' => $package->makkah_hotel_rating,
                'nights' => $package->makkah_nights,
                'walking_distance' => $package->walking_distance,
                'notes' => $package->hotel_information,
                'images' => [],
            ] : null,
            filled($package->madinah_hotel) ? [
                'location' => 'Madinah',
                'name' => $package->madinah_hotel,
                'rating' => $package->madinah_hotel_rating,
                'nights' => $package->madinah_nights,
                'walking_distance' => null,
                'notes' => null,
                'images' => [],
            ] : null,
        ]));
    }

    if ($flightOptions->isEmpty() && filled($package->airline)) {
        $flightOptions = collect([[
            'airline' => $package->airline,
            'departure_airport' => $package->departure_airport,
            'arrival_airport' => $package->arrival_airport,
            'notes' => $package->flight_information,
            'images' => [],
        ]]);
    }

    $packageNavItems = [
        ['label' => 'Pengenalan', 'target' => 'pengenalan-pakej'],
        ['label' => 'Itinerari', 'target' => 'itinerari-pakej', 'visible' => $package->itineraries->isNotEmpty()],
        ['label' => 'Penginapan', 'target' => 'penginapan-pakej', 'visible' => $hotelOptions->isNotEmpty()],
        ['label' => 'Penerbangan', 'target' => 'penerbangan-pakej', 'visible' => $flightOptions->isNotEmpty()],
        ['label' => 'Termasuk & Tidak', 'target' => 'termasuk-pakej'],
        ['label' => 'Harga & Tarikh', 'target' => 'harga-tarikh-pakej'],
    ];
@endphp

<x-layouts.app
    :title="$package->automaticSeoTitle()"
    :meta-description="$package->automaticSeoDescription()"
    :meta-image="$package->posterImageUrl()"
    :whats-app-message="$packageMessage"
>
    <section class="border-b border-[#e5eadf] bg-[#f7f8f4]">
        <div class="container-page flex flex-wrap items-center gap-1.5 py-2.5 text-[10px] font-semibold text-[#65716c]">
            <a href="{{ route('home') }}" class="transition hover:text-dark-green">Utama</a><span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <a href="{{ route('packages.index') }}" class="transition hover:text-dark-green">Pakej Umrah</a><span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="truncate text-dark-green">{{ $package->title }}</span>
        </div>
    </section>

    <section class="bg-[#f7f8f4]">
        <div @class([
            'grid',
            'lg:grid-cols-[minmax(0,1.62fr)_minmax(300px,.78fr)]' => $heroGalleryImages->isNotEmpty(),
        ])>
            <article class="package-cover grid overflow-hidden bg-[#082e27] lg:grid-cols-[minmax(0,.9fr)_minmax(21rem,1.1fr)]" data-package-cover>
                @if (filled($heroImagePath))
                    <div class="package-cover__media relative grid min-h-[26rem] place-items-center overflow-hidden bg-[radial-gradient(circle_at_center,rgba(255,224,128,.14),transparent_50%),linear-gradient(135deg,#0b4c3f,#04271f)] p-4 sm:min-h-[34rem] sm:p-7 lg:min-h-full">
                        <img src="{{ $package->travelImageUrl($heroImagePath) }}" alt="{{ $package->title }}" class="relative h-auto max-h-[34rem] w-auto max-w-full shadow-[0_22px_45px_-24px_rgba(0,0,0,.8)] object-contain" loading="eager" fetchpriority="high" data-package-cover-image>
                    </div>
                @else
                    <div class="grid min-h-[26rem] place-items-center bg-[radial-gradient(circle_at_70%_15%,#186c57,transparent_45%),linear-gradient(135deg,#003c32,#001e19)] text-[#ffe3a0] sm:min-h-[34rem] lg:min-h-full"><span class="material-symbols-outlined text-8xl">mosque</span></div>
                @endif
                <div class="package-cover__details flex flex-col justify-end p-6 text-white sm:p-8 lg:p-10">
                    @php($promotionBadge = $package->promotionBadge())
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-[#ffe080] px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[.08em] text-[#234133]">{{ $package->category->name }}</span>
                        @if ($promotionBadge)<span @class(['inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[.08em] text-white', 'bg-[#9d7628]' => $promotionBadge['type'] === \App\Models\Package::RecommendationBadge, 'bg-[#d92048]' => $promotionBadge['type'] === \App\Models\Package::SuperDealBadge])><span class="material-symbols-outlined text-[14px]">{{ $promotionBadge['icon'] }}</span>{{ $promotionBadge['label'] }}</span>@endif
                        @if (filled($package->season_label))<span class="rounded-full border border-white/30 bg-white/15 px-3 py-1.5 text-[10px] font-bold tracking-[.04em] text-white/95 backdrop-blur">{{ $package->season_label }}</span>@endif
                    </div>
                    <h1 class="mt-4 max-w-3xl font-display text-4xl leading-[1.06] text-white sm:text-5xl">{{ $package->title }}</h1>
                    <dl class="mt-7 grid gap-4 border-t border-white/25 pt-5 sm:grid-cols-2">
                        <div class="flex items-center gap-2.5"><span class="grid size-9 place-items-center rounded-lg bg-[#ffe080] text-[#183f32]"><span class="material-symbols-outlined text-[20px]">calendar_month</span></span><div><dt class="text-[8px] font-bold uppercase tracking-[.1em] text-white/60">Tempoh</dt><dd class="mt-0.5 text-[11px] font-bold">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</dd></div></div>
                        <div class="flex items-center gap-2.5"><span class="grid size-9 place-items-center rounded-lg bg-[#ffe080] text-[#183f32]"><span class="material-symbols-outlined text-[20px]">hotel</span></span><div><dt class="text-[8px] font-bold uppercase tracking-[.1em] text-white/60">Hotel</dt><dd class="mt-0.5 line-clamp-2 text-[11px] font-bold">{{ $package->makkah_hotel ?: 'Mengikut susunan pakej' }}</dd></div></div>
                        <div class="flex items-center gap-2.5"><span class="grid size-9 place-items-center rounded-lg bg-[#ffe080] text-[#183f32]"><span class="material-symbols-outlined text-[20px]">flight</span></span><div><dt class="text-[8px] font-bold uppercase tracking-[.1em] text-white/60">Penerbangan</dt><dd class="mt-0.5 line-clamp-2 text-[11px] font-bold">{{ $package->airline ?: 'Akan disahkan' }}</dd></div></div>
                        <div class="flex items-center gap-2.5"><span class="grid size-9 place-items-center rounded-lg bg-[#ffe080] text-[#183f32]"><span class="material-symbols-outlined text-[20px]">groups</span></span><div><dt class="text-[8px] font-bold uppercase tracking-[.1em] text-white/60">Kumpulan</dt><dd class="mt-0.5 text-[11px] font-bold">{{ $package->is_date_flexible ? 'Butiran akan disahkan' : ($firstDeparture ? 'Sehingga '.$firstDeparture->capacity.' jemaah' : 'Tarikh akan dibuka') }}</dd></div></div>
                    </dl>
                </div>
            </article>

            @if ($heroGalleryImages->isNotEmpty())
                <div class="hidden min-h-[550px] grid-cols-2 grid-rows-2 lg:grid">
                    @foreach ($heroGalleryImages as $index => $image)
                        <figure class="group relative col-span-2 overflow-hidden bg-[#0a3029]">
                            <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $package->title }}" class="size-full object-cover transition duration-500 group-hover:scale-105" style="object-position: {{ $imageFocusPositions[$image->path] ?? '50% 50%' }}" loading="lazy">
                            @if ($index === 0)
                                <figcaption class="absolute inset-x-0 bottom-0 flex justify-end bg-gradient-to-t from-black/65 to-transparent px-4 pb-3 pt-10"><a href="#penginapan-pakej" class="inline-flex items-center gap-1 rounded-lg bg-black/55 px-2.5 py-1.5 text-[10px] font-bold text-white backdrop-blur transition hover:bg-black/75"><span class="material-symbols-outlined text-[15px]">photo_library</span>{{ $heroImageCount }} foto galeri</a></figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <nav class="sticky top-[72px] z-30 border-b border-[#e3e8df] bg-white/95 shadow-[0_8px_18px_-18px_rgba(0,45,36,.55)] backdrop-blur" aria-label="Navigasi pakej">
        <div class="container-page no-scrollbar flex gap-6 overflow-x-auto py-3 text-[11px] font-bold whitespace-nowrap text-[#637069]">
            @foreach ($packageNavItems as $item)
                @if ($item['visible'] ?? true)<a href="#{{ $item['target'] }}" class="flex items-center gap-1.5 transition hover:text-dark-green"><span class="material-symbols-outlined text-[16px] text-[#b58d31]">{{ $item['target'] === 'itinerari-pakej' ? 'event_note' : ($item['target'] === 'penginapan-pakej' ? 'hotel' : ($item['target'] === 'penerbangan-pakej' ? 'flight' : ($item['target'] === 'harga-tarikh-pakej' ? 'calendar_month' : 'info'))) }}</span>{{ $item['label'] }}</a>@endif
            @endforeach
        </div>
    </nav>

    <section class="container-page py-10 sm:py-14">
        <div class="grid items-start gap-10 xl:grid-cols-[minmax(0,1fr)_330px]">
            <div class="min-w-0 space-y-14">
                <section id="pengenalan-pakej" class="scroll-mt-36">
                    <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#eff8f0] text-[#006b57]"><span class="material-symbols-outlined text-[20px]">menu_book</span></span><div><p class="section-eyebrow">Tentang perjalanan</p><h2 class="mt-1 font-display text-3xl text-dark-green sm:text-4xl">Pengenalan Pakej</h2></div></div>
                    <div class="mt-5 max-w-4xl space-y-4 text-sm leading-8 text-[#53615b] sm:text-[15px]">{!! $package->descriptionForDisplay() !!}</div>
                </section>

                @if ($package->itineraries->isNotEmpty())
                    <section id="itinerari-pakej" class="scroll-mt-36">
                        <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#fff6d9] text-[#8b671c]"><span class="material-symbols-outlined text-[20px]">event_note</span></span><div><p class="section-eyebrow">Atur cara ibadah</p><h2 class="mt-1 font-display text-3xl text-dark-green sm:text-4xl">Itinerari Perjalanan ({{ $package->duration_days }} Hari)</h2></div></div>
                        <ol class="mt-7 ml-3 space-y-3 border-l border-[#d7be76] pl-7">
                            @foreach ($package->itineraries as $itinerary)
                                <li class="relative"><span class="absolute -left-[2.12rem] top-5 grid size-4 place-items-center rounded-full border-2 border-[#b58d31] bg-[#fdfbf7]"><span class="size-1.5 rounded-full bg-[#b58d31]"></span></span><article class="grid gap-3 rounded-xl border border-[#e7ece4] bg-white p-3.5 shadow-[0_8px_22px_-20px_rgba(0,54,43,.45)] sm:grid-cols-[5.5rem_minmax(0,1fr)] sm:items-center"><span class="w-fit rounded-lg bg-[#fff5d7] px-3 py-2 text-center text-[11px] font-extrabold text-[#65511b]">Hari {{ $itinerary->day_number }}</span><div><h3 class="text-sm font-bold text-[#163f34]">{{ $itinerary->title }}</h3><p class="mt-1 text-[11px] leading-5 text-[#68746e]">{{ $itinerary->description }}</p></div></article></li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                @if ($hotelOptions->isNotEmpty())
                    <section id="penginapan-pakej" class="scroll-mt-36" data-travel-media>
                        <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#fff6d9] text-[#8b671c]"><span class="material-symbols-outlined text-[20px]">bed</span></span><div><p class="section-eyebrow">Rehat dengan selesa</p><h2 class="mt-1 font-display text-3xl text-dark-green sm:text-4xl">Pilihan Penginapan</h2></div></div>
                        <div class="mt-7 grid gap-5 md:grid-cols-2">
                            @foreach ($hotelOptions as $hotel)
                                @php($hotelImages = collect($hotel['images'] ?? [])->merge($roomImagesByHotel->get($hotel['key'] ?? '', []))->filter(fn (mixed $path): bool => is_string($path) && filled($path))->unique()->values())
                                <article class="overflow-hidden rounded-2xl border border-[#e4e9e1] bg-white shadow-[0_14px_30px_-26px_rgba(0,50,40,.55)]">
                                    @if ($hotelImages->isNotEmpty())
                                        <div class="relative" data-carousel data-autoplay="5000">
                                            <div data-carousel-track class="overflow-x-auto scroll-smooth"><div class="flex gap-0">@foreach ($hotelImages as $hotelImagePath)<button type="button" data-hotel-lightbox-trigger data-hotel-lightbox-src="{{ $package->travelImageUrl($hotelImagePath) }}" data-hotel-lightbox-alt="{{ $hotel['name'] }}" class="block aspect-[16/10] w-full shrink-0 cursor-zoom-in overflow-hidden text-left focus-visible:outline-2 focus-visible:outline-offset-[-3px] focus-visible:outline-[#ffdf9a]" aria-label="Lihat gambar {{ $hotel['name'] }}"><img src="{{ $package->travelImageUrl($hotelImagePath) }}" alt="{{ $hotel['name'] }}" class="size-full object-cover" style="object-position: {{ $imageFocusPositions[$hotelImagePath] ?? '50% 50%' }}" loading="lazy"></button>@endforeach</div></div>
                                            @if ($hotelImages->count() > 1)<button type="button" data-carousel-prev class="carousel-control absolute left-3 top-1/2 -translate-y-1/2" aria-label="Gambar penginapan sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button><button type="button" data-carousel-next class="carousel-control absolute right-3 top-1/2 -translate-y-1/2" aria-label="Gambar penginapan seterusnya"><span class="material-symbols-outlined">chevron_right</span></button>@endif
                                        </div>
                                    @else
                                        <div class="grid aspect-[16/10] place-items-center bg-[linear-gradient(135deg,#eaf4ec,#d9eadb)] text-[#23715c]"><span class="material-symbols-outlined text-5xl">hotel</span></div>
                                    @endif
                                    <div class="p-4 sm:p-5"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-bold uppercase tracking-[.1em] text-[#94722a]">{{ $hotel['location'] ?? 'Penginapan' }}</span><h3 class="mt-1 text-base font-extrabold text-[#163f34]">{{ $hotel['name'] }}</h3></div>@if (filled($hotel['rating'] ?? null))<span class="shrink-0 rounded-lg bg-[#ffe083] px-2 py-1 text-[10px] font-bold text-[#59480f]">{{ $hotel['rating'] }} Bintang</span>@endif</div><div class="mt-4 flex flex-wrap gap-x-4 gap-y-2 text-[11px] font-semibold text-[#607069]">@if (filled($hotel['walking_distance'] ?? null))<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-[#08725a]">location_on</span>{{ $hotel['walking_distance'] }}</span>@endif @if (filled($hotel['nights'] ?? null))<span class="inline-flex items-center gap-1"><span class="material-symbols-outlined text-[15px] text-[#08725a]">nights_stay</span>{{ $hotel['nights'] }} malam</span>@endif</div>@if (filled($hotel['notes'] ?? null))<p class="mt-3 text-[11px] leading-5 text-[#68746e]">{{ $hotel['notes'] }}</p>@endif</div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($flightOptions->isNotEmpty())
                    <section id="penerbangan-pakej" class="scroll-mt-36">
                        <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#fff6d9] text-[#8b671c]"><span class="material-symbols-outlined text-[20px]">flight_takeoff</span></span><div><p class="section-eyebrow">Perjalanan udara</p><h2 class="mt-1 font-display text-3xl text-dark-green sm:text-4xl">Pilihan Penerbangan</h2></div></div>
                        <div class="mt-7 space-y-3">
                            @foreach ($flightOptions as $flight)
                                @php($flightImages = collect($flight['images'] ?? [])->filter(fn (mixed $path): bool => is_string($path) && filled($path))->values())
                                <article class="overflow-hidden rounded-xl border border-[#e4e9e1] bg-white"><div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5"><div class="flex min-w-0 items-center gap-3"><span class="grid size-11 shrink-0 place-items-center overflow-hidden rounded-xl bg-[#eff8f0] text-[#08725a]">@if ($flightImages->isNotEmpty())<img src="{{ $package->travelImageUrl($flightImages->first()) }}" alt="" class="size-full object-contain p-1.5">@else<span class="material-symbols-outlined text-[23px]">flight</span>@endif</span><div><h3 class="text-sm font-extrabold text-[#163f34]">{{ $flight['airline'] }}</h3><p class="mt-1 text-[11px] text-[#68746e]">{{ implode(' → ', array_filter([$flight['departure_airport'] ?? null, $flight['arrival_airport'] ?? null])) ?: 'Butiran laluan akan disahkan.' }}</p>@if (filled($flight['notes'] ?? null))<p class="mt-1 text-[11px] text-[#68746e]">{{ $flight['notes'] }}</p>@endif</div></div><span class="inline-flex w-fit items-center gap-1.5 rounded-lg bg-[#f6f8f5] px-3 py-2 text-[10px] font-bold text-[#557067]"><span class="material-symbols-outlined text-[15px]">schedule</span>Tertakluk pengesahan</span></div>@if ($flightImages->count() > 1)<div class="relative border-t border-[#edf0eb]" data-carousel data-autoplay="5000"><div data-carousel-track class="overflow-x-auto scroll-smooth"><div class="flex gap-0">@foreach ($flightImages as $flightImagePath)<img src="{{ $package->travelImageUrl($flightImagePath) }}" alt="{{ $flight['airline'] }}" class="aspect-[16/7] w-full shrink-0 object-cover" style="object-position: {{ $imageFocusPositions[$flightImagePath] ?? '50% 50%' }}" loading="lazy">@endforeach</div></div><button type="button" data-carousel-prev class="carousel-control absolute left-3 top-1/2 -translate-y-1/2" aria-label="Gambar penerbangan sebelumnya"><span class="material-symbols-outlined">chevron_left</span></button><button type="button" data-carousel-next class="carousel-control absolute right-3 top-1/2 -translate-y-1/2" aria-label="Gambar penerbangan seterusnya"><span class="material-symbols-outlined">chevron_right</span></button></div>@endif</article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($ziarahPlaces->isNotEmpty() || $gifts->isNotEmpty())
                    <section class="grid gap-5 md:grid-cols-2">
                        @if ($ziarahPlaces->isNotEmpty())<article class="rounded-2xl border border-[#e4e9e1] bg-white p-5"><h2 class="flex items-center gap-2 text-xl font-display text-dark-green"><span class="material-symbols-outlined text-[#987a2f]">location_city</span>Lokasi ziarah</h2><div class="mt-4 space-y-3">@foreach ($ziarahPlaces as $place)@php($placeImage = $place['image'] ?? null)<div class="flex gap-3 border-b border-[#edf0eb] pb-3 last:border-0 last:pb-0">@if (filled($placeImage))<img src="{{ $package->travelImageUrl($placeImage) }}" alt="{{ $place['name'] }}" class="size-[4.5rem] shrink-0 rounded-xl object-cover" style="object-position: {{ $imageFocusPositions[$placeImage] ?? '50% 50%' }}" loading="lazy">@else<span class="grid size-[4.5rem] shrink-0 place-items-center rounded-xl bg-[#f1f8f1] text-[#258064]"><span class="material-symbols-outlined text-[27px]">location_on</span></span>@endif<div class="min-w-0"><h3 class="text-xs font-bold text-[#174639]">{{ $place['name'] }}@if (filled($place['location'] ?? null)) <span class="font-medium text-[#708078]">· {{ $place['location'] }}</span>@endif</h3>@if (filled($place['description'] ?? null))<p class="mt-1 text-[11px] leading-5 text-[#68746e]">{{ $place['description'] }}</p>@endif</div></div>@endforeach</div></article>@endif
                        @if ($gifts->isNotEmpty())<article class="rounded-2xl border border-[#e4e9e1] bg-white p-5"><h2 class="flex items-center gap-2 text-xl font-display text-dark-green"><span class="material-symbols-outlined text-[#987a2f]">redeem</span>Hadiah jemaah</h2><div class="mt-4 space-y-3">@foreach ($gifts as $gift)@php($giftImage = $gift['image'] ?? null)<div class="flex gap-3 border-b border-[#edf0eb] pb-3 last:border-0 last:pb-0">@if (filled($giftImage))<img src="{{ $package->travelImageUrl($giftImage) }}" alt="{{ $gift['title'] }}" class="size-[4.5rem] shrink-0 rounded-xl object-cover" style="object-position: {{ $imageFocusPositions[$giftImage] ?? '50% 50%' }}" loading="lazy">@else<span class="grid size-[4.5rem] shrink-0 place-items-center rounded-xl bg-[#fff8e8] text-[#9b782d]"><span class="material-symbols-outlined text-[27px]">redeem</span></span>@endif<div class="min-w-0"><h3 class="text-xs font-bold text-[#174639]">{{ $gift['title'] }}</h3>@if (filled($gift['description'] ?? null))<p class="mt-1 text-[11px] leading-5 text-[#68746e]">{{ $gift['description'] }}</p>@endif</div></div>@endforeach</div></article>@endif
                    </section>
                @endif

                <section id="termasuk-pakej" class="scroll-mt-36">
                    <div class="flex items-center gap-3"><span class="grid size-9 place-items-center rounded-xl bg-[#eff8f0] text-[#08725a]"><span class="material-symbols-outlined text-[20px]">fact_check</span></span><div><p class="section-eyebrow">Maklumat kos</p><h2 class="mt-1 font-display text-3xl text-dark-green sm:text-4xl">Termasuk &amp; Tidak Termasuk</h2></div></div>
                    <div class="mt-7 grid gap-4 md:grid-cols-2">
                        <article class="rounded-2xl border border-[#dceee0] bg-[#f3fbf3] p-5"><h3 class="flex items-center gap-2 text-sm font-extrabold text-[#183f34]"><span class="grid size-6 place-items-center rounded-full bg-[#15805e] text-white"><span class="material-symbols-outlined text-[16px]">check</span></span>Termasuk dalam Pakej</h3><ul class="mt-4 space-y-2.5 text-[12px] leading-5 text-[#446056]">@forelse ($package->inclusions ?? [] as $item)<li class="flex gap-2"><span class="font-extrabold text-[#168061]">✓</span><span>{{ $item }}</span></li>@empty<li>Butiran akan disahkan oleh agensi.</li>@endforelse</ul></article>
                        <article class="rounded-2xl border border-[#f0dddd] bg-[#fff4f3] p-5"><h3 class="flex items-center gap-2 text-sm font-extrabold text-[#493131]"><span class="grid size-6 place-items-center rounded-full bg-[#be4044] text-white"><span class="material-symbols-outlined text-[16px]">close</span></span>Tidak Termasuk dalam Pakej</h3><ul class="mt-4 space-y-2.5 text-[12px] leading-5 text-[#654b4b]">@forelse ($package->exclusions ?? [] as $item)<li class="flex gap-2"><span class="font-extrabold text-[#be4044]">✓</span><span>{{ $item }}</span></li>@empty<li>Butiran akan disahkan oleh agensi.</li>@endforelse</ul></article>
                    </div>
                </section>
            </div>

            <aside id="harga-tarikh-pakej" class="scroll-mt-36 xl:sticky xl:top-32">
                <section class="overflow-hidden rounded-2xl border border-[#e2e9e0] bg-white shadow-[0_18px_42px_-30px_rgba(0,50,40,.55)]">
                    <div class="border-b border-[#e8ece5] px-5 py-5"><div class="flex items-start justify-between gap-3"><div><p class="text-[9px] font-extrabold uppercase tracking-[.15em] text-[#7c8881]">Harga dari</p><p class="mt-1 font-display text-4xl leading-none text-[#123d32]">{{ $package->formattedStartingPrice() }}</p><p class="mt-2 text-[10px] text-[#748078]">Seorang · tertakluk kepada pengesahan</p></div><span class="rounded-lg bg-[#fff4ce] px-2.5 py-1.5 text-[9px] font-bold text-[#6e5718]">{{ $package->category->name }}</span></div>
                    @if (collect($package->room_prices ?? [])->isNotEmpty())<div class="mt-4 space-y-2 border-t border-[#e8ece5] pt-4">@foreach ($package->room_prices as $roomPrice)<div class="flex items-center justify-between gap-3 text-[11px]"><span class="flex min-w-0 items-center gap-2 text-[#607069]">@if (filled($roomPrice['image'] ?? null))<img src="{{ $package->travelImageUrl($roomPrice['image']) }}" alt="" class="size-8 shrink-0 rounded-md object-cover" style="object-position: {{ $imageFocusPositions[$roomPrice['image']] ?? '50% 50%' }}">@endif<span>{{ $roomPrice['room_type'] ?? '' }}</span></span><span class="shrink-0 font-bold text-[#183f34]">RM {{ number_format(($roomPrice['price_sen'] ?? 0) / 100, 2) }}</span></div>@endforeach</div>@endif</div>
                    <div class="px-5 py-5">
                        @if ($package->is_date_flexible)
                            <div class="flex gap-3 rounded-xl bg-[#f5f8f4] p-3.5 text-[11px] leading-5 text-[#68746e]">
                                <span class="material-symbols-outlined shrink-0 text-[18px] text-[#8f7129]">info</span>
                                <p>Tarikh perjalanan akan disahkan oleh agensi. Hubungi kami untuk memilih bulan yang sesuai.</p>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-3"><h2 class="flex items-center gap-2 text-sm font-extrabold text-[#183f34]"><span class="material-symbols-outlined text-[18px] text-[#8f7129]">calendar_month</span>Tarikh Berlepas</h2><span class="text-[10px] font-bold text-[#08725a]">{{ $package->departures->count() }} jadual dipaparkan</span></div>
                        <div class="mt-4 space-y-2.5">
                            @forelse ($package->departures as $departure)
                                @php($isDepartureOpen = $departure->status === 'open')
                                @php($departureMessage = 'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.' . "\nTarikh berlepas yang saya minat: " . $departure->departure_date->translatedFormat('d M Y') . ".\n\n" . $packageUrl)
                                <article class="rounded-xl border border-[#e6ebe5] bg-[#fbfcfa] p-3.5"><div class="flex items-start justify-between gap-3"><div><p class="text-xs font-extrabold text-[#183f34]">{{ $departure->departure_date->translatedFormat('d M Y') }}</p><p class="mt-1 text-[10px] leading-4 text-[#718078]">Pulang {{ $departure->return_date->translatedFormat('d M Y') }}</p><p class="mt-1 text-[10px] leading-4 text-[#718078]">{{ $departure->departure_airport }} · {{ $package->duration_days }}H {{ $package->duration_nights }}M</p></div><div class="text-right"><span @class(['rounded-full px-2 py-1 text-[9px] font-extrabold', 'bg-[#e8f7ee] text-[#08725a]' => $isDepartureOpen, 'bg-[#fff0ed] text-[#a14335]' => ! $isDepartureOpen])>{{ $isDepartureOpen ? 'Dibuka' : 'Ditutup' }}</span><p class="mt-2 text-xs font-extrabold text-[#183f34]">{{ $departure->formattedPrice() }}</p></div></div><div class="mt-3 flex items-center justify-between gap-3">@if ($isDepartureOpen)<span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#718078]"><span class="material-symbols-outlined text-[14px]">group</span>{{ $departure->availableSeats() }} tempat</span>@if (filled($whatsappNumber))<x-whats-app-link :number="$whatsappNumber" :message="$departureMessage" class="text-[10px] font-extrabold text-[#08725a]">Pilih <span aria-hidden="true">→</span></x-whats-app-link>@endif @else <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#a14335]"><span class="material-symbols-outlined text-[14px]">block</span>Tarikh ini tidak lagi tersedia.</span>@endif</div></article>
                            @empty
                                <p class="rounded-xl bg-[#f5f8f4] p-3.5 text-[11px] leading-5 text-[#68746e]">Tarikh baharu akan dikemas kini oleh agensi.</p>
                            @endforelse
                        </div>
                        @endif
                        @if (filled($whatsappNumber))<x-whats-app-link :number="$whatsappNumber" :message="$packageMessage" class="button-gold mt-5 w-full !rounded-xl"><span class="material-symbols-outlined text-[18px]">chat</span>WhatsApp Kami</x-whats-app-link>@endif
                        @if ($package->itineraryPdfUrl())<a href="{{ $package->itineraryPdfUrl() }}" target="_blank" rel="noopener noreferrer" class="button-secondary mt-3 w-full !rounded-xl !px-3 !py-2.5 !text-[11px]"><span class="material-symbols-outlined text-[18px]">download</span>Muat Turun Itinerari (PDF)</a>@endif
                    </div>
                </section>

            </aside>
        </div>
    </section>

    <dialog class="m-auto w-[min(94vw,72rem)] border-0 bg-transparent p-0 text-white backdrop:bg-[#001d16]/90 backdrop:backdrop-blur-sm" data-hotel-lightbox aria-label="Paparan gambar penginapan">
        <div class="relative overflow-hidden rounded-2xl border border-[#d5b469]/60 bg-[#00271f] p-4 shadow-2xl sm:p-6">
            <button type="button" class="absolute right-3 top-3 grid size-10 place-items-center rounded-full border border-[#d5b469]/60 bg-[#073e34] text-white transition hover:bg-[#184e3f] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ffdf9a]" data-hotel-lightbox-close aria-label="Tutup gambar penginapan"><span class="material-symbols-outlined text-[20px]">close</span></button>
            <figure class="m-0 text-center"><img src="" alt="" class="mx-auto max-h-[78vh] max-w-full rounded-lg object-contain" data-hotel-lightbox-image><figcaption class="mt-4 pr-10 text-sm font-semibold text-[#ffdf9a]" data-hotel-lightbox-caption></figcaption></figure>
        </div>
    </dialog>
</x-layouts.app>

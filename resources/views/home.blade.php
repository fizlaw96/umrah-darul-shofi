<x-layouts.app title="Pakej Umrah untuk perjalanan yang lebih tenang">
    @php
        $companyName = $homepageSettings->get('company_name') ?: 'SalamHaji Travel & Tours';
        $companyIntro = $homepageSettings->get('homepage_company_intro') ?: $homepageSettings->get('company_tagline');
        $packageHeading = $homepageSettings->get('homepage_package_heading') ?: 'Pakej Umrah pilihan';
        $campaignTitle = $homepageSettings->get('homepage_campaign_title');
        $campaignLink = $homepageSettings->get('homepage_campaign_link');
        $videoUrl = $homepageSettings->get('homepage_video_url');
        $videoCover = $homepageSettings->get('homepage_video_cover');
        $instagramUrl = $homepageSettings->get('instagram_url');
        $instagramCover = $homepageSettings->get('instagram_preview_image');
        $serviceGridClasses = match ($serviceItems->count()) {
            1 => 'grid-cols-1',
            2 => 'grid-cols-2',
            3 => 'grid-cols-2 sm:grid-cols-3',
            4 => 'grid-cols-2 sm:grid-cols-4',
            5 => 'grid-cols-2 sm:grid-cols-5',
            default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-6',
        };
    @endphp

    <section class="relative isolate min-h-[65svh] overflow-hidden bg-dark-green text-white sm:min-h-[72svh]" data-hero-carousel aria-label="Pakej utama">
        @forelse ($heroPackages as $package)
            @php($heroVideo = $heroVideos[$package->slug] ?? null)
            <article class="hero-slide absolute inset-0 {{ $loop->first ? 'is-active' : '' }}" data-hero-slide aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                @if ($heroVideo)
                    <video class="absolute inset-0 size-full object-cover" muted loop playsinline preload="none" poster="{{ $package->imageUrl() }}" data-hero-video data-video-src="{{ $heroVideo }}"></video>
                @endif
                @if ($package->imageUrl())
                    <img src="{{ $package->imageUrl() }}" alt="" class="absolute inset-0 -z-10 size-full object-cover {{ $heroVideo ? 'opacity-0' : '' }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" @if ($loop->first) fetchpriority="high" @endif>
                @endif
                <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(0,29,24,.92)_0%,rgba(0,45,37,.76)_43%,rgba(0,24,20,.24)_100%)]"></div>
            </article>
        @empty
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_78%_25%,rgba(213,180,105,.24),transparent_22rem),linear-gradient(120deg,#00271f,#073e34)]"></div>
        @endforelse

        <div class="container-page relative flex min-h-[65svh] items-end py-14 sm:min-h-[72svh] sm:py-20">
            @if ($heroPackages->isNotEmpty())
                @foreach ($heroPackages as $package)
                    <div class="hero-content max-w-2xl {{ $loop->first ? 'is-active' : '' }}" data-hero-content aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <p class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.18em] text-[#ffdf9a]">
                            <span class="size-1.5 rounded-full bg-[#ffdf9a]"></span>
                            {{ $packageHeading }}
                        </p>
                        <h1 class="mt-4 font-display text-5xl leading-[.96] tracking-[-0.045em] text-white sm:text-6xl lg:text-8xl">
                            {{ $package->title }}
                        </h1>
                        <p class="mt-5 max-w-xl text-sm leading-7 text-emerald-50/85 sm:text-base">
                            {{ \Illuminate\Support\Str::limit($package->short_description, 145) }}
                        </p>
                        <div class="mt-8 flex flex-wrap gap-3">
                            <a href="{{ route('packages.show', $package) }}" class="button-gold !rounded-full !px-5 !py-3">
                                Lihat Pakej <span class="material-symbols-outlined text-[17px]">arrow_forward</span>
                            </a>
                            @if (filled($whatsappNumber))
                                <x-whats-app-link
                                    :number="$whatsappNumber"
                                    :message="'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.'"
                                    class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-5 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white hover:text-dark-green"
                                >
                                    Tanya di WhatsApp
                                </x-whats-app-link>
                            @endif
                        </div>
                    </div>
                @endforeach
            @else
                <div class="max-w-2xl">
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-[#ffdf9a]">{{ $companyName }}</p>
                    <h1 class="mt-4 font-display text-5xl leading-[.96] tracking-[-0.045em] sm:text-7xl">Perjalanan suci bermula dengan persediaan yang baik.</h1>
                    <p class="mt-5 max-w-xl text-sm leading-7 text-emerald-50/85">Pakej yang telah diterbitkan akan dipaparkan di sini oleh pihak agensi.</p>
                    <a href="{{ route('packages.index') }}" class="button-gold mt-8 !rounded-full">Lihat Pakej <span class="material-symbols-outlined text-[17px]">arrow_forward</span></a>
                </div>
            @endif
        </div>

        @if ($heroPackages->count() > 1)
            <div class="container-page absolute inset-x-0 bottom-7 z-10 flex items-center justify-between gap-5 sm:bottom-9">
                <div class="flex items-center gap-2" role="tablist" aria-label="Pilih pakej utama">
                    @foreach ($heroPackages as $package)
                        <button class="hero-indicator {{ $loop->first ? 'is-active' : '' }}" type="button" data-hero-indicator="{{ $loop->index }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" aria-label="Paparkan {{ $package->title }}"></button>
                    @endforeach
                </div>
                <div class="flex items-center gap-2">
                    <button class="hero-control" type="button" data-hero-prev aria-label="Pakej sebelumnya"><span class="material-symbols-outlined text-[19px]">west</span></button>
                    <button class="hero-control" type="button" data-hero-next aria-label="Pakej seterusnya"><span class="material-symbols-outlined text-[19px]">east</span></button>
                    <button class="hero-control" type="button" data-hero-pause aria-label="Jeda tayangan automatik" aria-pressed="false"><span class="material-symbols-outlined text-[19px]" data-hero-pause-icon>pause</span></button>
                </div>
            </div>
        @endif
    </section>

    @if ($partnerLogos->isNotEmpty())
        <section class="overflow-hidden border-b border-[#eee8dd] bg-white py-5" aria-label="Rakan dan akreditasi">
            <div class="logo-marquee" data-marquee>
                <div class="logo-marquee__track">
                    @foreach ($partnerLogos as $logo)
                        <div class="logo-marquee__item"><img src="{{ $logo['url'] }}" alt="{{ $logo['alt'] }}" loading="lazy"></div>
                    @endforeach
                    @foreach ($partnerLogos as $logo)
                        <div class="logo-marquee__item" aria-hidden="true"><img src="{{ $logo['url'] }}" alt="" loading="lazy"></div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="container-page py-18 sm:py-24">
        <div class="grid gap-10 lg:grid-cols-[1.05fr_.95fr] lg:items-center">
            <div class="order-2 lg:order-1">
                <p class="section-eyebrow">Tentang {{ $companyName }}</p>
                <h2 class="mt-3 max-w-xl font-display text-5xl leading-[.98] tracking-[-0.04em] text-dark-green sm:text-6xl">Perjalanan suci, amanah kami.</h2>
                <p class="mt-6 max-w-xl text-sm leading-7 text-[#65706b] sm:text-base">{{ $companyIntro }}</p>
                <a href="{{ route('about') }}" class="button-primary mt-8 !rounded-full !px-5">Kenali Kami <span class="material-symbols-outlined text-[17px]">arrow_forward</span></a>
            </div>
            <div class="order-1 grid min-h-[360px] grid-cols-12 grid-rows-6 gap-3 sm:min-h-[470px] lg:order-2">
                @forelse ($aboutImages->take(3) as $image)
                    <figure class="overflow-hidden rounded-2xl bg-[#e7e4de] shadow-[0_20px_45px_-25px_rgba(0,39,31,.5)] {{ $loop->first ? 'col-span-7 row-span-6' : 'col-span-5 row-span-3' }}">
                        <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}" class="size-full object-cover" loading="lazy">
                    </figure>
                @empty
                    <div class="col-span-12 row-span-6 grid place-items-center rounded-2xl bg-[radial-gradient(circle_at_70%_30%,rgba(213,180,105,.34),transparent_12rem),linear-gradient(135deg,#003d32,#00271f)] text-center text-[#ffdf9a]">
                        <span class="material-symbols-outlined text-5xl">mosque</span>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="container-page pb-16 sm:pb-20">
        <div class="grid overflow-hidden rounded-2xl border border-[#e9e2d6] bg-white shadow-[0_16px_36px_-28px_rgba(0,39,31,.35)] {{ $serviceGridClasses }}">
            @foreach ($serviceItems as $service)
                <a href="{{ $service['url'] }}" class="group grid min-h-32 place-items-center border-[#eee8dd] px-3 py-5 text-center transition hover:bg-[#fbf8f1] {{ ! $loop->last ? 'border-r' : '' }}">
                    <span class="grid size-12 place-items-center rounded-full border border-[#d5b469] text-[#9a762e] transition duration-300 group-hover:-translate-y-1 group-hover:bg-[#f9f0da]"><span class="material-symbols-outlined text-[22px]">{{ $service['icon'] }}</span></span>
                    <span class="mt-3 text-[10px] font-extrabold tracking-[0.06em] text-dark-green">{{ $service['label'] }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <section id="pakej-pilihan" class="scroll-mt-24 bg-[#fbf8f1] py-16 sm:py-22">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <div>
                    <p class="section-eyebrow">Pilihan semasa</p>
                    <h2 class="mt-2 font-display text-4xl leading-tight text-dark-green sm:text-5xl">{{ $packageHeading }}</h2>
                </div>
                <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-dark-green transition hover:text-[#9a762e]">Lihat semua pakej <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
            </div>

            @if ($heroPackages->isNotEmpty())
                <div class="relative mt-8" data-carousel data-autoplay="6200" aria-label="Pakej Umrah pilihan">
                    <div class="no-scrollbar overflow-x-auto scroll-smooth pb-4" data-carousel-track tabindex="0">
                        <div class="flex w-max gap-4 px-1">
                            @foreach ($heroPackages as $package)
                                <article class="group w-[84vw] max-w-sm shrink-0 overflow-hidden rounded-2xl border border-[#e8e1d6] bg-white shadow-[0_18px_38px_-26px_rgba(0,39,31,.4)] sm:w-[calc((min(100vw-4rem,1280px)-1rem)/2)] lg:w-[calc((min(100vw-6rem,1280px)-2rem)/3)] xl:w-[calc((min(100vw-6rem,1280px)-3rem)/4)]">
                                    <a href="{{ route('packages.show', $package) }}" class="relative block aspect-[1.25/1] overflow-hidden bg-dark-green">
                                        @if ($package->imageUrl())
                                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover transition duration-700 group-hover:scale-105" loading="lazy">
                                        @endif
                                        <div class="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#00271f]/75 to-transparent"></div>
                                        <span class="absolute left-4 top-4 rounded-full bg-dark-green/90 px-3 py-1 text-[9px] font-bold uppercase tracking-[.12em] text-[#ffdf9a]">{{ $package->category->name }}</span>
                                    </a>
                                    <div class="p-5">
                                        <p class="text-[10px] font-bold text-[#7c847f]">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</p>
                                        <h3 class="mt-2 font-display text-[28px] leading-[1.06] text-dark-green"><a href="{{ route('packages.show', $package) }}">{{ $package->title }}</a></h3>
                                        <div class="mt-5 flex items-end justify-between gap-3 border-t border-[#eee9e0] pt-4">
                                            <div><p class="text-[9px] font-bold uppercase tracking-[.1em] text-[#888f8b]">Bermula dari</p><p class="mt-1 font-display text-2xl leading-none text-dark-green">{{ $package->formattedStartingPrice() }}</p></div>
                                            <a href="{{ route('packages.show', $package) }}" class="grid size-10 place-items-center rounded-full bg-dark-green text-[#ffdf9a] transition group-hover:bg-[#0b5547]" aria-label="Lihat {{ $package->title }}"><span class="material-symbols-outlined text-[18px]">arrow_forward</span></a>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-2 flex justify-end gap-2">
                        <button class="carousel-control" type="button" data-carousel-prev aria-label="Pakej sebelumnya"><span class="material-symbols-outlined">west</span></button>
                        <button class="carousel-control" type="button" data-carousel-next aria-label="Pakej seterusnya"><span class="material-symbols-outlined">east</span></button>
                    </div>
                </div>
            @else
                <div class="panel mt-8 p-8 text-center"><p class="font-bold text-dark-green">Pakej akan dipaparkan di sini selepas ia diterbitkan oleh agensi.</p></div>
            @endif
        </div>
    </section>

    @if ($secondaryPackages->isNotEmpty())
        <section class="container-page py-16 sm:py-20">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <div><p class="section-eyebrow">Teroka lebih banyak</p><h2 class="mt-2 font-display text-4xl leading-tight text-dark-green sm:text-5xl">Pilihan perjalanan lain.</h2></div>
                <a href="{{ route('packages.index') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-dark-green transition hover:text-[#9a762e]">Lihat semua <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a>
            </div>
            <div class="relative mt-8" data-carousel data-autoplay="7200" aria-label="Pakej perjalanan lain">
                <div class="no-scrollbar overflow-x-auto scroll-smooth pb-4" data-carousel-track tabindex="0">
                    <div class="flex w-max gap-4 px-1">
                        @foreach ($secondaryPackages as $package)
                            <a href="{{ route('packages.show', $package) }}" class="group w-64 shrink-0 overflow-hidden rounded-xl border border-[#e9e3d8] bg-white transition hover:-translate-y-1 hover:shadow-[0_18px_34px_-25px_rgba(0,39,31,.42)] sm:w-72">
                                <div class="aspect-[1.55/1] overflow-hidden bg-dark-green">@if ($package->imageUrl())<img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">@endif</div>
                                <div class="p-4"><p class="text-[9px] font-bold uppercase tracking-[.12em] text-[#9a762e]">{{ $package->category->name }}</p><h3 class="mt-1 font-bold text-dark-green">{{ $package->title }}</h3><p class="mt-2 text-[10px] text-[#65706b]">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam · {{ $package->formattedStartingPrice() }}</p></div>
                            </a>
                        @endforeach
                    </div>
                </div>
                <div class="mt-2 flex justify-end gap-2"><button class="carousel-control" type="button" data-carousel-prev aria-label="Pakej sebelumnya"><span class="material-symbols-outlined">west</span></button><button class="carousel-control" type="button" data-carousel-next aria-label="Pakej seterusnya"><span class="material-symbols-outlined">east</span></button></div>
            </div>
        </section>
    @endif

    @if (filled($campaignTitle) && filled($campaignLink))
        <section class="overflow-hidden bg-dark-green text-white">
            <div class="container-page relative py-16 sm:py-20">
                <div class="absolute -right-16 top-1/2 size-96 -translate-y-1/2 rounded-full border border-[#ffdf9a]/15"></div>
                <div class="relative max-w-3xl"><p class="text-[10px] font-bold uppercase tracking-[.16em] text-[#ffdf9a]">Kempen semasa</p><h2 class="mt-3 font-display text-5xl leading-[.96] sm:text-6xl">{{ $campaignTitle }}</h2>@if (filled($homepageSettings->get('homepage_campaign_description')))<p class="mt-5 max-w-2xl text-sm leading-7 text-emerald-50/80">{{ $homepageSettings->get('homepage_campaign_description') }}</p>@endif<a href="{{ $campaignLink }}" class="button-gold mt-8 !rounded-full">{{ $homepageSettings->get('homepage_campaign_cta') ?: 'Ketahui lebih lanjut' }} <span class="material-symbols-outlined text-[17px]">arrow_forward</span></a></div>
            </div>
        </section>
    @endif

    @if ($galleryItems->isNotEmpty())
        <section id="galeri-perjalanan" class="scroll-mt-24 overflow-hidden py-16 sm:py-22">
            <div class="container-page"><p class="section-eyebrow">Momen yang dikongsi</p><h2 class="mt-2 font-display text-4xl leading-tight text-dark-green sm:text-5xl">Galeri Perjalanan</h2></div>
            <div class="mt-9 space-y-3">
                @foreach ([0, 2, 4] as $row)
                    @php($rowImages = $galleryItems->slice($row)->concat($galleryItems->take($row)))
                    <div class="gallery-row {{ $loop->even ? 'gallery-row--left' : 'gallery-row--right' }}" data-gallery-row>
                        <div class="gallery-row__track">
                            @foreach ($rowImages as $image)
                                <button class="gallery-row__image" type="button" data-gallery-image data-gallery-full="{{ $image['full'] }}" data-gallery-alt="{{ $image['alt'] }}"><img src="{{ $image['thumbnail'] }}" alt="{{ $image['alt'] }}" loading="lazy"></button>
                            @endforeach
                            @foreach ($rowImages as $image)
                                <button class="gallery-row__image" type="button" tabindex="-1" aria-hidden="true"><img src="{{ $image['thumbnail'] }}" alt="" loading="lazy"></button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <dialog class="gallery-lightbox" data-gallery-lightbox aria-label="Paparan imej galeri">
            <button class="gallery-lightbox__close" type="button" data-gallery-close aria-label="Tutup galeri"><span class="material-symbols-outlined">close</span></button>
            <button class="gallery-lightbox__nav gallery-lightbox__nav--previous" type="button" data-gallery-previous aria-label="Imej sebelumnya"><span class="material-symbols-outlined">west</span></button>
            <figure><img src="" alt="" data-gallery-lightbox-image><figcaption data-gallery-lightbox-caption></figcaption></figure>
            <button class="gallery-lightbox__nav gallery-lightbox__nav--next" type="button" data-gallery-next aria-label="Imej seterusnya"><span class="material-symbols-outlined">east</span></button>
        </dialog>
    @endif

    @if (filled($videoUrl) || filled($instagramUrl))
        <section class="bg-[#f5f3ef] py-16 sm:py-22">
            <div class="container-page grid gap-5 {{ filled($videoUrl) && filled($instagramUrl) ? 'lg:grid-cols-2' : 'max-w-4xl' }}">
                @if (filled($videoUrl))
                    <article class="relative min-h-88 overflow-hidden rounded-2xl bg-dark-green p-7 text-white sm:p-9">
                        @if (filled($videoCover))<img src="{{ $videoCover }}" alt="" class="absolute inset-0 size-full object-cover opacity-55" loading="lazy">@endif
                        <div class="absolute inset-0 bg-gradient-to-t from-[#00271f] via-[#00271f]/45 to-transparent"></div>
                        <div class="relative flex min-h-[16rem] flex-col justify-end"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#ffdf9a]">Video pilihan</p><h2 class="mt-2 max-w-sm font-display text-4xl leading-tight">Persediaan ringkas sebelum Umrah.</h2><button class="mt-6 inline-flex w-fit items-center gap-2 rounded-full bg-[#ffdf9a] px-4 py-3 text-xs font-bold text-[#251a00]" type="button" data-video-launch data-video-url="{{ $videoUrl }}"><span class="grid size-6 place-items-center rounded-full bg-[#00271f] text-[#ffdf9a]"><span class="material-symbols-outlined text-[16px]">play_arrow</span></span> Tonton video</button></div>
                    </article>
                @endif
                @if (filled($instagramUrl))
                    <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" class="group relative min-h-88 overflow-hidden rounded-2xl bg-[#8d315e] p-7 text-white sm:p-9">
                        @if (filled($instagramCover))<img src="{{ $instagramCover }}" alt="" class="absolute inset-0 size-full object-cover opacity-60" loading="lazy">@endif
                        <div class="absolute inset-0 bg-[linear-gradient(135deg,rgba(70,18,70,.75),rgba(217,136,55,.45))]"></div>
                        <div class="relative flex min-h-[16rem] flex-col justify-end"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-white/75">Ikuti perkembangan kami</p><h2 class="mt-2 max-w-sm font-display text-4xl leading-tight">Instagram {{ $companyName }}</h2><span class="mt-6 inline-flex items-center gap-2 text-xs font-bold">Lihat Instagram Kami <span class="material-symbols-outlined text-[17px] transition group-hover:translate-x-1">arrow_forward</span></span></div>
                    </a>
                @endif
            </div>
        </section>
    @endif

    <section id="whatsapp-cta" class="bg-[#003d32] py-14 text-white sm:py-18">
        <div class="container-page"><div class="relative overflow-hidden rounded-2xl border border-[#ffdf9a]/20 bg-[#00271f] px-7 py-10 sm:px-10 sm:py-12"><div class="absolute -right-20 -top-20 size-72 rounded-full border border-[#ffdf9a]/15"></div><div class="relative flex flex-col gap-7 lg:flex-row lg:items-end lg:justify-between"><div class="max-w-2xl"><p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#ffdf9a]">Kami sedia membantu</p><h2 class="mt-2 font-display text-4xl leading-tight sm:text-5xl">Sedia untuk memulakan perjalanan Umrah?</h2></div>@if (filled($whatsappNumber))<x-whats-app-link :number="$whatsappNumber" message="Assalamualaikum, saya ingin bertanya tentang pakej Umrah." class="button-gold shrink-0 !rounded-full !px-5">WhatsApp Kami <span class="material-symbols-outlined text-[17px]">arrow_forward</span></x-whats-app-link>@else<a href="{{ route('packages.index') }}" class="button-gold shrink-0 !rounded-full !px-5">Lihat Pakej <span class="material-symbols-outlined text-[17px]">arrow_forward</span></a>@endif</div></div></div>
    </section>

    @if (filled($videoUrl))
        <dialog class="video-dialog" data-video-dialog aria-label="Video persediaan Umrah"><button type="button" class="video-dialog__close" data-video-close aria-label="Tutup video"><span class="material-symbols-outlined">close</span></button><div class="video-dialog__frame" data-video-frame></div></dialog>
    @endif
</x-layouts.app>

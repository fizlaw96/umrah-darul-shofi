<!DOCTYPE html>
<html lang="ms">
    <head>
        @php
            $pageTitle = $title ?? $siteSettings['company_name'];
            $documentTitle = $pageTitle.' · '.$siteSettings['company_name'];
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-seo-meta
            :title="$pageTitle"
            :description="$metaDescription ?? 'Cari pakej Umrah yang sesuai dengan bajet dan tarikh perjalanan anda.'"
            :image="$metaImage"
            :site-settings="$siteSettings"
        />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <title>{{ $documentTitle }}</title>
        @if (filled($siteSettings['company_favicon_url']))
            <link rel="icon" type="image/png" sizes="64x64" href="{{ $siteSettings['company_favicon_url'] }}">
        @endif
        @if (filled($siteSettings['company_apple_touch_icon_url']))
            <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteSettings['company_apple_touch_icon_url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('darul-shofi/css/footer.css') }}?v={{ filemtime(public_path('darul-shofi/css/footer.css')) }}">
    </head>
    <body>
        <header class="sticky top-0 z-40 border-b border-[#ebe5d9] bg-white/95 backdrop-blur">
            <div class="container-page flex h-[72px] items-center justify-between gap-3 sm:gap-5">
                <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-2.5" aria-label="{{ $siteSettings['company_name'] }}">
                    @if (filled($siteSettings['company_logo_url']))
                        <img src="{{ $siteSettings['company_logo_url'] }}" alt="" class="h-10 w-16 shrink-0 object-contain sm:h-12 sm:w-24">
                    @else
                        <span class="grid size-10 shrink-0 place-items-center rounded-lg bg-dark-green text-[#ffdf9a]">
                            <span class="material-symbols-outlined text-[20px]">mosque</span>
                        </span>
                    @endif
                    <span class="min-w-0 leading-none">
                        <span class="block text-[12px] font-extrabold tracking-[-0.04em] text-dark-green sm:text-sm">{{ $siteSettings['company_name'] }}</span>
                        <span class="site-header-tagline mt-1 block text-[7px] font-bold uppercase leading-3 tracking-[0.1em] text-[#9a762e] sm:max-w-56 sm:text-[8px] sm:tracking-[0.12em]">{{ $siteSettings['company_tagline'] }}</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-5 text-[12px] font-semibold text-[#404946] xl:flex" aria-label="Navigasi utama">
                    <a class="transition hover:text-dark-green {{ request()->routeIs('home') ? 'text-dark-green' : '' }}" href="{{ route('home') }}">Utama</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('packages.*', 'bookings.*') ? 'text-dark-green' : '' }}" href="{{ route('packages.index') }}">Pakej</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('about') ? 'text-dark-green' : '' }}" href="{{ route('about') }}">Tentang Kami</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('gallery.*') ? 'text-dark-green' : '' }}" href="{{ route('gallery.index') }}">Galeri</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('mutawifs.*') ? 'text-dark-green' : '' }}" href="{{ route('mutawifs.index') }}">Mutawif</a>
                </nav>

                <div class="hidden shrink-0 items-center gap-3 lg:flex">
                    <a class="button-gold !px-4 !py-2.5 !text-[11px]" href="{{ route('packages.index') }}">Lihat Semua Pakej</a>
                    <a href="{{ route('admin.login') }}" class="grid size-7 place-items-center rounded-full bg-dark-green text-white" aria-label="Log masuk pentadbir">
                        <span class="material-symbols-outlined text-[15px]">person</span>
                    </a>
                </div>

                <details class="relative shrink-0 lg:hidden">
                    <summary class="grid size-9 cursor-pointer place-items-center rounded-lg border border-[#e5e0d6] text-dark-green" aria-label="Buka menu">
                        <span class="material-symbols-outlined text-[21px]">menu</span>
                    </summary>
                    <nav class="absolute right-0 top-11 flex w-60 flex-col gap-1 rounded-xl border border-[#ebe5d9] bg-white p-2 shadow-[0_16px_36px_-6px_rgba(7,62,52,0.18)]" aria-label="Navigasi mudah alih">
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('home') }}">Utama</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('packages.index') }}">Pakej</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('about') }}">Tentang Kami</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('gallery.index') }}">Galeri</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('mutawifs.index') }}">Mutawif</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('packages.index') }}">Lihat Semua Pakej</a>
                    </nav>
                </details>
            </div>
        </header>

        @if (session('success'))
            <div class="container-page pt-5">
                <div class="rounded-lg border border-[#b8d6ca] bg-[#eaf6ef] px-4 py-3 text-sm font-semibold text-[#164b31]" role="status">{{ session('success') }}</div>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>

        @include('darul-shofi.partials.footer', ['footerClass' => 'ds-global-footer--spaced'])

        @php
            $floatingWhatsAppNumber = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number');
            $floatingWhatsAppMessage = $whatsAppMessage ?: 'Assalamualaikum, saya ingin bertanya tentang pakej Umrah.';
        @endphp
        @if ($showFloatingWhatsApp && filled($floatingWhatsAppNumber))
            @include('darul-shofi.partials.floating-whatsapp', [
                'number' => $floatingWhatsAppNumber,
                'whatsAppMessage' => $floatingWhatsAppMessage,
            ])
        @endif
    </body>
</html>

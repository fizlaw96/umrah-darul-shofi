<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $metaDescription ?? 'Cari pakej Umrah yang sesuai dengan bajet dan tarikh perjalanan anda.' }}">
        <link rel="canonical" href="{{ url()->current() }}">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="{{ $siteSettings['company_name'] }}">
        <meta property="og:title" content="{{ $title ?? $siteSettings['company_name'] }}">
        <meta property="og:description" content="{{ $metaDescription ?? 'Cari pakej Umrah yang sesuai dengan bajet dan tarikh perjalanan anda.' }}">
        <meta property="og:url" content="{{ url()->current() }}">
        @if (filled($metaImage))
            <meta property="og:image" content="{{ url($metaImage) }}">
            <meta name="twitter:card" content="summary_large_image">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
        <title>{{ $title ?? $siteSettings['company_name'] }} · {{ $siteSettings['company_name'] }}</title>
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
                    <a class="transition hover:text-dark-green" href="{{ route('home') }}#galeri-perjalanan">Galeri</a>
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
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('home') }}#galeri-perjalanan">Galeri</a>
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

        <main class="pb-16 md:pb-0">
            {{ $slot }}
        </main>

        @include('darul-shofi.partials.footer', ['footerClass' => 'ds-global-footer--spaced'])

        <nav class="fixed inset-x-0 bottom-0 z-40 grid h-16 grid-cols-5 border-t border-[#e5e0d6] bg-white md:hidden" aria-label="Navigasi bawah">
            <a href="{{ route('home') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">home</span>Utama</a>
            <a href="{{ route('packages.index') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">travel</span>Pakej</a>
            <a href="{{ route('about') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">info</span>Tentang</a>
            <a href="{{ route('mutawifs.index') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">person</span>Mutawif</a>
        </nav>

        @php
            $floatingWhatsAppNumber = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number');
            $floatingWhatsAppMessage = $whatsAppMessage ?: 'Assalamualaikum, saya ingin bertanya tentang pakej Umrah.';
        @endphp
        @if ($showFloatingWhatsApp && filled($floatingWhatsAppNumber))
            <div class="group fixed bottom-20 right-5 z-50 flex items-center md:bottom-6 md:right-7">
                <span class="pointer-events-none mr-3 hidden whitespace-nowrap rounded-full border border-[#e7c875]/40 bg-white px-3.5 py-1.5 text-xs font-semibold text-dark-green opacity-0 shadow-lg transition-opacity duration-200 group-hover:opacity-100 sm:block">Perlukan bantuan? WhatsApp kami</span>
                <x-whats-app-link
                    :number="$floatingWhatsAppNumber"
                    :message="$floatingWhatsAppMessage"
                    class="relative grid size-14 place-items-center rounded-full bg-[#25D366] text-white shadow-xl transition-transform duration-200 hover:scale-110"
                    aria-label="Hubungi kami melalui WhatsApp"
                >
                    <span class="absolute -right-1 -top-1 size-4 rounded-full border-2 border-white bg-[#e7c875] animate-ping"></span>
                    <span class="absolute -right-1 -top-1 size-4 rounded-full border-2 border-white bg-[#e7c875]"></span>
                    <svg class="size-7" viewBox="0 0 32 32" aria-hidden="true" fill="currentColor"><path d="M19.1 17.5c-.3-.2-1.8-.9-2.1-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.3-.6.1a8.6 8.6 0 0 1-2.5-1.5 9.4 9.4 0 0 1-1.8-2.3c-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.4.3-.6.1-.2 0-.5 0-.6l-1-2.3c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.5s-1.2 1.2-1.2 3 .1 3.1 1.3 4.8a11.6 11.6 0 0 0 4.4 4c.6.3 1.1.5 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.8-.7 2.1-1.4s.3-1.3.2-1.4-.3-.2-.6-.4Z"/><path d="M16 3.1a12.8 12.8 0 0 0-11 19.3L3.5 28.9l6.7-1.7A12.9 12.9 0 1 0 16 3.1Zm0 23.5a10.6 10.6 0 0 1-5.4-1.5l-.4-.2-4 .9.9-3.9-.3-.4a10.6 10.6 0 1 1 9.2 5.1Z"/></svg>
                </x-whats-app-link>
            </div>
        @endif
    </body>
</html>

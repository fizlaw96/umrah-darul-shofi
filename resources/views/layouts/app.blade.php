<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="{{ $metaDescription ?? 'Cari pakej Umrah yang sesuai dengan bajet dan tarikh perjalanan anda.' }}">
        <link rel="canonical" href="{{ url()->current() }}">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <title>{{ $title ?? $siteSettings['company_name'] }} · {{ $siteSettings['company_name'] }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <header class="sticky top-0 z-40 border-b border-[#ebe5d9] bg-white/95 backdrop-blur">
            <div class="container-page flex h-[72px] items-center justify-between gap-5">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5" aria-label="{{ $siteSettings['company_name'] }}">
                    @if (filled($siteSettings['company_logo_url']))
                        <img src="{{ $siteSettings['company_logo_url'] }}" alt="" class="size-9 rounded-lg object-contain">
                    @else
                        <span class="grid size-9 place-items-center rounded-lg bg-dark-green text-[#ffdf9a]">
                            <span class="material-symbols-outlined text-[20px]">mosque</span>
                        </span>
                    @endif
                    <span class="leading-none">
                        <span class="block text-sm font-extrabold tracking-[-0.04em] text-dark-green">{{ $siteSettings['company_name'] }}</span>
                        <span class="mt-1 block text-[8px] font-bold uppercase tracking-[0.16em] text-[#9a762e]">Umrah &amp; Travel</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-5 text-[12px] font-semibold text-[#404946] xl:flex" aria-label="Navigasi utama">
                    <a class="transition hover:text-dark-green {{ request()->routeIs('home') ? 'text-dark-green' : '' }}" href="{{ route('home') }}">Utama</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('packages.*', 'bookings.*') ? 'text-dark-green' : '' }}" href="{{ route('packages.index') }}">Pakej Umrah</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('about') ? 'text-dark-green' : '' }}" href="{{ route('about') }}">Tentang Kami</a>
                    <a class="transition hover:text-dark-green" href="{{ route('home') }}#galeri-perjalanan">Galeri</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('guides.*', 'faq') ? 'text-dark-green' : '' }}" href="{{ route('guides.index') }}">Panduan</a>
                    <a class="transition hover:text-dark-green {{ request()->routeIs('contact.*') ? 'text-dark-green' : '' }}" href="{{ route('contact.create') }}">Hubungi Kami</a>
                </nav>

                <div class="hidden shrink-0 items-center gap-3 lg:flex">
                    @if (filled($siteSettings['contact_whatsapp']))
                        <x-whats-app-link
                            :number="$siteSettings['contact_whatsapp']"
                            message="Assalamualaikum, saya ingin bertanya tentang pakej Umrah."
                            :fallback="route('contact.create')"
                            class="button-gold !px-4 !py-2.5 !text-[11px]"
                        >
                            <span class="material-symbols-outlined text-[15px]">chat</span> WhatsApp Kami
                        </x-whats-app-link>
                    @else
                        <a class="button-gold !px-4 !py-2.5 !text-[11px]" href="{{ route('contact.create') }}">Hantar Pertanyaan</a>
                    @endif
                    <a href="{{ route('admin.login') }}" class="grid size-7 place-items-center rounded-full bg-dark-green text-white" aria-label="Log masuk pentadbir">
                        <span class="material-symbols-outlined text-[15px]">person</span>
                    </a>
                </div>

                <details class="relative lg:hidden">
                    <summary class="grid size-9 cursor-pointer place-items-center rounded-lg border border-[#e5e0d6] text-dark-green" aria-label="Buka menu">
                        <span class="material-symbols-outlined text-[21px]">menu</span>
                    </summary>
                    <nav class="absolute right-0 top-11 flex w-60 flex-col gap-1 rounded-xl border border-[#ebe5d9] bg-white p-2 shadow-[0_16px_36px_-6px_rgba(7,62,52,0.18)]" aria-label="Navigasi mudah alih">
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('home') }}">Utama</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('packages.index') }}">Pakej Umrah</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('about') }}">Tentang Kami</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('home') }}#galeri-perjalanan">Galeri</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('guides.index') }}">Panduan</a>
                        <a class="rounded-lg px-3 py-2.5 text-sm font-bold hover:bg-[#f5f3ef]" href="{{ route('contact.create') }}">Hubungi Kami</a>
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

        <footer class="mt-20 bg-[#003d32] text-white">
            <div class="border-b border-white/10 bg-[#002f28]">
                <div class="container-page flex flex-col gap-2 py-3 text-[10px] font-medium text-[#c7e4db] sm:flex-row sm:items-center sm:justify-between">
                    <p class="flex items-center gap-2"><span class="material-symbols-outlined text-[15px] text-[#ffdf9a]">info</span> Laman maklumat pakej dan panduan Umrah.</p>
                    <p>Harga dan ketersediaan tertakluk kepada pengesahan agensi.</p>
                </div>
            </div>
            <div class="container-page grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.35fr_1fr_1fr_1.15fr]">
                <section>
                    <div class="flex items-center gap-2.5">
                        @if (filled($siteSettings['company_logo_url']))
                            <img src="{{ $siteSettings['company_logo_url'] }}" alt="" class="size-9 rounded-lg bg-white object-contain">
                        @else
                            <span class="grid size-9 place-items-center rounded-lg bg-white text-dark-green"><span class="material-symbols-outlined text-[19px]">mosque</span></span>
                        @endif
                        <span class="text-sm font-extrabold tracking-tight">{{ $siteSettings['company_name'] }}</span>
                    </div>
                    <p class="mt-4 max-w-xs text-xs leading-6 text-emerald-50/70">{{ $siteSettings['company_tagline'] }}</p>
                    @if (filled($siteSettings['facebook_url']) || filled($siteSettings['instagram_url']))
                        <div class="mt-5 flex gap-3 text-[11px] font-bold text-[#ffdf9a]">
                            @if (filled($siteSettings['facebook_url']))<a href="{{ $siteSettings['facebook_url'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">Facebook</a>@endif
                            @if (filled($siteSettings['instagram_url']))<a href="{{ $siteSettings['instagram_url'] }}" target="_blank" rel="noopener noreferrer" class="hover:text-white">Instagram</a>@endif
                        </div>
                    @endif
                </section>
                <section>
                    <h2 class="text-xs font-bold text-[#ffdf9a]">Pakej Pilihan</h2>
                    <div class="mt-4 flex flex-col gap-2.5 text-[11px] text-emerald-50/70">
                        <a class="hover:text-white" href="{{ route('packages.index') }}">Semua Pakej Umrah</a>
                        <a class="hover:text-white" href="{{ route('packages.index', ['duration' => 9]) }}">Pakej 9 Hari</a>
                        <a class="hover:text-white" href="{{ route('packages.index', ['duration' => 12]) }}">Pakej 12 Hari</a>
                        <a class="hover:text-white" href="{{ route('contact.create') }}">Tanya Pakej Sesuai</a>
                    </div>
                </section>
                <section>
                    <h2 class="text-xs font-bold text-[#ffdf9a]">Maklumat &amp; Panduan</h2>
                    <div class="mt-4 flex flex-col gap-2.5 text-[11px] text-emerald-50/70">
                        <a class="hover:text-white" href="{{ route('guides.index') }}">Panduan Umrah</a>
                        <a class="hover:text-white" href="{{ route('faq') }}">Soalan Lazim</a>
                        <a class="hover:text-white" href="{{ route('about') }}">Tentang Kami</a>
                        <a class="hover:text-white" href="{{ route('contact.create') }}">Hubungi Kami</a>
                    </div>
                </section>
                <section>
                    <h2 class="text-xs font-bold text-[#ffdf9a]">Ibu Pejabat</h2>
                    <div class="mt-4 space-y-2.5 text-[11px] leading-5 text-emerald-50/70">
                        @if (filled($siteSettings['office_address']))<p class="flex gap-2"><span class="material-symbols-outlined text-[15px] text-[#ffdf9a]">location_on</span>{{ $siteSettings['office_address'] }}</p>@endif
                        @if (filled($siteSettings['contact_phone']))<p class="flex gap-2"><span class="material-symbols-outlined text-[15px] text-[#ffdf9a]">call</span>{{ $siteSettings['contact_phone'] }}</p>@endif
                        @if (filled($siteSettings['contact_whatsapp']))<p class="flex gap-2"><span class="material-symbols-outlined text-[15px] text-[#ffdf9a]">chat</span><x-whats-app-link :number="$siteSettings['contact_whatsapp']" message="Assalamualaikum, saya ingin bertanya tentang pakej Umrah." class="hover:text-white">{{ $siteSettings['contact_whatsapp'] }}</x-whats-app-link></p>@endif
                        @if (filled($siteSettings['contact_email']))<p class="flex gap-2"><span class="material-symbols-outlined text-[15px] text-[#ffdf9a]">mail</span>{{ $siteSettings['contact_email'] }}</p>@endif
                        @if (! filled($siteSettings['contact_phone']) && ! filled($siteSettings['contact_whatsapp']) && ! filled($siteSettings['contact_email']))<p>Butiran rasmi agensi akan dikemas kini di sini.</p>@endif
                    </div>
                </section>
            </div>
            <div class="border-t border-white/10">
                <div class="container-page flex flex-col gap-2 py-5 text-[10px] text-emerald-50/45 sm:flex-row sm:justify-between">
                    <p>© {{ now()->year }} {{ $siteSettings['company_name'] }}. Semua hak terpelihara.</p>
                    <div class="flex flex-wrap gap-4">
                        @if (filled($siteSettings['privacy_url']))<a href="{{ $siteSettings['privacy_url'] }}" class="hover:text-white">Dasar Privasi</a>@endif
                        @if (filled($siteSettings['terms_url']))<a href="{{ $siteSettings['terms_url'] }}" class="hover:text-white">Terma &amp; Syarat</a>@endif
                    </div>
                </div>
            </div>
        </footer>

        <nav class="fixed inset-x-0 bottom-0 z-40 grid h-16 grid-cols-5 border-t border-[#e5e0d6] bg-white md:hidden" aria-label="Navigasi bawah">
            <a href="{{ route('home') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">home</span>Utama</a>
            <a href="{{ route('packages.index') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">travel</span>Pakej</a>
            <a href="{{ route('guides.index') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">menu_book</span>Panduan</a>
            <a href="{{ route('about') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">info</span>Tentang</a>
            <a href="{{ route('contact.create') }}" class="grid place-content-center gap-0.5 text-center text-[9px] font-bold text-[#404946]"><span class="material-symbols-outlined text-[18px]">support_agent</span>Hubungi</a>
        </nav>

        @if (filled($siteSettings['contact_whatsapp']))
            <x-whats-app-link
                :number="$siteSettings['contact_whatsapp']"
                message="Assalamualaikum, saya ingin bertanya tentang pakej Umrah."
                :fallback="route('contact.create')"
                class="group fixed bottom-20 right-5 z-50 inline-flex items-center gap-2 rounded-full bg-[#25D366] px-4 py-3 text-xs font-extrabold text-[#063b2d] shadow-[0_14px_32px_-12px_rgba(0,39,31,.68)] transition hover:-translate-y-1 hover:shadow-[0_18px_34px_-12px_rgba(0,39,31,.76)] md:bottom-6 md:right-7"
                aria-label="Hubungi kami melalui WhatsApp"
            >
                <svg class="size-5" viewBox="0 0 32 32" aria-hidden="true" fill="currentColor"><path d="M19.1 17.5c-.3-.2-1.8-.9-2.1-1s-.5-.2-.7.2-.8 1-.9 1.2-.3.3-.6.1a8.6 8.6 0 0 1-2.5-1.5 9.4 9.4 0 0 1-1.8-2.3c-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.4.3-.6.1-.2 0-.5 0-.6l-1-2.3c-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.6.1-.9.5s-1.2 1.2-1.2 3 .1 3.1 1.3 4.8a11.6 11.6 0 0 0 4.4 4c.6.3 1.1.5 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.8-.7 2.1-1.4s.3-1.3.2-1.4-.3-.2-.6-.4Z"/><path d="M16 3.1a12.8 12.8 0 0 0-11 19.3L3.5 28.9l6.7-1.7A12.9 12.9 0 1 0 16 3.1Zm0 23.5a10.6 10.6 0 0 1-5.4-1.5l-.4-.2-4 .9.9-3.9-.3-.4a10.6 10.6 0 1 1 9.2 5.1Z"/></svg>
                <span class="hidden sm:inline">WhatsApp Kami</span>
                <span class="pointer-events-none absolute bottom-full right-0 mb-2 whitespace-nowrap rounded bg-[#00271f] px-2.5 py-1.5 text-[10px] text-white opacity-0 transition group-hover:opacity-100">Ada pertanyaan?</span>
            </x-whats-app-link>
        @endif
    </body>
</html>

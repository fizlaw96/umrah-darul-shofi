<header class="ds-site-header sticky top-0 z-50 border-b border-[#E9E4DC] bg-white/95 backdrop-blur-md">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="ds-site-header-inner flex min-h-20 items-center justify-between gap-4 py-2">
            <a class="ds-site-brand group flex min-w-0 items-center gap-3" href="{{ route('home') }}">
                @if ($siteSettings['company_logo_url'])
                    <span class="ds-site-brand-logo flex shrink-0 items-center justify-center">
                        <img src="{{ $siteSettings['company_logo_url'] }}" alt="Logo {{ $siteSettings['company_name'] }}">
                    </span>
                @else
                    <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-brand-emerald text-brand-gold-light">
                        <i class="fa-solid fa-kaaba"></i>
                    </span>
                @endif

                <span class="min-w-0">
                    <span class="block font-serif-luxury text-lg font-bold tracking-wide text-brand-emerald sm:text-xl">{{ $siteSettings['company_name'] }}</span>
                    <span class="ds-site-tagline mt-0.5 block text-[9px] font-semibold uppercase leading-4 tracking-[.12em] text-brand-gold-dark">{{ $siteSettings['company_tagline'] }}</span>
                </span>
            </a>

            <nav class="ds-site-desktop-nav items-center gap-6 text-sm font-medium text-brand-charcoal" aria-label="Navigasi utama">
                <a class="transition hover:text-brand-emerald" href="{{ route('home') }}">Utama</a>
                <a class="transition hover:text-brand-emerald" href="{{ route('packages.index') }}">Pakej</a>
                <a class="transition hover:text-brand-emerald" href="{{ route('about') }}">Tentang Kami</a>
                <a class="transition hover:text-brand-emerald" href="{{ route('home') }}#galeri">Galeri</a>
                <a class="transition hover:text-brand-emerald" href="{{ route('mutawifs.index') }}">Mutawif</a>
            </nav>

            <div class="flex shrink-0 items-center gap-3">
                <button aria-controls="darul-mobile-nav" aria-expanded="false" aria-label="Buka menu navigasi" class="ds-site-menu-toggle inline-flex size-10 items-center justify-center rounded-xl border border-brand-gold/30 text-brand-emerald hover:bg-brand-cream" data-mobile-menu-toggle type="button">
                    <i aria-hidden="true" class="fa-solid fa-bars"></i>
                </button>
                <a class="ds-site-desktop-cta rounded-full border border-brand-emerald-light bg-brand-emerald px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-emerald-dark" href="{{ route('packages.index') }}">Lihat Semua Pakej</a>
            </div>
        </div>

        <nav aria-label="Navigasi mudah alih" class="hidden border-t border-brand-gold/20 py-4" data-mobile-menu id="darul-mobile-nav">
            <div class="grid grid-cols-2 gap-2 text-sm font-semibold text-brand-emerald">
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('home') }}">Utama</a>
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('packages.index') }}">Pakej</a>
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('about') }}">Tentang Kami</a>
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('mutawifs.index') }}">Mutawif</a>
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('home') }}#galeri">Galeri</a>
                <a class="rounded-xl px-3 py-3 hover:bg-brand-cream" href="{{ route('packages.index') }}">Lihat Semua Pakej</a>
            </div>
        </nav>
    </div>
</header>

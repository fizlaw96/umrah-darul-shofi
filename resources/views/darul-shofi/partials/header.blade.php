<header class="ds-site-header">
    <div class="ds-site-header__container">
        <div class="ds-site-header-inner">
            <a class="ds-site-brand" href="{{ route('home') }}">
                @if ($siteSettings['company_logo_url'])
                    <span class="ds-site-brand-logo">
                        <img src="{{ $siteSettings['company_logo_url'] }}" alt="Logo {{ $siteSettings['company_name'] }}">
                    </span>
                @else
                    <span class="ds-site-brand-logo ds-site-brand-logo--fallback">
                        <i class="fa-solid fa-kaaba"></i>
                    </span>
                @endif

                <span class="ds-site-brand-copy">
                    <span class="ds-site-brand-name">{{ $siteSettings['company_name'] }}</span>
                    <span class="ds-site-tagline">{{ $siteSettings['company_tagline'] }}</span>
                </span>
            </a>

            <nav class="ds-site-desktop-nav" aria-label="Navigasi utama">
                <a href="{{ route('packages.index') }}">Pakej</a>
                <details class="ds-site-services-menu"><summary>Perkhidmatan <i aria-hidden="true" class="fa-solid fa-chevron-down"></i></summary><div class="ds-site-services-menu__panel"><a href="{{ route('transport.show') }}">Transport</a><a href="{{ route('aqiqah-korban.show') }}">Aqiqah &amp; Korban</a></div></details>
                <a href="{{ route('about') }}">Tentang Kami</a>
                <a href="{{ route('gallery.index') }}">Galeri</a>
                <a href="{{ route('mutawifs.index') }}">Mutawif</a>
            </nav>

            <div class="ds-site-header-actions">
                <button aria-controls="darul-mobile-nav" aria-expanded="false" aria-label="Buka menu navigasi" class="ds-site-menu-toggle" data-mobile-menu-toggle type="button">
                    <i aria-hidden="true" class="fa-solid fa-bars"></i>
                </button>
                <a class="ds-site-desktop-cta" href="{{ route('packages.index') }}">Lihat Semua Pakej</a>
                <a aria-label="Log masuk pentadbir" class="ds-site-admin-login" href="{{ route('admin.login') }}">
                    <i aria-hidden="true" class="fa-solid fa-user"></i>
                </a>
            </div>
        </div>

    </div>
</header>

<nav aria-label="Navigasi mudah alih" class="ds-mobile-menu hidden" data-mobile-menu id="darul-mobile-nav">
    <div class="ds-mobile-menu__links">
        <a class="ds-mobile-menu__link" href="{{ route('packages.index') }}">
            <i aria-hidden="true" class="fa-solid fa-arrow-right"></i>
            <span>Lihat Semua Pakej</span>
        </a>
        <a class="ds-mobile-menu__link" href="{{ route('transport.show') }}">
            <i aria-hidden="true" class="fa-solid fa-van-shuttle"></i>
            <span>Transport</span>
        </a>
        <a class="ds-mobile-menu__link" href="{{ route('aqiqah-korban.show') }}">
            <i aria-hidden="true" class="fa-solid fa-hand-holding-heart"></i>
            <span>Aqiqah &amp; Korban</span>
        </a>
        <a class="ds-mobile-menu__link" href="{{ route('about') }}">
            <i aria-hidden="true" class="fa-solid fa-circle-info"></i>
            <span>Tentang Kami</span>
        </a>
        <a class="ds-mobile-menu__link" href="{{ route('gallery.index') }}">
            <i aria-hidden="true" class="fa-solid fa-images"></i>
            <span>Galeri</span>
        </a>
        <a class="ds-mobile-menu__link" href="{{ route('mutawifs.index') }}">
            <i aria-hidden="true" class="fa-solid fa-user-tie"></i>
            <span>Mutawif</span>
        </a>
        <a class="ds-mobile-menu__admin-login" href="{{ route('admin.login') }}">
            <i aria-hidden="true" class="fa-solid fa-user-shield"></i>
            <span>Log Masuk Admin</span>
        </a>
    </div>
</nav>

@php
    $companyAddress = $siteSettings['company_address'];
    $companyAddressLines = preg_split('/\R/', $companyAddress ?: '');
    $publicAddress = $siteSettings['public_contact_address'] ?: $siteSettings['office_address'];
    $publicAddressLines = preg_split('/\R/', $publicAddress ?: '');
    $darulSoffiLogoUrl = $siteSettings['footer_logo_url']
        ?: asset('darul-shofi/images/branding/darul-soffi-logo.jpg');
@endphp

<footer class="ds-global-footer {{ $footerClass ?? '' }}" id="hubungi">
    <div class="ds-global-footer__inner">
        <div class="ds-global-footer__top">
            <section class="ds-global-footer__brand" aria-label="{{ $siteSettings['company_name'] }}">
                <a class="ds-global-footer__company-logo" href="{{ route('home') }}" aria-label="{{ $siteSettings['company_name'] }}">
                    @if (filled($siteSettings['company_logo_url']))
                        <x-responsive-image :src="$siteSettings['company_logo_url']" sizes="190px" alt="Logo {{ $siteSettings['company_name'] }}" />
                    @else
                        <svg aria-hidden="true" focusable="false" viewBox="0 0 48 48">
                            <path d="M29.5 7.5a16.5 16.5 0 1 0 10.9 27.1A15.5 15.5 0 1 1 29.5 7.5Z" fill="currentColor"/>
                            <path d="m33.5 12.5.8 2.2 2.2.8-2.2.8-.8 2.2-.8-2.2-2.2-.8 2.2-.8.8-2.2Z" fill="currentColor"/>
                        </svg>
                    @endif
                </a>

                <h2>{{ $siteSettings['company_name'] }}</h2>
                <p class="ds-global-footer__tagline">{{ $siteSettings['company_tagline'] }}</p>
                @if (filled($companyAddress))
                    <p class="ds-global-footer__company-address">
                        <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                            <path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Z"/>
                            <circle cx="12" cy="9" r="2.25"/>
                        </svg>
                        <span class="ds-global-footer__address-copy">
                            @foreach ($companyAddressLines as $addressLine)
                                <span @class(['ds-global-footer__address-title' => $loop->first])>{{ $addressLine }}</span>
                            @endforeach
                        </span>
                    </p>
                @endif
                <span class="ds-global-footer__accent" aria-hidden="true"></span>
                <p class="ds-global-footer__mission">Kami komited membimbing perjalanan ibadah anda ke Tanah Suci dengan pakej yang terjamin, selesa dan bermakna.</p>

                <svg class="ds-global-footer__mosque" aria-hidden="true" focusable="false" viewBox="0 0 560 170" preserveAspectRatio="xMinYMax meet">
                    <path d="M0 147h560v23H0zM30 147V92h10v-18h5v18h10v55zm6-60h13l-6.5-13zM76 147v-39h11v-15h6v15h11v39zm8-44h12l-6-12zM142 147v-57h17v-11h7v11h17v57zm11-62h19l-9.5-18zM199 147v-29c0-29 18-52 43-61v-8l6-7 6 7v8c25 9 43 32 43 61v29zm14-32h70c-4-23-20-40-35-46-15 6-31 23-35 46zm87 32v-50h11V76h5v21h11v50zm6-55h13l-6.5-13zM353 147v-42h13V89h6v16h13v42zm8-47h16l-8-15zM425 147v-31h11v-15h6v15h11v31zm8-36h12l-6-12zM492 147V93h10V76h5v17h10v54zm6-60h13l-6.5-13z" fill="currentColor"/>
                </svg>
            </section>

            <section class="ds-global-footer__column ds-global-footer__column--links">
                <div class="ds-global-footer__heading">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                        <path d="m12 2.5 8.5 4.9v9.8L12 22l-8.5-4.8V7.4L12 2.5Z"/>
                        <path d="m3.5 7.4 8.5 4.9 8.5-4.9M12 12.3V22"/>
                    </svg>
                    <h3>Kategori Pakej</h3>
                </div>
                <ul>
                    @forelse ($footerCategories as $category)
                        <li><a href="{{ route('packages.index', ['category' => $category->slug]) }}">{{ $category->name }}</a></li>
                    @empty
                        <li><a href="{{ route('packages.index') }}">Lihat semua pakej</a></li>
                    @endforelse
                </ul>
            </section>

            <section class="ds-global-footer__column ds-global-footer__column--links">
                <div class="ds-global-footer__heading">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                        <rect x="5" y="2.5" width="14" height="19" rx="2"/>
                        <path d="M8.5 7.5h7M8.5 12h7M8.5 16.5h4.5"/>
                    </svg>
                    <h3>Maklumat &amp; Panduan</h3>
                </div>
                <ul>
                    <li><a href="{{ route('faq') }}">Soalan Lazim</a></li>
                    <li><a href="{{ route('mutawifs.index') }}">Mutawif</a></li>
                    <li><a href="{{ route('about') }}">Tentang Kami</a></li>
                    <li><a href="{{ route('transport.show') }}">Transport</a></li>
                    <li><a href="{{ route('aqiqah-korban.show') }}">Aqiqah &amp; Korban</a></li>
                    <li><a href="{{ route('terms') }}">Terma &amp; Syarat</a></li>
                </ul>
            </section>

            <section class="ds-global-footer__column ds-global-footer__column--contact">
                <div class="ds-global-footer__heading">
                    <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                        <path d="M6.8 3.5h2.8l1.4 4.2-1.8 1.7a15.8 15.8 0 0 0 5.4 5.4l1.7-1.8 4.2 1.4v2.8c0 1.2-1 2.2-2.2 2.2C10.7 19.4 4.6 13.3 4.6 5.7c0-1.2 1-2.2 2.2-2.2Z"/>
                    </svg>
                    <h3>Hubungi Kami</h3>
                </div>

                <div class="ds-global-footer__contact-card">
                    <div class="ds-global-footer__darul-logo">
                        <x-responsive-image :src="$darulSoffiLogoUrl" sizes="208px" alt="Logo Pusat Pendidikan dan Bimbingan Darul Soffi" />
                    </div>

                    @if (filled($publicAddress))
                        <p class="ds-global-footer__address">
                            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                                <path d="M12 21s7-6.2 7-12A7 7 0 1 0 5 9c0 5.8 7 12 7 12Z"/>
                                <circle cx="12" cy="9" r="2.25"/>
                            </svg>
                            <span class="ds-global-footer__address-copy">
                                @foreach ($publicAddressLines as $addressLine)
                                    <span @class(['ds-global-footer__address-title' => $loop->first])>{{ $addressLine }}</span>
                                @endforeach
                            </span>
                        </p>
                    @endif

                    @if (filled($siteSettings['map_url']))
                        <a class="ds-global-footer__map-link" href="{{ $siteSettings['map_url'] }}" target="_blank" rel="noopener noreferrer">
                            <svg aria-hidden="true" focusable="false" viewBox="0 0 24 24">
                                <path d="M14 4h6v6M20 4l-9 9"/>
                                <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>
                            </svg>
                            <span>Buka dalam Google Maps</span>
                        </a>
                    @endif

                    <p class="ds-global-footer__notice">Butiran rasmi agensi akan dikemas kini di sini.</p>
                </div>
            </section>
        </div>

        <div class="ds-global-footer__bottom">
            <div class="ds-global-footer__copyright">
                <p>© {{ now()->year }} {{ rtrim($siteSettings['company_legal_name'], '.') }}.</p>
                <p>Hak cipta terpelihara.</p>
            </div>

            <div class="ds-global-footer__credentials">
                <p>Lesen MOTAC: {{ $siteSettings['motac_license_number'] }}</p>
                <p>Website dihasilkan oleh <a href="https://ibpro.my" target="_blank" rel="noopener noreferrer">ibpro</a></p>
            </div>

            <nav class="ds-global-footer__legal-links" aria-label="Pautan undang-undang">
                <a href="{{ $siteSettings['privacy_url'] ?: route('terms') }}">Dasar Privasi</a>
                <a href="{{ route('terms') }}">Terma &amp; Syarat</a>
            </nav>
        </div>
    </div>
</footer>

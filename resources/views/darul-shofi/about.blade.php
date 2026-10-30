@extends('darul-shofi.layout')

@section('title', 'Tentang Kami | SalamHaji Travel & Tours')
@section('meta_description', 'Kenali SalamHaji Travel & Tours, agensi yang membantu bakal jemaah merancang perjalanan Umrah dan Haji dengan maklumat yang jelas.')

@section('content')
    @php
        $number = preg_replace('/\D+/', '', (string) ($siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number', '')));
        $whatsappDisplay = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_display', '');
        $whatsappLink = static fn (string $message = ''): string => $number !== ''
            ? 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '')
            : '#hubungi';
        $aboutDescription = $siteSettings['about_description']
            ?: $siteSettings['homepage_company_intro']
            ?: 'Kandungan pengenalan SalamHaji boleh dikemas kini oleh pentadbir.';
    @endphp

    @include('darul-shofi.partials.header')

    <main class="ds-about-page">
        <section class="ds-about-page__hero">
            <div class="ds-about-page__inner">
                <span>{{ $siteSettings['about_eyebrow'] ?: 'Bersama Menuju Baitullah' }}</span>
                <h1>{{ $siteSettings['about_heading'] ?: 'Perjalanan suci, amanah kami' }}</h1>
                <p class="whitespace-pre-line">{{ $aboutDescription }}</p>
            </div>
        </section>

        <section class="ds-about-page__gallery" data-full-gallery>
            <div class="ds-full-gallery-page__content ds-about-page__gallery-content">
                <div class="ds-about-page__gallery-divider" role="heading" aria-level="2">
                    <span aria-hidden="true"></span>
                    <em>Tentang Kami</em>
                    <span aria-hidden="true"></span>
                </div>

                @if ($aboutMedia->isNotEmpty())
                    <div class="ds-full-gallery-grid" data-full-gallery-grid data-full-gallery-view="grid">
                        @foreach ($aboutMedia as $mediaItem)
                            <button type="button" class="ds-full-gallery-item" data-full-gallery-open data-full-gallery-category="about" data-full-gallery-src="{{ $mediaItem->url() }}" data-full-gallery-alt="{{ $mediaItem->alt_text ?: 'Foto Tentang Kami SalamHaji' }}" data-full-gallery-caption="Tentang Kami" aria-label="Buka {{ $mediaItem->alt_text ?: 'foto Tentang Kami SalamHaji' }}">
                                <span class="ds-full-gallery-item__image"><img src="{{ $mediaItem->url() }}" alt="{{ $mediaItem->alt_text ?: 'Foto Tentang Kami SalamHaji' }}" style="object-position: {{ $imageFocusPositions[$mediaItem->path] ?? '50% 50%' }}" loading="lazy" decoding="async"></span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <div class="ds-full-gallery-page__empty"><i class="fa-solid fa-images"></i><h3>Galeri sedang dikemas kini</h3><p>Gambar Tentang Kami akan dipaparkan di sini tidak lama lagi.</p></div>
                @endif
            </div>

            <dialog aria-label="Paparan foto Tentang Kami" class="ds-lightbox" data-full-gallery-lightbox>
                <div class="ds-lightbox-inner">
                    <button aria-label="Tutup galeri" class="ds-lightbox-close" data-full-gallery-close type="button"><i class="fa-solid fa-xmark"></i></button>
                    <button aria-label="Foto sebelumnya" class="ds-lightbox-step ds-lightbox-step--prev" data-full-gallery-prev type="button"><i class="fa-solid fa-chevron-left"></i></button>
                    <figure><img alt="" data-full-gallery-preview decoding="async"><figcaption data-full-gallery-caption></figcaption></figure>
                    <button aria-label="Foto seterusnya" class="ds-lightbox-step ds-lightbox-step--next" data-full-gallery-next type="button"><i class="fa-solid fa-chevron-right"></i></button>
                </div>
            </dialog>
        </section>
    </main>

    @include('darul-shofi.partials.footer')
    @include('darul-shofi.partials.floating-whatsapp')
@endsection

@extends('darul-shofi.layout')

@section('title', 'SalamHaji Travel & Tours | Umrahku & Hajimu Yang Teristimewa')

@section('content')
    @php
        // Configure DARUL_SHOFI_WHATSAPP_NUMBER in .env before launch.
        $number = preg_replace('/\D+/', '', (string) ($siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number', '')));
        $whatsappDisplay = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_display', '');
        $whatsappLink = static fn (string $message = ''): string => $number !== ''
            ? 'https://wa.me/' . $number . ($message !== '' ? '?text=' . rawurlencode($message) : '')
            : '#hubungi';
    @endphp
    @include('darul-shofi.partials.header')
    @include('darul-shofi.sections.hero')
    @include('darul-shofi.sections.airline-partners')
    @include('darul-shofi.sections.about')
    @include('darul-shofi.sections.services')
    @include('darul-shofi.sections.packages')
    <dialog class="ds-lightbox" data-home-package-lightbox><div class="ds-lightbox-inner"><button type="button" data-home-package-lightbox-close aria-label="Tutup poster"><i class="fa-solid fa-xmark"></i></button><figure><img data-home-package-lightbox-image src="" alt=""><figcaption data-home-package-lightbox-caption></figcaption></figure></div></dialog>
    @include('darul-shofi.sections.haji-banner')
    @include('darul-shofi.sections.gallery')
    @include('darul-shofi.sections.videos')
    @include('darul-shofi.sections.final-cta')
    @include('darul-shofi.partials.footer')
    @include('darul-shofi.partials.floating-whatsapp')
@endsection

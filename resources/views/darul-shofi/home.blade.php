@extends('darul-shofi.layout')

@section('title', 'Darul Shofi Travel & Tours | Ibadah Mudah & Penuh Keberkatan')

@section('content')
    @php
        // Configure DARUL_SHOFI_WHATSAPP_NUMBER in .env before launch.
        $number = preg_replace('/\D+/', '', (string) config('darul_shofi.whatsapp_number', ''));
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
    @include('darul-shofi.sections.secondary-packages')
    @include('darul-shofi.sections.haji-banner')
    @include('darul-shofi.sections.gallery')
    @include('darul-shofi.sections.videos')
    @include('darul-shofi.sections.final-cta')
    @include('darul-shofi.partials.footer')
    @include('darul-shofi.partials.floating-whatsapp')
@endsection

@extends('darul-shofi.layout')

@section('title', 'Tentang Kami | SalamHaji Travel & Tours')

@section('content')
    @php
        $number = preg_replace('/\D+/', '', (string) ($siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number', '')));
        $whatsappDisplay = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_display', '');
        $whatsappLink = static fn (string $message = ''): string => $number !== ''
            ? 'https://wa.me/'.$number.($message !== '' ? '?text='.rawurlencode($message) : '')
            : '#hubungi';
    @endphp

    @include('darul-shofi.partials.header')
    @php($aboutHeadingTag = 'h1')
    @include('darul-shofi.sections.about')
    @include('darul-shofi.partials.footer')
    @include('darul-shofi.partials.floating-whatsapp')
@endsection

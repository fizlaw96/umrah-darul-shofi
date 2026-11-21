@props([
    'src',
    'sizes' => '100vw',
    'priority' => false,
])

@php
    // Falls back to the plain original <img> until WebP copies exist for this file.
    $responsiveImage = \App\Models\ImageDerivative::responsiveAttributesFor($src);
@endphp

<img src="{{ $src }}" @if ($responsiveImage) srcset="{{ $responsiveImage['srcset'] }}" sizes="{{ $sizes }}" width="{{ $responsiveImage['width'] }}" height="{{ $responsiveImage['height'] }}" @endif @if ($priority) fetchpriority="high" @endif {{ $attributes }}>

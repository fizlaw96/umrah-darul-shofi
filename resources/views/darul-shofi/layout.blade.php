<!doctype html>
<html lang="ms" class="scroll-smooth">
<head>
    @php
        $defaultTitle = 'SalamHaji Travel & Tours | Umrahku & Hajimu Yang Teristimewa';
        $defaultDescription = 'Pakej Umrah, Haji, Badal Haji dan pelancongan SalamHaji Travel & Tours.';
        $pageTitle = trim($__env->yieldContent('title', $defaultTitle));
        $pageDescription = trim($__env->yieldContent('meta_description', $defaultDescription));
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F382C">
    <x-seo-meta :title="$pageTitle" :description="$pageDescription" :site-settings="$siteSettings" />
    <title>{{ $pageTitle }}</title>
    @if (filled($siteSettings['company_favicon_url']))
        <link rel="icon" type="image/png" sizes="64x64" href="{{ $siteSettings['company_favicon_url'] }}">
    @endif
    @if (filled($siteSettings['company_apple_touch_icon_url']))
        <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteSettings['company_apple_touch_icon_url'] }}">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    {{-- Compiled, standalone utilities: no Tailwind CDN, no change to host Vite setup. --}}
    <link rel="stylesheet" href="{{ asset('darul-shofi/css/site.css') }}?v={{ filemtime(public_path('darul-shofi/css/site.css')) }}">
    <link rel="stylesheet" href="{{ asset('darul-shofi/css/footer.css') }}?v={{ filemtime(public_path('darul-shofi/css/footer.css')) }}">
</head>
<body class="antialiased selection:bg-brand-gold selection:text-white">
    @yield('content')
    <script src="{{ asset('darul-shofi/js/site.js') }}?v={{ filemtime(public_path('darul-shofi/js/site.js')) }}" defer></script>
</body>
</html>

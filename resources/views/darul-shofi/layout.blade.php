<!doctype html>
<html lang="ms" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0F382C">
    <meta name="description" content="Pakej Umrah, panduan dan maklumat perjalanan Darul Shofi Travel & Tours.">
    <title>@yield('title', 'Darul Shofi Travel & Tours | Ibadah Mudah & Penuh Keberkatan')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    {{-- Compiled, standalone utilities: no Tailwind CDN, no change to host Vite setup. --}}
    <link rel="stylesheet" href="{{ asset('darul-shofi/css/site.css') }}">
</head>
<body class="antialiased selection:bg-brand-gold selection:text-white">
    @yield('content')
    <script src="{{ asset('darul-shofi/js/site.js') }}" defer></script>
</body>
</html>

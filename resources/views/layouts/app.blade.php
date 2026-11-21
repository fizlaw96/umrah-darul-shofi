<!DOCTYPE html>
<html lang="ms">
    <head>
        @php
            $pageTitle = $title ?? $siteSettings['company_name'];
            $documentTitle = $pageTitle.' · '.$siteSettings['company_name'];
        @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <x-seo-meta
            :title="$pageTitle"
            :description="$metaDescription ?? 'Cari pakej Umrah yang sesuai dengan bajet dan tarikh perjalanan anda.'"
            :image="$metaImage"
            :site-settings="$siteSettings"
        />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;0,700;1,400;1,600&family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        {{-- Icon font subset to the icons public pages use; add a name here when a new public icon is introduced. --}}
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,0&icon_names=admin_panel_settings,arrow_forward,article,assignment,bed,block,calendar_month,chat,check,chevron_left,chevron_right,close,download,east,event_note,explore,fact_check,flight,flight_takeoff,forum,group,groups,hotel,info,key,local_fire_department,location_city,location_on,lock,lock_reset,mail,menu_book,mosque,nights_stay,pause,person,photo_library,play_arrow,redeem,restart_alt,schedule,search,send,shield,star,stars,support_agent,verified_user,visibility,visibility_off,volunteer_activism,west&display=block" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
        <title>{{ $documentTitle }}</title>
        @if (filled($siteSettings['company_favicon_url']))
            <link rel="icon" type="image/png" sizes="64x64" href="{{ $siteSettings['company_favicon_url'] }}">
        @endif
        @if (filled($siteSettings['company_apple_touch_icon_url']))
            <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteSettings['company_apple_touch_icon_url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('darul-shofi/css/header.css') }}?v={{ filemtime(public_path('darul-shofi/css/header.css')) }}">
        <link rel="stylesheet" href="{{ asset('darul-shofi/css/footer.css') }}?v={{ filemtime(public_path('darul-shofi/css/footer.css')) }}">
    </head>
    <body>
        @include('darul-shofi.partials.header')

        @if (session('success'))
            <div class="container-page pt-5">
                <div class="rounded-lg border border-[#b8d6ca] bg-[#eaf6ef] px-4 py-3 text-sm font-semibold text-[#164b31]" role="status">{{ session('success') }}</div>
            </div>
        @endif

        <main>
            {{ $slot }}
        </main>

        @include('darul-shofi.partials.footer', ['footerClass' => 'ds-global-footer--spaced'])

        @php
            $floatingWhatsAppNumber = $siteSettings['contact_whatsapp'] ?: config('darul_shofi.whatsapp_number');
            $floatingWhatsAppMessage = $whatsAppMessage ?: 'Assalamualaikum, saya ingin bertanya tentang pakej Umrah.';
        @endphp
        @if ($showFloatingWhatsApp && filled($floatingWhatsAppNumber))
            @include('darul-shofi.partials.floating-whatsapp', [
                'number' => $floatingWhatsAppNumber,
                'whatsAppMessage' => $floatingWhatsAppMessage,
            ])
        @endif
        <script src="{{ asset('darul-shofi/js/header.js') }}?v={{ filemtime(public_path('darul-shofi/js/header.js')) }}" defer></script>
    </body>
</html>

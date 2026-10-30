@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'siteSettings' => [],
])

@php
    $siteName = $siteSettings['company_name'];
    $pageTitle = filled($title) ? $title : $siteName;
    $pageDescription = filled($description)
        ? $description
        : 'Pakej Umrah, Haji, Badal Haji dan pelancongan SalamHaji Travel & Tours.';
    $canonicalUrl = url()->current();
    $socialImage = filled($image)
        ? url($image)
        : (filled($siteSettings['company_logo_url']) ? url($siteSettings['company_logo_url']) : null);
    $socialProfiles = collect([
        $siteSettings['facebook_url'],
        $siteSettings['instagram_url'],
        $siteSettings['tiktok_url'],
        $siteSettings['threads_url'],
    ])->filter()->values()->all();

    $organizationSchema = array_filter([
        '@type' => 'Organization',
        'name' => $siteName,
        'legalName' => $siteSettings['company_legal_name'],
        'url' => route('home'),
        'logo' => $socialImage,
        'description' => $pageDescription,
        'telephone' => $siteSettings['contact_phone'],
        'email' => $siteSettings['contact_email'],
        'sameAs' => $socialProfiles === [] ? null : $socialProfiles,
    ], fn (mixed $value): bool => filled($value));

    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            $organizationSchema,
            [
                '@type' => 'WebSite',
                'name' => $siteName,
                'alternateName' => 'SalamHaji',
                'url' => route('home'),
            ],
        ],
    ];

    $structuredDataJson = json_encode(
        $structuredData,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
    );
@endphp

<meta name="description" content="{{ $pageDescription }}">
<meta name="robots" content="index, follow, max-image-preview:large">
<link rel="canonical" href="{{ $canonicalUrl }}">
<meta property="og:type" content="website">
<meta property="og:locale" content="ms_MY">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:url" content="{{ $canonicalUrl }}">
@if (filled($socialImage))
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:alt" content="Logo {{ $siteName }}">
@endif
<meta name="twitter:card" content="{{ filled($socialImage) ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">

@if (request()->routeIs('home') && $structuredDataJson !== false)
    <script type="application/ld+json">{!! $structuredDataJson !!}</script>
@endif

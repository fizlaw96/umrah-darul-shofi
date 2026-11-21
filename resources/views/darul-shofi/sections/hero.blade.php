@php
    $fallbackPosters = [
        asset('darul-shofi/images/hero/umrah-ekonomi-madinah-hero.png'),
        asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png'),
        asset('darul-shofi/images/hero/umrah-ramadan-makkah-hero.png'),
        asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png'),
    ];
    $fallbackPoster = $fallbackPosters[0];
    $heroSlidesByCategory = $heroSlides->keyBy('package_category_id');

    $heroItems = collect($homepageCategories ?? [])
        ->values()
        ->take(4)
        ->map(function ($category, int $index) use ($fallbackPosters, $heroSlidesByCategory): array {
            $heroSlide = $heroSlidesByCategory->get($category->id);
            $ctaUrl = $heroSlide?->package
                ? route('packages.show', $heroSlide->package)
                : route('packages.index', ['category' => $category->slug]);

            return [
                'title' => $heroSlide?->title ?: $category->name,
                'subtitle' => $heroSlide?->subtitle ?: ($category->description ?: 'Terokai pilihan perjalanan ibadah bersama SalamHaji.'),
                'price' => $heroSlide?->package?->formattedStartingPrice() ?? $category->packages->first()?->formattedStartingPrice() ?? '',
                'cta_label' => $heroSlide?->cta_label ?: 'Terokai Pakej',
                'cta_url' => $ctaUrl,
                'poster' => $heroSlide?->imageUrl() ?: $fallbackPosters[$index % count($fallbackPosters)],
                'image_position' => $heroSlide?->background_image ? ($imageFocusPositions[$heroSlide->background_image] ?? '50% 50%') : '50% 50%',
                'video' => $heroSlide?->videoUrl() ?: '',
            ];
        });

    if ($heroItems->isEmpty()) {
        $heroItems = $heroSlides->values()->map(function ($heroSlide, int $index) use ($fallbackPosters): array {
            $ctaUrl = $heroSlide->package
                ? route('packages.show', $heroSlide->package)
                : ($heroSlide->category ? route('packages.index', ['category' => $heroSlide->category->slug]) : route('packages.index'));

            return [
                'title' => $heroSlide->title,
                'subtitle' => $heroSlide->subtitle,
                'price' => $heroSlide->package?->formattedStartingPrice() ?? '',
                'cta_label' => $heroSlide->cta_label ?: 'Lihat Pakej',
                'cta_url' => $ctaUrl,
                'poster' => $heroSlide->imageUrl() ?: $fallbackPosters[$index % count($fallbackPosters)],
                'image_position' => $heroSlide->background_image ? ($imageFocusPositions[$heroSlide->background_image] ?? '50% 50%') : '50% 50%',
                'video' => $heroSlide->videoUrl() ?: '',
            ];
        });

        if ($heroItems->isEmpty()) {
            $heroItems = collect([[
                'title' => $siteSettings['company_name'],
                'subtitle' => $siteSettings['company_tagline'],
                'price' => '',
                'cta_label' => 'Lihat Semua Pakej',
                'cta_url' => route('packages.index'),
                'poster' => $fallbackPoster,
                'image_position' => '50% 50%',
                'video' => '',
            ]]);
        }
    }

    $heroItems = $heroItems->map(function (array $heroItem): array {
        $responsivePoster = \App\Models\ImageDerivative::responsiveAttributesFor($heroItem['poster']);

        return $heroItem + [
            'poster_srcset' => $responsivePoster['srcset'] ?? '',
            'video_poster' => $responsivePoster['largest'] ?? $heroItem['poster'],
        ];
    });

    $firstHeroItem = $heroItems->first();
@endphp

<section class="ds-cinematic-hero" data-hero-carousel>
    <div class="ds-cinematic-hero__media" aria-hidden="true">
        <x-responsive-image :src="$firstHeroItem['poster']" sizes="100vw" :priority="true" class="ds-cinematic-hero__poster" data-hero-poster style="object-position: {{ $firstHeroItem['image_position'] }}" alt="" />
        <video class="ds-cinematic-hero__video ds-cinematic-hero__video--unavailable" data-hero-video muted loop playsinline preload="metadata" poster="{{ $firstHeroItem['video_poster'] }}"></video>
        <div class="ds-cinematic-hero__overlay"></div>
    </div>
    <div class="ds-cinematic-hero__content">
        <p class="ds-cinematic-hero__badge" data-hero-badge>{{ $siteSettings['company_name'] }}</p>
        <h1 class="ds-cinematic-hero__headline" data-hero-headline aria-label="{{ $firstHeroItem['title'] }} {{ $firstHeroItem['subtitle'] }}"><span class="ds-cinematic-hero__headline-line" data-hero-package-name>{{ $firstHeroItem['title'] }}</span><span class="ds-cinematic-hero__headline-line ds-cinematic-hero__headline-line--accent" data-hero-package-season>{{ $firstHeroItem['subtitle'] }}</span></h1>
        <p class="ds-cinematic-hero__price {{ blank($firstHeroItem['price']) ? 'hidden' : '' }}" data-hero-price-wrapper>Bermula dari <strong data-hero-package-price>{{ $firstHeroItem['price'] }}</strong></p>
        <a class="ds-cinematic-hero__cta" data-hero-cta href="{{ $firstHeroItem['cta_url'] }}">{{ $firstHeroItem['cta_label'] }} <span aria-hidden="true">↓</span></a>
    </div>
    <div hidden aria-hidden="true">
        @foreach ($heroItems as $heroItem)
            <span data-hero-slide data-hero-badge="{{ $siteSettings['company_name'] }}" data-hero-package-name="{{ $heroItem['title'] }}" data-hero-package-season="{{ $heroItem['subtitle'] }}" data-hero-package-price="{{ $heroItem['price'] }}" data-hero-cta-label="{{ $heroItem['cta_label'] }}" data-hero-cta-url="{{ $heroItem['cta_url'] }}" data-hero-poster="{{ $heroItem['poster'] }}" data-hero-poster-srcset="{{ $heroItem['poster_srcset'] }}" data-hero-video-poster="{{ $heroItem['video_poster'] }}" data-hero-image-position="{{ $heroItem['image_position'] }}" data-hero-video="{{ $heroItem['video'] }}" data-hero-video-position="center" data-hero-video-start="0"></span>
        @endforeach
    </div>
</section>

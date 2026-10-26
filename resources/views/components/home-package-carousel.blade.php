@props(['category', 'packages', 'index' => 0])

<section class="ds-package-category-section {{ $index % 2 === 0 ? 'ds-package-category-section--cream' : 'ds-package-category-section--white' }}" id="pakej-{{ $category->slug }}" data-package-carousel>
    <div class="ds-package-category-section__inner">
        <header class="ds-package-category-section__header">
            <div>
                <h2 class="ds-package-category-section__title">{{ $category->name }}</h2>
                @if (filled($category->description))
                    <p class="ds-package-category-section__description">{{ $category->description }}</p>
                @endif
            </div>
            <div class="ds-package-category-section__actions">
                <a class="ds-package-category-section__all" href="{{ route('packages.index', ['category' => $category->slug]) }}">Lihat Semua Pakej <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </header>
        @if ($packages->isEmpty())
            <div class="rounded-2xl border border-dashed border-[#d7ddd5] bg-white/70 px-6 py-10 text-center text-sm leading-6 text-[#69746f]">
                Pakej bagi kategori ini akan dikemas kini tidak lama lagi.
            </div>
        @else
            <div class="ds-package-category-scroller" data-package-scroller>
                @foreach ($packages as $package)
                    <article class="ds-home-package-card">
                        <button type="button" class="ds-home-package-card__poster" data-home-package-poster data-poster-src="{{ $package->posterImageUrl() }}" data-poster-alt="{{ $package->title }}" aria-label="Lihat poster {{ $package->title }}">
                            @if ($package->posterImageUrl())
                                <img src="{{ $package->posterImageUrl() }}" alt="{{ $package->title }}" loading="lazy">
                            @else
                                <span class="ds-home-package-card__empty-poster"><i class="fa-solid fa-kaaba"></i></span>
                            @endif
                        </button>
                        <div class="ds-home-package-card__body">
                            <h3 class="ds-home-package-card__title"><a href="{{ route('packages.show', $package) }}">{{ $package->title }}</a></h3>
                            <p class="ds-home-package-card__description">{{ $package->short_description }}</p>
                            <div class="ds-home-package-card__details">
                                @if ($package->departures->first())
                                    <p><i class="fa-regular fa-calendar"></i>{{ $package->departures->first()->departure_date->translatedFormat('d M Y') }}</p>
                                @endif
                                <p><i class="fa-regular fa-clock"></i>{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</p>
                            </div>
                            <div class="ds-home-package-card__footer">
                                <div><p>Bermula dari</p><strong>{{ $package->formattedStartingPrice() }}</strong></div>
                                <a href="{{ route('packages.show', $package) }}">Lihat</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

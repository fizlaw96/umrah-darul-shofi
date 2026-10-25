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
                <button type="button" data-package-scroll="previous" class="ds-package-scroll-button" aria-label="Pakej {{ $category->name }} sebelumnya"><i class="fa-solid fa-arrow-left"></i></button>
                <button type="button" data-package-scroll="next" class="ds-package-scroll-button" aria-label="Pakej {{ $category->name }} seterusnya"><i class="fa-solid fa-arrow-right"></i></button>
                <a class="ds-package-category-section__all" href="{{ route('packages.index', ['category' => $category->slug]) }}">Lihat Semua Pakej <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </header>
        <div class="ds-package-category-scroller" data-package-scroller>
            @foreach ($packages as $package)
                <article class="ds-home-package-card">
                    <a href="{{ route('packages.show', $package) }}" class="ds-home-package-card__poster" data-package-image-frame>
                        @if ($package->imageUrl())
                            <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" loading="lazy" data-package-image>
                        @else
                            <span class="ds-home-package-card__empty-poster"><i class="fa-solid fa-kaaba"></i></span>
                        @endif
                    </a>
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
    </div>
</section>

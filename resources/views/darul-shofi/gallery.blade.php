@extends('darul-shofi.layout')

@section('title', 'Galeri Jemaah | SalamHaji Travel & Tours')
@section('meta_description', 'Lihat galeri kenangan perjalanan dan ibadah jemaah bersama SalamHaji Travel & Tours.')

@section('content')
    @include('darul-shofi.partials.header')

    <main class="ds-full-gallery-page" data-full-gallery>
        <section class="ds-full-gallery-page__hero">
            <div class="ds-full-gallery-page__inner">
                <span>Galeri Perjalanan</span>
                <h1>Kenangan Indah Jemaah Bersama SalamHaji</h1>
                <p>Lihat semula momen persediaan, perjalanan dan ibadah jemaah kami mengikut kategori pakej.</p>
            </div>
        </section>

        <section class="ds-full-gallery-page__content">
            <div class="ds-full-gallery-page__toolbar">
                <div class="ds-full-gallery-page__filters" aria-label="Tapis galeri mengikut kategori">
                    <button class="is-active" type="button" data-full-gallery-filter="all" aria-pressed="true">Semua</button>
                    @foreach ($galleryCategories as $galleryCategory)
                        <button type="button" data-full-gallery-filter="{{ $galleryCategory->id }}" aria-pressed="false">{{ $galleryCategory->name }}</button>
                    @endforeach
                    @if ($galleryMedia->contains(fn ($mediaItem): bool => $mediaItem->package_category_id === null))
                        <button type="button" data-full-gallery-filter="general" aria-pressed="false">Umum</button>
                    @endif
                </div>
            </div>

            @if ($galleryMedia->isNotEmpty())
                <div class="ds-full-gallery-grid" data-full-gallery-grid data-full-gallery-view="grid">
                    @foreach ($galleryMedia as $mediaItem)
                        @php($categoryKey = $mediaItem->package_category_id ?: 'general')
                        @php($categoryLabel = $mediaItem->category?->name ?: 'Kenangan Umum')
                        <button type="button" class="ds-full-gallery-item" data-full-gallery-open data-full-gallery-category="{{ $categoryKey }}" data-full-gallery-src="{{ $mediaItem->url() }}" data-full-gallery-alt="{{ $mediaItem->alt_text ?: 'Foto galeri SalamHaji' }}" data-full-gallery-caption="{{ $categoryLabel }}" aria-label="Buka {{ $mediaItem->alt_text ?: 'foto galeri SalamHaji' }}">
                            <span class="ds-full-gallery-item__image"><x-responsive-image :src="$mediaItem->url()" sizes="(min-width: 1024px) 33vw, 50vw" alt="{{ $mediaItem->alt_text ?: 'Foto galeri SalamHaji' }}" style="object-position: {{ $imageFocusPositions[$mediaItem->path] ?? '50% 50%' }}" loading="lazy" decoding="async" /></span>
                        </button>
                    @endforeach
                </div>
            @else
                <div class="ds-full-gallery-page__empty"><i class="fa-solid fa-images"></i><h2>Galeri sedang dikemas kini</h2><p>Kenangan jemaah akan dipaparkan di sini tidak lama lagi.</p></div>
            @endif
        </section>

        <dialog aria-label="Paparan foto galeri" class="ds-lightbox" data-full-gallery-lightbox>
            <div class="ds-lightbox-inner">
                <button aria-label="Tutup galeri" class="ds-lightbox-close" data-full-gallery-close type="button"><i class="fa-solid fa-xmark"></i></button>
                <button aria-label="Foto sebelumnya" class="ds-lightbox-step ds-lightbox-step--prev" data-full-gallery-prev type="button"><i class="fa-solid fa-chevron-left"></i></button>
                <figure><img alt="" data-full-gallery-preview decoding="async"><figcaption data-full-gallery-caption></figcaption></figure>
                <button aria-label="Foto seterusnya" class="ds-lightbox-step ds-lightbox-step--next" data-full-gallery-next type="button"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </dialog>
    </main>

    @include('darul-shofi.partials.footer')
    @include('darul-shofi.partials.floating-whatsapp')
@endsection

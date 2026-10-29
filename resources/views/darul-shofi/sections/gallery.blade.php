{{-- Galeri ini diurus daripada CMS: Pentadbir > Galeri & Media. --}}
@php
    $galleryItems = $galleryMedia
        ->map(fn ($mediaItem): array => [
            'url' => $mediaItem->url(),
            'path' => $mediaItem->path,
            'alt' => $mediaItem->alt_text ?: 'Foto galeri SalamHaji',
        ]);

    $usingTemporaryGalleryImages = $galleryItems->isEmpty();

    if ($usingTemporaryGalleryImages) {
        $galleryItems = collect([
            [
                'url' => asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png'),
                'path' => null,
                'alt' => 'Pemandangan Masjidil Haram di Mekah',
            ],
            [
                'url' => asset('darul-shofi/images/hero/umrah-ramadan-makkah-hero.png'),
                'path' => null,
                'alt' => 'Suasana ibadah di Mekah',
            ],
            [
                'url' => asset('darul-shofi/images/hero/umrah-ekonomi-madinah-hero.png'),
                'path' => null,
                'alt' => 'Pemandangan Masjid Nabawi di Madinah',
            ],
        ]);
    }

    while ($galleryItems->count() < 5) {
        $galleryItems = $galleryItems->concat($galleryItems);
    }
@endphp

<section class="overflow-hidden bg-[#FAF8F5] py-20 lg:py-28" data-purpose="motion-gallery" id="galeri">
    <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
        <span class="mb-2 inline-block text-xs font-bold uppercase tracking-widest text-brand-gold-dark">Galeri Perjalanan</span>
        <h2 class="font-serif-luxury text-3xl font-bold text-brand-emerald sm:text-4xl">Kenangan Indah Jemaah Bersama SalamHaji</h2>
        <p class="mt-2 text-sm text-brand-muted">Setiap detik terukir penuh kerinduan dan kesyukuran di Tanah Suci</p>
        <a href="{{ route('gallery.index') }}" class="mt-5 inline-flex items-center gap-2 rounded-full border border-brand-emerald/20 bg-white px-4 py-2 text-xs font-bold text-brand-emerald transition hover:bg-brand-emerald hover:text-white">Lihat Galeri Penuh <i class="fa-solid fa-arrow-right text-[10px]"></i></a>

        @if ($usingTemporaryGalleryImages)
            <p class="mt-4 text-xs font-medium text-brand-gold-dark">Imej paparan sementara — akan diganti secara automatik apabila foto galeri dimuat naik.</p>
        @endif
    </div>

    <div class="mt-12 space-y-4">
        @foreach ([0, 1, 2] as $rowOffset)
            @php
                $rowItems = $galleryItems
                    ->slice($rowOffset)
                    ->concat($galleryItems->take($rowOffset));
            @endphp

            <div class="relative w-full overflow-hidden">
                <div class="ds-marquee-track {{ $loop->even ? 'ds-marquee-track--forward' : 'ds-marquee-track--reverse' }}">
                    <div class="ds-marquee-sequence ds-marquee-sequence--gallery">
                        @foreach ($rowItems as $galleryItem)
                            <button type="button" data-gallery-open="{{ ($loop->parent->index * $rowItems->count()) + $loop->index }}" class="ds-gallery-open h-40 w-64 shrink-0 overflow-hidden rounded-xl border border-brand-gold/20 bg-brand-cream shadow-sm" aria-label="Buka {{ $galleryItem['alt'] }}">
                                <img src="{{ $galleryItem['url'] }}" alt="{{ $galleryItem['alt'] }}" class="size-full object-cover" style="object-position: {{ $galleryItem['path'] ? ($imageFocusPositions[$galleryItem['path']] ?? '50% 50%') : '50% 50%' }}" decoding="async" loading="lazy">
                            </button>
                        @endforeach
                    </div>

                    <div aria-hidden="true" class="ds-marquee-sequence ds-marquee-sequence--gallery">
                        @foreach ($rowItems as $galleryItem)
                            <div class="h-40 w-64 shrink-0 overflow-hidden rounded-xl border border-brand-gold/20 bg-brand-cream shadow-sm">
                                <img src="{{ $galleryItem['url'] }}" alt="" class="size-full object-cover" style="object-position: {{ $galleryItem['path'] ? ($imageFocusPositions[$galleryItem['path']] ?? '50% 50%') : '50% 50%' }}" decoding="async" loading="lazy">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <dialog aria-label="Paparan foto galeri" class="ds-lightbox" id="darul-gallery-lightbox">
        <div class="ds-lightbox-inner">
            <button aria-label="Tutup galeri" class="ds-lightbox-close" data-gallery-close type="button"><i class="fa-solid fa-xmark"></i></button>
            <button aria-label="Foto sebelumnya" class="ds-lightbox-step ds-lightbox-step--prev" data-gallery-prev type="button"><i class="fa-solid fa-chevron-left"></i></button>
            <figure>
                <img alt="" data-gallery-preview decoding="async" loading="lazy">
            </figure>
            <button aria-label="Foto seterusnya" class="ds-lightbox-step ds-lightbox-step--next" data-gallery-next type="button"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </dialog>
</section>

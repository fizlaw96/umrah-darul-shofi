@php
    $aboutItems = $aboutMedia->values();
    $aboutCtaUrl = $siteSettings['about_cta_url'] ?: route('about');
    $aboutHeadingTag = $aboutHeadingTag ?? 'h2';
@endphp

<section class="bg-[#FAF8F5] py-20 lg:py-28" id="tentang">
    <div class="mx-auto grid max-w-7xl items-center gap-12 px-4 sm:px-6 lg:grid-cols-12 lg:gap-16 lg:px-8">
        <div class="grid grid-cols-2 gap-4 lg:col-span-6">
            @foreach (range(0, 3) as $imageIndex)
                @php($mediaItem = $aboutItems->get($imageIndex))

                <div class="ds-about-photo {{ in_array($imageIndex, [0, 3], true) ? 'ds-about-photo--portrait' : 'ds-about-photo--square' }} {{ in_array($imageIndex, [1, 3], true) ? 'ds-about-photo--offset' : '' }}">
                    @if ($mediaItem)
                        <x-responsive-image :src="$mediaItem->url()" sizes="(min-width: 1024px) 25vw, 50vw" alt="{{ $mediaItem->alt_text ?: 'Foto SalamHaji' }}" class="ds-about-photo__image" decoding="async" loading="lazy" style="object-position: {{ $imageFocusPositions[$mediaItem->path] ?? '50% 50%' }}" />
                    @else
                        <div class="ds-about-photo__placeholder">Slot gambar {{ $imageIndex + 1 }}<br>urus melalui Kandungan Homepage</div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="lg:col-span-6">
            <span class="mb-3 inline-block text-xs font-bold uppercase tracking-widest text-brand-gold-dark">{{ $siteSettings['about_eyebrow'] ?: 'Bersama Menuju Baitullah' }}</span>
            <{{ $aboutHeadingTag }} class="font-serif-luxury text-4xl font-bold leading-tight text-brand-emerald sm:text-5xl" data-reveal>{{ $siteSettings['about_heading'] ?: 'Perjalanan suci, amanah kami' }}</{{ $aboutHeadingTag }}>
            <div class="mt-6 whitespace-pre-line text-base font-light leading-relaxed text-gray-700">{{ $siteSettings['about_description'] ?: $siteSettings['homepage_company_intro'] ?: 'Kandungan pengenalan SalamHaji boleh dikemas kini oleh pentadbir.' }}</div>
            <a data-about-cta class="mt-8 inline-flex items-center gap-3 rounded-full bg-brand-emerald px-7 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-brand-emerald-dark" href="{{ $aboutCtaUrl }}">
                <span>{{ $siteSettings['about_cta_label'] ?: 'Kenali Kami Lebih Dekat' }}</span>
                <i class="fa-solid fa-arrow-right text-xs text-brand-gold"></i>
            </a>
        </div>
    </div>
</section>

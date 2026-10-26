<x-layouts.app title="Semua Pakej" :show-floating-whats-app="false">
    <section class="bg-mint py-14 sm:py-20">
        <div class="container-page">
            <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-brand">Katalog pakej</p>
            <h1 class="mt-2 font-display text-5xl text-dark-green sm:text-6xl">Cari pakej perjalanan anda</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-600">Gunakan penapis untuk mencari pilihan yang bersesuaian dengan rancangan perjalanan anda.</p>
        </div>
    </section>

    <section class="container-page py-10 sm:py-14">
        <form method="GET" class="panel flex flex-col gap-4 p-5 sm:flex-row sm:flex-wrap xl:flex-nowrap xl:items-end" data-package-filters>
            <label class="w-full sm:min-w-48 sm:flex-1 xl:w-48 xl:flex-none"><span class="field-label">Kategori</span><select class="field-control" name="category" data-auto-submit><option value="">Semua</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
            <label class="w-full sm:min-w-52 sm:flex-1 xl:w-52 xl:flex-none"><span class="field-label">Bulan berlepas</span><select class="field-control" name="month" data-auto-submit><option value="">Semua bulan</option>@foreach ($departureMonths as $month)<option value="{{ $month }}" @selected(request('month') === $month)>{{ \Carbon\CarbonImmutable::createFromFormat('Y-m', $month, 'Asia/Kuala_Lumpur')->translatedFormat('F Y') }}</option>@endforeach</select></label>
            <section class="w-full sm:basis-full xl:min-w-72 xl:flex-1" aria-labelledby="package-budget-label" data-budget-filter>
                <div class="flex items-center justify-between gap-3">
                    <span id="package-budget-label" class="field-label !mb-0">Bajet anda</span>
                    <output class="text-xs font-bold text-brand" aria-live="polite" data-budget-output></output>
                </div>
                <p class="mt-1 text-[11px] text-slate-500">Tarik pemegang untuk tetapkan julat bajet.</p>
                <div class="package-budget-range" data-budget-range>
                    <span class="package-budget-range__track" aria-hidden="true"></span>
                    <span class="package-budget-range__selection" aria-hidden="true" data-budget-selection></span>
                    <label class="sr-only" for="package-budget-minimum">Bajet minimum</label>
                    <input id="package-budget-minimum" class="package-budget-slider" type="range" name="min_price" min="{{ $priceRange['minimum'] }}" max="{{ $priceRange['maximum'] }}" step="100" value="{{ $priceRange['selectedMinimum'] }}" aria-describedby="package-budget-help" data-budget-minimum>
                    <label class="sr-only" for="package-budget-maximum">Bajet maksimum</label>
                    <input id="package-budget-maximum" class="package-budget-slider" type="range" name="max_price" min="{{ $priceRange['minimum'] }}" max="{{ $priceRange['maximum'] }}" step="100" value="{{ $priceRange['selectedMaximum'] }}" aria-describedby="package-budget-help" data-budget-maximum>
                </div>
                <span id="package-budget-help" class="sr-only">Hasil pakej dikemas kini selepas julat bajet dipilih.</span>
            </section>
            <label class="w-full sm:min-w-48 sm:flex-1 xl:w-48 xl:flex-none"><span class="field-label">Susun mengikut</span><select class="field-control" name="sort" data-auto-submit><option value="newest" @selected($selectedSort === 'newest')>Terbaharu</option><option value="price_asc" @selected($selectedSort === 'price_asc')>Harga terendah</option><option value="price_desc" @selected($selectedSort === 'price_desc')>Harga tertinggi</option><option value="departure" @selected($selectedSort === 'departure')>Tarikh berlepas</option></select></label>
            <a class="button-secondary w-full sm:w-auto xl:shrink-0" href="{{ route('packages.index') }}"><span class="material-symbols-outlined text-[18px]">restart_alt</span>Set semula</a>
            <noscript><button class="button-primary" type="submit">Tapis pakej</button></noscript>
        </form>

        <div class="mt-10 flex items-center justify-between gap-4"><p class="text-sm font-semibold text-slate-500">{{ $packages->total() }} pakej ditemui</p></div>
        @if ($packages->isNotEmpty())
            <div class="mt-5 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-3 [scrollbar-width:thin] sm:grid sm:grid-cols-2 sm:overflow-visible sm:pb-0 xl:grid-cols-3">@foreach ($packages as $package)<x-package-card :package="$package" />@endforeach</div>
            <div class="mt-10">{{ $packages->links() }}</div>
        @else
            <div class="panel mt-5 p-10 text-center"><p class="text-lg font-extrabold text-dark-green">Tiada pakej sepadan buat masa ini.</p><p class="mt-2 text-sm text-slate-500">Cuba longgarkan penapis anda untuk melihat pakej yang tersedia.</p><a class="button-primary mt-5" href="{{ route('packages.index') }}">Lihat semua pakej</a></div>
        @endif
    </section>

    <dialog class="m-auto w-[min(92vw,44rem)] border-0 bg-transparent p-0 text-white backdrop:bg-[#001d16]/90 backdrop:backdrop-blur-sm" data-package-poster-lightbox aria-label="Paparan poster pakej">
        <div class="relative overflow-hidden rounded-2xl border border-[#d5b469]/60 bg-[#00271f] p-4 shadow-2xl sm:p-6">
            <button type="button" class="absolute right-3 top-3 grid size-10 place-items-center rounded-full border border-[#d5b469]/60 bg-[#073e34] text-white transition hover:bg-[#184e3f] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#ffdf9a]" data-package-poster-lightbox-close aria-label="Tutup poster">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
            <figure class="m-0 text-center">
                <img src="" alt="" class="mx-auto max-h-[78vh] max-w-full rounded-lg object-contain" data-package-poster-lightbox-image>
                <figcaption class="mt-4 pr-10 text-sm font-semibold text-[#ffdf9a]" data-package-poster-lightbox-caption></figcaption>
            </figure>
        </div>
    </dialog>
</x-layouts.app>

@if ($homepageAdditionalPackages->isNotEmpty())
    <section class="border-y border-[#ECE7E0] bg-white py-16" id="ziarah">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-8 flex items-center justify-between"><div><span class="mb-1 inline-block text-xs font-bold uppercase tracking-widest text-brand-gold-dark">Pakej Popular &amp; Lanjutan</span><h3 class="font-serif-luxury text-2xl font-bold text-brand-emerald sm:text-3xl">Lebih Banyak Pilihan Perjalanan</h3></div><a class="text-sm font-semibold text-brand-emerald hover:text-brand-gold-dark" href="{{ route('packages.index') }}">Teroka semua <span aria-hidden="true">→</span></a></div>
            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-5">
                @foreach ($homepageAdditionalPackages as $package)
                    <a class="ds-secondary-card group block rounded-xl border border-brand-gold/20 bg-brand-cream p-3 transition duration-200 hover:border-brand-gold" href="{{ route('packages.show', $package) }}"><div class="mb-3 h-28 overflow-hidden rounded-lg bg-brand-emerald">@if ($package->imageUrl())<img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover transition duration-300 group-hover:scale-105" loading="lazy">@else<span class="grid size-full place-items-center text-brand-gold"><i class="fa-solid fa-kaaba text-2xl"></i></span>@endif</div><h4 class="truncate text-xs font-semibold text-brand-charcoal">{{ $package->title }}</h4><p class="mt-1 text-[11px] font-bold text-brand-gold-dark">Dari {{ $package->formattedStartingPrice() }}</p></a>
                @endforeach
            </div>
        </div>
    </section>
@endif

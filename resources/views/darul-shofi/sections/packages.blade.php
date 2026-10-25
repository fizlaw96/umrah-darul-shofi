<section class="ds-package-overview" id="pakej">
    <div class="ds-package-overview__inner">
        <p class="ds-package-overview__eyebrow">{{ $siteSettings['homepage_package_heading'] ?: 'Pilihan Pakej SalamHaji' }}</p>
        <p class="ds-package-overview__description">Terokai pakej yang telah diterbitkan mengikut kategori perjalanan.</p>
    </div>
</section>
@forelse ($homepageCategories as $category)
    <x-home-package-carousel :category="$category" :packages="$category->packages" :index="$loop->index" />
@empty
    <section class="bg-[#FAF8F5] py-16"><div class="mx-auto max-w-2xl px-4 text-center"><i class="fa-solid fa-kaaba text-3xl text-brand-gold"></i><h2 class="mt-4 font-serif-luxury text-3xl font-bold text-brand-emerald">Pakej sedang dikemas kini</h2><p class="mt-3 text-sm leading-6 text-brand-muted">Sila lihat katalog atau hubungi pasukan kami untuk maklumat terkini.</p><a class="mt-5 inline-flex rounded-full bg-brand-emerald px-5 py-3 text-sm font-bold text-white" href="{{ route('packages.index') }}">Lihat Semua Pakej</a></div></section>
@endforelse

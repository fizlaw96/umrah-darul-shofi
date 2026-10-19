<x-layouts.app title="Pakej Umrah">
    <section class="bg-mint py-14 sm:py-20">
        <div class="container-page">
            <p class="text-sm font-extrabold uppercase tracking-[0.18em] text-brand">Katalog pakej</p>
            <h1 class="mt-2 font-['DM_Serif_Display'] text-5xl text-dark-green sm:text-6xl">Cari pakej Umrah anda</h1>
            <p class="mt-4 max-w-2xl leading-7 text-slate-600">Gunakan penapis untuk mencari pilihan yang bersesuaian dengan rancangan perjalanan anda.</p>
        </div>
    </section>

    <section class="container-page py-10 sm:py-14">
        <form method="GET" class="panel grid gap-4 p-5 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
            <label><span class="field-label">Kata kunci</span><input class="field-control" name="q" value="{{ request('q') }}" placeholder="Nama pakej"></label>
            <label><span class="field-label">Kategori</span><select class="field-control" name="category"><option value="">Semua</option>@foreach ($categories as $category)<option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>@endforeach</select></label>
            <label><span class="field-label">Tarikh berlepas</span><input class="field-control" type="date" name="departure_date" value="{{ request('departure_date') }}"></label>
            <label><span class="field-label">Bajet min. (RM)</span><input class="field-control" type="number" name="min_price" value="{{ request('min_price') }}"></label>
            <label><span class="field-label">Bajet maks. (RM)</span><input class="field-control" type="number" name="max_price" value="{{ request('max_price') }}"></label>
            <label><span class="field-label">Susun mengikut</span><select class="field-control" name="sort"><option value="newest" @selected($selectedSort === 'newest')>Terbaharu</option><option value="price_asc" @selected($selectedSort === 'price_asc')>Harga terendah</option><option value="price_desc" @selected($selectedSort === 'price_desc')>Harga tertinggi</option><option value="departure" @selected($selectedSort === 'departure')>Tarikh berlepas</option></select></label>
            <div class="flex gap-3 xl:col-span-6"><button class="button-primary" type="submit">Tapis pakej</button><a class="button-secondary" href="{{ route('packages.index') }}">Set semula</a></div>
        </form>

        <div class="mt-10 flex items-center justify-between gap-4"><p class="text-sm font-semibold text-slate-500">{{ $packages->total() }} pakej ditemui</p></div>
        @if ($packages->isNotEmpty())
            <div class="mt-5 grid gap-6 md:grid-cols-2 xl:grid-cols-3">@foreach ($packages as $package)<x-package-card :package="$package" :whatsapp-number="$whatsappNumber" />@endforeach</div>
            <div class="mt-10">{{ $packages->links() }}</div>
        @else
            <div class="panel mt-5 p-10 text-center"><p class="text-lg font-extrabold text-dark-green">Tiada pakej sepadan buat masa ini.</p><p class="mt-2 text-sm text-slate-500">Cuba longgarkan penapis anda atau hubungi pasukan kami.</p><a class="button-primary mt-5" href="{{ route('contact.create') }}">Tanya kami</a></div>
        @endif
    </section>
</x-layouts.app>

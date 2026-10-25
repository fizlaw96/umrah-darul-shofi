<x-layouts.admin heading="Pengurusan Pakej">
    <section class="flex flex-col justify-between gap-5 xl:flex-row xl:items-end">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#64716a]">Katalog pelancongan</p>
            <h1 class="mt-1 text-4xl font-bold tracking-[-0.045em] text-[#00251e]">Pengurusan Pakej Umrah</h1>
            <p class="mt-2 text-sm leading-6 text-[#69746f]">Tambah, kemas kini, terbitkan dan urus susunan pakej yang dipaparkan di website rasmi.</p>
        </div>
        <div class="grid grid-cols-3 divide-x divide-[#edf0ec] overflow-hidden rounded-2xl border border-[#edf0ec] bg-white shadow-[0_12px_30px_-24px_rgba(0,37,30,.35)]">
            <div class="px-5 py-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wide text-[#7b8580]">Aktif Terbit</p><p class="mt-1 text-lg font-bold text-[#006b57]">{{ $statusCounts['published'] }} Pakej</p></div>
            <div class="px-5 py-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wide text-[#7b8580]">Draf</p><p class="mt-1 text-lg font-bold text-[#9b6910]">{{ $statusCounts['draft'] }} Pakej</p></div>
            <div class="px-5 py-3 text-center"><p class="text-[10px] font-bold uppercase tracking-wide text-[#7b8580]">Diarkib</p><p class="mt-1 text-lg font-bold text-[#64716a]">{{ $statusCounts['archived'] }} Pakej</p></div>
        </div>
    </section>

    <section class="admin-card mt-7 p-4">
        <form class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]" method="GET" data-auto-submit-filters>
            <label class="relative"><span class="sr-only">Cari pakej</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[#708078]">search</span><input class="admin-field pl-10" name="q" value="{{ request('q') }}" placeholder="Cari nama pakej, kod, hotel atau musim..."></label>
            <label><span class="sr-only">Kategori</span><select class="admin-field" name="category"><option value="">Semua Kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label><span class="sr-only">Status</span><select class="admin-field" name="status"><option value="">Semua Status</option><option value="published" @selected(request('status') === 'published')>Diterbitkan</option><option value="draft" @selected(request('status') === 'draft')>Draf</option><option value="archived" @selected(request('status') === 'archived')>Diarkibkan</option></select></label>
            <div class="flex justify-end"><a class="admin-button-primary whitespace-nowrap" href="{{ route('admin.packages.create') }}"><span class="material-symbols-outlined text-[18px]">add_circle</span>Tambah Pakej Baru</a></div>
        </form>
    </section>

    <form id="bulk-package-form" method="POST" action="{{ route('admin.packages.bulk') }}" class="mt-5">@csrf
        <div data-bulk-toolbar hidden class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-[#b9e4cb] bg-[#e9f7ed] px-4 py-3">
            <p class="text-sm font-bold text-[#075741]"><span data-bulk-count>0</span> pakej dipilih</p>
            <div class="flex gap-2"><button class="admin-button-secondary !border-[#8ccdab] !bg-white !py-2" type="submit" name="action" value="publish">Terbitkan dipilih</button><button class="admin-button-secondary !border-[#e7bdad] !bg-white !py-2 !text-[#a4392b]" type="submit" name="action" value="archive" data-confirm-archive>Arkibkan dipilih</button><button class="admin-button-secondary !py-2" type="button" data-clear-selection>Kosongkan</button></div>
        </div>
    </form>

    <section class="admin-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1060px] text-left">
                <thead class="bg-[#e9f7ed] text-[10px] font-bold uppercase tracking-[0.12em] text-[#64716a]"><tr><th class="w-12 px-4 py-4"><input data-select-all class="size-4 rounded border-[#aab9af] accent-[#007c68]" type="checkbox" aria-label="Pilih semua pakej"></th><th class="px-3 py-4">Gambar</th><th class="px-4 py-4">Nama Pakej</th><th class="px-4 py-4">Kategori</th><th class="px-4 py-4 text-right">Harga Bermula</th><th class="px-4 py-4">Tempoh &amp; Hotel</th><th class="px-4 py-4 text-center">Paparan Homepage</th><th class="px-4 py-4">Status</th><th class="px-4 py-4"><span class="sr-only">Tindakan</span></th></tr></thead>
                <tbody class="divide-y divide-[#e7eee9]">
                    @forelse ($packages as $package)
                        <tr class="transition hover:bg-[#fbfdfb]">
                            <td class="px-4 py-4"><input data-package-selection form="bulk-package-form" class="size-4 rounded border-[#aab9af] accent-[#007c68]" type="checkbox" name="package_ids[]" value="{{ $package->id }}" aria-label="Pilih {{ $package->title }}"></td>
                            <td class="px-3 py-4">@if ($package->imageUrl())<img src="{{ $package->imageUrl() }}" alt="" class="size-12 rounded-xl object-cover">@else<span class="grid size-12 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">image</span></span>@endif</td>
                            <td class="px-4 py-4"><a href="{{ route('admin.packages.edit', $package) }}" class="block max-w-52 font-bold leading-5 text-[#00251e] hover:text-[#006b57]">{{ $package->title }}</a><p class="mt-1 text-[10px] font-medium tracking-wide text-[#6d7973]">{{ $package->code ?: 'Kod belum ditetapkan' }}@if ($package->season_label) · {{ $package->season_label }}@endif</p></td>
                            <td class="px-4 py-4"><span class="inline-flex rounded-full bg-[#e4f2e8] px-3 py-1.5 text-[10px] font-bold tracking-[.05em] text-[#245446]">{{ $package->category->name }}</span></td>
                            <td class="px-4 py-4 text-right"><p class="font-bold text-[#00251e]">{{ $package->formattedStartingPrice() }}</p><p class="mt-1 text-[10px] text-[#77827c]">seorang</p></td>
                            <td class="px-4 py-4"><p class="text-xs font-bold text-[#163a30]">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</p><p class="mt-1 max-w-36 text-[10px] leading-4 text-[#77827c]">{{ $package->makkah_hotel ?: $package->hotel_information ?: 'Maklumat hotel belum ditetapkan' }}</p></td>
                            <td class="px-4 py-4 text-center">@if ($package->show_on_homepage)<span class="inline-flex items-center gap-1 rounded-full bg-[#e9f7ed] px-2.5 py-1 text-[10px] font-bold text-[#006b57]"><span class="material-symbols-outlined text-[14px]">visibility</span>{{ $package->is_featured ? 'Pilihan' : 'Papar' }}</span>@else<span class="text-[10px] font-medium text-[#85908a]">Tidak</span>@endif</td>
                            <td class="px-4 py-4"><x-admin.status-badge :status="$package->status" /></td>
                            <td class="px-4 py-4"><div class="flex justify-end gap-1"><a href="{{ route('admin.packages.show', $package) }}" class="grid size-9 place-items-center rounded-lg text-[#234b3e] transition hover:bg-[#e9f7ed]" aria-label="Lihat peserta {{ $package->title }}"><span class="material-symbols-outlined text-[19px]">groups</span></a><a href="{{ route('admin.packages.edit', $package) }}" class="grid size-9 place-items-center rounded-lg text-[#234b3e] transition hover:bg-[#e9f7ed]" aria-label="Urus {{ $package->title }}"><span class="material-symbols-outlined text-[19px]">edit</span></a>@if ($package->status === 'archived')<form method="POST" action="{{ route('admin.packages.restore', $package) }}">@csrf @method('PUT')<button class="grid size-9 place-items-center rounded-lg text-[#006b57] transition hover:bg-[#e9f7ed]" aria-label="Pulihkan {{ $package->title }}"><span class="material-symbols-outlined text-[19px]">restore</span></button></form>@endif</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-6 py-14 text-center"><span class="material-symbols-outlined text-4xl text-[#9ab0a2]">luggage</span><p class="mt-3 font-bold text-[#163a30]">Tiada pakej ditemui.</p><p class="mt-1 text-sm text-[#718078]">Cuba tetapkan semula penapis atau tambah pakej baharu.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="flex flex-col gap-3 border-t border-[#e7eee9] px-5 py-4 text-xs text-[#718078] sm:flex-row sm:items-center sm:justify-between"><p>Menunjukkan {{ $packages->firstItem() ?? 0 }} hingga {{ $packages->lastItem() ?? 0 }} daripada {{ $packages->total() }} rekod pakej</p><div>{{ $packages->links() }}</div></div>
    </section>
</x-layouts.admin>

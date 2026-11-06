<x-layouts.admin heading="Aqiqah & Korban">
    <div class="mx-auto max-w-6xl" data-aqiqah-korban-admin-index>
        <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-eyebrow">Perkhidmatan</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#00251e] sm:text-3xl">Aqiqah &amp; Korban</h1>
                <p class="mt-2 text-sm text-[#69746f]">Urus, tambah dan kemas kini tawaran perkhidmatan. Hanya tawaran diterbitkan akan dipaparkan kepada pelanggan.</p>
            </div>
            <a class="admin-button-primary" href="{{ route('admin.aqiqah-korban-services.create') }}"><span class="material-symbols-outlined text-[18px]">add</span>Tambah Perkhidmatan</a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="aq-admin-stat-card"><span class="aq-admin-stat-card__icon"><span class="material-symbols-outlined">inventory_2</span></span><div><strong>{{ $serviceCounts['all'] }}</strong><span>Jumlah Perkhidmatan<br>Keseluruhan</span></div></article>
            <article class="aq-admin-stat-card"><span class="aq-admin-stat-card__icon is-gold"><span class="material-symbols-outlined">pets</span></span><div><strong>{{ $serviceCounts['aqiqah'] }}</strong><span>Aqiqah<br>Aktif / draf</span></div></article>
            <article class="aq-admin-stat-card"><span class="aq-admin-stat-card__icon is-gold"><span class="material-symbols-outlined">agriculture</span></span><div><strong>{{ $serviceCounts['korban'] }}</strong><span>Korban<br>Aktif / draf</span></div></article>
            <article class="aq-admin-stat-card"><span class="aq-admin-stat-card__icon is-amber"><span class="material-symbols-outlined">visibility</span></span><div><strong>{{ $serviceCounts['published'] }}</strong><span>Dipaparkan<br>di website</span></div></article>
        </section>

        <form class="aq-admin-whatsapp-card mt-5" method="POST" action="{{ route('admin.aqiqah-korban-services.contact.update') }}">
            @csrf
            @method('PUT')

            <span class="aq-admin-whatsapp-card__icon"><span class="material-symbols-outlined">chat</span></span>
            <div class="min-w-0">
                <h2>Tetapan WhatsApp</h2>
                <p>Gunakan nombor WhatsApp untuk pertanyaan pelanggan.</p>
            </div>
            <label class="aq-admin-whatsapp-card__toggle"><span>Guna nombor rasmi umum</span><span class="admin-toggle"><input class="sr-only" type="checkbox" name="aqiqah_korban_use_general_whatsapp" value="1" @checked(old('aqiqah_korban_use_general_whatsapp', $contactSettings['aqiqah_korban_use_general_whatsapp'] === '1'))><span></span></span></label>
            <label class="min-w-0"><span class="sr-only">WhatsApp khusus</span><input class="admin-field" name="aqiqah_korban_whatsapp" value="{{ old('aqiqah_korban_whatsapp', $contactSettings['aqiqah_korban_whatsapp']) }}" placeholder="{{ $contactSettings['contact_whatsapp'] ?: '+60 12 345 6789' }}"></label>
            <button class="admin-button-primary !px-4 !py-2.5" type="submit">Simpan</button>
            <x-errors />
        </form>

        <section class="admin-card mt-5 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[#edf1ee] p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
                <div class="aq-admin-filter-tabs" role="tablist" aria-label="Tapis perkhidmatan">
                    <button type="button" class="is-active" data-aqiqah-korban-admin-filter="all">Semua ({{ $serviceCounts['all'] }})</button>
                    <button type="button" data-aqiqah-korban-admin-filter="Aqiqah">Aqiqah ({{ $serviceCounts['aqiqah'] }})</button>
                    <button type="button" data-aqiqah-korban-admin-filter="Korban">Korban ({{ $serviceCounts['korban'] }})</button>
                </div>
                <label class="relative block sm:w-60"><span class="sr-only">Cari perkhidmatan</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-[#7a8880]">search</span><input class="admin-field !bg-[#f5f8f5] !py-2.5 pl-10 text-xs" type="search" placeholder="Cari perkhidmatan..." data-aqiqah-korban-admin-search></label>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="border-b border-[#edf1ee] bg-[#fafcf9] text-[10px] font-bold uppercase tracking-[.08em] text-[#7a8880]">
                        <tr><th class="w-12 px-5 py-3.5">#</th><th class="px-3 py-3.5">Perkhidmatan</th><th class="px-3 py-3.5">Kategori</th><th class="px-3 py-3.5">Jenis haiwan</th><th class="px-3 py-3.5">Harga</th><th class="px-3 py-3.5">Status Paparan</th><th class="px-5 py-3.5 text-right">Tindakan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf1ee]" data-aqiqah-korban-admin-rows>
                        @forelse ($services as $service)
                            <tr data-aqiqah-korban-admin-row data-category="{{ $service->category }}" data-search="{{ \Illuminate\Support\Str::lower($service->name.' '.$service->animal_type.' '.$service->quantity_description) }}">
                                <td class="px-5 py-4 text-xs font-semibold text-[#7a8880]">{{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}</td>
                                <td class="px-3 py-4"><div class="flex items-center gap-3">@if ($service->imageUrl())<img src="{{ $service->imageUrl() }}" alt="" class="size-10 rounded-lg object-cover">@else<span class="grid size-10 place-items-center rounded-lg bg-[#edf7ef] text-[#19705d]"><span class="material-symbols-outlined text-[19px]">{{ $service->category === 'Aqiqah' ? 'pets' : 'agriculture' }}</span></span>@endif<div><p class="font-bold text-[#163a30]">{{ $service->name }}</p><p class="mt-0.5 text-[11px] text-[#7a8880]">{{ $service->quantity_description ?: 'Kuantiti belum dinyatakan' }}</p></div></div></td>
                                <td class="px-3 py-4"><span class="aq-admin-category-badge {{ $service->category === 'Aqiqah' ? 'is-aqiqah' : 'is-korban' }}">{{ $service->category }}</span></td>
                                <td class="px-3 py-4 text-xs font-semibold text-[#506058]">{{ $service->animal_type ?: '—' }}</td>
                                <td class="px-3 py-4 text-xs font-bold text-[#5a461a]">{{ $service->priceLabel() }}</td>
                                <td class="px-3 py-4"><span class="aq-admin-status-badge {{ $service->is_published ? 'is-published' : 'is-draft' }}"><span class="material-symbols-outlined">{{ $service->is_published ? 'check_circle' : 'edit_note' }}</span>{{ $service->is_published ? 'Dipaparkan' : 'Draf' }}</span>@if ($service->is_published && ! $service->isOpenForRegistration())<span class="mt-1 block text-[11px] font-semibold text-[#9b3029]">Di luar tarikh pendaftaran</span>@elseif ($service->registrationWindowLabel())<span class="mt-1 block text-[11px] text-[#7a8880]">{{ $service->registrationWindowLabel() }}</span>@endif</td>
                                <td class="px-5 py-4"><div class="flex justify-end gap-1"><a class="grid size-9 place-items-center rounded-lg text-[#234b3e] transition hover:bg-[#e9f7ed]" href="{{ route('admin.aqiqah-korban-services.edit', $service) }}" aria-label="Urus {{ $service->name }}"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">edit</span></a><form method="POST" action="{{ route('admin.aqiqah-korban-services.destroy', $service) }}" data-confirm-delete data-confirm-message="Padam perkhidmatan ini?">@csrf @method('DELETE')<button class="grid size-9 place-items-center rounded-lg text-[#b33129] transition hover:bg-[#fff0ef]" type="submit" aria-label="Padam {{ $service->name }}"><span class="material-symbols-outlined text-[19px]">delete</span></button></form></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-6 py-12 text-center"><span class="material-symbols-outlined text-3xl text-[#9aa89f]">inventory_2</span><p class="mt-2 text-sm font-semibold text-[#506058]">Belum ada perkhidmatan.</p><p class="mt-1 text-xs text-[#7a8880]">Tambah tawaran selepas butiran pembekal disahkan.</p></td></tr>
                        @endforelse
                        <tr hidden data-aqiqah-korban-admin-no-results><td colspan="7" class="px-6 py-10 text-center text-sm text-[#718078]">Tiada perkhidmatan sepadan ditemui.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">{{ $services->links() }}</div>
    </div>
</x-layouts.admin>

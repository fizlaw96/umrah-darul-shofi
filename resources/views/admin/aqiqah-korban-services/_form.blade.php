@php($isEditing = $service->exists)
@php($selectedCategory = old('category', $service->category ?: \App\Models\AqiqahKorbanService::Aqiqah))
@php($selectedPromotionBadge = old('promotion_badge', $service->promotion_badge))

<form class="space-y-5" method="POST" action="{{ $isEditing ? route('admin.aqiqah-korban-services.update', $service) : route('admin.aqiqah-korban-services.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    <x-errors />

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_18rem]">
        <div class="space-y-5">
            <section class="admin-card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">assignment</span></span>
                    <div><h2 class="text-sm font-bold text-[#163a30]">Maklumat Asas</h2><p class="mt-1 text-xs text-[#718078]">Butiran ini digunakan pada kad perkhidmatan awam.</p></div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label><span class="admin-label">Kategori</span><select class="admin-field" name="category" required data-aqiqah-korban-category>@foreach (\App\Models\AqiqahKorbanService::categories() as $category)<option value="{{ $category }}" @selected($selectedCategory === $category)>{{ $category }}</option>@endforeach</select></label>
                    <label><span class="admin-label">Nama perkhidmatan</span><input class="admin-field" name="name" value="{{ old('name', $service->name) }}" required placeholder="Contoh: Aqiqah seekor kambing"></label>
                    <label><span class="admin-label">Jenis haiwan</span><select class="admin-field" name="animal_type" required><option value="">Pilih jenis haiwan</option>@foreach (\App\Models\AqiqahKorbanService::animalTypes() as $animalType)<option value="{{ $animalType }}" @selected(old('animal_type', $service->animal_type) === $animalType)>{{ $animalType }}</option>@endforeach</select></label>
                    <label><span class="admin-label">Kuantiti / bahagian</span><select class="admin-field" name="quantity_description" required><option value="">Pilih kuantiti</option>@foreach (\App\Models\AqiqahKorbanService::quantityDescriptions() as $quantityDescription)<option value="{{ $quantityDescription }}" @selected(old('quantity_description', $service->quantity_description) === $quantityDescription)>{{ $quantityDescription }}</option>@endforeach</select></label>
                    <label><span class="admin-label">Lokasi perkhidmatan</span><input class="admin-field" name="service_location" value="{{ old('service_location', $service->service_location) }}" placeholder="Contoh: Tanah Suci / Tempatan"></label>
                    <label><span class="admin-label">Harga jualan (RM) <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" type="number" name="price" value="{{ old('price', $service->price) }}" min="0" step="0.01" placeholder="Kos perlu disahkan dahulu"></label>
                </div>
            </section>

            <section class="admin-card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">description</span></span>
                    <h2 class="text-sm font-bold text-[#163a30]">Maklumat Lanjut</h2>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label><span class="admin-label">Penerangan perkhidmatan</span><textarea class="admin-field min-h-32" name="full_description" maxlength="5000" placeholder="Maklumat lengkap yang akan dipaparkan kepada pelanggan">{{ old('full_description', $service->full_description) }}</textarea></label>
                    <label><span class="admin-label">Terma / nota penting</span><textarea class="admin-field min-h-32" name="additional_terms" maxlength="3000" placeholder="Hanya terma yang telah diluluskan">{{ old('additional_terms', $service->additional_terms) }}</textarea></label>
                </div>
                <fieldset class="mt-5 border-t border-[#e8eee9] pt-5">
                    <legend class="admin-label">Paparan promosi</legend>
                    <p class="mt-1 text-xs leading-5 text-[#718078]">Pilih satu badge promosi atau tiada. Susunan paparan ditetapkan melalui medan Susunan.</p>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-3 text-xs font-bold text-[#506058]"><input type="radio" name="promotion_badge" value="" @checked(blank($selectedPromotionBadge))><span class="material-symbols-outlined text-[18px]">remove_circle_outline</span>Tiada</label>
                        @foreach (\App\Models\AqiqahKorbanService::promotionBadges() as $badgeType => $badge)
                            <label @class(['flex cursor-pointer items-center gap-2 rounded-xl border p-3 text-xs font-bold', 'border-[#d2ad52] bg-[#fff8e6] text-[#725418]' => $badgeType === \App\Models\AqiqahKorbanService::RecommendationBadge, 'border-[#f5b6c4] bg-[#fff0f3] text-[#aa1530]' => $badgeType === \App\Models\AqiqahKorbanService::SuperDealBadge])><input type="radio" name="promotion_badge" value="{{ $badgeType }}" @checked($selectedPromotionBadge === $badgeType)><span class="material-symbols-outlined text-[18px]">{{ $badge['icon'] }}</span>{{ $badge['label'] }}</label>
                        @endforeach
                    </div>
                </fieldset>
            </section>

            <section class="admin-card p-5 sm:p-6" data-aqiqah-korban-korban-fields>
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#fff4db] text-[#9a762e]"><span class="material-symbols-outlined text-[21px]">event</span></span>
                    <div><h2 class="text-sm font-bold text-[#163a30]">Maklumat Korban</h2><p class="mt-1 text-xs text-[#718078]">Diaktifkan hanya apabila kategori Korban dipilih.</p></div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <label><span class="admin-label">Musim Korban</span><input class="admin-field" name="korban_season" value="{{ old('korban_season', $service->korban_season) }}" placeholder="Contoh: 1448H"></label>
                    <label><span class="admin-label">Catatan pendaftaran <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" name="registration_period" value="{{ old('registration_period', $service->registration_period) }}" placeholder="Contoh: Mengikut pengesahan operator"></label>
                    <label><span class="admin-label">Pendaftaran dibuka <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" type="date" name="registration_opens_on" value="{{ old('registration_opens_on', $service->registration_opens_on?->toDateString()) }}"></label>
                    <label><span class="admin-label">Pendaftaran ditutup <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" type="date" name="registration_closes_on" value="{{ old('registration_closes_on', $service->registration_closes_on?->toDateString()) }}"></label>
                </div>
                <p class="mt-3 text-xs leading-5 text-[#718078]">Tawaran Korban akan disembunyikan secara automatik di luar tarikh pendaftaran. Biarkan kosong jika tiada had tarikh.</p>
            </section>
        </div>

        <aside class="space-y-5 xl:sticky xl:top-5">
            <section class="admin-card overflow-hidden p-5">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">image</span></span>
                    <h2 class="text-sm font-bold text-[#163a30]">Gambar Utama</h2>
                </div>
                <div class="mt-4 aspect-[4/3] overflow-hidden rounded-xl border border-dashed border-[#b9d8c4] bg-[#f3faf5]">
                    @if ($service->imageUrl())
                        <img src="{{ $service->imageUrl() }}" alt="Gambar {{ $service->name }}" class="size-full object-cover">
                    @else
                        <span class="flex size-full flex-col items-center justify-center gap-2 px-5 text-center text-[#267564]"><span class="material-symbols-outlined text-4xl">add_photo_alternate</span><span class="text-xs font-bold">Klik untuk muat naik gambar</span></span>
                    @endif
                </div>
                <label class="mt-4 block"><span class="sr-only">Gambar utama</span><input class="admin-field text-xs" type="file" name="featured_image" accept="image/jpeg,image/png,image/webp"></label>
                <p class="mt-2 text-xs leading-5 text-[#718078]">JPEG, PNG atau WebP; maksimum 2 MB.</p>
                @if ($service->featured_image)
                    <label class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-[#9b3029]"><input class="size-4 accent-[#b33129]" type="checkbox" name="remove_featured_image" value="1"><span>Buang gambar semasa</span></label>
                @endif
            </section>

            <section class="admin-card p-5">
                <div class="flex items-start justify-between gap-4">
                    <div><h2 class="text-sm font-bold text-[#163a30]">Tetapan Paparan</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Terbitkan untuk paparan awam setelah butiran dan harga disahkan.</p></div>
                    <label class="admin-toggle" aria-label="Terbitkan untuk paparan awam"><input class="sr-only" type="checkbox" name="is_published" value="1" @checked(old('is_published', $service->is_published))><span></span></label>
                </div>
                <p class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#f3fbf5] px-3 py-2 text-xs font-bold text-[#086348]"><span class="material-symbols-outlined text-[16px]">visibility</span>Terbitkan untuk paparan awam</p>
                <label class="mt-4 block"><span class="admin-label">Susunan</span><input class="admin-field" type="number" name="sort_order" value="{{ old('sort_order', $service->sort_order ?? 0) }}" min="0" max="9999"></label>
                <p class="mt-2 text-xs leading-5 text-[#718078]">Nombor lebih kecil dipaparkan dahulu. Jika sama, perkhidmatan paling baharu muncul dahulu.</p>
            </section>
        </aside>
    </div>

    <div class="flex flex-wrap justify-end gap-3 border-t border-[#e8eee9] pt-5">
            @if ($isEditing)
                <button form="delete-aqiqah-korban-service" class="admin-button-secondary !border-[#f1c6c1] !text-[#9b3029]" type="submit"><span class="material-symbols-outlined text-[18px]">delete</span>Padam</button>
            @endif
            <button class="admin-button-primary" type="submit"><span class="material-symbols-outlined text-[18px]">{{ $isEditing ? 'save' : 'publish' }}</span>{{ $isEditing ? 'Simpan Perkhidmatan' : 'Tambah Perkhidmatan' }}</button>
    </div>
</form>

@if ($isEditing)
    <form id="delete-aqiqah-korban-service" method="POST" action="{{ route('admin.aqiqah-korban-services.destroy', $service) }}" data-confirm-delete data-confirm-message="Padam perkhidmatan ini?">
        @csrf
        @method('DELETE')
    </form>
@endif

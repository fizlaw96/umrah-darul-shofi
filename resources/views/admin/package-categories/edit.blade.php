<x-layouts.admin heading="Edit Kategori">
    <section class="flex items-center justify-between gap-4">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[.16em] text-[#006b57]">Pakej</p>
            <h1 class="mt-1 text-3xl font-bold tracking-[-.045em] text-[#00251e]">{{ $packageCategory->name }}</h1>
        </div>
        <a class="admin-button-secondary" href="{{ route('admin.package-categories.index') }}">Kembali</a>
    </section>

    @php($heroSlide = $packageCategory->heroSlide)
    @php($hasImage = filled($heroSlide?->imageUrl()))
    @php($hasVideo = filled($heroSlide?->videoUrl()))

    <form data-category-hero-form class="admin-card mt-7 max-w-4xl p-6 sm:p-7" method="POST" action="{{ route('admin.package-categories.update', $packageCategory) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <x-errors />

        <div class="grid gap-5 md:grid-cols-2">
            <label><span class="admin-label">Nama</span><input data-category-name class="admin-field" name="name" value="{{ old('name', $packageCategory->name) }}" required></label>
            <label><span class="admin-label">Slug</span><input data-category-slug-preview class="admin-field cursor-not-allowed text-[#718078]" value="{{ old('slug', $packageCategory->slug) }}" readonly aria-describedby="slug-help"></label>
            <div class="flex flex-wrap items-end gap-4 pb-3 md:col-span-2"><label class="flex items-center gap-2 text-sm font-semibold"><input class="size-4 accent-[#006b57]" type="checkbox" name="is_active" value="1" @checked(old('is_active', $packageCategory->is_active))>Aktif</label><label class="flex items-center gap-2 text-sm font-semibold"><input class="size-4 accent-[#006b57]" type="checkbox" name="show_on_homepage" value="1" @checked(old('show_on_homepage', $packageCategory->show_on_homepage))>Paparkan di homepage</label></div>
            <label class="md:col-span-2"><span class="admin-label">Subtajuk / tahun <span class="font-medium text-[#8a958f]">Pilihan</span></span><textarea class="admin-field min-h-28" name="description" maxlength="255">{{ old('description', $packageCategory->description) }}</textarea><span id="slug-help" class="mt-2 block text-xs text-[#718078]">Slug dijana secara automatik daripada nama. Subtajuk ini digunakan sekali sahaja untuk kategori dan slaid hero.</span></label>
        </div>

        <section class="mt-7 border-t border-[#e8eee9] pt-6">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-start"><div><p class="admin-label">Visual slaid hero</p><p class="text-sm leading-6 text-[#718078]">Tajuk dan pautan butang mengambil data kategori ini secara automatik.</p></div><span class="inline-flex w-fit rounded-full bg-[#e9f7ed] px-3 py-1.5 text-xs font-bold text-[#006b57]">Butang CTA: Terokai Pakej</span></div>

            <div class="mt-5 grid gap-5 md:grid-cols-2">
                <div data-hero-media-panel="image" class="rounded-xl border border-[#dce9df] bg-[#f8fbf8] p-4">
                    <div data-hero-media-preview="image" data-hero-media-present="{{ $hasImage ? 'true' : 'false' }}" class="relative grid aspect-video place-items-center overflow-hidden rounded-lg bg-[#e9f2eb] text-[#006b57]">@if ($hasImage)<img src="{{ $heroSlide->imageUrl() }}" alt="Poster hero semasa" class="size-full object-cover">@else<span class="material-symbols-outlined text-4xl">image</span>@endif<button type="button" data-hero-media-remove="image" class="absolute right-2 top-2 grid size-8 place-items-center rounded-full bg-white/95 text-[#b33129] shadow transition hover:bg-[#fff0ef] {{ $hasImage ? '' : 'hidden' }}" aria-label="Keluarkan poster imej"><span class="material-symbols-outlined text-[18px]">close</span></button></div>
                    <p class="mt-3 text-sm font-bold text-[#163a30]">Poster imej</p>
                    <label class="mt-3 block"><span class="sr-only">Muat naik poster imej baharu</span><input data-hero-media-input="image" class="admin-field text-xs" type="file" name="background_image" accept="image/jpeg,image/png,image/webp" @disabled($hasVideo)></label>
                    <input data-hero-media-remove-input="image" type="hidden" name="remove_background_image" value="">
                    <p data-hero-media-status="image" data-hero-media-default-status="JPEG, PNG atau WebP; maksimum 5MB. Pilih fail untuk pratonton sebelum simpan." class="mt-2 text-xs leading-5 text-[#718078]">{{ $hasVideo ? 'Keluarkan video terlebih dahulu untuk menggunakan imej.' : 'JPEG, PNG atau WebP; maksimum 5MB. Pilih fail untuk pratonton sebelum simpan.' }}</p>
                </div>

                <div data-hero-media-panel="video" class="rounded-xl border border-[#dce9df] bg-[#f8fbf8] p-4">
                    <div data-hero-media-preview="video" data-hero-media-present="{{ $hasVideo ? 'true' : 'false' }}" class="relative grid aspect-video place-items-center overflow-hidden rounded-lg bg-[#e9f2eb] text-[#006b57]">@if ($hasVideo)<video class="size-full object-cover" controls preload="metadata" poster="{{ $heroSlide->imageUrl() }}"><source src="{{ $heroSlide->videoUrl() }}"></video>@else<span class="material-symbols-outlined text-4xl">movie</span>@endif<button type="button" data-hero-media-remove="video" class="absolute right-2 top-2 grid size-8 place-items-center rounded-full bg-white/95 text-[#b33129] shadow transition hover:bg-[#fff0ef] {{ $hasVideo ? '' : 'hidden' }}" aria-label="Keluarkan video hero"><span class="material-symbols-outlined text-[18px]">close</span></button></div>
                    <p class="mt-3 text-sm font-bold text-[#163a30]">Video hero</p>
                    <label class="mt-3 block"><span class="sr-only">Muat naik video hero baharu</span><input data-hero-media-input="video" class="admin-field text-xs" type="file" name="background_video" accept="video/mp4,video/webm" @disabled($hasImage)></label>
                    <input data-hero-media-remove-input="video" type="hidden" name="remove_background_video" value="">
                    <p data-hero-media-status="video" data-hero-media-default-status="MP4 atau WebM; maksimum 10 saat. Pilih fail untuk pratonton sebelum simpan." class="mt-2 text-xs leading-5 text-[#718078]">{{ $hasImage ? 'Keluarkan imej terlebih dahulu untuk menggunakan video.' : 'MP4 atau WebM; maksimum 10 saat. Pilih fail untuk pratonton sebelum simpan.' }}</p>
                </div>
            </div>
        </section>

        <div class="mt-7 flex flex-wrap justify-between gap-3 border-t border-[#e8eee9] pt-5"><button class="admin-button-primary" type="submit">Simpan Kategori</button><button form="delete-category" class="admin-button-secondary !border-[#edc5be] !text-[#b33129]" type="submit">Padam Kategori</button></div>
    </form>

    <form id="delete-category" method="POST" action="{{ route('admin.package-categories.destroy', $packageCategory) }}" data-confirm-delete data-confirm-message="Padam kategori ini? Kategori yang masih mempunyai pakej tidak boleh dipadamkan.">
        @csrf
        @method('DELETE')
    </form>
</x-layouts.admin>

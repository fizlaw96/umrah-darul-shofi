@php($isEdit = $mutawif->exists)
<form class="admin-card mt-7 max-w-3xl p-6 sm:p-7" method="POST" action="{{ $isEdit ? route('admin.mutawifs.update', $mutawif) : route('admin.mutawifs.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <x-errors />
    <div class="grid gap-5 md:grid-cols-2">
        <label><span class="admin-label">Nama penuh</span><input class="admin-field" name="full_name" value="{{ old('full_name', $mutawif->full_name) }}" required></label>
        <div><span class="admin-label">Foto profil</span>
            @if ($isEdit && $mutawif->photoUrl())
                <div data-current-image class="relative mb-3 w-fit"><img data-image-focus-path="{{ $mutawif->photo }}" src="{{ $mutawif->photoUrl() }}" alt="Foto {{ $mutawif->full_name }}" class="size-24 rounded-xl object-cover" style="object-position: {{ $imageFocusPositions[$mutawif->photo] ?? '50% 50%' }}"><button data-current-image-remove data-remove-input="remove-mutawif-photo" class="absolute -right-2 -top-2 grid size-7 place-items-center rounded-full bg-[#b33129] text-white shadow" type="button" aria-label="Buang foto semasa"><span class="material-symbols-outlined text-[16px]">close</span></button></div>
                <x-admin.image-focus-button :path="$mutawif->photo" :url="$mutawif->photoUrl()" label="Laraskan fokus foto" />
                <input id="remove-mutawif-photo" data-current-image-remove-input type="hidden" name="remove_photo" value="{{ old('remove_photo') ? '1' : '' }}">
            @endif
            <input class="admin-field text-xs" type="file" name="photo" accept="image/jpeg,image/png,image/webp"><span class="mt-2 block text-xs text-[#718078]">Muat naik fail baharu untuk menggantikan foto semasa.</span>
        </div>
        <label class="md:col-span-2"><span class="admin-label">Kelayakan <span class="font-medium text-[#8a958f]">Pilihan</span></span><textarea class="admin-field min-h-28" name="qualification" maxlength="2000">{{ old('qualification', $mutawif->qualification) }}</textarea></label>
        <label class="flex items-end gap-2 pb-3 text-sm font-semibold"><input class="size-4 accent-[#006b57]" type="checkbox" name="is_published" value="1" @checked(old('is_published', $mutawif->is_published))>Terbitkan untuk halaman awam</label>
    </div>
    <div class="mt-7 flex justify-end border-t border-[#e8eee9] pt-5"><button class="admin-button-primary" type="submit">{{ $isEdit ? 'Simpan Profil' : 'Tambah Profil' }}</button></div>
</form>

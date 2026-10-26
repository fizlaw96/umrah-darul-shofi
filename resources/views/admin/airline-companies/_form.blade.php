@php($isEdit = $airlineCompany->exists)
<form class="admin-card mt-7 max-w-3xl p-6 sm:p-7" method="POST" action="{{ $isEdit ? route('admin.airline-companies.update', $airlineCompany) : route('admin.airline-companies.store') }}" enctype="multipart/form-data">
    @csrf
    @if ($isEdit) @method('PUT') @endif
    <x-errors />
    <div class="grid gap-5 md:grid-cols-2">
        <label><span class="admin-label">Nama syarikat</span><input class="admin-field" name="name" value="{{ old('name', $airlineCompany->name) }}" required></label>
        <label><span class="admin-label">Susunan</span><input class="admin-field" type="number" name="sort_order" min="0" value="{{ old('sort_order', $airlineCompany->sort_order ?: 0) }}"></label>
        <label class="md:col-span-2"><span class="admin-label">Logo {{ $isEdit ? '(pilihan untuk tukar)' : '' }}</span><input class="admin-field text-xs" type="file" name="logo" accept="image/jpeg,image/png,image/webp" @required(! $isEdit)><span class="mt-2 block text-xs text-[#718078]">Logo akan dipadankan pada kanvas 480 × 200 px supaya semua logo seragam di halaman utama.</span></label>
        @if ($isEdit)
            <div class="md:col-span-2 rounded-xl border border-[#e2ebe4] bg-[#f8fbf8] p-4"><p class="admin-label">Logo semasa</p><span class="mt-2 grid h-24 w-48 place-items-center rounded-lg bg-white p-2"><img src="{{ $airlineCompany->logoUrl() }}" alt="{{ $airlineCompany->name }}" class="max-h-full max-w-full object-contain"></span></div>
        @endif
        <label class="flex items-center gap-2 text-sm font-semibold text-[#163a30]"><input class="size-4 accent-[#006b57]" type="checkbox" name="is_published" value="1" @checked(old('is_published', $airlineCompany->is_published ?? true))>Terbitkan pada halaman utama</label>
    </div>
    <div class="mt-7 flex justify-end gap-3 border-t border-[#e8eee9] pt-5"><a href="{{ route('admin.airline-companies.index') }}" class="admin-button-secondary">Batal</a><button class="admin-button-primary" type="submit">{{ $isEdit ? 'Simpan Syarikat' : 'Tambah Syarikat' }}</button></div>
</form>

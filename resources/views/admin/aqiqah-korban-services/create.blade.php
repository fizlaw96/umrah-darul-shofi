<x-layouts.admin heading="Tambah perkhidmatan Aqiqah & Korban">
    <div class="mx-auto max-w-6xl">
        <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
            <div><p class="section-eyebrow">Perkhidmatan</p><h1 class="mt-1 text-2xl font-bold tracking-tight text-[#00251e] sm:text-3xl">Tambah Perkhidmatan Aqiqah &amp; Korban</h1><p class="mt-2 text-sm text-[#69746f]">Lengkapkan maklumat perkhidmatan. Harga hanya akan dipaparkan selepas disahkan oleh admin.</p></div>
            <a class="admin-button-secondary" href="{{ route('admin.aqiqah-korban-services.index') }}"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Kembali</a>
        </div>
        @include('admin.aqiqah-korban-services._form')
    </div>
</x-layouts.admin>

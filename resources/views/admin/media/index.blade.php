<x-layouts.admin heading="Pengurusan Galeri">
    <section class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Kandungan website</p>
            <h1 class="mt-1 text-3xl font-bold tracking-[-0.045em] text-[#00251e]">Pengurusan Galeri</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#69746f]">Pilih bahagian dahulu supaya setiap gambar masuk ke tempat yang betul. Teks alternatif dijana automatik daripada nama fail dan kapsyen tidak diperlukan.</p>
        </div>
    </section>

    <nav class="mt-7 flex flex-wrap gap-2" aria-label="Jenis media">
        <a href="{{ route('admin.media.index') }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition {{ $selectedTab === 'gallery' ? 'border-[#006b57] bg-[#e9f7ed] text-[#006b57]' : 'border-[#dce7df] bg-white text-[#52645b]' }}"><span class="material-symbols-outlined text-[18px]">photo_library</span>Galeri Website</a>
        <a href="{{ route('admin.media.index', ['tab' => 'package-media']) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition {{ $selectedTab === 'package-media' ? 'border-[#006b57] bg-[#e9f7ed] text-[#006b57]' : 'border-[#dce7df] bg-white text-[#52645b]' }}"><span class="material-symbols-outlined text-[18px]">folder_copy</span>Media Pakej <span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px]">{{ $packageMediaAssets->count() }}</span></a>
    </nav>

    @if ($selectedTab === 'gallery')
    <nav class="mt-7 flex flex-wrap gap-2" aria-label="Bahagian galeri">
        @foreach ($mediaSections as $section => $mediaSection)
            <a href="{{ route('admin.media.index', ['section' => $section]) }}" class="inline-flex items-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition {{ $selectedSection === $section ? 'border-[#006b57] bg-[#e9f7ed] text-[#006b57]' : 'border-[#dce7df] bg-white text-[#52645b] hover:border-[#9dcdb0]' }}"><span>{{ $mediaSection['label'] }}</span><span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px]">{{ $mediaSection['count'] }}/{{ $mediaSection['limit'] }}</span></a>
        @endforeach
    </nav>

    @php($selectedMediaSection = $mediaSections[$selectedSection])
    <section class="admin-card mt-5 p-6 sm:p-7">
        <div class="flex items-start gap-3"><span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">upload</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Tambah ke {{ $selectedMediaSection['label'] }}</h2><p class="mt-1 text-sm text-[#69746f]">{{ $selectedMediaSection['description'] }} Boleh seret dan lepas banyak gambar sekali gus.</p></div></div>
        <form data-media-upload-form class="mt-6 grid gap-5" method="POST" action="{{ route('admin.media.store') }}" enctype="multipart/form-data">
            @csrf
            <x-errors />
            <input type="hidden" name="section" value="{{ $selectedSection }}">
            <input data-media-file-input id="media-images" class="sr-only" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required>
            <div data-media-dropzone class="grid min-h-44 cursor-pointer place-items-center rounded-2xl border-2 border-dashed border-[#b8d8c1] bg-[#f7fcf8] p-6 text-center transition hover:border-[#006b57] hover:bg-[#eff9f1]" role="button" tabindex="0" aria-controls="media-images">
                <div><span class="material-symbols-outlined text-4xl text-[#007c68]">drive_folder_upload</span><p class="mt-3 font-bold text-[#163a30]">Seret dan lepas gambar di sini</p><p class="mt-1 text-sm text-[#718078]">atau klik untuk pilih banyak fail. JPEG, PNG atau WebP; maksimum 2 MB setiap satu.</p></div>
            </div>
            <div data-media-upload-preview class="hidden grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-live="polite"></div>
            <label class="flex items-center gap-2 text-sm font-semibold text-[#163a30]"><input class="size-4 accent-[#006b57]" type="checkbox" name="is_published" value="1" @checked(old('is_published', true))>Paparkan di galeri awam</label>
            <div><button class="admin-button-primary" type="submit"><span class="material-symbols-outlined text-[18px]">upload</span>Muat Naik Media</button></div>
        </form>
    </section>

    <section class="mt-7">
        <div class="mb-4 flex items-center justify-between gap-4"><div><h2 class="text-xl font-bold text-[#00251e]">Gambar {{ $selectedMediaSection['label'] }}</h2><p class="mt-1 text-xs text-[#718078]">{{ $mediaItems->count() }} gambar dimasukkan dalam bahagian ini</p></div></div>
        @if ($mediaItems->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($mediaItems as $mediaItem)
                    <article class="admin-card overflow-hidden">
                        <img src="{{ $mediaItem->url() }}" alt="{{ $mediaItem->alt_text ?: 'Foto galeri SalamHaji' }}" class="aspect-[4/3] w-full object-cover">
                        <form class="space-y-4 p-5" method="POST" action="{{ route('admin.media.update', $mediaItem) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="flex items-center justify-between gap-3"><div class="flex flex-wrap items-center gap-2"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $mediaItem->is_published ? 'bg-[#dcf7ed] text-[#006b57]' : 'bg-[#f2f4f2] text-[#66736c]' }}">{{ $mediaItem->is_published ? 'Dipaparkan' : 'Disorokkan' }}</span></div><span class="text-[11px] text-[#718078]">#{{ $mediaItem->sort_order }}</span></div>
                            <p class="rounded-lg bg-[#f5f8f5] p-3 text-xs leading-5 text-[#607068]"><span class="font-bold text-[#163a30]">Teks alternatif:</span> {{ $mediaItem->alt_text ?: 'Akan dijana apabila gambar diganti.' }}</p>
                            <label><span class="admin-label">Ganti gambar <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field text-xs" type="file" name="image" accept="image/jpeg,image/png,image/webp"><span class="mt-1 block text-[10px] text-[#718078]">Teks alternatif akan dijana semula daripada nama fail.</span></label>
                            <div class="flex items-end"><label class="mb-3 flex items-center gap-2 text-xs font-semibold text-[#163a30]"><input class="size-4 accent-[#006b57]" type="checkbox" name="is_published" value="1" @checked($mediaItem->is_published)>Papar</label></div>
                            <div class="flex gap-2 border-t border-[#e8eee9] pt-4"><button class="admin-button-secondary !px-3" type="submit"><span class="material-symbols-outlined text-[18px]">save</span>Simpan</button><button form="delete-media-{{ $mediaItem->id }}" class="admin-button-secondary !border-[#edc5be] !px-3 !text-[#b33129]" type="submit"><span class="material-symbols-outlined text-[18px]">delete</span>Buang</button></div>
                        </form>
                        <form id="delete-media-{{ $mediaItem->id }}" method="POST" action="{{ route('admin.media.destroy', $mediaItem) }}" data-confirm-media-delete>@csrf @method('DELETE')</form>
                    </article>
                @endforeach
            </div>
        @else
            <div class="admin-card p-12 text-center"><span class="material-symbols-outlined text-4xl text-[#9aa69f]">photo_library</span><h2 class="mt-3 text-lg font-bold text-[#00251e]">Belum ada gambar dalam bahagian ini</h2><p class="mt-2 text-sm text-[#69746f]">Seret gambar pertama ke ruang muat naik di atas.</p></div>
        @endif
    </section>
    @else
        <section class="mt-6 rounded-2xl border border-[#dbe8df] bg-[#fbfefc] p-5"><div class="flex items-start gap-3"><span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">folder_open</span></span><div><h2 class="text-lg font-bold text-[#00251e]">Pustaka media yang digunakan oleh pakej</h2><p class="mt-1 text-sm leading-6 text-[#69746f]">Setiap folder dipaparkan dalam tab tersendiri. Nama media boleh ditukar tanpa memutuskan pautan pada pakej yang sedang menggunakannya.</p></div></div></section>

        <nav class="mt-6 flex gap-2 overflow-x-auto pb-1" aria-label="Folder media pakej">
            @foreach (\App\Models\PackageMediaAsset::folders() as $folder => $folderDetails)
                @php($folderAssetCount = $packageMediaAssets->where('folder', $folder)->count())
                <a href="{{ route('admin.media.index', ['tab' => 'package-media', 'folder' => $folder]) }}" @class(['inline-flex shrink-0 items-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition', 'border-[#006b57] bg-[#e9f7ed] text-[#006b57]' => $selectedPackageFolder === $folder, 'border-[#dce7df] bg-white text-[#52645b] hover:border-[#9dcdb0]' => $selectedPackageFolder !== $folder])><span>{{ $folderDetails['label'] }}</span><span class="rounded-full bg-white/80 px-2 py-0.5 text-[10px]">{{ $folderAssetCount }}</span></a>
            @endforeach
        </nav>

        <section class="mt-6">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3"><div><h2 class="text-xl font-bold text-[#00251e]">{{ \App\Models\PackageMediaAsset::folders()[$selectedPackageFolder]['label'] }}</h2><p class="mt-1 text-xs text-[#718078]">Disusun daripada paling banyak digunakan kepada paling sedikit.</p></div><span class="rounded-full bg-[#e9f7ed] px-3 py-1 text-[10px] font-bold text-[#006b57]">{{ $selectedPackageMediaAssets->count() }} media</span></div>
            @if ($selectedPackageMediaAssets->isNotEmpty())
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($selectedPackageMediaAssets as $asset)
                        @php($usages = $packageMediaUsages->get($asset->path, collect()))
                        <article class="overflow-hidden rounded-xl bg-white shadow-[0_8px_24px_-18px_rgba(0,37,30,.35)]"><img src="{{ $asset->url() }}" alt="{{ $asset->original_name }}" class="aspect-[4/3] w-full object-cover"><div class="p-4"><form method="POST" action="{{ route('admin.media.package-assets.rename', $asset) }}">@csrf @method('PUT')<label><span class="admin-label">Nama media</span><input class="admin-field !py-2 text-xs" name="name" value="{{ pathinfo($asset->original_name, PATHINFO_FILENAME) }}" required></label><p class="mt-2 text-[11px] text-[#718078]">{{ $asset->width }} × {{ $asset->height }} px · digunakan {{ $usages->count() }} kali</p><button class="mt-3 text-xs font-bold text-[#006b57]" type="submit">Simpan nama</button></form><div class="mt-4 flex flex-wrap items-center gap-3 border-t border-[#e8eee9] pt-4"><button type="button" data-package-media-usage-open="package-media-usage-{{ $asset->id }}" class="text-xs font-bold text-[#006b57]">Lihat penggunaan</button>@if ($usages->isEmpty())<form method="POST" action="{{ route('admin.media.package-assets.destroy', $asset) }}" data-confirm-delete data-confirm-message="Padam media ini daripada pustaka?">@csrf @method('DELETE')<button class="text-xs font-bold text-[#b33129]" type="submit">Padam media</button></form>@endif</div></div></article>

                        <dialog id="package-media-usage-{{ $asset->id }}" data-package-media-usage-dialog class="admin-package-media-preview-dialog w-[min(44rem,94vw)] max-w-none rounded-2xl border border-[#dbe8df] bg-white p-0 text-[#163a30] shadow-2xl"><div class="flex items-start justify-between gap-4 border-b border-[#e8eee9] px-5 py-4"><div><h3 class="text-lg font-bold text-[#00251e]">Penggunaan media</h3><p class="mt-1 max-w-sm truncate text-xs text-[#718078]">{{ $asset->original_name }}</p></div><button type="button" data-package-media-usage-close class="grid size-9 place-items-center rounded-lg hover:bg-[#f2f5f2]" aria-label="Tutup penggunaan media"><span class="material-symbols-outlined">close</span></button></div><div class="max-h-[65vh] overflow-y-auto p-5"><div class="overflow-x-auto"><table class="w-full min-w-[28rem] text-left text-sm"><thead class="border-b border-[#e8eee9] text-[10px] font-bold uppercase tracking-[.1em] text-[#718078]"><tr><th class="px-3 py-3">Pakej</th><th class="px-3 py-3">Bahagian</th><th class="px-3 py-3 text-right">Tindakan</th></tr></thead><tbody class="divide-y divide-[#edf1ee]">@forelse ($usages as $usage)<tr data-package-media-usage-row><td class="px-3 py-3 font-semibold text-[#163a30]">{{ $usage['package']->title }}</td><td class="px-3 py-3 text-[#506058]">{{ $usage['section'] }}</td><td class="px-3 py-3 text-right"><a class="text-xs font-bold text-[#006b57] hover:underline" href="{{ route('admin.packages.edit', $usage['package']) }}">Buka pakej</a></td></tr>@empty<tr><td colspan="3" class="px-3 py-8 text-center text-sm text-[#718078]">Belum digunakan oleh mana-mana pakej.</td></tr>@endforelse</tbody></table></div><div data-package-media-usage-pagination class="mt-4 flex items-center justify-between gap-3 border-t border-[#edf1ee] pt-4" hidden><p data-package-media-usage-page-status class="text-xs text-[#718078]" aria-live="polite"></p><div class="flex gap-2"><button type="button" data-package-media-usage-previous class="admin-button-secondary !px-3 !py-2 text-xs" aria-label="Halaman penggunaan media sebelumnya"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span></button><button type="button" data-package-media-usage-next class="admin-button-secondary !px-3 !py-2 text-xs" aria-label="Halaman penggunaan media seterusnya"><span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span></button></div></div></div></dialog>
                    @endforeach
                </div>
            @else
                <div class="admin-card p-12 text-center"><span class="material-symbols-outlined text-4xl text-[#9aa69f]">folder_off</span><h2 class="mt-3 text-lg font-bold text-[#00251e]">Belum ada media dalam folder ini</h2><p class="mt-2 text-sm text-[#69746f]">Muat naik media daripada tab berkaitan dalam borang pakej untuk mengisinya.</p></div>
            @endif
        </section>
    @endif
</x-layouts.admin>

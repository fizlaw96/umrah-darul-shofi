<x-layouts.admin heading="Pengurusan Galeri">
    <section class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Kandungan website</p>
            <h1 class="mt-1 text-3xl font-bold tracking-[-0.045em] text-[#00251e]">Pengurusan Galeri</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#69746f]">Pilih bahagian dahulu supaya setiap gambar masuk ke tempat yang betul. Teks alternatif dijana automatik daripada nama fail dan kapsyen tidak diperlukan.</p>
        </div>
    </section>

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
</x-layouts.admin>

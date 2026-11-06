<x-layouts.admin heading="Transport">
    @php
        $transportVisualUrl = $transportBannerUrl ?: asset('darul-shofi/images/hero/transport-hero-v1.png');
        $vehicleImageOptions = \App\Models\WebsiteSetting::transportVehicleImages();
        $maximumVehicleOptions = \App\Models\WebsiteSetting::MaximumTransportVehicleOptions;
        $editableVehicleOptions = array_values(old('transport_vehicle_options', $transportVehicleOptions));
        $namedVehicleCount = count(array_filter($editableVehicleOptions, fn (array $vehicle): bool => filled($vehicle['name'] ?? null)));
        $hasBlankVehicleRow = collect($editableVehicleOptions)->contains(fn (array $vehicle): bool => blank($vehicle['name'] ?? null));

        if ($namedVehicleCount < $maximumVehicleOptions && ! $hasBlankVehicleRow) {
            $editableVehicleOptions[] = ['key' => '', 'name' => '', 'description' => '', 'image' => array_key_first($vehicleImageOptions), 'capacity' => null, 'enabled' => true];
        }
    @endphp

    <div class="mx-auto max-w-6xl">
        <div class="mb-7">
            <div>
                <p class="section-eyebrow">Perkhidmatan</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#00251e] sm:text-3xl">Tetapan SalamHaji Transport</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-[#69746f]">Urus maklumat pengangkutan yang dipaparkan di laman utama. Harga dan ketersediaan kekal disahkan oleh operator.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.transport-settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <x-errors />

            <div class="grid items-start gap-5 lg:grid-cols-2">
                <div class="space-y-5">
                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex gap-3">
                                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">lock_open</span></span>
                                <div>
                                    <h2 class="text-sm font-bold text-[#163a30]">Status Perkhidmatan</h2>
                                    <p class="mt-1 text-xs leading-5 text-[#718078]">Halaman ini akan dipaparkan di website dan borang pertanyaan akan dihantar ke nombor WhatsApp operator.</p>
                                </div>
                            </div>
                            <label class="admin-toggle" aria-label="Aktifkan halaman Transport">
                                <input class="sr-only" type="checkbox" name="transport_enabled" value="1" @checked(old('transport_enabled', $settings['transport_enabled'] === '1'))>
                                <span></span>
                            </label>
                        </div>
                        <p class="mt-4 inline-flex items-center gap-2 rounded-lg bg-[#f3fbf5] px-3 py-2 text-xs font-bold text-[#086348]"><span class="material-symbols-outlined text-[16px]">check_circle</span>Aktifkan halaman Transport</p>
                    </section>

                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">format_quote</span></span>
                            <h2 class="text-sm font-bold text-[#163a30]">Pengenalan Perkhidmatan</h2>
                        </div>
                        <label class="mt-4 block">
                            <span class="admin-label">Penerangan</span>
                            <textarea class="admin-field min-h-30" name="transport_intro" maxlength="2000">{{ old('transport_intro', $settings['transport_intro']) }}</textarea>
                        </label>
                    </section>

                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">map</span></span>
                            <h2 class="text-sm font-bold text-[#163a30]">Kawasan Liputan</h2>
                        </div>
                        <label class="mt-4 block">
                            <span class="sr-only">Maklumat liputan</span>
                            <textarea class="admin-field min-h-30" name="transport_coverage_info" maxlength="2000" placeholder="Senaraikan kawasan atau maklumat liputan yang telah disahkan.">{{ old('transport_coverage_info', $settings['transport_coverage_info']) }}</textarea>
                        </label>
                        <p class="mt-2 text-xs leading-5 text-[#718078]">Maklumat ini dipaparkan sebagai rujukan, bukan untuk menolak pertanyaan secara automatik.</p>
                    </section>

                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">airport_shuttle</span></span>
                            <div>
                                <h2 class="text-sm font-bold text-[#163a30]">Pilihan Kenderaan</h2>
                                <p class="mt-1 text-xs leading-5 text-[#718078]">Pelanggan akan memilih daripada senarai ini pada langkah Butiran Penumpang. Ketersediaan sebenar tetap disahkan oleh operator.</p>
                            </div>
                        </div>

                        <p class="mt-3 text-xs leading-5 text-[#718078]">Maksimum {{ $maximumVehicleOptions }} pilihan. Isi baris kosong di bawah untuk menambah kenderaan baharu. Kapasiti digunakan untuk menyemak jumlah penumpang; biarkan kosong jika tiada had.</p>

                        <div class="mt-4 space-y-4">
                            @foreach ($editableVehicleOptions as $index => $vehicle)
                                @php($isNewVehicle = blank($vehicle['name'] ?? null))
                                <article @class(['grid gap-3 rounded-xl border p-3 sm:grid-cols-[5.5rem_minmax(0,1fr)]', 'border-[#e2ebe4] bg-[#fbfefc]' => ! $isNewVehicle, 'border-dashed border-[#b9d8c4] bg-white' => $isNewVehicle])>
                                    <div class="overflow-hidden rounded-lg border border-[#e1e9e3] bg-white">
                                        <img src="{{ asset($vehicle['image'] ?? array_key_first($vehicleImageOptions)) }}" alt="Pratonton {{ $vehicle['name'] ?: 'kenderaan baharu' }}" class="aspect-[4/3] w-full object-contain p-1">
                                    </div>
                                    <div class="min-w-0">
                                        @if ($isNewVehicle)
                                            <p class="mb-2 text-xs font-bold text-[#00705b]">Tambah kenderaan baharu</p>
                                        @endif
                                        <input type="hidden" name="transport_vehicle_options[{{ $index }}][key]" value="{{ $vehicle['key'] ?? '' }}">
                                        <div class="grid gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_6.5rem]">
                                            <label>
                                                <span class="admin-label">Nama pilihan</span>
                                                <input class="admin-field" name="transport_vehicle_options[{{ $index }}][name]" value="{{ $vehicle['name'] ?? '' }}" maxlength="80" placeholder="{{ $isNewVehicle ? 'Contoh: Van VIP' : '' }}">
                                            </label>
                                            <label>
                                                <span class="admin-label">Keterangan ringkas</span>
                                                <input class="admin-field" name="transport_vehicle_options[{{ $index }}][description]" value="{{ $vehicle['description'] ?? '' }}" maxlength="120">
                                            </label>
                                            <label>
                                                <span class="admin-label">Kapasiti</span>
                                                <input class="admin-field" type="number" name="transport_vehicle_options[{{ $index }}][capacity]" value="{{ $vehicle['capacity'] ?? '' }}" min="1" max="60" placeholder="Orang">
                                            </label>
                                        </div>
                                        <div class="mt-3 flex flex-wrap items-end justify-between gap-3">
                                            <label class="min-w-48 flex-1">
                                                <span class="admin-label">Imej kenderaan</span>
                                                <select class="admin-field" name="transport_vehicle_options[{{ $index }}][image]">
                                                    @foreach ($vehicleImageOptions as $image => $imageLabel)
                                                        <option value="{{ $image }}" @selected(($vehicle['image'] ?? null) === $image)>{{ $imageLabel }}</option>
                                                    @endforeach
                                                </select>
                                            </label>
                                            <div class="flex flex-wrap items-center gap-4">
                                                <label class="inline-flex min-h-11 items-center gap-2 text-xs font-bold text-[#28574a]">
                                                    <input class="size-4 accent-[#00705b]" type="checkbox" name="transport_vehicle_options[{{ $index }}][enabled]" value="1" @checked(filter_var($vehicle['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN))>
                                                    Paparkan pilihan ini
                                                </label>
                                                @unless ($isNewVehicle)
                                                    <label class="inline-flex min-h-11 items-center gap-2 text-xs font-bold text-[#9b3029]">
                                                        <input class="size-4 accent-[#b33129]" type="checkbox" name="transport_vehicle_options[{{ $index }}][remove]" value="1" @checked(filter_var($vehicle['remove'] ?? false, FILTER_VALIDATE_BOOLEAN))>
                                                        Buang
                                                    </label>
                                                @endunless
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                </div>

                <div class="space-y-5">
                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">chat</span></span>
                            <h2 class="text-sm font-bold text-[#163a30]">WhatsApp Operator</h2>
                        </div>
                        <label class="mt-4 block">
                            <span class="admin-label">Nombor WhatsApp</span>
                            <input class="admin-field" name="transport_whatsapp" value="{{ old('transport_whatsapp', $settings['transport_whatsapp']) }}" placeholder="+60 12 345 6789">
                        </label>
                        <p class="mt-2 text-xs leading-5 text-[#718078]">Masukkan nombor dengan format antarabangsa. Contoh: +60 12 345 6789. Nombor ini khusus untuk pertanyaan Transport.</p>
                    </section>

                    <section class="admin-card p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">schedule</span></span>
                            <h2 class="text-sm font-bold text-[#163a30]">Waktu Operasi / Nota Operator</h2>
                        </div>
                        <label class="mt-4 block">
                            <span class="admin-label">Nota kepada pengguna</span>
                            <textarea class="admin-field min-h-36" name="transport_operating_notes" maxlength="1000" placeholder="Contoh: Operator akan menghubungi anda melalui WhatsApp untuk pengesahan sebut harga.">{{ old('transport_operating_notes', $settings['transport_operating_notes']) }}</textarea>
                        </label>
                    </section>

                    <section class="admin-card overflow-hidden p-5 sm:p-6">
                        <div class="flex items-center gap-3">
                            <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">image</span></span>
                            <h2 class="text-sm font-bold text-[#163a30]">Banner Transport</h2>
                        </div>
                        <div class="mt-4 overflow-hidden rounded-xl border border-[#e2ece4] bg-[#edf4ef]">
                            <img src="{{ $transportVisualUrl }}" alt="Pratonton banner transport" class="aspect-[16/8] w-full object-cover">
                        </div>
                        <label class="mt-3 block">
                            <span class="sr-only">Muat naik banner Transport</span>
                            <input class="admin-field text-xs" type="file" name="transport_banner" accept="image/jpeg,image/png,image/webp">
                        </label>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs text-[#718078]">
                            <span>JPEG, PNG atau WebP; maksimum 4 MB. Cadangan saiz: 1920 × 600px.</span>
                            @if ($settings['transport_banner_path'])
                                <label class="inline-flex items-center gap-2 font-semibold text-[#9b3029]"><input class="size-4 accent-[#b33129]" type="checkbox" name="remove_transport_banner" value="1">Buang banner</label>
                            @endif
                        </div>
                    </section>
                </div>
            </div>

            <section class="admin-card mt-6 p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">preview</span></span>
                    <div><h2 class="text-sm font-bold text-[#163a30]">Preview Paparan Website</h2><p class="mt-1 text-xs text-[#718078]">Gambaran hero yang akan dilihat oleh pelanggan.</p></div>
                </div>
                <div class="mt-4 overflow-hidden rounded-xl bg-[#002f28]">
                    <div class="relative aspect-[16/6] min-h-44 overflow-hidden sm:min-h-56">
                        <img src="{{ $transportVisualUrl }}" alt="Preview SalamHaji Transport" class="absolute inset-0 size-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#00271f]/95 via-[#00271f]/68 to-transparent"></div>
                        <div class="relative flex h-full max-w-xl flex-col justify-center p-5 sm:p-8">
                            <p class="text-[9px] font-bold uppercase tracking-[.16em] text-[#ffdf9a]">SalamHaji Transport</p>
                            <p class="mt-2 font-display text-2xl leading-none text-white sm:text-4xl">Perjalanan anda,<br>kami bantu uruskan.</p>
                            <p class="mt-3 max-w-md text-[10px] leading-4 text-white/80 sm:text-xs sm:leading-5">{{ $settings['transport_intro'] }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="mt-6 flex justify-end">
                <button class="admin-button-primary" type="submit"><span class="material-symbols-outlined text-[18px]">save</span>Simpan Tetapan Transport</button>
            </div>
        </form>
    </div>
</x-layouts.admin>

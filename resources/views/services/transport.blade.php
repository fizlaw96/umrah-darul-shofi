<x-layouts.app title="SalamHaji Transport" meta-description="Buat pertanyaan pengangkutan SalamHaji dengan lokasi pickup dan destinasi yang anda sahkan." :show-floating-whats-app="false">
    @push('scripts')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin="" defer></script>
    @endpush

    @php($transportVisualUrl = $transportBannerUrl ?: asset('darul-shofi/images/hero/transport-hero-v1.png'))

    <div class="transport-page pb-14 sm:pb-20">
        <section class="service-hero-fluid transport-hero-fluid">
            <div class="transport-hero">
                <img src="{{ $transportVisualUrl }}" alt="Van pengangkutan di Masjidil Haram" class="transport-hero__image">
                <div class="transport-hero__shade"></div>

                <div class="relative flex min-h-[calc(100svh-4.25rem)] flex-col px-5 py-6 sm:min-h-[calc(100svh-5rem)] sm:px-10 sm:py-8 lg:px-14 lg:py-10">
                    <nav class="flex items-center gap-1 text-[10px] font-semibold text-white/70" aria-label="Breadcrumb">
                        <a href="{{ route('home') }}" class="transition hover:text-[#ffdf9a]">Utama</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-[#ffdf9a]">Transport</span>
                    </nav>

                    <div class="mt-7 max-w-xl sm:mt-9">
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#ffdf9a]">SalamHaji Transport</p>
                        <h1 class="mt-3 font-display text-[2.45rem] leading-[.95] text-white sm:text-5xl lg:text-6xl">Perjalanan anda,<br>kami bantu uruskan.</h1>
                        <p class="mt-4 max-w-md text-sm leading-6 text-white/85 sm:text-base sm:leading-7">{{ $transportSettings['transport_intro'] }}</p>
                    </div>

                    <div class="mt-auto grid max-w-2xl grid-cols-3 gap-2 pt-8 sm:gap-3">
                        <div class="transport-hero__benefit">
                            <span class="material-symbols-outlined">chat</span>
                            <span><strong>Mudah &amp; Pantas</strong><small>Terus ke WhatsApp</small></span>
                        </div>
                        <div class="transport-hero__benefit">
                            <span class="material-symbols-outlined">support_agent</span>
                            <span><strong>Operator Berpengalaman</strong><small>Semakan peribadi</small></span>
                        </div>
                        <div class="transport-hero__benefit">
                            <span class="material-symbols-outlined">mosque</span>
                            <span><strong>Untuk Umrah &amp; Ziarah</strong><small>Di Tanah Suci</small></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if (! $transportIsEnabled)
            <section class="container-page pt-10 sm:pt-14">
                <div class="panel mx-auto max-w-3xl p-7 text-center sm:p-10">
                    <span class="material-symbols-outlined text-4xl text-[#8b671c]">schedule</span>
                    <h2 class="mt-3 font-display text-3xl text-dark-green">Pertanyaan Transport sedang tidak tersedia</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-[#5c6461]">Sila hubungi pasukan SalamHaji untuk pertanyaan lanjut.</p>
                </div>
            </section>
        @elseif (! $transportWhatsAppConfigured)
            <section class="container-page pt-10 sm:pt-14">
                <div class="panel mx-auto max-w-3xl p-7 text-center sm:p-10">
                    <span class="material-symbols-outlined text-4xl text-[#8b671c]">support_agent</span>
                    <h2 class="mt-3 font-display text-3xl text-dark-green">Operator Transport belum tersedia</h2>
                    <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-[#5c6461]">Saluran WhatsApp operator Transport sedang disediakan. Sila hubungi pasukan SalamHaji untuk pertanyaan pengangkutan buat masa ini.</p>
                </div>
            </section>
        @else
            <section class="container-page pt-7 sm:pt-10" data-transport-enquiry>
                @php($availableVehicleOptions = array_values(array_filter($transportVehicleOptions, fn (array $vehicle): bool => $vehicle['enabled'])))
                @php($selectedVehicle = collect($availableVehicleOptions)->first(fn (array $vehicle): bool => $vehicle['name'] === old('vehicle_type')))

                <form method="POST" action="{{ route('transport.prepare') }}" data-transport-form novalidate>
                    @csrf
                    <input type="hidden" name="user_timezone" value="{{ old('user_timezone') }}" data-transport-user-timezone>

                    <x-errors />

                    <div class="mx-auto max-w-5xl">
                        <nav class="transport-progress" aria-label="Kemajuan pertanyaan Transport">
                            <button class="is-current" type="button" data-transport-progress-step="1" aria-current="step"><span class="transport-progress__number">1</span><span class="transport-progress__label">Destinasi</span></button>
                            <button type="button" data-transport-progress-step="2"><span class="transport-progress__number">2</span><span class="transport-progress__label">Lokasi anda</span></button>
                            <button type="button" data-transport-progress-step="3"><span class="transport-progress__number">3</span><span class="transport-progress__label">Penumpang</span></button>
                            <button type="button" data-transport-progress-step="4"><span class="transport-progress__number">4</span><span class="transport-progress__label">Perjalanan</span></button>
                            <button type="button" data-transport-progress-step="5"><span class="transport-progress__number">5</span><span class="transport-progress__label">Semak</span></button>
                        </nav>

                        <div class="mt-5 space-y-4 sm:mt-6 sm:space-y-5">
                            <section class="transport-step-card" data-transport-step="1">
                                <div class="transport-step-summary">
                                    <span class="transport-step-number">1</span>
                                    <span><strong>Destinasi</strong><small>Pilih tempat yang anda ingin pergi dahulu.</small></span>
                                </div>

                                <div class="transport-step-content">
                                    <p class="mb-5 text-sm leading-6 text-[#61716a]">Cari destinasi secara manual, buka carian Google Maps, atau gunakan pautan Google Maps. Anda boleh semak dan laraskan pin di peta.</p>

                                    <fieldset class="transport-location-panel" data-transport-location data-location-key="destination">
                                        <legend>Ke mana anda mahu pergi?</legend>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button class="transport-location-action" type="button" data-location-select><span class="material-symbols-outlined">edit_location_alt</span>Laraskan pin</button>
                                        </div>
                                        <p class="mt-3 min-h-5 text-xs font-semibold text-[#006b57]" aria-live="polite" data-location-status></p>
                                        <label class="mt-3 block">
                                            <span class="field-label">Cari destinasi</span>
                                            <div class="flex gap-2">
                                                <input class="field-control" name="destination_place" value="{{ old('destination_place') }}" required placeholder="Hotel, lapangan terbang, masjid..." data-location-place data-transport-summary-destination>
                                                <button class="transport-search-button" type="button" data-location-open-search aria-label="Cari destinasi di Google Maps"><span class="material-symbols-outlined">search</span></button>
                                            </div>
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="field-label">Alamat / maklumat lokasi</span>
                                            <textarea class="field-control min-h-20" name="destination_address" maxlength="1000" data-location-address placeholder="Bangunan, nombor pintu atau mercu tanda">{{ old('destination_address') }}</textarea>
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="field-label">Link Google Maps (pilihan)</span>
                                            <div class="flex gap-2">
                                                <input class="field-control" type="url" name="destination_google_maps_url" value="{{ old('destination_google_maps_url') }}" placeholder="https://www.google.com/maps/..." data-location-link>
                                                <button class="transport-link-button" type="button" data-location-apply-link>Guna</button>
                                            </div>
                                        </label>
                                        <input type="hidden" name="destination_latitude" value="{{ old('destination_latitude') }}" data-location-latitude>
                                        <input type="hidden" name="destination_longitude" value="{{ old('destination_longitude') }}" data-location-longitude>
                                        <input type="hidden" name="destination_confirmed" value="{{ old('destination_confirmed') }}" data-location-confirmed>
                                        <button class="mt-4 w-full rounded-lg border border-[#8bcfb0] bg-[#eff9f2] px-3 py-2.5 text-xs font-bold text-[#006b57] transition hover:bg-[#e2f5e8]" type="button" data-location-confirm>Sahkan Destinasi</button>
                                    </fieldset>

                                    <p class="transport-step-feedback" data-transport-step-feedback="1" aria-live="polite"></p>
                                    <div class="transport-step-actions">
                                        <span>Sahkan destinasi sebelum meneruskan.</span>
                                        <button class="button-primary !px-4 !py-2.5" type="button" data-transport-next-step="2">Seterusnya: Lokasi anda<span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
                                    </div>
                                </div>
                            </section>

                            <section class="transport-step-card" data-transport-step="2" hidden>
                                <div class="transport-step-summary">
                                    <span class="transport-step-number">2</span>
                                    <span><strong>Lokasi anda</strong><small>Benarkan GPS atau tetapkan titik pickup anda sendiri.</small></span>
                                </div>

                                <div class="transport-step-content">
                                    <p class="mb-5 text-sm leading-6 text-[#61716a]">Tekan <strong>Guna Lokasi Saya</strong> untuk mendapatkan titik mula daripada GPS. Jika GPS tidak aktif, anda masih boleh mencari manual, mengalihkan pin atau menggunakan pautan Google Maps.</p>

                                    <fieldset class="transport-location-panel" data-transport-location data-location-key="pickup">
                                        <legend>Di mana anda mahu dijemput?</legend>
                                        <div class="mt-3 flex flex-wrap gap-2">
                                            <button class="transport-location-action" type="button" data-location-use-gps><span class="material-symbols-outlined">my_location</span>Guna Lokasi Saya</button>
                                            <button class="transport-location-action" type="button" data-location-select><span class="material-symbols-outlined">edit_location_alt</span>Laraskan pin</button>
                                        </div>
                                        <p class="mt-3 min-h-5 text-xs font-semibold text-[#006b57]" aria-live="polite" data-location-status></p>
                                        <label class="mt-3 block">
                                            <span class="field-label">Cari lokasi pickup</span>
                                            <div class="flex gap-2">
                                                <input class="field-control" name="pickup_place" value="{{ old('pickup_place') }}" required placeholder="Hotel, lapangan terbang, alamat..." data-location-place data-transport-summary-pickup>
                                                <button class="transport-search-button" type="button" data-location-open-search aria-label="Cari pickup di Google Maps"><span class="material-symbols-outlined">search</span></button>
                                            </div>
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="field-label">Alamat / maklumat lokasi</span>
                                            <textarea class="field-control min-h-20" name="pickup_address" maxlength="1000" data-location-address placeholder="Bangunan, nombor pintu atau mercu tanda">{{ old('pickup_address') }}</textarea>
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="field-label">Arahan pickup / mercu tanda <em class="font-medium normal-case text-[#87928d]">(jika ada)</em></span>
                                            <input class="field-control" name="pickup_instructions" value="{{ old('pickup_instructions') }}" maxlength="1000" placeholder="Contoh: Lobi hotel, pintu 3">
                                        </label>
                                        <label class="mt-3 block">
                                            <span class="field-label">Link Google Maps (pilihan)</span>
                                            <div class="flex gap-2">
                                                <input class="field-control" type="url" name="pickup_google_maps_url" value="{{ old('pickup_google_maps_url') }}" placeholder="https://www.google.com/maps/..." data-location-link>
                                                <button class="transport-link-button" type="button" data-location-apply-link>Guna</button>
                                            </div>
                                        </label>
                                        <input type="hidden" name="pickup_latitude" value="{{ old('pickup_latitude') }}" data-location-latitude>
                                        <input type="hidden" name="pickup_longitude" value="{{ old('pickup_longitude') }}" data-location-longitude>
                                        <input type="hidden" name="pickup_confirmed" value="{{ old('pickup_confirmed') }}" data-location-confirmed>
                                        <button class="mt-4 w-full rounded-lg border border-[#8bcfb0] bg-[#eff9f2] px-3 py-2.5 text-xs font-bold text-[#006b57] transition hover:bg-[#e2f5e8]" type="button" data-location-confirm>Sahkan Lokasi Pickup</button>
                                    </fieldset>

                                    <p class="transport-step-feedback" data-transport-step-feedback="2" aria-live="polite"></p>
                                    <div class="transport-step-actions">
                                        <span>Sahkan lokasi pickup sebelum meneruskan.</span>
                                        <button class="button-primary !px-4 !py-2.5" type="button" data-transport-next-step="3">Seterusnya: Penumpang<span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
                                    </div>
                                </div>
                            </section>

                            <div class="overflow-hidden rounded-xl border border-[#dce9df] bg-[#f8fbf8]" data-transport-map-wrap hidden>
                                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#dce9df] px-4 py-3">
                                    <p class="flex items-center gap-2 text-sm font-bold text-[#163a30]"><span class="material-symbols-outlined text-[#00705b]">map</span>Semak pin lokasi</p>
                                    <p class="text-xs text-[#718078]" data-transport-map-status>Pilih “Laraskan pin”, kemudian tekan atau seret marker di peta.</p>
                                </div>
                                <div class="h-72 sm:h-80" data-transport-map aria-label="Peta pickup dan destinasi"></div>
                            </div>

                            <section class="transport-step-card" data-transport-step="3" hidden>
                                <div class="transport-step-summary">
                                    <span class="transport-step-number">3</span>
                                    <span><strong>Butiran Penumpang</strong><small>Jumlah penumpang, bagasi dan kenderaan pilihan.</small></span>
                                </div>

                                <div class="transport-step-content">
                                    <div class="grid gap-3 sm:grid-cols-3">
                                        <label>
                                            <span class="field-label">Dewasa</span>
                                            <span class="transport-counter">
                                                <button type="button" data-transport-counter="decrement" aria-label="Kurangkan dewasa"><span class="material-symbols-outlined">remove</span></button>
                                                <input type="number" name="adults" value="{{ old('adults', 1) }}" min="1" max="100" required data-transport-summary-adults>
                                                <button type="button" data-transport-counter="increment" aria-label="Tambah dewasa"><span class="material-symbols-outlined">add</span></button>
                                            </span>
                                        </label>
                                        <label>
                                            <span class="field-label">Kanak-kanak</span>
                                            <span class="transport-counter">
                                                <button type="button" data-transport-counter="decrement" aria-label="Kurangkan kanak-kanak"><span class="material-symbols-outlined">remove</span></button>
                                                <input type="number" name="children" value="{{ old('children', 0) }}" min="0" max="100" data-transport-summary-children>
                                                <button type="button" data-transport-counter="increment" aria-label="Tambah kanak-kanak"><span class="material-symbols-outlined">add</span></button>
                                            </span>
                                        </label>
                                        <label>
                                            <span class="field-label">Item bagasi</span>
                                            <span class="transport-counter">
                                                <button type="button" data-transport-counter="decrement" aria-label="Kurangkan bagasi"><span class="material-symbols-outlined">remove</span></button>
                                                <input type="number" name="luggage_items" value="{{ old('luggage_items', 0) }}" min="0" max="100" data-transport-summary-luggage>
                                                <button type="button" data-transport-counter="increment" aria-label="Tambah bagasi"><span class="material-symbols-outlined">add</span></button>
                                            </span>
                                        </label>
                                    </div>

                                    <div class="mt-5">
                                        <span class="field-label">Jenis kenderaan pilihan <em class="font-medium normal-case text-[#87928d]">(jika ada)</em></span>
                                        @if ($availableVehicleOptions !== [])
                                            <div class="transport-vehicle-picker" data-transport-vehicle-picker>
                                                <input type="hidden" name="vehicle_type" value="{{ old('vehicle_type') }}" data-transport-summary-vehicle data-transport-vehicle-input>
                                                <button class="transport-vehicle-picker__trigger" type="button" data-transport-vehicle-trigger aria-expanded="false">
                                                    <span class="transport-vehicle-picker__preview" data-transport-vehicle-preview>
                                                        @if ($selectedVehicle)
                                                            <img src="{{ asset($selectedVehicle['image']) }}" alt="">
                                                        @else
                                                            <span class="material-symbols-outlined">directions_car</span>
                                                        @endif
                                                    </span>
                                                    <span class="min-w-0 flex-1 text-left"><strong data-transport-vehicle-label>{{ $selectedVehicle['name'] ?? 'Pilih jenis kenderaan' }}</strong><small data-transport-vehicle-description>{{ $selectedVehicle['description'] ?? 'Operator akan mengesahkan ketersediaan.' }}</small></span>
                                                    <span class="material-symbols-outlined text-[#607168]">expand_more</span>
                                                </button>
                                                <div class="transport-vehicle-picker__options" data-transport-vehicle-options hidden>
                                                    @foreach ($availableVehicleOptions as $vehicle)
                                                        @php($vehicleDescription = collect([$vehicle['description'], $vehicle['capacity'] ? 'Maks. '.$vehicle['capacity'].' penumpang' : null])->filter()->implode(' · '))
                                                        <button type="button" data-transport-vehicle-option data-value="{{ $vehicle['name'] }}" data-label="{{ $vehicle['name'] }}" data-description="{{ $vehicleDescription }}" data-capacity="{{ $vehicle['capacity'] }}" data-image="{{ asset($vehicle['image']) }}" aria-selected="{{ $selectedVehicle === $vehicle ? 'true' : 'false' }}">
                                                            <img src="{{ asset($vehicle['image']) }}" alt="">
                                                            <span><strong>{{ $vehicle['name'] }}</strong><small>{{ $vehicleDescription }}</small></span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <p class="rounded-xl border border-dashed border-[#d9e5dc] bg-[#fbfefc] px-4 py-3 text-xs leading-5 text-[#68786f]">Operator akan mencadangkan kenderaan yang sesuai berdasarkan penumpang dan bagasi.</p>
                                        @endif
                                    </div>

                                    <p class="transport-step-feedback" data-transport-step-feedback="3" aria-live="polite"></p>
                                    <div class="transport-step-actions">
                                        <button class="button-primary !px-4 !py-2.5" type="button" data-transport-next-step="4">Seterusnya: Perjalanan<span class="material-symbols-outlined text-[18px]">arrow_forward</span></button>
                                    </div>
                                </div>
                            </section>

                            <section class="transport-step-card" data-transport-step="4" hidden>
                                <div class="transport-step-summary">
                                    <span class="transport-step-number">4</span>
                                    <span><strong>Perjalanan &amp; Hubungi</strong><small>Pilih masa perjalanan dan cara operator menghubungi anda.</small></span>
                                </div>

                                <div class="transport-step-content">
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="transport-request-type">
                                            <input type="radio" name="request_type" value="scheduled" @checked(old('request_type', 'scheduled') === 'scheduled')>
                                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#e5f6ec] text-[#00705b]"><span class="material-symbols-outlined text-[20px]">calendar_month</span></span>
                                            <span><strong>Jadualkan Perjalanan</strong><small>Pilih tarikh dan masa pickup.</small></span>
                                        </label>
                                        <label class="transport-request-type">
                                            <input type="radio" name="request_type" value="immediate" @checked(old('request_type') === 'immediate')>
                                            <span class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#f7f2e5] text-[#8b671c]"><span class="material-symbols-outlined text-[20px]">bolt</span></span>
                                            <span><strong>Perlu Segera</strong><small>Operator akan semak secepat mungkin.</small></span>
                                        </label>
                                    </div>

                                    <p class="mt-4 rounded-xl border border-[#f4dfac] bg-[#fffaec] px-4 py-3 text-xs leading-5 text-[#765417]" data-transport-immediate-notice hidden>Permintaan segera tertakluk kepada ketersediaan kenderaan dan pengesahan operator.</p>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2" data-transport-scheduled-fields>
                                        <label>
                                            <span class="field-label">Tarikh pickup</span>
                                            <input class="field-control" type="date" name="pickup_date" value="{{ old('pickup_date') }}" required data-transport-summary-date>
                                        </label>
                                        <label>
                                            <span class="field-label">Masa pickup</span>
                                            <input class="field-control" type="time" name="pickup_time" value="{{ old('pickup_time') }}" required data-transport-summary-time>
                                        </label>
                                    </div>

                                    <div class="mt-5 grid gap-4 border-t border-[#edf1ee] pt-5 sm:grid-cols-2">
                                        <label>
                                            <span class="field-label">Nama penuh</span>
                                            <input class="field-control" name="full_name" value="{{ old('full_name') }}" required autocomplete="name" placeholder="Nama seperti dalam pasport">
                                        </label>
                                        <label>
                                            <span class="field-label">Nombor WhatsApp</span>
                                            <input class="field-control" name="contact_number" value="{{ old('contact_number') }}" required autocomplete="tel" placeholder="Contoh: +60 12 345 6789">
                                        </label>
                                    </div>

                                    <p class="transport-step-feedback" data-transport-step-feedback="4" aria-live="polite"></p>
                                    <div class="transport-step-actions">
                                        <button class="button-primary !px-4 !py-2.5" type="button" data-transport-next-step="5">Semak Semua Maklumat<span class="material-symbols-outlined text-[18px]">fact_check</span></button>
                                    </div>
                                </div>
                            </section>

                            <section class="transport-review-card" data-transport-review hidden tabindex="-1">
                                <div class="flex items-start gap-3">
                                    <span class="transport-step-number">5</span>
                                    <div><p class="section-eyebrow">Semakan akhir</p><h2>Semak sebelum ke WhatsApp</h2><p>Pastikan semua maklumat di bawah tepat. Tekan indikator di atas untuk kembali ke langkah yang ingin diubah.</p></div>
                                </div>

                                <div class="transport-review-grid">
                                    <article>
                                        <header><h3>1. Destinasi</h3></header>
                                        <dl><div><dt>Tempat dituju</dt><dd data-transport-review-destination>—</dd></div></dl>
                                    </article>
                                    <article>
                                        <header><h3>2. Lokasi pickup</h3></header>
                                        <dl><div><dt>Titik pickup</dt><dd data-transport-review-pickup>—</dd></div><div><dt>Arahan pickup</dt><dd data-transport-review-instructions>—</dd></div></dl>
                                    </article>
                                    <article>
                                        <header><h3>3. Penumpang &amp; kenderaan</h3></header>
                                        <dl><div><dt>Penumpang &amp; bagasi</dt><dd data-transport-review-passengers>—</dd></div><div><dt>Kenderaan pilihan</dt><dd data-transport-review-vehicle>—</dd></div></dl>
                                    </article>
                                    <article>
                                        <header><h3>4. Perjalanan &amp; hubungi</h3></header>
                                        <dl><div><dt>Jenis perjalanan</dt><dd data-transport-review-request>—</dd></div><div><dt>Tarikh &amp; masa</dt><dd data-transport-review-schedule>—</dd></div><div><dt>Nama penuh</dt><dd data-transport-review-name>—</dd></div><div><dt>WhatsApp</dt><dd data-transport-review-contact>—</dd></div></dl>
                                    </article>
                                </div>

                                <div class="mt-5 rounded-xl border border-[#f1dfa7] bg-[#fffaec] p-4">
                                    <p class="flex items-center gap-2 text-xs font-bold text-[#624713]"><span class="material-symbols-outlined text-[17px]">info</span>Maklumat penting</p>
                                    <ul class="mt-2 space-y-1.5 text-xs leading-5 text-[#765417]">
                                        <li>• Ini ialah perkhidmatan sebut harga, bukan tempahan segera.</li>
                                        <li>• Operator akan menghubungi anda melalui WhatsApp untuk pengesahan.</li>
                                        <li>• Sila pastikan maklumat lokasi adalah tepat.</li>
                                    </ul>
                                    <label class="transport-acknowledgement mt-4">
                                        <input type="checkbox" name="important_information_acknowledged" value="1" required data-transport-info-acknowledgement @checked(old('important_information_acknowledged'))>
                                        <span>Saya sudah membaca dan memahami maklumat penting di atas.</span>
                                    </label>
                                </div>

                                @if (filled($transportSettings['transport_operating_notes']))
                                    <p class="mt-4 text-xs leading-5 text-[#69746f]">{{ $transportSettings['transport_operating_notes'] }}</p>
                                @endif

                                <button class="button-primary mt-5 w-full !rounded-xl !py-3.5" type="submit" data-transport-submit disabled><span class="material-symbols-outlined text-[20px]">chat</span>Hantar Pertanyaan Melalui WhatsApp<span class="material-symbols-outlined text-[20px]">arrow_forward</span></button>
                            </section>
                        </div>
                    </div>
                </form>
            </section>
        @endif
    </div>
</x-layouts.app>

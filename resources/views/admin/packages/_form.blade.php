@php
    $isEditing = $package->exists;
    $description = old('description', $package->description) ?? '';
    $safeDescription = \App\Models\Package::sanitizeDescription($description);
    $aiCategoryNames = $categories->pluck('name')->join(', ');
    $defaultPackageServices = is_array($package->service_options)
        ? $package->service_options
        : [...$serviceOptions->pluck('name')->all(), ...($package->inclusions ?? []), ...($package->exclusions ?? [])];
    $packageServices = collect(old('package_services', $defaultPackageServices))
        ->map(function (mixed $service) use ($package): ?array {
            $serviceName = is_array($service) ? ($service['name'] ?? null) : $service;

            if (! is_string($serviceName) || blank($serviceName)) {
                return null;
            }

            return [
                'name' => trim($serviceName),
                'included' => is_array($service)
                    ? filter_var($service['included'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    : in_array($serviceName, $package->inclusions ?? [], true),
                'excluded' => is_array($service)
                    ? filter_var($service['excluded'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    : in_array($serviceName, $package->exclusions ?? [], true),
            ];
        })
        ->filter()
        ->unique(fn (array $service): string => mb_strtolower($service['name']))
        ->values()
        ->all();
    $aiExistingServiceNames = collect($packageServices)
        ->pluck('name')
        ->filter(fn (mixed $name): bool => is_string($name) && filled($name))
        ->values();
    $aiExistingServiceList = $aiExistingServiceNames
        ->map(fn (string $name): string => "- {$name}")
        ->join("\n");
    $aiAdditionalServiceSlots = max(0, 20 - $aiExistingServiceNames->count());
    $ziarahPlaces = old('ziarah_places', $package->ziarah_places ?? []);
    $gifts = old('gifts', $package->gifts ?? []);
    $flightOptions = old('flight_options', $package->flight_options ?? []);
    if ($flightOptions === [] && filled($package->airline)) {
        $flightOptions = [[
            'airline' => $package->airline,
            'departure_airport' => $package->departure_airport,
            'arrival_airport' => $package->arrival_airport,
            'proposed_departure_date' => $package->proposed_departure_date?->toDateString(),
            'notes' => $package->flight_information,
            'images' => [],
        ]];
    }
    $hotelOptions = old('hotel_options', $package->hotel_options ?? []);
    if ($hotelOptions === []) {
        $hotelOptions = collect([
            [
                'location' => 'Makkah',
                'name' => $package->makkah_hotel,
                'rating' => $package->makkah_hotel_rating,
                'nights' => $package->makkah_nights,
                'walking_distance' => $package->walking_distance,
                'notes' => $package->hotel_information,
            ],
            [
                'location' => 'Madinah',
                'name' => $package->madinah_hotel,
                'rating' => $package->madinah_hotel_rating,
                'nights' => $package->madinah_nights,
                'walking_distance' => null,
                'notes' => null,
            ],
        ])->filter(fn (array $hotel): bool => filled($hotel['name']))->values()->all();
    }
    $hotelOptions = collect($hotelOptions)
        ->filter(fn (mixed $hotel): bool => is_array($hotel))
        ->map(function (array $hotel): array {
            $hotel['key'] = filled($hotel['key'] ?? null)
                ? $hotel['key']
                : (string) \Illuminate\Support\Str::uuid();

            return $hotel;
        })
        ->values()
        ->all();
    $hotelSelectOptions = collect($hotelOptions)
        ->filter(fn (array $hotel): bool => filled($hotel['key'] ?? null) && filled($hotel['name'] ?? null))
        ->values();
    $roomPrices = collect(old('room_prices', $package->room_prices ?? []))
        ->filter(fn (mixed $price): bool => is_array($price))
        ->map(fn (array $price): array => [
            'room_type' => $price['room_type'] ?? '',
            'price' => $price['price'] ?? (isset($price['price_sen']) ? number_format($price['price_sen'] / 100, 2, '.', '') : ''),
            'hotel_key' => $price['hotel_key'] ?? '',
            'existing_image' => $price['existing_image'] ?? $price['image'] ?? null,
        ])
        ->all();
    $seasonYears = range(now()->year, now()->addYears(2)->year);
    $selectedSeasonYear = (string) old('season_label', $package->season_label);
    if (preg_match('/(\d{4})M$/', $selectedSeasonYear, $seasonMatch) === 1) {
        $selectedSeasonYear = $seasonMatch[1];
    }
    if (! in_array((int) $selectedSeasonYear, $seasonYears, true)) {
        $selectedSeasonYear = '';
    }
    $seasonYearList = implode(', ', $seasonYears);
    $isDateFlexible = filter_var(old('is_date_flexible', $package->is_date_flexible), FILTER_VALIDATE_BOOLEAN);
    $departureRows = old('departure_rows', $package->departures->map(fn (\App\Models\PackageDeparture $departure): array => ['id' => $departure->id, 'departure_date' => $departure->departure_date->toDateString(), 'return_date' => $departure->return_date->toDateString(), 'departure_airport' => $departure->departure_airport, 'price' => number_format($departure->price_sen / 100, 2, '.', ''), 'capacity' => $departure->capacity, 'status' => $departure->status])->all());
    $existingItineraries = $package->itineraries->keyBy('day_number');
    $itineraryDays = old('itinerary_days', []);
    $durationDays = max(1, min(60, (int) old('duration_days', $package->duration_days ?: 1)));
    $galleryImages = collect([$package->cover_image])->merge($package->images->pluck('path'))->filter()->unique()->values();
    $isFeatured = filter_var(old('is_featured', $package->is_featured), FILTER_VALIDATE_BOOLEAN);
    $selectedPromotionBadge = old('promotion_badge', $package->promotion_badge);
    $validationErrorTabs = [
        'departure_rows' => 'departures',
        'flight_options' => 'flights',
        'hotel_options' => 'hotels',
        'package_services' => 'inclusions',
        'itinerary_days' => 'itinerary',
        'room_prices' => 'rooms',
        'ziarah_places' => 'ziarah',
        'gifts' => 'gifts',
        'promotion_badge' => 'display',
        'cover_image' => 'media',
        'gallery' => 'media',
        'primary_image_path' => 'media',
        'gallery_primary_index' => 'media',
        'itinerary_pdf' => 'media',
    ];
    $validationErrorTab = 'basic';

    foreach ($errors->keys() as $errorKey) {
        foreach ($validationErrorTabs as $input => $tab) {
            if ($errorKey === $input || str_starts_with($errorKey, "{$input}.")) {
                $validationErrorTab = $tab;

                break 2;
            }
        }
    }
@endphp

<form data-package-form data-admin-tabs data-default-tab="{{ $validationErrorTab }}" @if ($isEditing) data-autosave-url="{{ route('admin.packages.autosave', $package) }}" @endif method="POST" enctype="multipart/form-data" action="{{ $isEditing ? route('admin.packages.update', $package) : route('admin.packages.store') }}" novalidate>
    @csrf
    @if ($isEditing) @method('PUT') @endif
    <input type="hidden" name="description" data-rich-editor-input value="{{ $description }}">

    <section class="admin-card overflow-hidden">
        <div class="flex flex-col justify-between gap-5 px-6 py-6 xl:flex-row xl:items-start">
            <div>
                <nav class="flex flex-wrap items-center gap-1.5 text-[10px] font-bold uppercase tracking-[.13em] text-[#718078]" aria-label="Breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="transition hover:text-[#006b57]">Portal Pentadbir</a><span>›</span>
                    <a href="{{ route('admin.packages.index') }}" class="transition hover:text-[#006b57]">Senarai Pakej</a><span>›</span>
                    <span>{{ $isEditing ? 'Kemas Kini Pakej' : 'Pakej Baharu' }}</span>
                </nav>
                <h1 class="mt-2 text-3xl font-bold tracking-[-.045em] text-[#00251e]">{{ $isEditing ? 'Edit Pakej: '.$package->title : 'Cipta Pakej Umrah' }}</h1>
                <p class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-[#dcf7ed] px-3 py-1 text-[10px] font-bold tracking-[.05em] text-[#006b57]"><span class="size-1.5 rounded-full bg-current"></span>{{ $package->status === 'published' ? 'Sedang Disunting · Diterbitkan' : 'Sedang Disunting · Draf' }}</p>
            </div>
            <a href="{{ route('admin.packages.index') }}" class="admin-button-secondary"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Kembali</a>
        </div>
        <details class="group border-t border-[#e8eee9]" data-ai-assistant-collapse>
            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-6 py-4 transition hover:bg-[#fbfefc] [&::-webkit-details-marker]:hidden">
                <span class="flex items-center gap-3">
                    <span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined text-[20px]">auto_awesome</span></span>
                    <span>
                        <span class="block text-sm font-bold text-[#163a30]">Pembantu AI</span>
                        <span class="mt-0.5 block text-xs text-[#718078]">Import maklumat pakej atau bina prompt poster.</span>
                    </span>
                </span>
                <span class="inline-flex shrink-0 items-center gap-1.5 text-xs font-bold text-[#006b57]">Buka pembantu<span class="material-symbols-outlined text-[18px]">expand_more</span></span>
            </summary>
            <div class="border-t border-[#e8eee9] px-6 pb-6">
                <div class="mt-5 grid gap-6 xl:grid-cols-2">
    <section class="admin-card flex h-full flex-col border-[#c8e5d5] bg-[#f4fbf6] p-5 sm:p-6" data-ai-import-assistant>
        <div class="flex flex-1 flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#d8f1e1] text-[#006b57]"><span class="material-symbols-outlined text-[21px]">auto_awesome</span></span>
                <div>
                    <h2 class="text-base font-bold text-[#163a30]">Isi keseluruhan pakej dengan AI</h2>
                    <p class="mt-1 max-w-3xl text-xs leading-5 text-[#607068]">Salin prompt, tampal jawapan JSON daripada ChatGPT, dan sync terus ke borang untuk semakan manual.</p>
                </div>
            </div>
            <button class="admin-button-secondary shrink-0 !border-[#9bcdb2] !bg-white" type="button" data-ai-import-open><span class="material-symbols-outlined text-[18px]">auto_awesome</span>AI Prompt</button>
        </div>

        <dialog class="admin-ai-import-dialog" data-ai-import-dialog aria-labelledby="ai-import-title">
            <div class="flex items-start justify-between gap-5 border-b border-[#e2eee6] px-5 py-4 sm:px-7 sm:py-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">auto_awesome</span></span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.12em] text-[#718078]">Pembantu Pengisian</p>
                        <h2 id="ai-import-title" class="mt-1 text-xl font-bold text-[#00251e]">Import keseluruhan pakej dengan AI</h2>
                        <p class="mt-1 text-xs leading-5 text-[#607068]">Data akan dimasukkan ke borang untuk semakan anda. Simpan sebagai draf atau terbitkan selepas menyemak.</p>
                    </div>
                </div>
                <button class="grid size-9 shrink-0 place-items-center rounded-lg text-[#607068] transition hover:bg-[#f1f6f2]" type="button" data-ai-import-close aria-label="Tutup import AI"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="admin-ai-import-dialog__body grid gap-5 px-5 py-5 lg:grid-cols-2 sm:px-7">
                <section>
                    <div class="flex items-center justify-between gap-3">
                        <label class="admin-label !mb-0" for="ai-import-prompt">1. Prompt untuk ChatGPT</label>
                        <button class="text-xs font-bold text-[#006b57]" type="button" data-ai-import-copy-prompt>Salin prompt</button>
                    </div>
                    <textarea id="ai-import-prompt" data-ai-import-prompt class="admin-field mt-3 min-h-80 resize-y bg-[#f7fbf8] font-mono text-xs leading-5" readonly>Anda ialah pembantu pengisian data pakej Umrah. Berdasarkan bahan promosi yang saya lampirkan (teks atau gambar), ekstrak maklumat yang benar-benar dinyatakan sahaja. Jangan mereka maklumat. Balas dengan tepat satu objek JSON sahaja, tanpa markdown, penerangan, komen, trailing comma, atau kunci tambahan.

PERATURAN
1. Gunakan null untuk nilai yang tidak diketahui. Jangan gunakan "N/A", teks kosong, pautan gambar atau kunci tambahan.
2. Kategori yang sah hanya: {{ $aiCategoryNames }}. Gunakan satu nama ini dengan tepat, atau null.
3. Semua tarikh mesti dalam format YYYY-MM-DD. Harga ialah nombor tanpa RM atau koma. duration_days ialah integer 1 hingga 60; duration_nights ialah integer 0 hingga 59.
4. Jangan sertakan gambar, fail, slug, kod, key, hotel_key, image atau existing_image. Gambar dimuat naik kemudian secara manual.
5. Hanya gunakan perkhidmatan yang benar-benar dinyatakan oleh bahan promosi.

PERKHIDMATAN SEDIA ADA DALAM BORANG ({{ $aiExistingServiceNames->count() }}/20)
{{ $aiExistingServiceList ?: '- Tiada perkhidmatan sedia ada.' }}

Jika maksud perkhidmatan daripada poster sama atau hampir sama dengan senarai di atas, gunakan ejaan nama sedia ada. Hanya {{ $aiAdditionalServiceSlots }} perkhidmatan baharu boleh ditambah.

SKEMA JSON
{
  "title": null,
  "package_category": null,
  "season_label": null,
  "description": null,
  "starting_price": null,
  "duration_days": null,
  "duration_nights": null,
  "departure_rows": null,
  "flight_options": null,
  "hotel_options": null,
  "included_services": null,
  "excluded_services": null,
  "itinerary_days": null,
  "room_prices": null,
  "ziarah_places": null,
  "gifts": null,
  "is_featured": null
}

CONTOH BENTUK SENARAI
departure_rows: [{"departure_date":"YYYY-MM-DD","return_date":"YYYY-MM-DD","departure_airport":null,"price":0,"capacity":null,"status":null}]
flight_options: [{"airline":"...","departure_airport":"...","arrival_airport":"...","proposed_departure_date":"YYYY-MM-DD","notes":"..."}]
hotel_options: [{"location":"...","name":"...","rating":5,"nights":4,"walking_distance":"...","notes":"..."}]
room_prices: [{"hotel_name":"...","room_type":"...","price":0}]
itinerary_days: [{"title":"...","description":"..."}]
ziarah_places: [{"name":"...","location":"...","description":"..."}]
gifts: [{"title":"...","description":"..."}]</textarea>
                    <p class="mt-2 text-[11px] leading-4 text-[#718078]">Lampirkan poster atau screenshot promosi bersama prompt ini. Gambar dimuat naik secara manual.</p>
                </section>

                <section>
                    <label class="admin-label" for="ai-import-response">2. Tampal jawapan AI</label>
                    <textarea id="ai-import-response" data-ai-import-response class="admin-field min-h-80 resize-y font-mono text-xs leading-5" placeholder='Contoh: {"title":"Pakej Umrah...", "starting_price":8490}' spellcheck="false"></textarea>
                    <p class="mt-2 text-[11px] leading-4 text-[#718078]">JSON dalam blok kod juga diterima. Nilai <code>null</code> tidak akan memadam data sedia ada.</p>
                    <p class="mt-3 min-h-5 text-xs font-semibold text-[#006b57]" data-ai-import-status role="status" aria-live="polite"></p>
                    <div class="mt-3 hidden rounded-xl border border-[#edc5be] bg-[#fff6f4] p-3 text-xs text-[#9e3028]" data-ai-import-errors role="alert">
                        <p class="font-bold">Sila semak dan betulkan ralat berikut sebelum sync semula:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-4 leading-5" data-ai-import-error-list></ul>
                    </div>
                </section>
            </div>

            <div class="flex flex-col-reverse justify-between gap-3 border-t border-[#e2eee6] bg-[#fbfefc] px-5 py-4 sm:flex-row sm:items-center sm:px-7">
                <button class="admin-button-secondary self-start sm:self-auto" type="button" data-ai-import-close>Kembali</button>
                <button class="admin-button-primary" type="button" data-ai-import-sync disabled><span class="material-symbols-outlined text-[18px]">sync</span>Sync ke borang</button>
            </div>
        </dialog>
    </section>

    <section class="admin-card flex h-full flex-col border-[#c8e5d5] bg-[#f4fbf6] p-5 sm:p-6" data-poster-prompt-assistant>
        <div class="flex flex-1 flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div class="flex items-start gap-3">
                <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#d8f1e1] text-[#006b57]"><span class="material-symbols-outlined text-[21px]">auto_awesome</span></span>
                <div>
                    <h2 class="text-base font-bold text-[#163a30]">Jana poster pakej dengan AI</h2>
                    <p class="mt-1 max-w-3xl text-xs leading-5 text-[#607068]">Gunakan data yang telah diisi dalam borang untuk membina prompt poster yang sedia disalin ke ChatGPT.</p>
                    <p class="mt-2 text-[11px] font-semibold text-[#006b57]" data-poster-prompt-completion role="status" aria-live="polite">Isi sekurang-kurangnya 50% maklumat pakej untuk jana prompt poster.</p>
                </div>
            </div>
            <button class="admin-button-secondary shrink-0 !border-[#9bcdb2] !bg-white disabled:cursor-not-allowed disabled:opacity-50" type="button" data-poster-prompt-open disabled><span class="material-symbols-outlined text-[18px]">image</span>Jana poster</button>
        </div>

        <dialog class="admin-poster-prompt-dialog" data-poster-prompt-dialog aria-labelledby="poster-prompt-title">
            <div class="flex items-start justify-between gap-5 border-b border-[#e2eee6] px-5 py-4 sm:px-7 sm:py-5">
                <div class="flex items-start gap-3">
                    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">image</span></span>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[.12em] text-[#718078]">Pembantu Poster</p>
                        <h2 id="poster-prompt-title" class="mt-1 text-xl font-bold text-[#00251e]">Prompt poster sedia untuk disalin</h2>
                        <p class="mt-1 text-xs leading-5 text-[#607068]">Prompt ini menggunakan data yang sedang ada dalam borang. Ia tidak menyimpan atau mengubah data pakej.</p>
                    </div>
                </div>
                <button class="grid size-9 shrink-0 place-items-center rounded-lg text-[#607068] transition hover:bg-[#f1f6f2]" type="button" data-poster-prompt-close aria-label="Tutup prompt poster"><span class="material-symbols-outlined">close</span></button>
            </div>

            <div class="admin-poster-prompt-dialog__body px-5 py-5 sm:px-7">
                <div class="flex items-center justify-between gap-3">
                    <label class="admin-label !mb-0" for="poster-prompt">Prompt untuk ChatGPT</label>
                    <button class="text-xs font-bold text-[#006b57]" type="button" data-poster-prompt-copy>Salin prompt</button>
                </div>
                <textarea id="poster-prompt" data-poster-prompt class="admin-field mt-3 min-h-96 resize-y bg-[#f7fbf8] font-mono text-xs leading-5" readonly></textarea>
                <p class="mt-2 text-[11px] leading-4 text-[#718078]">Buka ChatGPT, tampal prompt ini, kemudian jana visual poster. Maklumat yang kosong tidak akan dimasukkan.</p>
                <p class="mt-3 min-h-5 text-xs font-semibold text-[#006b57]" data-poster-prompt-status role="status" aria-live="polite"></p>
            </div>

            <div class="flex justify-end border-t border-[#e2eee6] bg-[#fbfefc] px-5 py-4 sm:px-7">
                <button class="admin-button-secondary" type="button" data-poster-prompt-close>Tutup</button>
            </div>
        </dialog>
    </section>
                </div>
            </div>
        </details>

        <nav class="flex overflow-x-auto border-t border-[#e8eee9] px-3" aria-label="Seksyen borang pakej" role="tablist">
            <button type="button" role="tab" data-admin-tab-trigger="basic" class="admin-package-tab">1. Maklumat Asas</button>
            <button type="button" role="tab" data-admin-tab-trigger="departures" class="admin-package-tab">2. Tarikh Berlepas</button>
            <button type="button" role="tab" data-admin-tab-trigger="flights" class="admin-package-tab">3. Perjalanan</button>
            <button type="button" role="tab" data-admin-tab-trigger="hotels" class="admin-package-tab">4. Penginapan Hotel</button>
            <button type="button" role="tab" data-admin-tab-trigger="inclusions" class="admin-package-tab">5. Inklusi &amp; Syarat</button>
            <button type="button" role="tab" data-admin-tab-trigger="itinerary" class="admin-package-tab">6. Itinerari</button>
            <button type="button" role="tab" data-admin-tab-trigger="rooms" class="admin-package-tab">7. Harga Bilik</button>
            <button type="button" role="tab" data-admin-tab-trigger="ziarah" class="admin-package-tab">8. Tempat Ziarah</button>
            <button type="button" role="tab" data-admin-tab-trigger="gifts" class="admin-package-tab">9. Set Hadiah</button>
            <button type="button" role="tab" data-admin-tab-trigger="display" class="admin-package-tab">10. Paparan Web</button>
            <button type="button" role="tab" data-admin-tab-trigger="media" class="admin-package-tab">11. Gambar &amp; Media</button>
        </nav>
    </section>

    <x-errors />

    <section class="admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="basic">
        <div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">badge</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Maklumat Asas Pakej</h2><p class="mt-1 text-xs text-[#718078]">Kod pakej dan pautan URL dijana automatik selepas pakej disimpan.</p></div></div>
        <div class="mt-7 grid gap-5 md:grid-cols-2">
            <label class="md:col-span-2"><span class="admin-label">Nama Rasmi Pakej</span><input class="admin-field" name="title" value="{{ old('title', $package->title) }}" required></label>
            <label><span class="admin-label">Kategori Pakej</span><select class="admin-field" name="package_category_id" required><option value="">Pilih kategori</option>@foreach ($categories as $category)<option value="{{ $category->id }}" @selected((int) old('package_category_id', $package->package_category_id) === $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label><span class="admin-label">Musim</span><select class="admin-field" name="season_label"><option value="">Pilih tahun</option>@foreach ($seasonYears as $seasonYear)<option value="{{ $seasonYear }}" @selected($selectedSeasonYear === (string) $seasonYear)>{{ $seasonYear }}</option>@endforeach</select></label>
            <label><span class="admin-label">Harga Bermula (MYR)</span><div class="relative"><span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-[#607068]">RM</span><input class="admin-field pl-11" type="number" step="0.01" min="0" name="starting_price" value="{{ old('starting_price', $isEditing ? number_format($package->starting_price_sen / 100, 2, '.', '') : '') }}" required></div></label>
            <label><span class="admin-label">Tempoh Perjalanan (Hari)</span><input data-duration-days class="admin-field" type="number" min="1" max="60" name="duration_days" value="{{ old('duration_days', $package->duration_days) }}" required></label>
            <label><span class="admin-label">Tempoh Perjalanan (Malam)</span><input class="admin-field" type="number" min="0" max="59" name="duration_nights" value="{{ old('duration_nights', $package->duration_nights) }}" required></label>
            <p class="md:col-span-2 text-[11px] leading-5 text-[#718078]">Ringkasan pada hero halaman pakej dikemas kini secara automatik daripada Tempoh Perjalanan di sini, Kapasiti di Tarikh Berlepas, Maklumat Perjalanan, dan Penginapan Hotel.</p>
            <div class="md:col-span-2"><span class="admin-label">Penerangan Lengkap</span><div class="overflow-hidden rounded-xl border border-[#dbe8df] bg-white"><div class="flex flex-wrap gap-1 border-b border-[#dbe8df] bg-[#e9f7ed] p-2"><button type="button" data-editor-command="bold" class="grid size-8 place-items-center rounded-lg font-bold hover:bg-white">B</button><button type="button" data-editor-command="italic" class="grid size-8 place-items-center rounded-lg text-lg italic hover:bg-white">I</button><button type="button" data-editor-command="insertUnorderedList" class="grid size-8 place-items-center rounded-lg hover:bg-white"><span class="material-symbols-outlined text-[18px]">format_list_bulleted</span></button><button type="button" data-editor-command="insertOrderedList" class="grid size-8 place-items-center rounded-lg hover:bg-white"><span class="material-symbols-outlined text-[18px]">format_list_numbered</span></button></div><div data-rich-editor contenteditable="true" role="textbox" aria-multiline="true" class="min-h-52 p-4 text-sm leading-7 text-[#354940] outline-none focus:bg-[#fbfefc]">{!! $safeDescription !!}</div></div><p class="mt-2 text-[10px] leading-4 text-[#718078]">Ringkasan untuk paparan awam dijana automatik daripada penerangan ini.</p></div>
        </div>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="departures" data-flexible-departure-settings>
        <div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">calendar_month</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Tarikh Berlepas</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Tetapkan jadual khusus, atau tandakan tarikh fleksibel bagi pakej yang pelanggan boleh pilih sendiri.</p></div></div>
        <label class="mt-6 flex cursor-pointer items-center justify-between gap-4 rounded-xl border border-[#bfe0ca] bg-[#f3fbf5] p-4"><span><span class="block text-sm font-bold text-[#163a30]">Tarikh fleksibel</span><span class="mt-1 block text-[11px] leading-4 text-[#607068]">Pelanggan memilih tarikh atau bulan sendiri di halaman pakej, kemudian agensi mengesahkan ketersediaan.</span></span><span class="admin-toggle"><input data-is-date-flexible class="sr-only" type="checkbox" name="is_date_flexible" value="1" @checked($isDateFlexible)><span></span></span></label>
        <div data-fixed-departures data-package-collection="departure_rows" data-collection-next-index="{{ count($departureRows) }}" @if ($isDateFlexible) hidden @endif>
            <div class="mt-6 flex items-start justify-between gap-4"><div><h3 class="text-sm font-bold text-[#163a30]">Jadual tarikh tetap</h3><p class="mt-1 text-xs leading-5 text-[#718078]">Tarikh pergi, balik dan harga diperlukan. Draf tidak dipaparkan kepada pelanggan. Pilih Dibuka untuk dipaparkan sebagai tersedia (kapasiti wajib), atau Ditutup untuk memaparkan jadual yang tidak lagi tersedia.</p></div><button type="button" data-collection-add class="text-xs font-bold text-[#006b57]">+ Tambah tarikh</button></div>
        <div data-collection-items class="mt-6 space-y-3">@forelse ($departureRows as $index => $departure)<div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><input type="hidden" name="departure_rows[{{ $index }}][id]" value="{{ $departure['id'] ?? '' }}"><div class="flex items-center justify-between gap-3"><p class="text-sm font-bold text-[#163a30]">Jadual berlepas</p><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang jadual"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"><label><span class="admin-label">Tarikh pergi</span><input class="admin-field !py-2" type="date" name="departure_rows[{{ $index }}][departure_date]" value="{{ $departure['departure_date'] ?? '' }}"></label><label><span class="admin-label">Tarikh balik</span><input class="admin-field !py-2" type="date" name="departure_rows[{{ $index }}][return_date]" value="{{ $departure['return_date'] ?? '' }}"></label><label><span class="admin-label">Lapangan terbang (jika diketahui)</span><input class="admin-field !py-2" name="departure_rows[{{ $index }}][departure_airport]" value="{{ $departure['departure_airport'] ?? '' }}"></label><label><span class="admin-label">Harga seorang (MYR)</span><input class="admin-field !py-2" type="number" min="0" step="0.01" name="departure_rows[{{ $index }}][price]" value="{{ $departure['price'] ?? '' }}"></label><label><span class="admin-label">Kapasiti (wajib jika dibuka)</span><input class="admin-field !py-2" type="number" min="1" name="departure_rows[{{ $index }}][capacity]" value="{{ $departure['capacity'] ?? '' }}"></label><label><span class="admin-label">Status</span><select class="admin-field !py-2" name="departure_rows[{{ $index }}][status]"><option value="draft" @selected(($departure['status'] ?? 'draft') === 'draft')>Draf — tidak dipaparkan</option><option value="open" @selected(($departure['status'] ?? '') === 'open')>Dibuka — tersedia</option><option value="closed" @selected(($departure['status'] ?? '') === 'closed')>Ditutup — dipaparkan, tidak tersedia</option></select></label></div></div>@empty<p class="rounded-xl border border-dashed border-[#cfe1d4] bg-[#fbfefc] px-4 py-8 text-center text-xs text-[#718078]">Belum ada tarikh. Tekan “Tambah tarikh” untuk memasukkan jadual pertama.</p>@endforelse</div>
        <template data-collection-template><div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><input type="hidden" name="departure_rows[__INDEX__][id]"><div class="flex items-center justify-between gap-3"><p class="text-sm font-bold text-[#163a30]">Jadual berlepas</p><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang jadual"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3"><label><span class="admin-label">Tarikh pergi</span><input data-collection-field="departure_date" class="admin-field !py-2" type="date" name="departure_rows[__INDEX__][departure_date]"></label><label><span class="admin-label">Tarikh balik</span><input data-collection-field="return_date" class="admin-field !py-2" type="date" name="departure_rows[__INDEX__][return_date]"></label><label><span class="admin-label">Lapangan terbang (jika diketahui)</span><input data-collection-field="departure_airport" class="admin-field !py-2" name="departure_rows[__INDEX__][departure_airport]"></label><label><span class="admin-label">Harga seorang (MYR)</span><input data-collection-field="price" class="admin-field !py-2" type="number" min="0" step="0.01" name="departure_rows[__INDEX__][price]"></label><label><span class="admin-label">Kapasiti (wajib jika dibuka)</span><input data-collection-field="capacity" class="admin-field !py-2" type="number" min="1" name="departure_rows[__INDEX__][capacity]"></label><label><span class="admin-label">Status</span><select data-collection-field="status" class="admin-field !py-2" name="departure_rows[__INDEX__][status]"><option value="draft" selected>Draf — tidak dipaparkan</option><option value="open">Dibuka — tersedia</option><option value="closed">Ditutup — dipaparkan, tidak tersedia</option></select></label></div></div></template>
        </div>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="flights" data-package-collection="flight_options" data-collection-next-index="{{ count($flightOptions) }}">
        <input type="hidden" name="flight_options_present" value="1">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">flight_takeoff</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Maklumat Perjalanan</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Gambar penerbangan adalah pilihan dan boleh lebih daripada satu.</p></div></div><button type="button" data-collection-add class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#006b57] px-3.5 py-2.5 text-xs font-extrabold text-white shadow-[0_8px_18px_-10px_rgba(0,107,87,.9)] transition hover:-translate-y-0.5 hover:bg-[#005544] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]"><span class="material-symbols-outlined text-[18px]">add_circle</span>Tambah penerbangan</button></div>
        <div data-collection-items class="mt-6 space-y-3">@foreach ($flightOptions as $index => $flightOption)@php($flightImages = $flightOption['images'] ?? [])<div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang penerbangan"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2"><label><span class="admin-label">Syarikat penerbangan</span><input class="admin-field !py-2" name="flight_options[{{ $index }}][airline]" value="{{ $flightOption['airline'] ?? '' }}"></label><label><span class="admin-label">Tarikh cadangan</span><input class="admin-field !py-2" type="date" name="flight_options[{{ $index }}][proposed_departure_date]" value="{{ $flightOption['proposed_departure_date'] ?? '' }}"></label><label><span class="admin-label">Lapangan terbang pelepasan</span><input class="admin-field !py-2" name="flight_options[{{ $index }}][departure_airport]" value="{{ $flightOption['departure_airport'] ?? '' }}"></label><label><span class="admin-label">Lapangan terbang ketibaan</span><input class="admin-field !py-2" name="flight_options[{{ $index }}][arrival_airport]" value="{{ $flightOption['arrival_airport'] ?? '' }}"></label></div><label><span class="admin-label">Nota <span class="font-medium text-[#8a958f]">Pilihan</span></span><textarea class="admin-field min-h-20 !py-2" name="flight_options[{{ $index }}][notes]">{{ $flightOption['notes'] ?? '' }}</textarea></label><div><span class="admin-label">Gambar penerbangan <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="flight_options[{{ $index }}][images][]" multiple><div data-collection-existing-images class="mt-3 grid grid-cols-4 gap-2">@foreach ($flightImages as $imagePath)<div data-collection-existing-image class="group relative aspect-square overflow-hidden rounded-lg"><input type="hidden" name="flight_options[{{ $index }}][existing_images][]" value="{{ $imagePath }}"><img src="{{ $package->travelImageUrl($imagePath) }}" alt="" class="size-full object-cover"><button type="button" data-collection-existing-image-remove class="absolute right-1 top-1 grid size-7 place-items-center rounded-md bg-white/95 text-[#b33129] shadow" aria-label="Buang gambar"><span class="material-symbols-outlined text-[16px]">close</span></button></div>@endforeach</div><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div>@endforeach</div>
        <template data-collection-template><div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang penerbangan"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2"><label><span class="admin-label">Syarikat penerbangan</span><input data-collection-field="airline" class="admin-field !py-2" name="flight_options[__INDEX__][airline]"></label><label><span class="admin-label">Tarikh cadangan</span><input data-collection-field="proposed_departure_date" class="admin-field !py-2" type="date" name="flight_options[__INDEX__][proposed_departure_date]"></label><label><span class="admin-label">Lapangan terbang pelepasan</span><input data-collection-field="departure_airport" class="admin-field !py-2" name="flight_options[__INDEX__][departure_airport]"></label><label><span class="admin-label">Lapangan terbang ketibaan</span><input data-collection-field="arrival_airport" class="admin-field !py-2" name="flight_options[__INDEX__][arrival_airport]"></label></div><label><span class="admin-label">Nota</span><textarea data-collection-field="notes" class="admin-field min-h-20 !py-2" name="flight_options[__INDEX__][notes]"></textarea></label><div><span class="admin-label">Gambar penerbangan <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="flight_options[__INDEX__][images][]" multiple><div data-collection-existing-images class="mt-3 grid grid-cols-4 gap-2"></div><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div></template>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="hotels" data-package-collection="hotel_options" data-collection-next-index="{{ count($hotelOptions) }}">
        <input type="hidden" name="hotel_options_present" value="1">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">hotel</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Penginapan Hotel</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Muat naik gambar hotel di sini untuk carousel hotel pada halaman pakej.</p></div></div><button type="button" data-collection-add class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-[#006b57] px-3.5 py-2.5 text-xs font-extrabold text-white shadow-[0_8px_18px_-10px_rgba(0,107,87,.9)] transition hover:-translate-y-0.5 hover:bg-[#005544] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]"><span class="material-symbols-outlined text-[18px]">add_circle</span>Tambah hotel</button></div>
        <div data-collection-items class="mt-6 space-y-3">@foreach ($hotelOptions as $index => $hotelOption)@php($hotelImages = $hotelOption['images'] ?? [])<div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><input data-hotel-key type="hidden" name="hotel_options[{{ $index }}][key]" value="{{ $hotelOption['key'] }}"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang hotel"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2"><label><span class="admin-label">Lokasi</span><input class="admin-field !py-2" name="hotel_options[{{ $index }}][location]" value="{{ $hotelOption['location'] ?? '' }}" placeholder="Contoh: Shanghai, China"></label><label><span class="admin-label">Nama hotel</span><input class="admin-field !py-2" name="hotel_options[{{ $index }}][name]" value="{{ $hotelOption['name'] ?? '' }}"></label><label><span class="admin-label">Rating</span><select class="admin-field !py-2" name="hotel_options[{{ $index }}][rating]"><option value="">Belum ditetapkan</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}" @selected((int) ($hotelOption['rating'] ?? 0) === $rating)>{{ $rating }} Bintang</option>@endfor</select></label><label><span class="admin-label">Bilangan malam</span><input class="admin-field !py-2" type="number" min="0" name="hotel_options[{{ $index }}][nights]" value="{{ $hotelOption['nights'] ?? '' }}"></label><label><span class="admin-label">Jarak / akses</span><input class="admin-field !py-2" name="hotel_options[{{ $index }}][walking_distance]" value="{{ $hotelOption['walking_distance'] ?? '' }}"></label></div><label><span class="admin-label">Nota <span class="font-medium text-[#8a958f]">Pilihan</span></span><textarea class="admin-field min-h-20 !py-2" name="hotel_options[{{ $index }}][notes]">{{ $hotelOption['notes'] ?? '' }}</textarea></label><div><span class="admin-label">Gambar hotel <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="hotel_options[{{ $index }}][images][]" multiple><div data-collection-existing-images class="mt-3 grid grid-cols-4 gap-2">@foreach ($hotelImages as $imagePath)<div data-collection-existing-image class="group relative aspect-square overflow-hidden rounded-lg"><input type="hidden" name="hotel_options[{{ $index }}][existing_images][]" value="{{ $imagePath }}"><img src="{{ $package->travelImageUrl($imagePath) }}" alt="" class="size-full object-cover"><button type="button" data-collection-existing-image-remove class="absolute right-1 top-1 grid size-7 place-items-center rounded-md bg-white/95 text-[#b33129] shadow" aria-label="Buang gambar"><span class="material-symbols-outlined text-[16px]">close</span></button></div>@endforeach</div><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div>@endforeach</div>
        <template data-collection-template><div data-collection-row class="grid gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><input data-hotel-key data-collection-field="key" type="hidden" name="hotel_options[__INDEX__][key]"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang hotel"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3 md:grid-cols-2"><label><span class="admin-label">Lokasi</span><input data-collection-field="location" class="admin-field !py-2" name="hotel_options[__INDEX__][location]" placeholder="Contoh: Shanghai, China"></label><label><span class="admin-label">Nama hotel</span><input data-collection-field="name" class="admin-field !py-2" name="hotel_options[__INDEX__][name]"></label><label><span class="admin-label">Rating</span><select data-collection-field="rating" class="admin-field !py-2" name="hotel_options[__INDEX__][rating]"><option value="">Belum ditetapkan</option>@for ($rating = 1; $rating <= 5; $rating++)<option value="{{ $rating }}">{{ $rating }} Bintang</option>@endfor</select></label><label><span class="admin-label">Bilangan malam</span><input data-collection-field="nights" class="admin-field !py-2" type="number" min="0" name="hotel_options[__INDEX__][nights]"></label><label><span class="admin-label">Jarak / akses</span><input data-collection-field="walking_distance" class="admin-field !py-2" name="hotel_options[__INDEX__][walking_distance]"></label></div><label><span class="admin-label">Nota</span><textarea data-collection-field="notes" class="admin-field min-h-20 !py-2" name="hotel_options[__INDEX__][notes]"></textarea></label><div><span class="admin-label">Gambar hotel <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="hotel_options[__INDEX__][images][]" multiple><div data-collection-existing-images class="mt-3 grid grid-cols-4 gap-2"></div><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div></template>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="inclusions">
        <input type="hidden" name="service_options_present" value="1">
        <div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">fact_check</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Inklusi &amp; Syarat Pakej</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Tetapkan perkhidmatan untuk pakej ini, kemudian tandakan sama ada ia termasuk atau tidak termasuk.</p></div></div>
        <div class="mt-7" data-package-collection="package_services" data-collection-next-index="{{ count($packageServices) }}">
            <div class="grid gap-2 px-1 text-sm font-bold text-[#163a30] md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"><span>Perkhidmatan Termasuk</span><span>Perkhidmatan Tidak Termasuk</span><span class="sr-only">Tindakan</span></div>
            <div data-collection-items class="mt-4 space-y-2">@foreach ($packageServices as $index => $service)<article data-collection-row data-package-service-row class="grid gap-2 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"><label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] px-3 py-2.5 text-sm font-medium text-[#163a30]"><input type="hidden" data-package-service-name name="package_services[{{ $index }}][name]" value="{{ $service['name'] }}"><input data-package-service-status="included" class="size-4 accent-[#007c68]" type="checkbox" name="package_services[{{ $index }}][included]" value="1" @checked($service['included'])><span>{{ $service['name'] }}</span></label><label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-[#ead9cc] bg-[#fffaf7] px-3 py-2.5 text-sm font-medium text-[#163a30]"><input data-package-service-status="excluded" class="size-4 accent-[#b06b38]" type="checkbox" name="package_services[{{ $index }}][excluded]" value="1" @checked($service['excluded'])><span>{{ $service['name'] }}</span></label><button type="button" data-collection-remove class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-[#edc5be] px-3 text-xs font-bold text-[#b33129] transition hover:bg-[#fff0ef] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b33129]" aria-label="Buang {{ $service['name'] }}"><span class="material-symbols-outlined text-[18px]">delete</span><span>Buang</span></button></article>@endforeach</div>
            <template data-collection-template><article data-collection-row data-package-service-row class="grid gap-2 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto]"><div class="flex min-h-12 items-center gap-3 rounded-xl border border-[#dbe8df] bg-[#fbfefc] px-3 py-2.5"><label class="grid size-5 shrink-0 cursor-pointer place-items-center" title="Termasuk"><input data-package-service-status="included" class="size-4 accent-[#007c68]" type="checkbox" name="package_services[__INDEX__][included]" value="1"><span class="sr-only">Termasuk</span></label><input data-collection-field="name" data-package-service-name class="min-w-0 flex-1 bg-transparent text-sm font-medium text-[#163a30] outline-none placeholder:text-[#8a958f]" name="package_services[__INDEX__][name]" placeholder="Nama perkhidmatan"></div><label class="flex min-h-12 cursor-pointer items-center gap-3 rounded-xl border border-[#ead9cc] bg-[#fffaf7] px-3 py-2.5 text-sm font-medium text-[#163a30]"><input data-package-service-status="excluded" class="size-4 accent-[#b06b38]" type="checkbox" name="package_services[__INDEX__][excluded]" value="1"><span>Perkhidmatan ini tidak termasuk</span></label><button type="button" data-collection-remove class="inline-flex min-h-12 items-center justify-center gap-1.5 rounded-xl border border-[#edc5be] px-3 text-xs font-bold text-[#b33129] transition hover:bg-[#fff0ef] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b33129]" aria-label="Buang perkhidmatan"><span class="material-symbols-outlined text-[18px]">delete</span><span>Buang</span></button></article></template>
            <div class="mt-5 flex justify-end"><button type="button" data-collection-add class="inline-flex items-center gap-1.5 rounded-xl bg-[#006b57] px-4 py-2.5 text-xs font-extrabold text-white shadow-[0_8px_18px_-10px_rgba(0,107,87,.9)] transition hover:-translate-y-0.5 hover:bg-[#005544] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]"><span class="material-symbols-outlined text-[18px]">add</span>Tambah perkhidmatan</button></div>
        </div>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="itinerary" data-itinerary-builder>
        <div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#fff6d9] text-[#8b671c]"><span class="material-symbols-outlined">event_note</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Itinerari Perjalanan</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Bilangan hari mengikuti Tempoh Perjalanan agar sama dengan paparan pakej.</p></div></div>
        <div data-itinerary-days class="mt-7 space-y-3">@for ($dayNumber = 1; $dayNumber <= $durationDays; $dayNumber++)@php($itineraryDay = $itineraryDays[$dayNumber - 1] ?? $existingItineraries->get($dayNumber))<article class="grid gap-3 rounded-xl border border-[#e2eae4] bg-[#fbfefc] p-4 md:grid-cols-[5.5rem_minmax(0,1fr)]"><span class="grid h-10 place-items-center rounded-lg bg-[#fff4d7] text-xs font-extrabold text-[#65511b]">Hari {{ $dayNumber }}</span><div class="grid gap-3"><label><span class="admin-label">Tajuk</span><input class="admin-field !py-2" name="itinerary_days[{{ $dayNumber - 1 }}][title]" value="{{ is_array($itineraryDay) ? ($itineraryDay['title'] ?? '') : ($itineraryDay?->title ?? '') }}"></label><label><span class="admin-label">Butiran aktiviti hari ini</span><textarea class="admin-field min-h-20 !py-2" name="itinerary_days[{{ $dayNumber - 1 }}][description]" placeholder="Masukkan aktiviti dan maklumat penting hari ini.">{{ is_array($itineraryDay) ? ($itineraryDay['description'] ?? '') : ($itineraryDay?->description ?? '') }}</textarea></label></div></article>@endfor</div>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="rooms" data-package-collection="room_prices" data-collection-next-index="{{ count($roomPrices) }}">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">bedroom_parent</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Harga Bilik</h2><p class="mt-1 text-xs text-[#718078]">Pilih hotel penginapan bagi setiap harga bilik. Gambar bilik kekal bersama harga bilik yang dipilih.</p></div></div><button type="button" data-collection-add class="text-xs font-bold text-[#006b57]">+ Tambah harga bilik</button></div>
        <div data-collection-items class="mt-6 grid gap-4 lg:grid-cols-2">@foreach ($roomPrices as $index => $roomPrice)<div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Hotel penginapan</span><select data-room-hotel-select class="admin-field !py-2" name="room_prices[{{ $index }}][hotel_key]" required><option value="">Pilih hotel penginapan</option>@foreach ($hotelSelectOptions as $hotelOption)<option value="{{ $hotelOption['key'] }}" @selected(($roomPrice['hotel_key'] ?? '') === $hotelOption['key'])>{{ filled($hotelOption['location'] ?? null) ? $hotelOption['location'].' · ' : '' }}{{ $hotelOption['name'] }}</option>@endforeach</select></label><label><span class="admin-label">Jenis bilik</span><input class="admin-field !py-2" name="room_prices[{{ $index }}][room_type]" value="{{ $roomPrice['room_type'] ?? '' }}"></label><label><span class="admin-label">Harga seorang (MYR)</span><input class="admin-field !py-2" type="number" min="0" step="0.01" name="room_prices[{{ $index }}][price]" value="{{ $roomPrice['price'] ?? '' }}"></label><div><span class="admin-label">Gambar bilik <span class="font-medium text-[#8a958f]">Pilihan</span></span>@if (filled($roomPrice['existing_image'] ?? null))<div data-collection-existing-image class="group relative mt-2 aspect-[16/9] overflow-hidden rounded-lg"><input type="hidden" name="room_prices[{{ $index }}][existing_image]" value="{{ $roomPrice['existing_image'] }}"><img src="{{ $package->travelImageUrl($roomPrice['existing_image']) }}" alt="" class="size-full object-cover"><button type="button" data-collection-existing-image-remove class="absolute right-1 top-1 grid size-7 place-items-center rounded-md bg-white/95 text-[#b33129] shadow" aria-label="Buang gambar bilik"><span class="material-symbols-outlined text-[16px]">close</span></button></div>@endif<input data-collection-image-input data-room-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="room_prices[{{ $index }}][image]"><p class="mt-1 text-[11px] text-[#718078]">Gambar ini akan dipaparkan bersama harga bilik.</p><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div></div>@endforeach</div>
        <template data-collection-template><div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Hotel penginapan</span><select data-collection-field="hotel_key" data-room-hotel-select class="admin-field !py-2" name="room_prices[__INDEX__][hotel_key]" required><option value="">Pilih hotel penginapan</option></select></label><label><span class="admin-label">Jenis bilik</span><input data-collection-field="room_type" class="admin-field !py-2" name="room_prices[__INDEX__][room_type]"></label><label><span class="admin-label">Harga seorang (MYR)</span><input data-collection-field="price" class="admin-field !py-2" type="number" min="0" step="0.01" name="room_prices[__INDEX__][price]"></label><div><span class="admin-label">Gambar bilik <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input data-room-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="room_prices[__INDEX__][image]"><p class="mt-1 text-[11px] text-[#718078]">Gambar ini akan dipaparkan bersama harga bilik.</p><div data-collection-image-preview class="mt-2 grid grid-cols-4 gap-2"></div></div></div></div></template>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="ziarah" data-package-collection="ziarah_places" data-collection-next-index="{{ count($ziarahPlaces) }}">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">location_city</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Tempat Ziarah</h2><p class="mt-1 text-xs text-[#718078]">Setiap lokasi mempunyai satu gambar pilihan.</p></div></div><button type="button" data-collection-add class="text-xs font-bold text-[#006b57]">+ Tambah tempat</button></div>
        <div data-collection-items class="mt-6 grid gap-4 lg:grid-cols-2">@foreach ($ziarahPlaces as $index => $place)<div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Nama tempat</span><input class="admin-field !py-2" name="ziarah_places[{{ $index }}][name]" value="{{ $place['name'] ?? '' }}"></label><label><span class="admin-label">Lokasi</span><input class="admin-field !py-2" name="ziarah_places[{{ $index }}][location]" value="{{ $place['location'] ?? '' }}"></label><label><span class="admin-label">Penerangan</span><textarea class="admin-field min-h-20 !py-2" name="ziarah_places[{{ $index }}][description]">{{ $place['description'] ?? '' }}</textarea></label><div><span class="admin-label">Gambar lokasi <span class="font-medium text-[#8a958f]">Pilihan</span></span>@if (filled($place['image'] ?? null))<div data-collection-existing-image class="group relative mt-2 aspect-[16/9] overflow-hidden rounded-lg"><input type="hidden" name="ziarah_places[{{ $index }}][existing_image]" value="{{ $place['image'] }}"><img src="{{ $package->travelImageUrl($place['image']) }}" alt="" class="size-full object-cover"><button type="button" data-collection-existing-image-remove class="absolute right-1 top-1 grid size-7 place-items-center rounded-md bg-white/95 text-[#b33129] shadow"><span class="material-symbols-outlined text-[16px]">close</span></button></div>@endif<input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="ziarah_places[{{ $index }}][image]"><div data-collection-image-preview class="mt-2"></div></div></div></div>@endforeach</div>
        <template data-collection-template><div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Nama tempat</span><input data-collection-field="name" class="admin-field !py-2" name="ziarah_places[__INDEX__][name]"></label><label><span class="admin-label">Lokasi</span><input data-collection-field="location" class="admin-field !py-2" name="ziarah_places[__INDEX__][location]"></label><label><span class="admin-label">Penerangan</span><textarea data-collection-field="description" class="admin-field min-h-20 !py-2" name="ziarah_places[__INDEX__][description]"></textarea></label><div><span class="admin-label">Gambar lokasi <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="ziarah_places[__INDEX__][image]"><div data-collection-image-preview class="mt-2"></div></div></div></div></template>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="gifts" data-package-collection="gifts" data-collection-next-index="{{ count($gifts) }}">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">redeem</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Set Hadiah Jemaah</h2><p class="mt-1 text-xs text-[#718078]">Setiap hadiah mempunyai satu gambar pilihan.</p></div></div><button type="button" data-collection-add class="text-xs font-bold text-[#006b57]">+ Tambah hadiah</button></div>
        <div data-collection-items class="mt-6 grid gap-4 lg:grid-cols-2">@foreach ($gifts as $index => $gift)<div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Nama hadiah</span><input class="admin-field !py-2" name="gifts[{{ $index }}][title]" value="{{ $gift['title'] ?? '' }}"></label><label><span class="admin-label">Penerangan</span><textarea class="admin-field min-h-20 !py-2" name="gifts[{{ $index }}][description]">{{ $gift['description'] ?? '' }}</textarea></label><div><span class="admin-label">Gambar hadiah <span class="font-medium text-[#8a958f]">Pilihan</span></span>@if (filled($gift['image'] ?? null))<div data-collection-existing-image class="group relative mt-2 aspect-[16/9] overflow-hidden rounded-lg"><input type="hidden" name="gifts[{{ $index }}][existing_image]" value="{{ $gift['image'] }}"><img src="{{ $package->travelImageUrl($gift['image']) }}" alt="" class="size-full object-cover"><button type="button" data-collection-existing-image-remove class="absolute right-1 top-1 grid size-7 place-items-center rounded-md bg-white/95 text-[#b33129] shadow"><span class="material-symbols-outlined text-[16px]">close</span></button></div>@endif<input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="gifts[{{ $index }}][image]"><div data-collection-image-preview class="mt-2"></div></div></div></div>@endforeach</div>
        <template data-collection-template><div data-collection-row class="rounded-xl border border-[#dbe8df] bg-[#fbfefc] p-4"><div class="flex justify-end"><button type="button" data-collection-remove class="grid size-9 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]"><span class="material-symbols-outlined">close</span></button></div><div class="grid gap-3"><label><span class="admin-label">Nama hadiah</span><input data-collection-field="title" class="admin-field !py-2" name="gifts[__INDEX__][title]"></label><label><span class="admin-label">Penerangan</span><textarea data-collection-field="description" class="admin-field min-h-20 !py-2" name="gifts[__INDEX__][description]"></textarea></label><div><span class="admin-label">Gambar hadiah <span class="font-medium text-[#8a958f]">Pilihan</span></span><input data-collection-image-input class="admin-field mt-2 !py-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="gifts[__INDEX__][image]"><div data-collection-image-preview class="mt-2"></div></div></div></div></template>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="display">
        <div class="flex items-center justify-between"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">tune</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Tetapan Paparan Web</h2></div></div><span class="size-2 rounded-full bg-[#007c68]"></span></div>
        <div class="mt-7 space-y-5">
            <div class="rounded-xl bg-[#f4faf5] px-4 py-3 text-xs leading-5 text-[#506058]"><span class="font-bold text-[#006b57]">Paparan automatik.</span> Pakej yang diterbitkan akan dipaparkan pada halaman awam.</div>
            <label class="flex items-center justify-between gap-4">
                <span><span class="block text-sm font-bold text-[#163a30]">Pakej Pilihan</span><span class="mt-1 block text-[11px] leading-4 text-[#718078]">Diletakkan mendahului pakej biasa dalam hasil carian dan pada homepage.</span></span>
                <span class="admin-toggle"><input data-featured-badge-toggle class="sr-only" type="checkbox" name="is_featured" value="1" @checked($isFeatured)><span></span></span>
            </label>
            <fieldset data-promotion-badge-options @class(['border-t border-[#e8eee9] pt-5', 'hidden' => ! $isFeatured])>
                <legend class="text-sm font-bold text-[#163a30]">Badge promosi <span class="font-medium text-[#8a958f]">Pilih satu sahaja · wajib untuk pakej pilihan</span></legend>
                <p class="mt-1 text-[11px] leading-4 text-[#718078]">Badge besar akan melekat seperti sticker pada sudut atas kad pakej.</p>
                <div class="mt-3 grid gap-3 sm:grid-cols-2">
                    @foreach (\App\Models\Package::promotionBadges() as $badgeType => $badge)
                        <label @class(['flex cursor-pointer items-center gap-2 rounded-xl border p-3 text-xs font-bold', 'border-[#d2ad52] bg-[#fff8e6] text-[#725418]' => $badgeType === \App\Models\Package::RecommendationBadge, 'border-[#f5b6c4] bg-[#fff0f3] text-[#aa1530]' => $badgeType === \App\Models\Package::SuperDealBadge])>
                            <input data-promotion-badge-input type="radio" name="promotion_badge" value="{{ $badgeType }}" @checked($selectedPromotionBadge === $badgeType) @disabled(! $isFeatured)>
                            <span class="material-symbols-outlined text-[18px]">{{ $badge['icon'] }}</span>{{ $badge['label'] }}
                        </label>
                    @endforeach
                </div>
            </fieldset>
        </div>
    </section>

    <section class="hidden admin-card mt-6 p-6 sm:p-7" role="tabpanel" data-admin-tab-panel="media">
        <div class="flex items-start justify-between gap-4"><div class="flex items-start gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">photo_library</span></span><div><h2 class="text-xl font-bold text-[#00251e]">Gambar &amp; Media Pakej</h2><p class="mt-1 text-xs leading-5 text-[#718078]">Maksimum 3 gambar. Pilih satu sebagai gambar utama untuk homepage, halaman pakej dan pautan yang dikongsi.</p></div></div><span class="rounded-full bg-[#e9f7ed] px-3 py-1 text-[10px] font-bold text-[#006b57]">{{ $galleryImages->count() }}/3 gambar</span></div>
        <input type="hidden" name="gallery_primary_index" data-gallery-primary-index>
        <section class="mt-6 rounded-2xl border border-[#dbe8df] bg-[#fbfefc] p-5">
            <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="text-sm font-bold text-[#163a30]">Poster pakej <span class="text-[#b33129]">*</span></h3><p class="mt-1 text-[11px] leading-5 text-[#718078]">Satu gambar potret sahaja. Digunakan untuk kad homepage, carian pakej dan pratonton WhatsApp.</p></div><span class="rounded-full bg-[#fff4d7] px-3 py-1 text-[10px] font-bold text-[#8a671d]">Potret wajib untuk terbit</span></div>
            <div class="mt-4 grid gap-4 md:grid-cols-[10rem_minmax(0,1fr)]"><div class="relative aspect-[3/4] overflow-hidden rounded-xl bg-[#e9f2eb]">@if ($package->posterImageUrl())<img data-image-focus-path="{{ $package->poster_image ?: $package->cover_image }}" src="{{ $package->posterImageUrl() }}" alt="Poster pakej semasa" class="size-full object-cover" style="object-position: {{ $imageFocusPositions[$package->poster_image ?: $package->cover_image] ?? '50% 50%' }}">@else<span class="grid size-full place-items-center text-[#006b57]"><span class="material-symbols-outlined text-4xl">image</span></span>@endif</div><div><label class="block"><span class="admin-label">Tambah poster</span><input data-package-media-upload-input class="admin-field mt-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="poster_image"></label>@if ($package->posterImageUrl())<div class="mt-3"><x-admin.image-focus-button :path="$package->poster_image ?: $package->cover_image" :url="$package->posterImageUrl()" /></div>@endif @if ($package->poster_image)<label class="mt-3 inline-flex items-center gap-2 text-xs font-semibold text-[#b33129]"><input type="checkbox" name="remove_poster_image" value="1">Buang poster semasa</label>@endif</div></div>
        </section>
        <div class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">@foreach ($galleryImages as $imagePath)@php($image = $package->images->first(fn (\App\Models\PackageImage $packageImage): bool => $packageImage->path === $imagePath))<article class="relative overflow-hidden rounded-xl border {{ $package->cover_image === $imagePath ? 'border-[#007c68] ring-2 ring-[#bfe8cf]' : 'border-[#dbe8df]' }} bg-white"><img data-image-focus-path="{{ $imagePath }}" src="{{ $package->travelImageUrl($imagePath) }}" alt="" class="aspect-[4/3] w-full object-cover" style="object-position: {{ $imageFocusPositions[$imagePath] ?? '50% 50%' }}"><div class="flex items-center justify-between gap-2 px-3 py-2"><label class="flex cursor-pointer items-center gap-2 text-xs font-bold text-[#163a30]"><input type="radio" name="primary_image_path" value="{{ $imagePath }}" @checked($package->cover_image === $imagePath)><span>Gambar utama</span></label><x-admin.image-focus-button :path="$imagePath" :url="$package->travelImageUrl($imagePath)" label="Fokus" /></div>@if ($image)<button form="delete-image-{{ $image->id }}" class="absolute right-2 top-2 grid size-8 place-items-center rounded-lg bg-white/95 text-[#b33129] shadow" aria-label="Buang gambar"><span class="material-symbols-outlined text-[18px]">close</span></button>@elseif ($package->cover_image === $imagePath)<button form="delete-cover-image" class="absolute right-2 top-2 grid size-8 place-items-center rounded-lg bg-white/95 text-[#b33129] shadow" aria-label="Buang gambar utama"><span class="material-symbols-outlined text-[18px]">close</span></button>@endif</article>@endforeach</div>
        <div class="mt-5 rounded-xl border border-dashed border-[#bddcca] bg-[#fbfefc] p-4"><label><span class="admin-label">Tambah gambar landscape baharu</span><input data-gallery-preview data-package-media-upload-input class="admin-field mt-2 text-xs" type="file" accept="image/jpeg,image/png,image/webp" name="gallery[]" multiple></label><p class="mt-2 text-[10px] leading-4 text-[#718078]">Hanya gambar landscape. JPEG, PNG atau WebP; maksimum 2MB setiap gambar. Tandakan Gambar utama pada imej baharu, kemudian simpan untuk terus menggunakannya sebagai gambar utama.</p><div data-gallery-preview-list class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"></div></div>
        <section class="mt-8 rounded-2xl border border-[#dbe8df] bg-[#fbfefc] p-5"><div class="flex items-start gap-3"><span class="grid size-9 shrink-0 place-items-center rounded-lg bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">picture_as_pdf</span></span><div><h3 class="text-sm font-bold text-[#163a30]">PDF Itinerari</h3><p class="mt-1 text-[11px] leading-5 text-[#718078]">Pilihan. Fail ini dipautkan kepada jemaah untuk dimuat turun.</p></div></div>@if ($package->itineraryPdfUrl())<div data-current-pdf class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-white px-3 py-2"><a href="{{ $package->itineraryPdfUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-bold text-[#006b57]"><span class="material-symbols-outlined text-[17px]">open_in_new</span>Lihat PDF itinerari semasa</a><button type="button" data-remove-pdf class="grid size-8 place-items-center rounded-lg text-[#b33129] hover:bg-[#fff0ef]" aria-label="Buang PDF itinerari"><span class="material-symbols-outlined text-[18px]">close</span></button></div><input type="hidden" name="remove_itinerary_pdf" value="0" data-remove-pdf-input>@endif<label class="mt-4 block"><span class="sr-only">Muat naik PDF itinerari</span><input class="admin-field text-xs" type="file" accept="application/pdf,.pdf" name="itinerary_pdf"></label><p class="mt-2 text-[10px] leading-4 text-[#718078]">PDF sahaja, maksimum 10MB.</p></section>
    </section>

    <dialog data-package-media-picker class="admin-package-media-dialog w-[min(68rem,96vw)] max-w-none rounded-2xl border border-[#dbe8df] bg-white p-0 text-[#163a30] shadow-2xl"><div class="flex items-center justify-between border-b border-[#e8eee9] px-5 py-4"><div><p class="text-lg font-bold text-[#00251e]">Pustaka Media Pakej</p><p class="mt-1 text-xs text-[#718078]">Tekan imej untuk pratonton, kemudian sahkan pilihan.</p></div><button type="button" data-package-media-picker-close class="grid size-9 place-items-center rounded-lg hover:bg-[#f2f5f2]" aria-label="Tutup pustaka"><span class="material-symbols-outlined">close</span></button></div><div class="border-b border-[#edf1ee] px-5 py-3 text-xs"><button data-package-media-folder-all type="button" class="font-bold text-[#006b57]">Semua folder</button><span class="mx-2 text-[#a4aea8]">/</span><span data-package-media-breadcrumb>Poster</span></div><div class="flex gap-2 overflow-x-auto border-b border-[#edf1ee] px-5 py-3">@foreach (\App\Models\PackageMediaAsset::folders() as $folder => $folderDetails)<button type="button" data-package-media-folder-button="{{ $folder }}" class="shrink-0 rounded-full border border-[#d6e5da] px-3 py-1.5 text-xs font-bold text-[#506058] hover:bg-[#e9f7ed]">{{ $folderDetails['label'] }}</button>@endforeach</div><div class="min-h-0 flex-1 overflow-y-auto p-5"><div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">@foreach ($packageMediaAssets as $asset)<button type="button" data-package-media-asset data-asset-id="{{ $asset->id }}" data-asset-folder="{{ $asset->folder }}" data-asset-url="{{ $asset->url() }}" data-asset-name="{{ $asset->original_name }}" class="overflow-hidden rounded-xl bg-[#f5f8f5] text-left transition hover:ring-2 hover:ring-[#8ccdab]"><img src="{{ $asset->url() }}" alt="" class="aspect-square w-full object-cover"><span class="block truncate px-2 py-2 text-[10px] font-semibold">{{ $asset->original_name }}</span></button>@endforeach</div>@if ($packageMediaAssets->isEmpty())<p class="py-12 text-center text-sm text-[#718078]">Belum ada gambar dalam pustaka. Muat naik gambar pada medan di bawah tab ini, kemudian simpan pakej untuk menambahkannya.</p>@endif</div><div class="flex justify-end border-t border-[#e8eee9] px-5 py-4"><button type="button" data-package-media-picker-done class="admin-button-primary">Selesai</button></div></dialog>
    <dialog data-package-media-preview class="admin-package-media-preview-dialog w-[min(42rem,94vw)] max-w-none rounded-2xl border-0 bg-transparent p-0 text-white"><div class="relative rounded-2xl bg-[#002f28] p-5 shadow-2xl"><button type="button" data-package-media-preview-close class="absolute right-3 top-3 grid size-9 place-items-center rounded-full bg-white/15" aria-label="Tutup pratonton"><span class="material-symbols-outlined">close</span></button><img data-package-media-preview-image src="" alt="" class="mx-auto max-h-[65vh] max-w-full rounded-xl object-contain"><div class="mt-4 flex items-center justify-between gap-3"><p data-package-media-preview-name class="truncate text-sm"></p><button data-package-media-preview-select type="button" class="rounded-lg bg-[#e7c875] px-4 py-2 text-xs font-extrabold text-[#002f28]">Pilih gambar ini</button></div></div></dialog>

    <div class="sticky bottom-0 z-20 mt-6 flex flex-col justify-between gap-3 rounded-2xl border border-[#dceae1] bg-white/95 p-4 shadow-[0_-10px_30px_-24px_rgba(0,37,30,.4)] backdrop-blur sm:flex-row sm:items-center"><p class="text-xs text-[#718078]" data-autosave-status>{{ $isEditing ? 'Perubahan disimpan secara automatik.' : 'Simpan sebagai draf untuk mula menggunakan autosimpan.' }}</p><div class="flex flex-wrap gap-2"><button class="admin-button-secondary" type="submit" name="intent" value="draft"><span class="material-symbols-outlined text-[18px]">bookmark</span>Simpan Draf</button><button class="admin-button-primary" type="submit" name="intent" value="publish"><span class="material-symbols-outlined text-[18px]">check_circle</span>Simpan &amp; Terbitkan</button>@if ($isEditing && $package->status !== 'archived')<button form="archive-package" class="admin-button-secondary !border-[#edc5be] !text-[#b33129]" type="submit"><span class="material-symbols-outlined text-[18px]">inventory_2</span>Arkibkan</button>@endif</div></div>
</form>

@if ($isEditing)
    <form id="archive-package" method="POST" action="{{ route('admin.packages.destroy', $package) }}" data-confirm-archive>@csrf @method('DELETE')</form>
    @foreach ($package->images as $image)<form id="delete-image-{{ $image->id }}" method="POST" action="{{ route('admin.packages.images.destroy', [$package, $image]) }}" data-confirm-image-delete>@csrf @method('DELETE')</form>@endforeach
    <form id="delete-cover-image" method="POST" action="{{ route('admin.packages.cover-image.destroy', $package) }}" data-confirm-image-delete>@csrf @method('DELETE')</form>
@endif

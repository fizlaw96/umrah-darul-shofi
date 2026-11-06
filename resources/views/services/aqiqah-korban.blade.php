<x-layouts.app title="Servis Aqiqah & Korban" meta-description="Lihat pilihan perkhidmatan Aqiqah dan Korban SalamHaji serta mulakan pertanyaan melalui WhatsApp." :show-floating-whats-app="false">
    @php($aqiqahKorbanHeroUrl = asset('darul-shofi/images/hero/aqiqah-korban-hero-v1.png'))
    @php($hasServices = $aqiqahServices->isNotEmpty() || $korbanServices->isNotEmpty())
    @php($wizardServices = $aqiqahServices->concat($korbanServices))

    <div class="aqiqah-korban-page pb-14 sm:pb-20">
        <section class="service-hero-fluid aqiqah-korban-hero-fluid">
            <div class="aqiqah-korban-hero">
                <img src="{{ $aqiqahKorbanHeroUrl }}" alt="Kaabah di Masjidil Haram" class="aqiqah-korban-hero__image">
                <div class="aqiqah-korban-hero__shade"></div>

                <div class="relative flex min-h-[calc(100svh-4.25rem)] flex-col px-5 py-6 sm:min-h-[calc(100svh-5rem)] sm:px-10 sm:py-8 lg:px-14 lg:py-10">
                    <nav class="flex items-center gap-1 text-[10px] font-semibold text-white/70" aria-label="Breadcrumb">
                        <a href="{{ route('home') }}" class="transition hover:text-[#ffdf9a]">Utama</a>
                        <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        <span class="text-[#ffdf9a]">Aqiqah &amp; Korban</span>
                    </nav>

                    <div class="mt-7 max-w-2xl sm:mt-8">
                        <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#ffdf9a]">Aqiqah &amp; Korban</p>
                        <h1 class="mt-3 font-display text-[2.35rem] leading-[.97] text-white sm:text-5xl lg:text-6xl">Urusan Aqiqah &amp; Korban<br class="hidden sm:block"> Dipermudahkan</h1>
                        <p class="mt-4 max-w-xl text-sm leading-6 text-white/85 sm:text-base sm:leading-7">Lihat semua pilihan yang telah disahkan oleh SalamHaji, kemudian mulakan servis apabila anda sudah bersedia.</p>
                        @if ($hasServices)
                            <a class="button-primary mt-6 !border-[#f3cf77] !bg-[#f5d27e] !text-[#173d32] hover:!bg-[#ffe4a1]" href="#mulakan-servis-aqiqah-korban"><span class="material-symbols-outlined text-[19px]">arrow_forward</span>Mulakan Servis</a>
                        @endif
                    </div>

                    <div class="mt-auto grid max-w-3xl grid-cols-2 gap-x-4 gap-y-3 pt-8 sm:grid-cols-4 sm:gap-3">
                        <div class="aqiqah-korban-hero__benefit"><span class="material-symbols-outlined">verified</span><span><strong>Dipercayai</strong><small>&amp; berpengalaman</small></span></div>
                        <div class="aqiqah-korban-hero__benefit"><span class="material-symbols-outlined">task_alt</span><span><strong>Proses</strong><small>mudah</small></span></div>
                        <div class="aqiqah-korban-hero__benefit"><span class="material-symbols-outlined">volunteer_activism</span><span><strong>Bimbingan</strong><small>penuh</small></span></div>
                        <div class="aqiqah-korban-hero__benefit"><span class="material-symbols-outlined">my_location</span><span><strong>Ikut lokasi anda</strong><small>GPS atau manual</small></span></div>
                    </div>
                </div>
            </div>
        </section>

        <section class="container-page pt-6 sm:pt-8">
            <div class="grid gap-3 sm:grid-cols-2 sm:gap-5">
                <a class="aqiqah-korban-category-link" href="#pilihan-aqiqah">
                    <span class="aqiqah-korban-category-link__icon"><span class="material-symbols-outlined">pets</span></span>
                    <span><strong>Pilihan Aqiqah</strong><small>Aqiqah cahaya mata tercinta dengan pelaksanaan yang amanah dan teratur.</small></span>
                    <span class="material-symbols-outlined aqiqah-korban-category-link__arrow">arrow_forward</span>
                </a>
                <a class="aqiqah-korban-category-link" href="#pilihan-korban">
                    <span class="aqiqah-korban-category-link__icon"><span class="material-symbols-outlined">agriculture</span></span>
                    <span><strong>Pilihan Korban</strong><small>Korban mengikut musim haji dengan proses yang dipercayai.</small></span>
                    <span class="material-symbols-outlined aqiqah-korban-category-link__arrow">arrow_forward</span>
                </a>
            </div>
        </section>

        <section class="container-page pt-9 sm:pt-12" data-aqiqah-korban-catalogue>
            <div id="pilihan-aqiqah" class="scroll-mt-6" data-aqiqah-korban-service-group="Aqiqah">
                <div class="aqiqah-korban-section-heading">
                    <div><p class="section-eyebrow">Perkhidmatan</p><h2>Pilihan Aqiqah</h2></div>
                    @if ($aqiqahServices->isNotEmpty())
                        <a href="#mulakan-servis-aqiqah-korban">Mulakan servis <span class="material-symbols-outlined">arrow_forward</span></a>
                    @endif
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($aqiqahServices as $service)
                        @include('services.partials.aqiqah-korban-service-card', ['service' => $service])
                    @empty
                        <div class="aqiqah-korban-empty-card md:col-span-2 xl:col-span-3"><span class="material-symbols-outlined">inventory_2</span><p>Pilihan Aqiqah akan dipaparkan apabila perkhidmatan telah disahkan dan diterbitkan oleh SalamHaji.</p></div>
                    @endforelse
                </div>
            </div>

            <div id="pilihan-korban" class="mt-10 scroll-mt-6 sm:mt-14" data-aqiqah-korban-service-group="Korban">
                <div class="aqiqah-korban-section-heading">
                    <div><p class="section-eyebrow">Perkhidmatan</p><h2>Pilihan Korban</h2></div>
                    @if ($korbanServices->isNotEmpty())
                        <a href="#mulakan-servis-aqiqah-korban">Mulakan servis <span class="material-symbols-outlined">arrow_forward</span></a>
                    @endif
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($korbanServices as $service)
                        @include('services.partials.aqiqah-korban-service-card', ['service' => $service])
                    @empty
                        <div class="aqiqah-korban-empty-card md:col-span-2 xl:col-span-3"><span class="material-symbols-outlined">inventory_2</span><p>Pilihan Korban hanya dipaparkan mengikut musim dan tempoh pendaftaran yang telah disahkan.</p></div>
                    @endforelse
                </div>
            </div>
        </section>

        @if ($hasServices)
            <section id="mulakan-servis-aqiqah-korban" class="container-page scroll-mt-6 pt-12 sm:pt-16" data-aqiqah-korban-enquiry>
                <form class="aqiqah-korban-wizard" method="POST" action="{{ route('aqiqah-korban.prepare') }}" novalidate data-aqiqah-korban-enquiry>
                    @csrf
                    <x-errors />

                    <div class="aqiqah-korban-progress" aria-label="Kemajuan permintaan Aqiqah dan Korban">
                        <button type="button" data-aqiqah-korban-progress-step="1"><span>1</span><b>Pilih servis</b></button>
                        <button type="button" data-aqiqah-korban-progress-step="2" disabled><span>2</span><b>Pilih tawaran</b></button>
                        <button type="button" data-aqiqah-korban-progress-step="3" disabled><span>3</span><b>Semak</b></button>
                    </div>

                    <section class="aqiqah-korban-wizard-card" data-aqiqah-korban-step="1">
                        <div class="aqiqah-korban-wizard-heading"><span class="aqiqah-korban-step-number">1</span><span><p class="section-eyebrow">Langkah pertama</p><h2>Pilih Aqiqah atau Korban</h2><p>Pilih jenis perkhidmatan sebelum melihat tawaran yang sesuai.</p></span></div>
                        <div class="mt-6 grid gap-4 sm:grid-cols-2">
                            @foreach (\App\Models\AqiqahKorbanService::categories() as $category)
                                @php($available = $category === \App\Models\AqiqahKorbanService::Aqiqah ? $aqiqahServices->isNotEmpty() : $korbanServices->isNotEmpty())
                                <label @class(['aqiqah-korban-choice-card', 'is-unavailable' => ! $available])>
                                    <input type="radio" name="category" value="{{ $category }}" data-aqiqah-korban-category @checked(old('category') === $category) @disabled(! $available) required>
                                    <span class="aqiqah-korban-choice-card__icon material-symbols-outlined">{{ $category === \App\Models\AqiqahKorbanService::Aqiqah ? 'pets' : 'agriculture' }}</span>
                                    <span><strong>{{ $category }}</strong><small>{{ $category === \App\Models\AqiqahKorbanService::Aqiqah ? 'Perkhidmatan aqiqah yang tersedia.' : 'Perkhidmatan korban yang tersedia.' }}</small></span>
                                </label>
                            @endforeach
                        </div>
                        <p class="aqiqah-korban-wizard-feedback" data-aqiqah-korban-feedback="1" aria-live="polite"></p>
                        <div class="aqiqah-korban-wizard-actions"><span>Pilih satu jenis perkhidmatan untuk meneruskan.</span><button class="button-primary" type="button" data-aqiqah-korban-next-step="2">Seterusnya<span class="material-symbols-outlined text-[18px]">arrow_forward</span></button></div>
                    </section>

                    <section class="aqiqah-korban-wizard-card" data-aqiqah-korban-step="2" hidden>
                        <div class="aqiqah-korban-wizard-heading"><span class="aqiqah-korban-step-number">2</span><span><p class="section-eyebrow">Langkah kedua</p><h2>Pilih tawaran yang tersedia</h2><p>Pilihan ini diurus dan diterbitkan sendiri oleh pihak admin SalamHaji.</p></span></div>
                        <input type="hidden" name="service_id" value="{{ old('service_id') }}" data-aqiqah-korban-service-input>

                        <div class="mt-6 grid gap-3 md:grid-cols-2 xl:grid-cols-3" data-aqiqah-korban-service-options>
                            @foreach ($wizardServices as $service)
                                <button class="aqiqah-korban-service-option" type="button" data-aqiqah-korban-service-option data-category="{{ $service->category }}" data-service-id="{{ $service->id }}" data-service-name="{{ $service->name }}" data-service-animal="{{ $service->animal_type }}" data-service-quantity="{{ $service->quantity_description }}" data-service-location="{{ $service->service_location }}" data-service-price="{{ $service->priceLabel() }}" data-service-price-value="{{ $service->price }}" data-service-slots="{{ $service->participantSlotsPerUnit() }}">
                                    @if ($service->imageUrl())<img src="{{ $service->imageUrl() }}" alt="">@else<span class="material-symbols-outlined">{{ $service->category === \App\Models\AqiqahKorbanService::Aqiqah ? 'pets' : 'agriculture' }}</span>@endif
                                    <span><strong>{{ $service->name }}</strong><small>{{ $service->animal_type }} · {{ $service->quantity_description }}</small><em>{{ $service->price === null ? 'Harga atas pengesahan' : $service->priceLabel() }}</em></span>
                                </button>
                            @endforeach
                        </div>

                        <div class="mt-6" data-aqiqah-korban-details hidden>
                            <div class="rounded-xl border border-[#d7e8dd] bg-[#f8fcf9] p-4 text-sm leading-6 text-[#31594c]">Lengkapkan butiran di bawah supaya operator boleh menyemak pelaksanaan mengikut lokasi dan zon waktu anda.</div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <label><span class="field-label">Kuantiti diperlukan</span><input class="field-control" type="number" name="quantity" min="1" max="99" value="{{ old('quantity', 1) }}" required></label>
                                <label><span class="field-label">Nama anda</span><input class="field-control" name="full_name" value="{{ old('full_name') }}" maxlength="255" autocomplete="name" required></label>
                                <label><span class="field-label">Nombor WhatsApp</span><input class="field-control" name="contact_number" value="{{ old('contact_number') }}" maxlength="40" inputmode="tel" autocomplete="tel" required></label>
                            </div>
                            <fieldset class="mt-4 grid gap-4 sm:grid-cols-2" data-aqiqah-korban-only="Aqiqah">
                                <legend class="sr-only">Butiran anak</legend>
                                <label><span class="field-label">Nama anak</span><input class="field-control" name="child_name" value="{{ old('child_name') }}" maxlength="255" required></label>
                                <label><span class="field-label">Jantina anak</span><select class="field-control" name="child_gender" required data-aqiqah-korban-child-gender><option value="">Pilih jantina</option>@foreach ([\App\Http\Requests\AqiqahKorbanEnquiryRequest::BoyGender, \App\Http\Requests\AqiqahKorbanEnquiryRequest::GirlGender] as $gender)<option value="{{ $gender }}" @selected(old('child_gender') === $gender)>{{ $gender }}</option>@endforeach</select></label>
                                <label class="sm:col-span-2"><span class="field-label">Nama bapa <em class="font-medium normal-case text-[#87928d]">(pilihan, untuk bin/binti)</em></span><input class="field-control" name="father_name" value="{{ old('father_name') }}" maxlength="255"></label>
                                <p class="text-xs leading-5 text-[#718078] sm:col-span-2" aria-live="polite" data-aqiqah-korban-gender-hint>Lazimnya 2 ekor kambing untuk anak lelaki dan 1 ekor untuk anak perempuan.</p>
                            </fieldset>
                            <label class="mt-4 block" data-aqiqah-korban-only="Korban">
                                <span class="field-label">Nama peserta korban</span>
                                <textarea class="field-control min-h-32" name="participant_names" required placeholder="Satu nama bagi setiap baris" data-aqiqah-korban-participant-names>{{ is_array(old('participant_names')) ? implode("\n", old('participant_names')) : old('participant_names') }}</textarea>
                                <span class="mt-1 block text-xs text-[#718078]" data-aqiqah-korban-participant-hint>Satu nama bagi setiap baris (satu nama untuk setiap bahagian).</span>
                            </label>
                            <section class="aqiqah-korban-location-card mt-4">
                                <div class="flex flex-wrap items-start justify-between gap-3"><div><h3>Lokasi anda</h3><p>GPS membantu operator memahami negara dan masa anda. Jika tidak dapat digunakan, isi bandar atau negara secara manual.</p></div><button class="aqiqah-korban-location-button" type="button" data-aqiqah-korban-use-gps><span class="material-symbols-outlined">my_location</span>Guna Lokasi Saya</button></div>
                                <p class="mt-3 min-h-5 text-xs font-semibold text-[#006b57]" aria-live="polite" data-aqiqah-korban-location-status></p>
                                <label class="mt-3 block"><span class="field-label">Bandar / negara anda</span><input class="field-control" name="user_location" value="{{ old('user_location') }}" maxlength="255" placeholder="Contoh: Kuala Lumpur, Malaysia" data-aqiqah-korban-user-location required></label>
                                <input type="hidden" name="user_latitude" value="{{ old('user_latitude') }}" data-aqiqah-korban-user-latitude>
                                <input type="hidden" name="user_longitude" value="{{ old('user_longitude') }}" data-aqiqah-korban-user-longitude>
                                <input type="hidden" name="user_timezone" value="{{ old('user_timezone') }}" data-aqiqah-korban-user-timezone>
                            </section>
                            <label class="mt-4 block" data-aqiqah-korban-only="Aqiqah"><span class="field-label">Tarikh pelaksanaan diminta <em class="font-medium normal-case text-[#87928d]">(pilihan)</em></span><input class="field-control" type="date" name="requested_execution_date" value="{{ old('requested_execution_date') }}"><span class="mt-1 block text-xs text-[#718078]">Hanya untuk Aqiqah dan tertakluk kepada pengesahan operator.</span></label>
                        </div>
                        <p class="aqiqah-korban-wizard-feedback" data-aqiqah-korban-feedback="2" aria-live="polite"></p>
                        <div class="aqiqah-korban-wizard-actions"><span>Pilih tawaran dan lengkapkan butiran sebelum meneruskan.</span><button class="button-primary" type="button" data-aqiqah-korban-next-step="3">Semak maklumat<span class="material-symbols-outlined text-[18px]">arrow_forward</span></button></div>
                    </section>

                    <section class="aqiqah-korban-wizard-card" data-aqiqah-korban-step="3" hidden>
                        <div class="aqiqah-korban-wizard-heading"><span class="aqiqah-korban-step-number">3</span><span><p class="section-eyebrow">Langkah terakhir</p><h2>Semak sebelum ke WhatsApp</h2><p>Pastikan semua maklumat tepat. Harga dan ketersediaan kekal tertakluk kepada pengesahan operator.</p></span></div>
                        <div class="aqiqah-korban-review-grid mt-6">
                            <article><h3>Perkhidmatan</h3><dl><div><dt>Jenis</dt><dd data-aqiqah-korban-review-category>—</dd></div><div><dt>Tawaran</dt><dd data-aqiqah-korban-review-service>—</dd></div><div><dt>Haiwan / kuantiti</dt><dd data-aqiqah-korban-review-service-details>—</dd></div><div><dt>Harga seunit</dt><dd data-aqiqah-korban-review-price>—</dd></div><div><dt>Anggaran jumlah</dt><dd data-aqiqah-korban-review-total>—</dd></div></dl></article>
                            <article><h3>Butiran anda</h3><dl><div><dt>Nama</dt><dd data-aqiqah-korban-review-name>—</dd></div><div><dt>WhatsApp</dt><dd data-aqiqah-korban-review-contact>—</dd></div><div><dt>Anak / peserta</dt><dd class="whitespace-pre-line" data-aqiqah-korban-review-beneficiary>—</dd></div><div><dt>Jumlah diminta</dt><dd data-aqiqah-korban-review-quantity>—</dd></div></dl></article>
                            <article><h3>Lokasi &amp; pelaksanaan</h3><dl><div><dt>Lokasi anda</dt><dd data-aqiqah-korban-review-location>—</dd></div><div><dt>Zon waktu</dt><dd data-aqiqah-korban-review-timezone>—</dd></div><div><dt>Tarikh diminta</dt><dd data-aqiqah-korban-review-date>—</dd></div></dl></article>
                        </div>
                        <aside class="aqiqah-korban-important-card mt-5">
                            <span class="grid size-11 place-items-center rounded-full bg-[#f4e8bd] text-[#9a762e]"><span class="material-symbols-outlined">verified_user</span></span>
                            <h2>Maklumat penting</h2>
                            <p>Ini ialah pertanyaan sebut harga. Harga, tarikh pelaksanaan dan ketersediaan perkhidmatan tertakluk kepada pengesahan pihak SalamHaji.</p>
                            <ul><li><span class="material-symbols-outlined">check_circle</span>Tiada bayaran atau tempahan automatik dibuat dalam laman ini.</li><li><span class="material-symbols-outlined">check_circle</span>Operator akan menghubungi anda melalui WhatsApp untuk pengesahan.</li><li><span class="material-symbols-outlined">check_circle</span>Sila pastikan servis dan lokasi yang dipilih adalah tepat.</li></ul>
                            <label class="aqiqah-korban-acknowledgement mt-5"><input type="checkbox" name="important_information_acknowledged" value="1" @checked(old('important_information_acknowledged')) required data-aqiqah-korban-acknowledgement><span>Saya telah membaca dan memahami maklumat penting di atas.</span></label>
                        </aside>
                        <p class="aqiqah-korban-wizard-feedback" data-aqiqah-korban-feedback="3" aria-live="polite"></p>
                        <div class="aqiqah-korban-wizard-actions"><span>Anda akan dibawa ke WhatsApp dengan butiran pertanyaan yang telah disediakan.</span><button class="button-primary" type="submit" @disabled(! $whatsAppConfigured) data-aqiqah-korban-submit><span class="material-symbols-outlined text-[18px]">chat</span>Teruskan ke WhatsApp</button></div>
                        @if (! $whatsAppConfigured)<p class="mt-3 text-sm font-semibold text-[#9b3029]">WhatsApp perkhidmatan belum dikonfigurasi oleh admin.</p>@endif
                    </section>
                </form>
            </section>
        @endif
    </div>
</x-layouts.app>

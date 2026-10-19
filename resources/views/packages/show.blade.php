<x-layouts.app :title="$package->seo_title ?: $package->title" :meta-description="$package->seo_description ?: $package->short_description">
    <section class="border-b border-[#ebe5d9] bg-[#f5f3ef]">
        <div class="container-page flex flex-wrap items-center gap-2 py-3 text-[10px] font-semibold text-[#5c6461]">
            <a href="{{ route('home') }}" class="hover:text-dark-green">Utama</a><span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <a href="{{ route('packages.index') }}" class="hover:text-dark-green">Pakej Umrah</a><span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-dark-green">{{ $package->title }}</span>
        </div>
    </section>

    <section class="container-page py-8 sm:py-10">
        <div class="grid gap-8 lg:grid-cols-[1fr_auto] lg:items-end">
            <div>
                <div class="flex flex-wrap items-center gap-2"><span class="rounded-md bg-[#00271f] px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-[#ffdf9a]">{{ $package->category->name }}</span><span class="rounded-md bg-[#f5f3ef] px-2 py-1 text-[9px] font-bold text-[#404946]">{{ $package->duration_days }} Hari {{ $package->duration_nights }} Malam</span></div>
                <h1 class="mt-3 max-w-4xl font-display text-4xl leading-[1.1] text-dark-green sm:text-5xl">{{ $package->title }}</h1>
                <p class="mt-3 max-w-3xl text-sm leading-7 text-[#5c6461]">{{ $package->short_description }}</p>
            </div>
            <div class="rounded-lg border border-[#ebe5d9] bg-white px-5 py-4 lg:text-right">
                <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-[#707976]">Anggaran harga bermula</p>
                <p class="mt-1 font-display text-4xl leading-none text-dark-green">{{ $package->formattedStartingPrice() }}</p>
                <p class="mt-1 text-[10px] text-[#707976]">Seorang · tertakluk kepada pengesahan agensi</p>
            </div>
        </div>

        <div class="mt-6 grid gap-3 border-y border-[#ebe5d9] py-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">calendar_month</span></span><div><p class="text-[9px] font-bold uppercase tracking-wide text-[#707976]">Tempoh perjalanan</p><p class="mt-0.5 text-xs font-bold text-dark-green">{{ $package->duration_days }} Hari / {{ $package->duration_nights }} Malam</p></div></div>
            <div class="flex gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">hotel</span></span><div><p class="text-[9px] font-bold uppercase tracking-wide text-[#707976]">Penginapan</p><p class="mt-0.5 text-xs font-bold text-dark-green">Butiran hotel disertakan</p></div></div>
            <div class="flex gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">flight</span></span><div><p class="text-[9px] font-bold uppercase tracking-wide text-[#707976]">Penerbangan</p><p class="mt-0.5 text-xs font-bold text-dark-green">Jadual semakan agensi</p></div></div>
            <div class="flex gap-3"><span class="grid size-9 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">support_agent</span></span><div><p class="text-[9px] font-bold uppercase tracking-wide text-[#707976]">Bantuan</p><p class="mt-0.5 text-xs font-bold text-dark-green">Maklumat &amp; pertanyaan</p></div></div>
        </div>
    </section>

    <section class="container-page pb-16 sm:pb-20">
        <div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_330px]">
            <div class="space-y-12">
                <section>
                    <div class="grid gap-3 sm:grid-cols-[1.5fr_.85fr]">
                        <div class="relative min-h-72 overflow-hidden rounded-xl bg-dark-green sm:min-h-[420px]">
                            @if ($package->imageUrl())
                                <img src="{{ $package->imageUrl() }}" alt="{{ $package->title }}" class="size-full object-cover">
                            @else
                                <div class="grid size-full place-items-center text-[#ffdf9a]"><span class="material-symbols-outlined text-7xl">mosque</span></div>
                            @endif
                            <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#00271f]/85 to-transparent p-5 text-white"><p class="text-[9px] font-bold uppercase tracking-[0.12em] text-[#ffdf9a]">Pakej {{ $package->category->name }}</p><p class="mt-1 font-display text-xl">Perjalanan Umrah yang dirancang dengan kemas</p></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-1">
                            @forelse ($package->images->take(2) as $image)
                                <img src="{{ $image->url() }}" alt="{{ $image->alt_text ?: $package->title }}" class="h-36 w-full rounded-xl object-cover sm:h-full" loading="lazy">
                            @empty
                                <div class="rounded-xl bg-[#f5f3ef]"></div><div class="rounded-xl bg-[#e4e2de]"></div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section>
                    <p class="section-eyebrow">Ringkasan pakej</p>
                    <h2 class="mt-2 font-display text-3xl text-dark-green">Mengapa pakej ini menjadi pilihan</h2>
                    <p class="mt-5 whitespace-pre-line text-sm leading-8 text-[#4b5552]">{{ $package->description }}</p>
                </section>

                <section class="grid gap-4 sm:grid-cols-2">
                    <article class="rounded-xl bg-[#f5f3ef] p-5"><span class="grid size-9 place-items-center rounded-lg bg-white text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">hotel</span></span><h2 class="mt-4 font-display text-2xl text-dark-green">Penginapan</h2><p class="mt-3 text-xs leading-6 text-[#5c6461]">{{ $package->hotel_information ?: 'Butiran hotel akan disahkan oleh pihak agensi mengikut susunan perjalanan.' }}</p></article>
                    <article class="rounded-xl bg-[#f5f3ef] p-5"><span class="grid size-9 place-items-center rounded-lg bg-white text-[#8b671c]"><span class="material-symbols-outlined text-[19px]">flight</span></span><h2 class="mt-4 font-display text-2xl text-dark-green">Penerbangan</h2><p class="mt-3 text-xs leading-6 text-[#5c6461]">{{ $package->flight_information ?: 'Butiran penerbangan akan disahkan oleh pihak agensi sebelum perjalanan.' }}</p></article>
                </section>

                @if ($package->itineraries->isNotEmpty())
                    <section>
                        <div class="flex items-end justify-between gap-4"><div><p class="section-eyebrow">Atur cara ibadah</p><h2 class="mt-2 font-display text-3xl text-dark-green">Itinerari perjalanan {{ $package->duration_days }} hari</h2></div></div>
                        <ol class="mt-6 space-y-3">
                            @foreach ($package->itineraries as $itinerary)
                                <li class="group rounded-lg border border-[#ebe5d9] bg-white px-4 py-3.5">
                                    <div class="flex gap-3"><span class="grid size-6 shrink-0 place-items-center rounded-md bg-[#f5f3ef] text-[10px] font-bold text-dark-green">{{ $itinerary->day_number }}</span><div><h3 class="text-xs font-bold text-dark-green">{{ $itinerary->title }}</h3><p class="mt-1 text-[11px] leading-5 text-[#5c6461]">{{ $itinerary->description }}</p></div></div>
                                </li>
                            @endforeach
                        </ol>
                    </section>
                @endif

                <section>
                    <p class="section-eyebrow">Apa yang disediakan</p>
                    <h2 class="mt-2 font-display text-3xl text-dark-green">Perincian kos &amp; kemudahan pakej</h2>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <article class="rounded-xl bg-[#f5f3ef] p-5"><h3 class="flex items-center gap-2 text-sm font-bold text-dark-green"><span class="material-symbols-outlined text-[17px] text-[#2d5a27]">check_circle</span> Termasuk dalam pakej</h3><ul class="mt-4 space-y-2.5 text-[11px] leading-5 text-[#4b5552]">@forelse ($package->inclusions ?? [] as $item)<li class="flex gap-2"><span class="text-[#2d5a27]">✓</span><span>{{ $item }}</span></li>@empty<li>Butiran akan disahkan oleh agensi.</li>@endforelse</ul></article>
                        <article class="rounded-xl bg-[#f8f2ef] p-5"><h3 class="flex items-center gap-2 text-sm font-bold text-dark-green"><span class="material-symbols-outlined text-[17px] text-[#9e2a2b]">cancel</span> Tidak termasuk</h3><ul class="mt-4 space-y-2.5 text-[11px] leading-5 text-[#4b5552]">@forelse ($package->exclusions ?? [] as $item)<li class="flex gap-2"><span class="text-[#9e2a2b]">—</span><span>{{ $item }}</span></li>@empty<li>Butiran akan disahkan oleh agensi.</li>@endforelse</ul></article>
                    </div>
                </section>
            </div>

            <aside class="h-fit lg:sticky lg:top-24">
                <section class="rounded-xl border border-[#ebe5d9] bg-white p-5 shadow-[0_12px_30px_-15px_rgba(16,42,60,0.18)]">
                    <div class="flex items-start justify-between gap-3"><div><p class="section-eyebrow">Maklumat &amp; pertanyaan</p><h2 class="mt-1 font-display text-2xl text-dark-green">Tanya tentang pakej ini</h2></div><span class="grid size-9 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined">chat</span></span></div>
                    <p class="mt-3 text-[11px] leading-5 text-[#5c6461]">Pilih tarikh yang ingin anda bincangkan, kemudian hubungi agensi untuk maklumat lanjut dan pengesahan ketersediaan.</p>
                    <div class="mt-5 space-y-3">
                        @forelse ($package->departures as $departure)
                            @php
                                $departureMessage = 'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.' bagi tarikh berlepas '.$departure->departure_date->translatedFormat('d M Y').'.';
                            @endphp
                            <article class="rounded-lg border border-[#ebe5d9] bg-[#fdfbf7] p-3">
                                <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-bold text-dark-green">{{ $departure->departure_date->translatedFormat('d M Y') }}</p><p class="mt-1 text-[10px] leading-4 text-[#5c6461]">Pulang {{ $departure->return_date->translatedFormat('d M Y') }} · {{ $departure->departure_airport }}</p></div><span class="rounded-md bg-white px-2 py-1 text-[9px] font-bold text-[#8b671c]">{{ $departure->availableSeats() }} tempat</span></div>
                                <div class="mt-3 flex items-center justify-between gap-3"><p class="font-display text-xl text-dark-green">{{ $departure->formattedPrice() }}</p>
                                    @if (filled($whatsappNumber))
                                        <x-whats-app-link :number="$whatsappNumber" :message="$departureMessage" :fallback="route('contact.create', ['package_id' => $package->id])" class="text-[10px] font-bold text-dark-green">Tanya <span aria-hidden="true">→</span></x-whats-app-link>
                                    @else
                                        <a href="{{ route('contact.create', ['package_id' => $package->id]) }}" class="text-[10px] font-bold text-dark-green">Tanya <span aria-hidden="true">→</span></a>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <p class="rounded-lg bg-[#f5f3ef] p-3 text-[11px] leading-5 text-[#5c6461]">Tarikh baharu akan dikemas kini oleh agensi.</p>
                        @endforelse
                    </div>
                    @php
                        $packageMessage = 'Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.';
                    @endphp
                    @if (filled($whatsappNumber))
                        <x-whats-app-link :number="$whatsappNumber" :message="$packageMessage" :fallback="route('contact.create', ['package_id' => $package->id])" class="button-gold mt-5 w-full"><span class="material-symbols-outlined text-[17px]">chat</span> Tanya melalui WhatsApp</x-whats-app-link>
                    @else
                        <a href="{{ route('contact.create', ['package_id' => $package->id]) }}" class="button-gold mt-5 w-full"><span class="material-symbols-outlined text-[17px]">forum</span> Hantar Pertanyaan</a>
                    @endif
                    <p class="mt-4 border-t border-[#ebe5d9] pt-4 text-[9px] leading-4 text-[#707976]">Harga dan ketersediaan tempat tertakluk kepada pengesahan pihak agensi.</p>
                </section>
            </aside>
        </div>
    </section>
</x-layouts.app>

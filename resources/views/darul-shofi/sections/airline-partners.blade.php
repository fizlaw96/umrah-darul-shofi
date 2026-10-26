@if ($airlineCompanies->isNotEmpty())
    <section class="ds-airline-partners border-y border-[#EAE5DE] bg-white py-6 overflow-hidden marquee-container" data-purpose="partners-marquee">
        <div class="mx-auto mb-3 max-w-7xl px-4 sm:px-6"><p class="text-center text-[11px] font-semibold uppercase tracking-widest text-brand-muted">Rakan Syarikat Penerbangan &amp; Akreditasi Rasmi</p></div>
        <div class="relative flex w-full items-center overflow-hidden"><div class="ds-marquee-track ds-marquee-track--forward items-center">
            @foreach ([false, true] as $isDuplicate)
                <div @class(['ds-marquee-sequence', 'ds-marquee-sequence--partners', 'items-center']) @if ($isDuplicate) aria-hidden="true" @endif>
                    @foreach ($airlineCompanies as $airlineCompany)
                        <span class="ds-airline-partner font-serif-luxury flex items-center gap-2 text-lg font-bold tracking-wider text-gray-600"><img @if (! $isDuplicate) alt="{{ $airlineCompany->name }}" @else alt="" @endif class="ds-airline-logo" src="{{ $airlineCompany->logoUrl() }}"><span>{{ $airlineCompany->name }}</span></span>
                    @endforeach
                </div>
            @endforeach
        </div></div>
    </section>
@endif

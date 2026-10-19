<x-layouts.app title="Hubungi Kami">
    <section class="bg-mint py-14 sm:py-20">
        <div class="container-page">
            <p class="section-eyebrow">Kami sedia membantu</p>
            <h1 class="mt-2 page-heading">Hubungi kami</h1>
            <p class="mt-4 max-w-2xl text-sm leading-7 text-[#5c6461]">Gunakan borang ini untuk meminta maklumat tentang pakej. Tiada tempahan atau pembayaran dibuat melalui laman ini.</p>
        </div>
    </section>

    <section class="container-page py-10 sm:py-14">
        <div class="grid gap-8 lg:grid-cols-[1fr_.6fr]">
            <form method="POST" action="{{ route('contact.store') }}" class="panel space-y-5 p-6 sm:p-8">
                @csrf

                <x-errors />

                <div>
                    <h2 class="text-xl font-extrabold text-dark-green">Hantar pertanyaan</h2>
                    <p class="mt-1 text-sm text-slate-500">Kongsi maklumat ringkas supaya agensi dapat memahami keperluan anda.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <label>
                        <span class="field-label">Nama</span>
                        <input class="field-control" name="name" value="{{ old('name') }}" required>
                    </label>
                    <label>
                        <span class="field-label">Telefon</span>
                        <input class="field-control" name="phone" value="{{ old('phone') }}" required>
                    </label>
                </div>

                <label>
                    <span class="field-label">E-mel</span>
                    <input class="field-control" type="email" name="email" value="{{ old('email') }}" required>
                </label>

                <label>
                    <span class="field-label">Pakej yang diminati <span class="font-normal text-slate-400">(pilihan)</span></span>
                    <select class="field-control" name="package_id">
                        <option value="">Belum pasti</option>
                        @foreach ($packages as $package)
                            <option value="{{ $package->id }}" @selected(old('package_id', request('package_id')) == $package->id)>{{ $package->title }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span class="field-label">Pertanyaan</span>
                    <textarea class="field-control min-h-32" name="message" required>{{ old('message') }}</textarea>
                </label>

                <label class="flex gap-3 text-sm leading-6 text-slate-600">
                    <input class="mt-1 size-4 rounded border-slate-300 text-brand" type="checkbox" name="privacy_consent" value="1" @checked(old('privacy_consent')) required>
                    <span>Saya bersetuju maklumat ini digunakan untuk menjawab pertanyaan saya.</span>
                </label>

                <button class="button-primary" type="submit">Hantar pertanyaan</button>
            </form>

            <aside class="rounded-2xl bg-dark-green p-7 text-white sm:p-9">
                <p class="text-[10px] font-bold uppercase tracking-[0.14em] text-[#ffdf9a]">Butiran agensi</p>
                <div class="mt-7 space-y-6">
                    @if (filled($siteSettings['contact_whatsapp']))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/55">WhatsApp</p>
                            <x-whats-app-link :number="$siteSettings['contact_whatsapp']" message="Assalamualaikum, saya ingin bertanya tentang pakej Umrah." class="mt-1 inline-flex items-center gap-2 font-bold text-[#ffdf9a]"><span class="material-symbols-outlined text-[17px]">chat</span> Hubungi melalui WhatsApp</x-whats-app-link>
                        </div>
                    @endif
                    @if (filled($siteSettings['contact_phone']))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/55">Telefon</p>
                            <p class="mt-1 font-bold">{{ $siteSettings['contact_phone'] }}</p>
                        </div>
                    @endif
                    @if (filled($siteSettings['contact_email']))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/55">E-mel</p>
                            <p class="mt-1 font-bold">{{ $siteSettings['contact_email'] }}</p>
                        </div>
                    @endif
                    @if (filled($siteSettings['office_address']))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/55">Alamat</p>
                            <p class="mt-1 leading-6 text-emerald-50/80">{{ $siteSettings['office_address'] }}</p>
                        </div>
                    @endif
                    @if (filled($siteSettings['office_hours']))
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-50/55">Waktu operasi</p>
                            <p class="mt-1 leading-6 text-emerald-50/80">{{ $siteSettings['office_hours'] }}</p>
                        </div>
                    @endif
                    @if (! filled($siteSettings['contact_phone']) && ! filled($siteSettings['contact_whatsapp']) && ! filled($siteSettings['contact_email']))
                        <p class="leading-7 text-emerald-50/75">Butiran rasmi untuk dihubungi akan dikemas kini oleh agensi tidak lama lagi.</p>
                    @endif
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>

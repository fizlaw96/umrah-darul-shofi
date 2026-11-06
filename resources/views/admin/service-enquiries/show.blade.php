<x-layouts.admin heading="Permintaan Servis">
    <div class="mx-auto max-w-6xl">
        <div class="mb-7 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="section-eyebrow">{{ $enquiry->serviceTypeLabel() }}</p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#00251e] sm:text-3xl">{{ $enquiry->full_name }}</h1>
                <p class="mt-2 text-sm text-[#69746f]">{{ $enquiry->summary }} · Diterima {{ $enquiry->created_at->timezone(config('app.timezone'))->format('d/m/Y g:i A') }}</p>
            </div>
            <a class="admin-button-secondary" href="{{ route('admin.service-enquiries.index') }}"><span class="material-symbols-outlined text-[18px]">arrow_back</span>Kembali</a>
        </div>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_20rem]">
            <section class="admin-card p-5 sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-[#e3f5e9] text-[#00705b]"><span class="material-symbols-outlined text-[21px]">chat</span></span>
                    <div><h2 class="text-sm font-bold text-[#163a30]">Mesej WhatsApp yang disediakan</h2><p class="mt-1 text-xs text-[#718078]">Salinan tepat mesej yang dibuka di WhatsApp pelanggan.</p></div>
                </div>
                <pre class="mt-4 whitespace-pre-wrap rounded-xl border border-[#e7ece8] bg-[#fbfdfb] p-4 font-sans text-xs leading-5 text-[#3f514a]">{{ $enquiry->message }}</pre>
                @if ($enquiry->aqiqahKorbanService)
                    <p class="mt-4 text-xs text-[#506058]">Pakej: <a class="font-bold text-[#00705b] underline" href="{{ route('admin.aqiqah-korban-services.edit', $enquiry->aqiqahKorbanService) }}">{{ $enquiry->aqiqahKorbanService->name }}</a></p>
                @endif
            </section>

            <aside class="space-y-5 xl:sticky xl:top-5">
                <section class="admin-card p-5">
                    <h2 class="text-sm font-bold text-[#163a30]">Pelanggan</h2>
                    <dl class="mt-3 space-y-2 text-xs text-[#506058]">
                        <div><dt class="font-bold text-[#7a8880]">Nama</dt><dd>{{ $enquiry->full_name }}</dd></div>
                        <div><dt class="font-bold text-[#7a8880]">Telefon</dt><dd>{{ $enquiry->contact_number }}</dd></div>
                        @if ($enquiry->contacted_at)
                            <div><dt class="font-bold text-[#7a8880]">Mula dihubungi</dt><dd>{{ $enquiry->contacted_at->timezone(config('app.timezone'))->format('d/m/Y g:i A') }}</dd></div>
                        @endif
                    </dl>
                    @if ($enquiry->customerWhatsAppUrl())
                        <a class="admin-button-primary mt-4 w-full justify-center" href="{{ $enquiry->customerWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer"><span class="material-symbols-outlined text-[18px]">chat</span>WhatsApp Pelanggan</a>
                    @endif
                </section>

                <form class="admin-card p-5" method="POST" action="{{ route('admin.service-enquiries.update', $enquiry) }}">
                    @csrf
                    @method('PUT')
                    <x-errors />
                    <h2 class="text-sm font-bold text-[#163a30]">Status susulan</h2>
                    <label class="mt-3 block"><span class="admin-label">Status</span><select class="admin-field" name="status">@foreach (\App\Models\ServiceEnquiry::statuses() as $statusKey => $statusDetails)<option value="{{ $statusKey }}" @selected(old('status', $enquiry->status) === $statusKey)>{{ $statusDetails['label'] }}</option>@endforeach</select></label>
                    <label class="mt-3 block"><span class="admin-label">Nota dalaman</span><textarea class="admin-field min-h-28" name="staff_notes" maxlength="3000" placeholder="Contoh: Sebut harga dihantar, menunggu pengesahan">{{ old('staff_notes', $enquiry->staff_notes) }}</textarea></label>
                    <button class="admin-button-primary mt-4 w-full justify-center" type="submit"><span class="material-symbols-outlined text-[18px]">save</span>Simpan Status</button>
                </form>

                <form method="POST" action="{{ route('admin.service-enquiries.destroy', $enquiry) }}" data-confirm-delete data-confirm-message="Padam permintaan ini?">
                    @csrf
                    @method('DELETE')
                    <button class="admin-button-secondary w-full justify-center !border-[#f1c6c1] !text-[#9b3029]" type="submit"><span class="material-symbols-outlined text-[18px]">delete</span>Padam Permintaan</button>
                </form>
            </aside>
        </div>
    </div>
</x-layouts.admin>

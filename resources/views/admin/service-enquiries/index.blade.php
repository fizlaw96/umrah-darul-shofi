<x-layouts.admin heading="Permintaan Servis">
    @php
        $statuses = \App\Models\ServiceEnquiry::statuses();
        $serviceTypes = \App\Models\ServiceEnquiry::serviceTypes();
        $totalCount = $statusCounts->sum();
    @endphp

    <div class="mx-auto max-w-6xl">
        <div class="mb-7">
            <p class="section-eyebrow">Perkhidmatan</p>
            <h1 class="mt-1 text-2xl font-bold tracking-tight text-[#00251e] sm:text-3xl">Permintaan Servis</h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-[#69746f]">Setiap borang Transport dan Aqiqah &amp; Korban yang lengkap direkodkan di sini. Status <strong>Baru</strong> bermaksud pelanggan telah mengisi borang, tetapi mungkin belum menekan Send di WhatsApp. Hubungi mereka untuk susulan.</p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statuses as $statusKey => $statusDetails)
                <a class="aq-admin-stat-card" href="{{ route('admin.service-enquiries.index', array_filter(['type' => $selectedServiceType, 'status' => $statusKey])) }}">
                    <span @class(['aq-admin-stat-card__icon', 'is-gold' => $statusKey === \App\Models\ServiceEnquiry::NewStatus, 'is-amber' => $statusKey === \App\Models\ServiceEnquiry::CancelledStatus])><span class="material-symbols-outlined">{{ $statusDetails['icon'] }}</span></span>
                    <div><strong>{{ $statusCounts[$statusKey] ?? 0 }}</strong><span>{{ $statusDetails['label'] }}</span></div>
                </a>
            @endforeach
        </section>

        <section class="admin-card mt-5 overflow-hidden">
            <div class="flex flex-col gap-3 border-b border-[#edf1ee] p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex flex-wrap gap-3">
                    <nav class="aq-admin-filter-tabs" aria-label="Tapis jenis servis">
                        <a href="{{ route('admin.service-enquiries.index', array_filter(['status' => $selectedStatus])) }}" @class(['is-active' => $selectedServiceType === null])>Semua servis</a>
                        @foreach ($serviceTypes as $typeKey => $typeLabel)
                            <a href="{{ route('admin.service-enquiries.index', array_filter(['type' => $typeKey, 'status' => $selectedStatus])) }}" @class(['is-active' => $selectedServiceType === $typeKey])>{{ $typeLabel }}</a>
                        @endforeach
                    </nav>
                    <nav class="aq-admin-filter-tabs" aria-label="Tapis status">
                        <a href="{{ route('admin.service-enquiries.index', array_filter(['type' => $selectedServiceType])) }}" @class(['is-active' => $selectedStatus === null])>Semua ({{ $totalCount }})</a>
                        @foreach ($statuses as $statusKey => $statusDetails)
                            <a href="{{ route('admin.service-enquiries.index', array_filter(['type' => $selectedServiceType, 'status' => $statusKey])) }}" @class(['is-active' => $selectedStatus === $statusKey])>{{ $statusDetails['label'] }} ({{ $statusCounts[$statusKey] ?? 0 }})</a>
                        @endforeach
                    </nav>
                </div>
                <form method="GET" action="{{ route('admin.service-enquiries.index') }}" class="relative block lg:w-64">
                    @if ($selectedServiceType)<input type="hidden" name="type" value="{{ $selectedServiceType }}">@endif
                    @if ($selectedStatus)<input type="hidden" name="status" value="{{ $selectedStatus }}">@endif
                    <label><span class="sr-only">Cari permintaan</span><span class="material-symbols-outlined pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-[18px] text-[#7a8880]">search</span><input class="admin-field !bg-[#f5f8f5] !py-2.5 pl-10 text-xs" type="search" name="q" value="{{ request('q') }}" placeholder="Nama, telefon atau ringkasan..."></label>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left text-sm">
                    <thead class="border-b border-[#edf1ee] bg-[#fafcf9] text-[10px] font-bold uppercase tracking-[.08em] text-[#7a8880]">
                        <tr><th class="px-5 py-3.5">Diterima</th><th class="px-3 py-3.5">Pelanggan</th><th class="px-3 py-3.5">Servis</th><th class="px-3 py-3.5">Ringkasan</th><th class="px-3 py-3.5">Status</th><th class="px-5 py-3.5 text-right">Tindakan</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#edf1ee]">
                        @forelse ($enquiries as $enquiry)
                            <tr>
                                <td class="px-5 py-4 text-xs text-[#506058]"><span class="block font-semibold">{{ $enquiry->created_at->timezone(config('app.timezone'))->format('d/m/Y') }}</span><span class="text-[#7a8880]">{{ $enquiry->created_at->timezone(config('app.timezone'))->format('g:i A') }}</span></td>
                                <td class="px-3 py-4"><p class="font-bold text-[#163a30]">{{ $enquiry->full_name }}</p><p class="mt-0.5 text-[11px] text-[#7a8880]">{{ $enquiry->contact_number }}</p></td>
                                <td class="px-3 py-4"><span class="aq-admin-category-badge {{ $enquiry->service_type === \App\Models\ServiceEnquiry::TransportType ? 'is-aqiqah' : 'is-korban' }}">{{ $enquiry->serviceTypeLabel() }}</span></td>
                                <td class="max-w-72 px-3 py-4 text-xs text-[#506058]">{{ $enquiry->summary }}</td>
                                <td class="px-3 py-4"><span class="aq-admin-status-badge is-{{ $enquiry->status }}"><span class="material-symbols-outlined">{{ $enquiry->statusIcon() }}</span>{{ $enquiry->statusLabel() }}</span></td>
                                <td class="px-5 py-4"><div class="flex justify-end gap-1">
                                    @if ($enquiry->customerWhatsAppUrl())
                                        <a class="grid size-9 place-items-center rounded-lg text-[#08715b] transition hover:bg-[#e9f7ed]" href="{{ $enquiry->customerWhatsAppUrl() }}" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp {{ $enquiry->full_name }}"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">chat</span></a>
                                    @endif
                                    <a class="grid size-9 place-items-center rounded-lg text-[#234b3e] transition hover:bg-[#e9f7ed]" href="{{ route('admin.service-enquiries.show', $enquiry) }}" aria-label="Lihat permintaan {{ $enquiry->full_name }}"><span class="material-symbols-outlined text-[19px]" aria-hidden="true">visibility</span></a>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center"><span class="material-symbols-outlined text-3xl text-[#9aa89f]">inbox</span><p class="mt-2 text-sm font-semibold text-[#506058]">Tiada permintaan ditemui.</p><p class="mt-1 text-xs text-[#7a8880]">Permintaan baharu akan muncul di sini selepas pelanggan melengkapkan borang.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-5">{{ $enquiries->links() }}</div>
    </div>
</x-layouts.admin>

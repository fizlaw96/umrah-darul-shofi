<x-layouts.admin heading="Dashboard">
    <section class="flex flex-col justify-between gap-5 xl:flex-row xl:items-end">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Panel kawalan CMS</p>
            <h1 class="mt-1 text-4xl font-bold tracking-[-0.045em] text-[#00251e]">Dashboard</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#69746f]">Gambaran keseluruhan kandungan pakej dan kesediaan laman web berdasarkan data semasa.</p>
        </div>
        <a href="{{ route('admin.packages.create') }}" class="admin-button-primary"><span class="material-symbols-outlined text-[18px]">add_circle</span>Pakej Baharu</a>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
        <x-admin.stat-card label="Jumlah pakej" :value="$packageCount" detail="Rekod pakej dalam CMS" icon="deployed_code" tone="ink" />
        <x-admin.stat-card label="Pakej diterbitkan" :value="$publishedPackageCount" detail="Boleh dipaparkan kepada pengunjung" icon="check_circle" />
        <x-admin.stat-card label="Pakej draf" :value="$draftPackageCount" detail="Masih dalam semakan atau penyediaan" icon="edit_note" tone="gold" />
        <x-admin.stat-card label="Jadual berlepas aktif" :value="$upcomingDepartureCount" detail="Tarikh akan datang yang masih dibuka" icon="flight_takeoff" tone="ink" />
    </section>

    <section class="mt-8">
        <div class="admin-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-[#e8eee9] px-6 py-5">
                <h2 class="flex items-center gap-2 text-xl font-bold text-[#00251e]"><span class="material-symbols-outlined text-[#006b57]">luggage</span>Pakej Terkini</h2>
                <a href="{{ route('admin.packages.index') }}" class="text-xs font-bold text-[#006b57] transition hover:text-[#00251e]">Urus Semua Pakej <span aria-hidden="true">→</span></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[840px] text-left">
                    <thead class="bg-[#e9f7ed] text-[10px] font-bold uppercase tracking-[0.12em] text-[#65716b]"><tr><th class="px-6 py-4">Gambar</th><th class="px-4 py-4">Nama pakej</th><th class="px-4 py-4">Kategori</th><th class="px-4 py-4">Bulan berlepas</th><th class="px-4 py-4 text-right">Harga bermula</th><th class="px-6 py-4 text-right">Status</th></tr></thead>
                    <tbody class="divide-y divide-[#e8eee9]">
                        @forelse ($recentPackages as $package)
                            <tr class="transition hover:bg-[#f9fcf9]">
                                <td class="px-6 py-4">@if ($package->imageUrl())<img src="{{ $package->imageUrl() }}" alt="" class="size-11 rounded-xl object-cover">@else<span class="grid size-11 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">image</span></span>@endif</td>
                                <td class="px-4 py-4"><a href="{{ route('admin.packages.edit', $package) }}" class="font-bold text-[#00251e] hover:text-[#006b57]">{{ $package->title }}</a><p class="mt-1 text-xs text-[#77827c]">{{ $package->code ?: $package->duration_days.' Hari '.$package->duration_nights.' Malam' }}</p></td>
                                <td class="px-4 py-4"><span class="rounded-full bg-[#e9f2eb] px-3 py-1 text-[10px] font-bold tracking-wide text-[#245446]">{{ $package->category->name }}</span></td>
                                <td class="px-4 py-4">@if ($package->next_departure_date)<p class="text-sm font-bold text-[#163a30]">{{ $package->next_departure_date->translatedFormat('F Y') }}</p><p class="mt-1 text-[10px] text-[#77827c]">{{ $package->next_departure_date->translatedFormat('d M Y') }}</p>@else<span class="text-xs font-medium text-[#85908a]">Belum ditetapkan</span>@endif</td>
                                <td class="px-4 py-4 text-right text-sm font-bold text-[#00251e]">{{ $package->formattedStartingPrice() }}</td>
                                <td class="px-6 py-4 text-right"><x-admin.status-badge :status="$package->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-[#69746f]">Belum ada pakej. Tambah pakej pertama untuk melihat ringkasan di sini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col gap-3 border-t border-[#e7eee9] px-5 py-4 text-xs text-[#718078] sm:flex-row sm:items-center sm:justify-between"><p>Menunjukkan {{ $recentPackages->firstItem() ?? 0 }} hingga {{ $recentPackages->lastItem() ?? 0 }} daripada {{ $recentPackages->total() }} rekod pakej</p><div>{{ $recentPackages->links() }}</div></div>
        </div>

    </section>

    <section class="admin-card mt-6 p-6">
        <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[#006b57]">bolt</span><h2 class="text-lg font-bold text-[#00251e]">Tindakan Pantas</h2></div>
        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <a href="{{ route('admin.packages.create') }}" class="admin-shortcut"><span class="material-symbols-outlined">add</span><strong>Tambah Pakej Baru</strong><small>Borang pendaftaran</small></a>
            <a href="{{ route('admin.settings.edit', ['tab' => 'contact']) }}" class="admin-shortcut"><span class="material-symbols-outlined">chat</span><strong>Tetapan WhatsApp</strong><small>Terus ke tab hubungan</small></a>
            <a href="{{ route('admin.media.index') }}" class="admin-shortcut"><span class="material-symbols-outlined">photo_library</span><strong>Urus Galeri</strong><small>Gambar laman web</small></a>
            <a href="{{ route('admin.settings.edit', ['tab' => 'identity']) }}" class="admin-shortcut"><span class="material-symbols-outlined">business</span><strong>Identiti Website</strong><small>Logo &amp; nama agensi</small></a>
        </div>
    </section>
</x-layouts.admin>

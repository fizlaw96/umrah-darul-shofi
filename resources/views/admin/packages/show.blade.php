<x-layouts.admin :heading="'Peserta · '.$package->title">
    <section class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[.16em] text-[#006b57]">Pengurusan pakej</p>
            <h1 class="mt-1 text-3xl font-bold tracking-[-.045em] text-[#00251e]">{{ $package->title }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#69746f]">Masukkan senarai peserta secara ringkas dan muat turun rekod dalam format CSV yang boleh dibuka terus dengan Excel.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a class="admin-button-primary" href="{{ route('admin.packages.participants.export', $package) }}"><span class="material-symbols-outlined text-[18px]">download</span>Eksport Excel</a>
        </div>
    </section>

    <section class="admin-card mt-7 p-6 sm:p-7">
        <div class="flex items-start gap-3">
            <span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">person_add</span></span>
            <div><h2 class="text-xl font-bold text-[#00251e]">Tambah peserta</h2><p class="mt-1 text-sm text-[#69746f]">Pilih tarikh berlepas supaya baki tempat untuk tarikh tersebut dikemas kini. Hanya nama diperlukan; butiran lain boleh ditambah apabila ada.</p></div>
        </div>
        @if ($package->departures->isNotEmpty())
            <form class="mt-6 grid gap-5 md:grid-cols-2" method="POST" action="{{ route('admin.packages.participants.store', $package) }}">
                @csrf
                <x-errors />
                <label><span class="admin-label">Nama peserta</span><input class="admin-field" name="full_name" value="{{ old('full_name') }}" required></label>
                <label><span class="admin-label">Tarikh berlepas</span><select class="admin-field" name="package_departure_id" required><option value="" disabled @selected(blank(old('package_departure_id')))>Pilih tarikh berlepas</option>@foreach ($package->departures as $departure)<option value="{{ $departure->id }}" @selected((string) old('package_departure_id') === (string) $departure->id)>{{ $departure->departure_date->translatedFormat('d M Y') }} · Pulang {{ $departure->return_date->translatedFormat('d M Y') }}</option>@endforeach</select></label>
                <label><span class="admin-label">Telefon <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" name="phone" value="{{ old('phone') }}"></label>
                <label><span class="admin-label">Catatan <span class="font-medium text-[#8a958f]">Pilihan</span></span><input class="admin-field" name="notes" value="{{ old('notes') }}" maxlength="1000"></label>
                <div class="flex items-end"><button class="admin-button-primary" type="submit"><span class="material-symbols-outlined text-[18px]">add</span>Tambah Peserta</button></div>
            </form>
        @else
            <p class="mt-6 rounded-xl bg-[#fff8e7] px-4 py-3 text-sm leading-6 text-[#745b1d]">Tambah sekurang-kurangnya satu tarikh berlepas sebelum merekodkan peserta.</p>
        @endif
    </section>

    <section class="admin-card mt-7 overflow-hidden">
        <div class="flex items-center justify-between border-b border-[#e8eee9] px-6 py-5"><div><h2 class="text-xl font-bold text-[#00251e]">Senarai peserta</h2><p class="mt-1 text-xs text-[#718078]">{{ $package->participants->count() }} peserta direkodkan</p></div></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-[#e9f7ed] text-[10px] font-bold uppercase tracking-[.12em] text-[#65716b]"><tr><th class="px-6 py-4">Peserta</th><th class="px-4 py-4">Tarikh berlepas</th><th class="px-4 py-4">Telefon</th><th class="px-4 py-4">Catatan</th><th class="px-6 py-4 text-right">Tindakan</th></tr></thead>
                <tbody class="divide-y divide-[#e8eee9]">
                    @forelse ($package->participants as $participant)
                        <tr><td class="px-6 py-4"><p class="font-bold text-[#00251e]">{{ $participant->full_name }}</p><p class="mt-1 text-xs text-[#718078]">Ditambah {{ $participant->created_at->format('d M Y') }}</p></td><td class="px-4 py-4 text-sm font-semibold text-[#506058]">{{ $participant->departure?->departure_date->translatedFormat('d M Y') ?: 'Belum ditetapkan' }}</td><td class="px-4 py-4 text-sm text-[#506058]">{{ $participant->phone ?: '—' }}</td><td class="max-w-72 px-4 py-4 text-sm text-[#506058]">{{ $participant->notes ?: '—' }}</td><td class="px-6 py-4 text-right"><form method="POST" action="{{ route('admin.packages.participants.destroy', [$package, $participant]) }}">@csrf @method('DELETE')<button class="admin-button-secondary !border-[#edc5be] !px-3 !text-[#b33129]" type="submit">Buang</button></form></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#69746f]">Belum ada peserta dimasukkan untuk pakej ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>

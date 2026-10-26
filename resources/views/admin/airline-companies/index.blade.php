<x-layouts.admin heading="Syarikat Penerbangan">
    <section class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Homepage</p>
            <h1 class="mt-1 text-3xl font-bold tracking-[-0.045em] text-[#00251e]">Syarikat Penerbangan</h1>
            <p class="mt-2 text-sm text-[#69746f]">Logo dinormalkan kepada kanvas yang sama dan dipaparkan pada bahagian Rakan Syarikat Penerbangan. Maksimum 20 syarikat.</p>
        </div>
        @if ($airlineCompanies->count() < \App\Models\AirlineCompany::MaximumCompanies)
            <a href="{{ route('admin.airline-companies.create') }}" class="admin-button-primary">+ Tambah syarikat</a>
        @endif
    </section>

    <section class="admin-card mt-7 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left">
                <thead class="bg-[#e9f7ed] text-[10px] font-bold uppercase tracking-[.12em] text-[#65716b]"><tr><th class="px-6 py-4">Logo</th><th class="px-4 py-4">Nama</th><th class="px-4 py-4">Paparan</th><th class="px-4 py-4">Susunan</th><th class="px-6 py-4 text-right">Tindakan</th></tr></thead>
                <tbody class="divide-y divide-[#e8eee9]">
                    @forelse ($airlineCompanies as $airlineCompany)
                        <tr>
                            <td class="px-6 py-3"><span class="grid h-12 w-28 place-items-center rounded-lg bg-[#f6f8f5] p-1"><img src="{{ $airlineCompany->logoUrl() }}" alt="" class="max-h-full max-w-full object-contain"></span></td>
                            <td class="px-4 py-3 font-bold text-[#00251e]">{{ $airlineCompany->name }}</td>
                            <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $airlineCompany->is_published ? 'bg-[#dcf7ed] text-[#006b57]' : 'bg-[#f2f4f2] text-[#66736c]' }}">{{ $airlineCompany->is_published ? 'Diterbitkan' : 'Draf' }}</span></td>
                            <td class="px-4 py-3 text-sm text-[#506058]">{{ $airlineCompany->sort_order }}</td>
                            <td class="px-6 py-3"><div class="flex justify-end gap-2"><a href="{{ route('admin.airline-companies.edit', $airlineCompany) }}" class="admin-button-secondary !px-3">Edit</a><form method="POST" action="{{ route('admin.airline-companies.destroy', $airlineCompany) }}" data-confirm-delete data-confirm-message="Padam syarikat penerbangan ini?">@csrf @method('DELETE')<button class="rounded-lg border border-[#efc2bd] px-3 py-2 text-xs font-bold text-[#b33930] hover:bg-[#fff1ef]" type="submit">Padam</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-[#69746f]">Belum ada syarikat penerbangan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.admin>

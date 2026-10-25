<x-layouts.admin heading="Profil Saya">
    <section>
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Akaun</p>
        <h1 class="mt-1 text-3xl font-bold tracking-[-0.045em] text-[#00251e]">Profil Saya</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#69746f]">Kemas kini nama, e-mel dan kata laluan untuk akaun pentadbir ini.</p>
    </section>

    <form class="mt-7 space-y-7" method="POST" action="{{ route('admin.profile.update') }}">
        @csrf
        @method('PUT')

        <x-errors />

        <section class="admin-card p-6 sm:p-7">
            <div class="flex items-start gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">person</span></span>
                <div>
                    <h2 class="text-xl font-bold text-[#00251e]">Maklumat akaun</h2>
                    <p class="mt-1 text-sm text-[#69746f]">Maklumat ini digunakan untuk mengenal pasti anda di portal pentadbir.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label>
                    <span class="admin-label">Nama</span>
                    <input class="admin-field" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name">
                </label>
                <label>
                    <span class="admin-label">E-mel</span>
                    <input class="admin-field" type="email" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">
                </label>
            </div>
        </section>

        <section class="admin-card p-6 sm:p-7">
            <div class="flex items-start gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">key</span></span>
                <div>
                    <h2 class="text-xl font-bold text-[#00251e]">Tukar kata laluan</h2>
                    <p class="mt-1 text-sm text-[#69746f]">Biarkan kosong jika anda hanya mahu mengemas kini nama atau e-mel. Kata laluan baharu mestilah sekurang-kurangnya 8 aksara.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-3">
                <label>
                    <span class="admin-label">Kata laluan semasa</span>
                    <input class="admin-field" type="password" name="current_password" autocomplete="current-password">
                </label>
                <label>
                    <span class="admin-label">Kata laluan baharu</span>
                    <input class="admin-field" type="password" name="password" autocomplete="new-password">
                </label>
                <label>
                    <span class="admin-label">Sahkan kata laluan baharu</span>
                    <input class="admin-field" type="password" name="password_confirmation" autocomplete="new-password">
                </label>
            </div>
        </section>

        <div class="sticky bottom-0 z-20 flex justify-end rounded-2xl border border-[#dceae1] bg-white/95 p-4 shadow-[0_-10px_30px_-24px_rgba(0,37,30,.4)] backdrop-blur">
            <button class="admin-button-primary" type="submit">Simpan Profil</button>
        </div>
    </form>
</x-layouts.admin>

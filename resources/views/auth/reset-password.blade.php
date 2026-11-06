<x-layouts.app title="Tetapkan Semula Kata Laluan">
    <section class="container-page flex min-h-[70vh] max-w-lg items-center py-12">
        <form method="POST" action="{{ route('admin.password.update') }}" class="admin-card w-full space-y-6 p-7 sm:p-9">@csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div><span class="inline-flex items-center gap-2 rounded-full bg-[#e9f7ed] px-3 py-1.5 text-xs font-bold text-[#006b57]"><span class="material-symbols-outlined text-[16px]">lock_reset</span>Tetapkan semula</span><h1 class="mt-4 text-3xl font-bold tracking-[-.04em] text-[#00251e]">Kata laluan baharu</h1><p class="mt-2 text-sm leading-6 text-[#66746c]">Pilih kata laluan baharu yang kukuh untuk akses pentadbir anda.</p></div>
            <x-errors />
            <label><span class="admin-label">Alamat e-mel</span><input class="admin-field" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="email"></label>
            <label><span class="admin-label">Kata laluan baharu</span><input class="admin-field" type="password" name="password" required autocomplete="new-password"></label>
            <label><span class="admin-label">Sahkan kata laluan baharu</span><input class="admin-field" type="password" name="password_confirmation" required autocomplete="new-password"></label>
            <button class="admin-button-primary w-full" type="submit">Simpan kata laluan baharu</button>
        </form>
    </section>
</x-layouts.app>

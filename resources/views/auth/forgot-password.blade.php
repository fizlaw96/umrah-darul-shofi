<div>
    <!-- The whole future lies in uncertainty: live immediately. - Seneca -->
</div>
<x-layouts.app title="Tetapkan Semula Kata Laluan">
    <section class="container-page flex min-h-[70vh] max-w-lg items-center py-12">
        <form method="POST" action="{{ route('admin.password.email') }}" class="admin-card w-full space-y-6 p-7 sm:p-9">@csrf
            <div><span class="inline-flex items-center gap-2 rounded-full bg-[#e9f7ed] px-3 py-1.5 text-xs font-bold text-[#006b57]"><span class="material-symbols-outlined text-[16px]">key</span>Pemulihan akses</span><h1 class="mt-4 text-3xl font-bold tracking-[-.04em] text-[#00251e]">Lupa kata laluan?</h1><p class="mt-2 text-sm leading-6 text-[#66746c]">Masukkan e-mel pentadbir anda. Jika akaun itu dibenarkan, pautan tetapan semula akan dihantar.</p></div>
            <x-errors />
            @if (session('status'))<p class="rounded-xl bg-[#e9f7ed] px-4 py-3 text-sm text-[#075741]">{{ session('status') }}</p>@endif
            <label><span class="admin-label">Alamat e-mel</span><input class="admin-field" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"></label>
            <button class="admin-button-primary w-full" type="submit">Hantar pautan tetapan semula</button>
            <a href="{{ route('admin.login') }}" class="block text-center text-sm font-bold text-[#006b57] hover:text-[#002f28]">← Kembali ke log masuk</a>
        </form>
    </section>
</x-layouts.app>

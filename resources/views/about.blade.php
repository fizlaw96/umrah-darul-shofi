<x-layouts.app title="Tentang Kami">
    <section class="relative overflow-hidden bg-[#073e34] py-14 text-white sm:py-20">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 1px 1px, #ffdf9a 1px, transparent 0); background-size: 26px 26px;"></div>
        <div class="absolute -right-20 -top-20 size-80 rounded-full bg-[#ffdf9a]/10 blur-3xl"></div>
        <div class="container-page relative">
            <nav class="flex items-center gap-1 text-[10px] font-semibold text-[#c7e4db]"><a href="{{ route('home') }}" class="hover:text-[#ffdf9a]">Utama</a><span class="material-symbols-outlined text-[14px]">chevron_right</span><span class="text-[#ffdf9a]">Tentang Kami</span></nav>
            <div class="mt-6 max-w-3xl">
                <p class="inline-flex items-center gap-2 rounded-md bg-[#00271f] px-3 py-1.5 text-[9px] font-bold uppercase tracking-[0.12em] text-[#ffdf9a]"><span class="material-symbols-outlined text-[15px]">verified_user</span> Mengenai perkhidmatan kami</p>
                <h1 class="mt-4 font-display text-4xl leading-tight sm:text-5xl">Membantu perjalanan Umrah yang lebih jelas dan tenang.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-[#c7e4db]">Kami memudahkan bakal jemaah memahami pilihan pakej, menghantar pertanyaan dan berhubung dengan pihak agensi secara lebih teratur.</p>
            </div>
            <div class="mt-10 grid gap-px overflow-hidden rounded-xl bg-white/10 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['inventory_2', 'Pakej dalam satu tempat', 'Semak pilihan yang diterbitkan'], ['calendar_month', 'Tarikh lebih teratur', 'Rujuk jadual berlepas yang terbuka'], ['assignment', 'Permohonan jelas', 'Hantar minat anda dengan butiran lengkap'], ['support_agent', 'Bantuan pertanyaan', 'Berhubung terus dengan agensi']] as [$icon, $title, $description])
                    <div class="bg-[#002f28]/70 p-5"><span class="material-symbols-outlined text-[21px] text-[#ffdf9a]">{{ $icon }}</span><h2 class="mt-3 text-xs font-bold">{{ $title }}</h2><p class="mt-1 text-[10px] leading-5 text-[#c7e4db]">{{ $description }}</p></div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container-page py-16 sm:py-20">
        <div class="mx-auto max-w-2xl text-center"><p class="section-eyebrow">Falsafah perkhidmatan</p><h2 class="mt-2 font-display text-3xl leading-tight text-dark-green sm:text-4xl">Teras untuk membantu bakal jemaah</h2><p class="mt-3 text-sm leading-6 text-[#5c6461]">Setiap maklumat disusun supaya perjalanan anda dapat dirancang dengan lebih yakin dan mudah difahami.</p></div>
        <div class="mt-10 grid gap-5 lg:grid-cols-3">
            <article class="rounded-xl border border-[#ebe5d9] bg-white p-7"><span class="grid size-11 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[23px]">explore</span></span><h3 class="mt-5 font-display text-2xl text-dark-green">Misi Kami</h3><p class="mt-3 text-xs leading-6 text-[#5c6461]">Memudahkan bakal jemaah mencari dan memahami pilihan perjalanan Umrah dengan maklumat yang kemas serta saluran pertanyaan yang jelas.</p><p class="mt-6 rounded-md bg-[#f5f3ef] px-3 py-2 text-[10px] font-bold text-[#404946]">Telus · Teratur · Membantu</p></article>
            <article class="rounded-xl border border-[#ebe5d9] bg-white p-7"><span class="grid size-11 place-items-center rounded-lg bg-[#f5f3ef] text-[#8b671c]"><span class="material-symbols-outlined text-[23px]">stars</span></span><h3 class="mt-5 font-display text-2xl text-dark-green">Visi Kami</h3><p class="mt-3 text-xs leading-6 text-[#5c6461]">Mewujudkan pengalaman perancangan Umrah yang lebih mudah diakses oleh keluarga Malaysia, dari pencarian pakej hingga proses pertanyaan.</p><p class="mt-6 rounded-md bg-[#f5f3ef] px-3 py-2 text-[10px] font-bold text-[#404946]">Mudah · Mesra · Bertanggungjawab</p></article>
            <article class="rounded-xl bg-[#073e34] p-7 text-white"><span class="grid size-11 place-items-center rounded-lg bg-[#00271f] text-[#ffdf9a]"><span class="material-symbols-outlined text-[23px]">volunteer_activism</span></span><h3 class="mt-5 font-display text-2xl text-[#ffdf9a]">Janji Kami</h3><div class="mt-4 space-y-3 text-[11px] leading-5 text-[#c7e4db]"><p class="flex gap-2"><span class="font-bold text-[#ffdf9a]">01</span>Maklumat pakej dipaparkan dengan ringkas dan jelas.</p><p class="flex gap-2"><span class="font-bold text-[#ffdf9a]">02</span>Permohonan dihantar terus untuk semakan agensi.</p><p class="flex gap-2"><span class="font-bold text-[#ffdf9a]">03</span>Harga dan ketersediaan disahkan sebelum anda membuat keputusan.</p></div></article>
        </div>
    </section>

    <section class="bg-[#f5f3ef] py-16 sm:py-20">
        <div class="container-page">
            <div class="flex flex-col justify-between gap-5 md:flex-row md:items-end"><div class="max-w-2xl"><p class="section-eyebrow">Cara kami membantu</p><h2 class="mt-2 font-display text-3xl leading-tight text-dark-green sm:text-4xl">Dari pencarian hingga pertanyaan</h2><p class="mt-3 text-sm leading-6 text-[#5c6461]">Struktur platform ini direka untuk menjadikan proses awal lebih tenang—tanpa mendesak anda membuat keputusan sebelum bersedia.</p></div><a href="{{ route('contact.create') }}" class="button-secondary shrink-0">Hubungi kami <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a></div>
            <div class="mt-9 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['search', 'Cari dan bandingkan', 'Tapis pilihan mengikut tempoh, tarikh dan bajet.'], ['article', 'Semak butiran', 'Baca ringkasan, penerbangan dan kemudahan pakej.'], ['send', 'Hantar permohonan', 'Kongsi keperluan anda untuk semakan pasukan agensi.'], ['forum', 'Terima maklum balas', 'Berbincang dengan agensi sebelum pengesahan seterusnya.']] as [$icon, $title, $description])
                    <article class="rounded-xl bg-white p-5"><span class="material-symbols-outlined text-[21px] text-[#8b671c]">{{ $icon }}</span><h3 class="mt-5 text-sm font-bold text-dark-green">{{ $title }}</h3><p class="mt-2 text-[11px] leading-5 text-[#5c6461]">{{ $description }}</p></article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="container-page py-16 sm:py-20">
        <div class="grid gap-8 rounded-xl border border-[#ebe5d9] bg-white p-6 sm:p-9 lg:grid-cols-[1fr_.9fr]">
            <div><p class="section-eyebrow">Sedia untuk bermula?</p><h2 class="mt-2 font-display text-3xl leading-tight text-dark-green">Rancang perjalanan anda dengan maklumat yang jelas.</h2><p class="mt-4 max-w-xl text-sm leading-7 text-[#5c6461]">Lihat pakej yang tersedia atau hantar pertanyaan kepada pasukan agensi untuk mendapatkan pandangan awal.</p><div class="mt-6 flex flex-wrap gap-3"><a href="{{ route('packages.index') }}" class="button-primary">Lihat Pakej Umrah <span class="material-symbols-outlined text-[16px]">arrow_forward</span></a><a href="{{ route('contact.create') }}" class="button-secondary">Hubungi Kami</a></div></div>
            <div class="relative min-h-52 overflow-hidden rounded-lg bg-dark-green p-6 text-white"><img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=900&q=80" alt="Jemaah Umrah" class="absolute inset-0 size-full object-cover opacity-35"><div class="absolute inset-0 bg-gradient-to-t from-[#00271f] to-[#00271f]/30"></div><div class="relative flex h-full flex-col justify-end"><span class="material-symbols-outlined text-[26px] text-[#ffdf9a]">mosque</span><p class="mt-3 font-display text-2xl">Langkah yang tenang bermula dengan pilihan yang jelas.</p></div></div>
        </div>
    </section>
</x-layouts.app>

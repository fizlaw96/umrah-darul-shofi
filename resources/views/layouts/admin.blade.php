<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? 'Pentadbiran' }} · {{ $siteSettings['company_name'] }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100">
        <div class="min-h-screen lg:grid lg:grid-cols-[250px_1fr]">
            <aside class="bg-dark-green p-5 text-white lg:min-h-screen">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3"><span class="grid size-10 place-items-center rounded-xl bg-white text-brand"><span class="material-symbols-outlined text-[20px]">mosque</span></span><span class="font-extrabold">{{ $siteSettings['company_name'] }}</span></a>
                <nav class="mt-8 grid gap-1 text-sm font-bold"><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.dashboard') }}">Dashboard</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.packages.index') }}">Pakej</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.departures.index') }}">Jadual Berlepas</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.bookings.index') }}">Tempahan</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.enquiries.index') }}">Pertanyaan</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.articles.index') }}">Artikel</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.faqs.index') }}">Soalan Lazim</a><a class="rounded-xl px-3 py-2.5 hover:bg-white/10" href="{{ route('admin.settings.edit') }}">Tetapan Laman</a></nav>
                <form class="mt-8" method="POST" action="{{ route('admin.logout') }}">@csrf<button class="w-full rounded-xl border border-white/20 px-3 py-2 text-left text-sm font-bold hover:bg-white/10">Log keluar</button></form>
            </aside>
            <main class="min-w-0 p-5 sm:p-8">
                <div class="mx-auto max-w-7xl"><div class="mb-7 flex items-center justify-between gap-4"><div><p class="text-xs font-bold uppercase tracking-wider text-brand">Pentadbiran</p><h1 class="text-2xl font-extrabold text-dark-green">{{ $heading ?? 'Dashboard' }}</h1></div><a class="text-sm font-bold text-brand" href="{{ route('home') }}" target="_blank">Lihat laman →</a></div>@if (session('success'))<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-900">{{ session('success') }}</div>@endif{{ $slot }}</div>
            </main>
        </div>
    </body>
</html>

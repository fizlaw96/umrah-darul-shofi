<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'Pentadbiran' }} · {{ $siteSettings['company_name'] }}</title>
        @if (filled($siteSettings['company_favicon_url']))
            <link rel="icon" type="image/png" sizes="64x64" href="{{ $siteSettings['company_favicon_url'] }}">
        @endif
        @if (filled($siteSettings['company_apple_touch_icon_url']))
            <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteSettings['company_apple_touch_icon_url'] }}">
        @endif
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0,0&display=block" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin.js'])
    </head>
    <body class="admin-shell bg-[#f7f8f6] text-[#163a30]">
        @php
            $user = auth()->user();
            $packageArchivedNotificationType = \App\Notifications\PackageArchived::class;
            $unreadPackageNotificationCount = $user->unreadNotifications()->where('type', $packageArchivedNotificationType)->count();
            $packageNotificationQuery = $user->notifications()->where('type', $packageArchivedNotificationType);
            $packageNotifications = $unreadPackageNotificationCount
                ? $packageNotificationQuery->orderByRaw('read_at IS NULL DESC')->latest()->limit(5)->get()
                : $packageNotificationQuery->latest()->limit(1)->get();
        @endphp
        <div data-admin-backdrop class="fixed inset-0 z-40 hidden bg-[#00251e]/35 lg:hidden"></div>
        <aside data-admin-sidebar class="fixed inset-y-0 left-0 z-50 flex w-[278px] -translate-x-full flex-col border-r border-[#e6ece7] bg-white transition-transform duration-300 lg:translate-x-0">
            <div class="flex items-center justify-between px-6 pb-6 pt-7">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    @if (filled($siteSettings['company_logo_url']))
                        <span class="grid size-10 place-items-center overflow-hidden rounded-xl border border-[#d8e6dc] bg-white"><x-responsive-image :src="$siteSettings['company_logo_url']" sizes="40px" alt="" class="size-full object-contain p-1" /></span>
                    @else
                        <span class="grid size-10 place-items-center rounded-xl bg-[#003d32] text-[#f7d687]"><span class="material-symbols-outlined text-[22px]">mosque</span></span>
                    @endif
                    <span><span class="block text-sm font-bold tracking-[-0.03em] text-[#00251e]">{{ $siteSettings['company_name'] }}</span><span class="block text-[10px] font-bold tracking-[0.13em] text-[#00705b]">{{ $siteSettings['company_tagline'] ?: 'TRAVEL & TOURS' }}</span></span>
                </a>
                <button type="button" data-admin-menu-close class="grid size-9 place-items-center rounded-lg text-[#506058] hover:bg-[#e9f7ed] lg:hidden" aria-label="Tutup menu"><span class="material-symbols-outlined">close</span></button>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 pb-6" aria-label="Navigasi pentadbir">
                <p class="mb-2 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">UTAMA</p>
                <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">grid_view</span>Dashboard</a>

                <p class="mb-2 mt-7 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">PAKEJ</p>
                <a href="{{ route('admin.packages.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.packages.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">luggage</span>Senarai Pakej</a>
                <a href="{{ route('admin.package-categories.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.package-categories.*', 'admin.hero-slides.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">category</span>Kategori &amp; Hero</a>

                <p class="mb-2 mt-7 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">MEDIA</p>
                <a href="{{ route('admin.media.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.media.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">photo_library</span>Pengurusan Galeri</a>
                <a href="{{ route('admin.media-social.edit') }}" class="admin-sidebar-link {{ request()->routeIs('admin.media-social.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">smart_display</span>Video &amp; Media Sosial</a>

                <p class="mb-2 mt-7 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">MAKLUMAT</p>
                <a href="{{ route('admin.testimonials.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.testimonials.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">format_quote</span>Testimoni</a>
                <a href="{{ route('admin.faqs.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.faqs.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">help</span>FAQ</a>
                <a href="{{ route('admin.mutawifs.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.mutawifs.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">groups</span>Mutawif</a>
                <a href="{{ route('admin.airline-companies.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.airline-companies.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">flight</span>Syarikat Penerbangan</a>

                <p class="mb-2 mt-7 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">PERKHIDMATAN</p>
                <a href="{{ route('admin.transport-settings.edit') }}" class="admin-sidebar-link {{ request()->routeIs('admin.transport-settings.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">airport_shuttle</span>Transport</a>
                <a href="{{ route('admin.aqiqah-korban-services.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.aqiqah-korban-services.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">volunteer_activism</span>Aqiqah &amp; Korban</a>
                <a href="{{ route('admin.service-enquiries.index') }}" class="admin-sidebar-link {{ request()->routeIs('admin.service-enquiries.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">inbox</span>Permintaan Servis</a>

                <p class="mb-2 mt-7 px-2 text-[10px] font-bold tracking-[0.1em] text-[#69746f]">TETAPAN</p>
                <a href="{{ route('admin.settings.edit') }}" class="admin-sidebar-link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}"><span class="material-symbols-outlined text-[19px]">tune</span>Tetapan Website</a>
            </nav>

            <div class="border-t border-[#e6ece7] p-4">
                <form method="POST" action="{{ route('admin.logout') }}" class="mt-1">@csrf<button class="admin-sidebar-link w-full text-left text-[#be332b] hover:bg-[#fff1ef] hover:text-[#a5241d]"><span class="material-symbols-outlined text-[19px]">logout</span>Log Keluar</button></form>
            </div>
        </aside>

        <div class="min-h-screen lg:pl-[278px]">
            <header class="sticky top-0 z-30 flex h-[72px] items-center justify-between border-b border-[#ecf0ed] bg-white/95 px-5 backdrop-blur sm:px-8">
                <div class="flex items-center gap-4"><button type="button" data-admin-menu-toggle class="grid size-10 place-items-center rounded-lg border border-[#e1e9e3] text-[#00251e] lg:hidden" aria-label="Buka menu" aria-expanded="false"><span class="material-symbols-outlined">menu</span></button><p class="hidden text-xs font-medium text-[#69746f] sm:block">Portal Pentadbir <span class="mx-2 text-[#a4aea8]">/</span><span class="font-bold text-[#163a30]">{{ $heading ?? 'Panel Kawalan' }}</span></p></div>
                <div class="flex items-center gap-3 sm:gap-5">
                    <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="hidden items-center gap-1.5 rounded-full bg-[#e9f7ed] px-4 py-2 text-xs font-bold text-[#006b57] transition hover:bg-[#d8f0e0] sm:inline-flex"><span class="material-symbols-outlined text-[16px]">public</span>Lihat Website <span aria-hidden="true">↗</span></a>
                    <div data-notification-menu class="relative border-r border-[#e2eae4] pr-3">
                        <button type="button" data-notification-toggle class="relative grid size-9 place-items-center rounded-lg text-[#5e6a64] transition hover:bg-[#e9f7ed] hover:text-[#006b57] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]" aria-label="{{ $unreadPackageNotificationCount ? $unreadPackageNotificationCount.' pemberitahuan baharu' : 'Tiada pemberitahuan baharu' }}" aria-expanded="false" aria-controls="package-notifications">
                            <span class="material-symbols-outlined text-[20px]">notifications</span>
                            @if ($unreadPackageNotificationCount)
                                <span class="absolute -right-0.5 -top-0.5 grid size-4 place-items-center rounded-full bg-[#be332b] text-[9px] font-bold text-white">{{ $unreadPackageNotificationCount > 9 ? '9+' : $unreadPackageNotificationCount }}</span>
                            @endif
                        </button>
                        <div id="package-notifications" data-notification-panel class="absolute right-0 top-full z-50 mt-3 w-[min(22rem,calc(100vw-2.5rem))] overflow-hidden rounded-2xl border border-[#dce9df] bg-white shadow-[0_18px_45px_-20px_rgba(0,37,30,.45)]" hidden>
                            <div class="flex items-center justify-between gap-3 border-b border-[#e8eee9] px-4 py-3">
                                <p class="text-sm font-bold text-[#00251e]">Pemberitahuan</p>
                                <div class="flex items-center gap-3">
                                    @if ($unreadPackageNotificationCount)
                                        <form method="POST" action="{{ route('admin.package-notifications.mark-all-as-read') }}">
                                            @csrf
                                            <button type="submit" class="text-[10px] font-bold text-[#006b57] transition hover:text-[#004d3f] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]">Tandakan semua dibaca</button>
                                        </form>
                                    @endif
                                    <span class="whitespace-nowrap text-[10px] font-bold uppercase tracking-[.08em] text-[#006b57]">{{ $unreadPackageNotificationCount ? $unreadPackageNotificationCount.' baharu' : 'Tiada baharu' }}</span>
                                </div>
                            </div>
                            <div class="max-h-96 overflow-y-auto">
                                @forelse ($packageNotifications as $notification)
                                    <a href="{{ route('admin.package-notifications.show', $notification->id) }}" class="flex gap-3 border-b border-[#edf1ee] px-4 py-3.5 transition hover:bg-[#f5faf6] {{ is_null($notification->read_at) ? 'bg-[#e9f7ed]' : '' }}">
                                        <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white text-[#006b57] shadow-sm"><span class="material-symbols-outlined text-[18px]">inventory_2</span></span>
                                        <span class="min-w-0"><span class="block text-xs font-bold leading-5 text-[#163a30]">{{ $notification->data['message'] ?? 'Pakej telah diarkibkan.' }}</span><span class="mt-1 block text-[10px] font-medium text-[#718078]">{{ $notification->created_at->format('d/m/Y, H:i') }}</span></span>
                                    </a>
                                @empty
                                    <p class="px-4 py-8 text-center text-xs leading-5 text-[#718078]">Belum ada pakej yang diarkibkan.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-2.5 rounded-lg px-1.5 py-1 transition hover:bg-[#e9f7ed] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#006b57]" aria-label="Buka profil saya"><span class="grid size-9 place-items-center rounded-full bg-[#002f28] text-white"><span class="material-symbols-outlined text-[19px]">person</span></span><span class="hidden leading-4 sm:block"><span class="block text-xs font-bold text-[#153a30]">{{ $user?->name }}</span><span class="block text-[10px] font-medium text-[#69746f]">{{ $user?->role === 'super_admin' ? 'Admin Utama' : 'Pentadbir' }}</span></span></a>
                </div>
            </header>

            <main class="min-w-0 px-5 py-7 sm:px-8 lg:px-10">
                <div class="mx-auto max-w-[1560px]">
                    @if (session('success'))<div class="mb-6 flex items-center gap-2 rounded-xl border border-[#bce6cd] bg-[#e9f7ed] px-4 py-3 text-sm font-semibold text-[#075741]" role="status"><span class="material-symbols-outlined text-[18px]">check_circle</span>{{ session('success') }}</div>@endif
                    @if (session('status'))<div class="mb-6 flex items-center gap-2 rounded-xl border border-[#bce6cd] bg-[#e9f7ed] px-4 py-3 text-sm font-semibold text-[#075741]" role="status"><span class="material-symbols-outlined text-[18px]">info</span>{{ session('status') }}</div>@endif
                    @if (session('error'))<div class="mb-6 flex items-center gap-2 rounded-xl border border-[#f4c9c4] bg-[#fff1ef] px-4 py-3 text-sm font-semibold text-[#9b3029]" role="alert"><span class="material-symbols-outlined text-[18px]">error</span>{{ session('error') }}</div>@endif
                    {{ $slot }}
                </div>
            </main>
        </div>
        <script type="application/json" data-image-focus-positions>@json($imageFocusPositions)</script>
        <dialog data-image-focus-dialog data-image-focus-save-url="{{ route('admin.image-focus.update') }}" class="m-auto max-h-[calc(100dvh-2rem)] w-[min(94vw,46rem)] max-w-none flex-col overflow-hidden rounded-2xl border border-[#dbe8df] bg-white p-0 text-[#163a30] shadow-2xl open:flex backdrop:bg-[#00251e]/70">
            <div class="flex shrink-0 items-start justify-between gap-4 border-b border-[#e8eee9] px-5 py-4 sm:px-6">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[.14em] text-[#00705b]">Paparan imej</p>
                    <h2 class="mt-1 text-lg font-bold text-[#00251e]">Laraskan fokus gambar</h2>
                    <p class="mt-1 text-xs leading-5 text-[#718078]">Tarik titik fokus pada gambar atau gunakan pelaras. Fail asal tidak dipotong.</p>
                </div>
                <button type="button" data-image-focus-close class="grid size-9 place-items-center rounded-lg text-[#506058] transition hover:bg-[#f2f5f2]" aria-label="Tutup pelaras fokus gambar"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto p-5 sm:p-6">
                <div class="space-y-5">
                    <div data-image-focus-stage class="relative aspect-[4/3] cursor-crosshair overflow-hidden rounded-xl bg-[#e9f2eb] touch-none">
                        <img data-image-focus-preview src="" alt="Paparan fokus gambar" class="size-full select-none object-cover" draggable="false">
                        <span data-image-focus-marker class="pointer-events-none absolute grid size-9 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full border-2 border-white bg-[#006b57]/80 text-white shadow-lg"><span class="material-symbols-outlined text-[19px]">center_focus_strong</span></span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label><span class="admin-label">Kedudukan mendatar</span><input data-image-focus-x class="mt-2 w-full accent-[#006b57]" type="range" min="0" max="100" value="50"></label>
                        <label><span class="admin-label">Kedudukan menegak</span><input data-image-focus-y class="mt-2 w-full accent-[#006b57]" type="range" min="0" max="100" value="50"></label>
                    </div>
                    <p data-image-focus-status class="min-h-5 text-xs font-semibold text-[#006b57]" aria-live="polite"></p>
                </div>
            </div>
            <div class="flex shrink-0 flex-wrap justify-end gap-2 border-t border-[#e8eee9] bg-white px-5 py-4 sm:px-6">
                <button type="button" data-image-focus-reset class="admin-button-secondary !px-3">Set semula</button>
                <button type="button" data-image-focus-save class="admin-button-primary !px-4"><span class="material-symbols-outlined text-[18px]">save</span>Simpan fokus</button>
            </div>
        </dialog>
        @stack('scripts')
    </body>
</html>

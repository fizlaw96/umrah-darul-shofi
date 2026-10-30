<!DOCTYPE html>
<html lang="ms">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Log Masuk Pentadbir · {{ $siteSettings['company_name'] }}</title>
        @if (filled($siteSettings['company_favicon_url']))
            <link rel="icon" type="image/png" sizes="64x64" href="{{ $siteSettings['company_favicon_url'] }}">
        @endif
        @if (filled($siteSettings['company_apple_touch_icon_url']))
            <link rel="apple-touch-icon" sizes="180x180" href="{{ $siteSettings['company_apple_touch_icon_url'] }}">
        @endif
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-shell min-h-screen overflow-x-hidden bg-[#f7f8f5] text-[#163a30]">
        <div class="pointer-events-none fixed inset-0 overflow-hidden" aria-hidden="true">
            <img src="{{ asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png') }}" alt="" class="size-full object-cover object-center opacity-[0.10] grayscale">
            <div class="absolute inset-0 bg-[#f7f8f5]/85"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_30%,transparent_0,rgba(247,248,245,.18)_44%,rgba(247,248,245,.96)_100%)]"></div>
        </div>

        <main class="relative z-10 mx-auto flex min-h-screen max-w-[1380px] items-center px-4 py-3 sm:px-8 sm:py-5 lg:h-dvh lg:min-h-0">
            <section class="mx-auto grid w-full max-w-[1316px] overflow-hidden rounded-[30px] bg-white shadow-[0_32px_80px_-38px_rgba(0,37,30,.40)] lg:h-[min(740px,calc(100dvh-2.5rem))] lg:grid-cols-[1.3fr_1fr]">
                <aside class="relative hidden min-h-[740px] overflow-hidden bg-[#003d32] px-11 py-8 text-white lg:flex lg:min-h-0 lg:flex-col xl:px-[60px]">
                    <img src="{{ asset('darul-shofi/images/hero/umrah-premium-makkah-hero.png') }}" alt="" class="absolute inset-0 size-full object-cover object-[58%_center] opacity-80" aria-hidden="true">
                    <div class="absolute inset-0 bg-[linear-gradient(180deg,rgba(0,58,47,.97)_0%,rgba(0,59,48,.90)_50%,rgba(0,54,43,.42)_100%)]"></div>
                    <div class="absolute inset-0 opacity-25 [background-image:radial-gradient(rgba(122,223,184,.58)_1px,transparent_1px)] [background-size:24px_24px]"></div>
                    <div class="absolute bottom-[-152px] right-[-156px] h-[530px] w-[430px] rounded-t-[245px] border-[3px] border-[#d9ad49]/75"></div>
                    <div class="absolute bottom-[-142px] right-[-128px] h-[468px] w-[370px] rounded-t-[215px] border border-[#6dccac]/30"></div>

                    <div class="relative flex items-center gap-4">
                        <span class="grid size-[86px] shrink-0 place-items-center overflow-hidden rounded-2xl bg-white p-2 shadow-[0_12px_28px_rgba(0,0,0,.18)]">
                            @if (filled($siteSettings['company_logo_url']))
                                <img src="{{ $siteSettings['company_logo_url'] }}" alt="" class="size-full object-contain">
                            @else
                                <span class="material-symbols-outlined text-[38px] text-[#007c68]">mosque</span>
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="block text-[23px] font-bold leading-tight tracking-[-.025em] text-white">{{ $siteSettings['company_name'] }}</span>
                            <span class="mt-1 block text-sm font-medium tracking-[.04em] text-[#bce9d8]">{{ $siteSettings['company_tagline'] ?: 'Umrahku & Hajimu Yang Teristimewa' }}</span>
                        </span>
                    </div>

                    <div class="relative my-auto max-w-[30rem] pt-10">
                        <span class="inline-flex items-center gap-3 rounded-full border border-[#f0c968] bg-[#003f33]/75 px-5 py-3 text-xs font-bold tracking-[.08em] text-[#ffe195] shadow-sm backdrop-blur-sm">
                            <span class="material-symbols-outlined text-[20px]">verified_user</span>
                            PORTAL KORPORAT
                        </span>
                        <h1 class="mt-9 font-display text-[48px] leading-[1.16] tracking-[-.035em] text-white xl:text-[54px]">
                            <span class="block">Pengurusan Website</span>
                            <span class="block text-[#f7d687]">{{ $siteSettings['company_name'] }}</span>
                        </h1>
                        <span class="mt-7 block h-1.5 w-12 rounded-full bg-[#f7d687]"></span>
                        <p class="mt-5 max-w-[25rem] text-lg leading-8 text-[#e1f1e9]">Urus pakej Umrah, media dan kandungan rasmi daripada satu ruang pentadbiran yang selamat dan teratur.</p>
                    </div>

                    <div class="relative max-w-[30rem] rounded-[22px] border border-[#91d8bd]/45 bg-[#003d32]/75 p-5 shadow-[0_12px_28px_rgba(0,0,0,.14)] backdrop-blur-sm">
                        <div class="flex gap-4">
                            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-[#e5c26c] text-[#244a3d] shadow-[0_6px_14px_rgba(0,0,0,.18)]">
                                <span class="material-symbols-outlined text-[25px]">verified_user</span>
                            </span>
                            <div>
                                <p class="font-bold text-white">Akses Terkawal &amp; Selamat</p>
                                <p class="mt-1 text-sm leading-6 text-[#d1e9df]">Gunakan akaun pentadbir yang diluluskan untuk mengemas kini kandungan sistem.</p>
                            </div>
                        </div>
                    </div>
                </aside>

                <div class="flex min-h-[620px] items-center bg-white px-5 py-5 sm:px-12 sm:py-8 lg:min-h-0 lg:px-11 xl:px-[70px]">
                    <form method="POST" action="{{ route('admin.login.store') }}" class="mx-auto w-full max-w-[450px] space-y-3 sm:space-y-4">
                        @csrf

                        <div>
                            <span class="inline-flex items-center gap-2.5 rounded-full bg-[#eaf7ef] px-4 py-2.5 text-[13px] font-bold text-[#096541] sm:gap-3 sm:px-5 sm:py-3 sm:text-sm">
                                <span class="material-symbols-outlined text-[22px]">admin_panel_settings</span>
                                Pengesahan Pentadbir
                            </span>
                            <h2 class="mt-6 font-display text-[44px] leading-[.98] tracking-[-.04em] text-[#002f28] sm:mt-8 sm:text-[54px]">Selamat Kembali</h2>
                            <p class="mt-3 max-w-[25rem] text-[16px] leading-6 text-[#697a82] sm:mt-4 sm:text-[17px] sm:leading-7">Sila log masuk untuk mengurus website {{ $siteSettings['company_name'] }}.</p>
                        </div>

                        <x-errors />

                        @if (session('status'))
                            <p class="rounded-xl bg-[#eaf7ef] px-4 py-3 text-sm text-[#075741]">{{ session('status') }}</p>
                        @endif

                        <label class="block">
                            <span class="admin-label !mb-2 !text-[13px] !text-[#364b53]">
                                Alamat E-mel Rasmi
                                <span class="font-medium text-[#89969e]">Kakitangan {{ $siteSettings['company_name'] }}</span>
                            </span>
                            <span class="relative block">
                                <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[24px] text-[#6d7e89]">mail</span>
                                <input class="admin-field !rounded-xl !border-[#e1e6e9] !bg-[#f5f7f8] !py-4 !pl-12 focus:!bg-white" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
                            </span>
                        </label>

                        <label class="block">
                            <span class="admin-label !mb-2 !text-[13px] !text-[#364b53]">
                                Kata Laluan
                                <span class="font-medium text-[#89969e]">Min. 8 aksara</span>
                            </span>
                            <span class="relative block">
                                <span class="material-symbols-outlined pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-[24px] text-[#6d7e89]">lock</span>
                                <input id="admin-password" class="admin-field !rounded-xl !border-[#e1e6e9] !bg-[#f5f7f8] !py-4 !pl-12 !pr-12 focus:!bg-white" type="password" name="password" required autocomplete="current-password">
                                <button type="button" data-password-toggle data-password-target="admin-password" class="absolute right-4 top-1/2 grid -translate-y-1/2 place-items-center text-[#6d7e89] transition hover:text-[#003d32]" aria-label="Papar kata laluan">
                                    <span class="material-symbols-outlined text-[25px]">visibility</span>
                                </button>
                            </span>
                        </label>

                        <div class="flex items-center justify-between gap-4">
                            <label class="flex cursor-pointer items-center gap-2.5 text-sm font-medium text-[#40514a]">
                                <input class="size-5 rounded accent-[#007c68]" type="checkbox" name="remember" value="1" @checked(old('remember', true))>
                                Kekal log masuk pada peranti ini
                            </label>
                            <a href="{{ route('admin.password.request') }}" class="text-sm font-bold text-[#007c68] transition hover:text-[#002f28]">Lupa Kata Laluan?</a>
                        </div>

                        <button class="admin-button-primary w-full !rounded-[14px] !py-[17px] !text-[16px]" type="submit">
                            Log Masuk ke Dashboard
                            <span class="material-symbols-outlined text-[24px]">arrow_forward</span>
                        </button>

                        <div class="flex items-center gap-3 py-1 text-sm text-[#65737a]" aria-hidden="true">
                            <span class="h-px flex-1 bg-[#dce3e2]"></span>
                            atau
                            <span class="h-px flex-1 bg-[#dce3e2]"></span>
                        </div>

                        <div class="flex gap-4 rounded-2xl bg-[#eaf7ef] px-5 py-4 text-[#4f6366]">
                            <span class="grid size-12 shrink-0 place-items-center rounded-full bg-[#cfeee0] text-[#08704c]">
                                <span class="material-symbols-outlined text-[27px]">shield</span>
                            </span>
                            <div>
                                <p class="text-sm font-bold text-[#08704c]">Jangan kongsi kata laluan anda.</p>
                                <p class="mt-1 text-xs leading-5">Sebarang akses tanpa kebenaran perlu dilaporkan kepada pentadbir sistem.</p>
                            </div>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>

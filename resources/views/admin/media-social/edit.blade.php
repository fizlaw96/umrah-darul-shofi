<x-layouts.admin heading="Video & Media Sosial">
    <section>
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#006b57]">Media</p>
        <h1 class="mt-1 text-3xl font-bold tracking-[-0.045em] text-[#00251e]">Video &amp; Media Sosial</h1>
        <p class="mt-2 max-w-3xl text-sm leading-6 text-[#69746f]">Urus satu video panduan utama dan pautan akaun rasmi. Hero dan Galeri diurus melalui modul masing-masing.</p>
    </section>

    <form class="mt-7 space-y-7" method="POST" action="{{ route('admin.media-social.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <x-errors />

        @php($hasFeaturedVideo = filled($featuredVideoUploadUrl) || filled($settings['featured_video_url']))

        <section data-featured-video-form class="admin-card p-6 sm:p-7">
            <div class="flex items-start gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">smart_display</span></span>
                <div>
                    <h2 class="text-xl font-bold text-[#00251e]">Video panduan utama</h2>
                    <p class="mt-1 text-sm text-[#69746f]">Pilih satu sumber sahaja: pautan YouTube atau fail video MP4/WebM. Thumbnail tidak diperlukan.</p>
                </div>
            </div>

            <input data-featured-video-remove-input type="hidden" name="remove_featured_video" value="">

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label><span class="admin-label">Tajuk video</span><input class="admin-field" name="featured_video_title" value="{{ old('featured_video_title', $settings['featured_video_title']) }}"></label>
            </div>

            <div data-featured-video-current data-featured-video-present="{{ $hasFeaturedVideo ? 'true' : 'false' }}" class="relative mt-5 overflow-hidden rounded-xl border border-[#dce9df] bg-[#f8fbf8] {{ $hasFeaturedVideo ? '' : 'hidden' }}">
                @if ($featuredVideoUploadUrl)
                    <video class="aspect-video w-full bg-[#00251e] object-cover" controls preload="metadata"><source src="{{ $featuredVideoUploadUrl }}"></video>
                @elseif ($featuredVideoEmbedUrl)
                    <iframe class="aspect-video w-full bg-[#00251e]" src="{{ $featuredVideoEmbedUrl }}" title="{{ $settings['featured_video_title'] ?: 'Video panduan semasa' }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                @elseif (filled($settings['featured_video_url']))
                    <p class="flex aspect-video items-center justify-center bg-[#00251e] p-6 text-center text-sm font-bold text-white">Pautan video sedia ada tidak lagi disokong. Keluarkan untuk memilih sumber baharu.</p>
                @endif
                <button type="button" data-featured-video-remove class="absolute right-3 top-3 grid size-9 place-items-center rounded-full bg-white/95 text-[#b33129] shadow transition hover:bg-[#fff0ef]" aria-label="Keluarkan video semasa"><span class="material-symbols-outlined text-[20px]">close</span></button>
            </div>

            <div data-featured-video-sources class="mt-5 grid gap-5 md:grid-cols-2 {{ $hasFeaturedVideo ? 'opacity-60' : '' }}">
                <label>
                    <span class="admin-label">Pautan YouTube</span>
                    <input data-featured-video-url class="admin-field" type="url" name="featured_video_url" value="{{ old('featured_video_url') }}" placeholder="https://www.youtube.com/watch?v=..." @disabled($hasFeaturedVideo)>
                </label>
                <label>
                    <span class="admin-label">Muat naik video</span>
                    <input data-featured-video-file class="admin-field text-xs" type="file" name="featured_video_file" accept="video/mp4,video/webm" @disabled($hasFeaturedVideo)>
                </label>
            </div>

            <p data-featured-video-status class="mt-3 text-xs leading-5 text-[#718078]">{{ $hasFeaturedVideo ? 'Tekan × pada video semasa untuk membuangnya sebelum memilih video baharu.' : 'Masukkan pautan YouTube atau pilih fail MP4/WebM (maksimum 100MB). Pilih satu sahaja.' }}</p>
        </section>

        <section class="admin-card p-6 sm:p-7">
            <div class="flex items-start gap-3">
                <span class="grid size-10 place-items-center rounded-xl bg-[#e9f7ed] text-[#006b57]"><span class="material-symbols-outlined">share</span></span>
                <div>
                    <h2 class="text-xl font-bold text-[#00251e]">Akaun media sosial rasmi</h2>
                    <p class="mt-1 text-sm text-[#69746f]">Masukkan URL profil atau pengecam akaun seperti <code>@salamhaji</code>. Pautan profil kekal dibuka pada tab baharu daripada laman utama.</p>
                </div>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label><span class="admin-label">Instagram</span><input class="admin-field" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url']) }}" placeholder="https://instagram.com/... atau @akaun"></label>
                <label><span class="admin-label">TikTok</span><input class="admin-field" name="tiktok_url" value="{{ old('tiktok_url', $settings['tiktok_url']) }}" placeholder="https://tiktok.com/... atau @akaun"></label>
                <label><span class="admin-label">Facebook</span><input class="admin-field" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url']) }}" placeholder="https://facebook.com/... atau akaun"></label>
                <label><span class="admin-label">Threads</span><input class="admin-field" name="threads_url" value="{{ old('threads_url', $settings['threads_url']) }}" placeholder="https://threads.com/... atau @akaun"></label>
            </div>
        </section>

        <div class="sticky bottom-0 z-20 flex justify-end rounded-2xl border border-[#dceae1] bg-white/95 p-4 shadow-[0_-10px_30px_-24px_rgba(0,37,30,.4)] backdrop-blur">
            <button class="admin-button-primary" type="submit">Simpan Video &amp; Media Sosial</button>
        </div>
    </form>
</x-layouts.admin>

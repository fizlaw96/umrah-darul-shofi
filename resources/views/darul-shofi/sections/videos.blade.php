@php($featuredVideoUploadUrl = $siteSettings['featured_video_upload_url'])
@php($featuredVideoEmbedUrl = $siteSettings['featured_video_embed_url'])
@php($hasFeaturedVideo = filled($featuredVideoUploadUrl) || filled($featuredVideoEmbedUrl))

<section class="ds-media-section" id="panduan">
    <div class="ds-media-section__inner">
        <div class="ds-media-section__heading">
            <span>Bimbingan Video &amp; Media</span>
            <h2>Tips &amp; Panduan Ibadah Umrah</h2>
        </div>

        <div class="ds-media-section__layout">
            @if ($hasFeaturedVideo)
                @if ($featuredVideoUploadUrl)
                    <div class="ds-featured-video ds-featured-video--player">
                        <video class="ds-featured-video__frame" controls preload="metadata"><source src="{{ $featuredVideoUploadUrl }}"></video>
                    </div>
                @elseif ($featuredVideoEmbedUrl)
                    <div class="ds-featured-video ds-featured-video--player">
                        <iframe class="ds-featured-video__frame" src="{{ $featuredVideoEmbedUrl }}" title="{{ $siteSettings['featured_video_title'] ?: 'Video panduan SalamHaji' }}" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                    </div>
                @endif
            @else
                <div class="ds-featured-video ds-featured-video--empty">
                    <div>
                        <i class="fa-solid fa-circle-play"></i>
                        <p>Video panduan akan dipaparkan selepas dikonfigurasi oleh pentadbir.</p>
                    </div>
                </div>
            @endif

            <aside class="ds-social-panel">
                <h3>Ikuti media sosial rasmi</h3>
                <p>Dapatkan kemas kini melalui akaun rasmi SalamHaji.</p>

                <div class="ds-social-list">
                    @foreach (['instagram_url' => ['Instagram', 'instagram'], 'tiktok_url' => ['TikTok', 'tiktok'], 'facebook_url' => ['Facebook', 'facebook-f'], 'threads_url' => ['Threads', 'threads']] as $key => [$label, $icon])
                        @if (filled($siteSettings[$key]))
                            @php($profileName = \App\Models\WebsiteSetting::socialProfileName($siteSettings[$key]))
                            <a class="ds-social-row" href="{{ $siteSettings[$key] }}" target="_blank" rel="noopener noreferrer">
                                <i class="fa-brands fa-{{ $icon }}"></i>
                                <span>{{ $profileName ?: $label }}</span>
                                <b>↗</b>
                            </a>
                        @else
                            <span class="ds-social-row ds-social-row--empty" aria-label="{{ $label }} akan dikemas kini">
                                <i class="fa-brands fa-{{ $icon }}"></i>
                                <span class="sr-only">{{ $label }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </aside>
        </div>
    </div>
</section>

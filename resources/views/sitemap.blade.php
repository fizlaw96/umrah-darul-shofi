<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('packages.index') }}</loc></url>
    <url><loc>{{ route('about') }}</loc></url>
    <url><loc>{{ route('gallery.index') }}</loc></url>
    <url><loc>{{ route('faq') }}</loc></url>
    <url><loc>{{ route('mutawifs.index') }}</loc></url>
    <url><loc>{{ route('terms') }}</loc></url>
    <url><loc>{{ route('transport.show') }}</loc></url>
    <url><loc>{{ route('aqiqah-korban.show') }}</loc></url>
    @foreach ($packages as $package)
        <url><loc>{{ route('packages.show', $package->slug) }}</loc><lastmod>{{ $package->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>

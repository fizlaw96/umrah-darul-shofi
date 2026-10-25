<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url><loc>{{ route('home') }}</loc></url>
    <url><loc>{{ route('packages.index') }}</loc></url>
    <url><loc>{{ route('about') }}</loc></url>
    @foreach ($packages as $package)
        <url><loc>{{ route('packages.show', $package->slug) }}</loc><lastmod>{{ $package->updated_at->toAtomString() }}</lastmod></url>
    @endforeach
</urlset>

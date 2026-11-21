<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('serves uploaded files through the application media endpoint', function () {
    Storage::fake('public', ['url' => '/media']);

    $path = UploadedFile::fake()->image('poster-umrah.jpg', 1200, 800)->store('hero', 'public');
    $url = Storage::disk('public')->url($path);

    expect($url)->toStartWith('/media/hero/');

    $this->get($url)
        ->assertOk()
        ->assertHeader('accept-ranges', 'bytes')
        ->assertHeader('content-type', 'image/jpeg')
        ->assertHeader('x-content-type-options', 'nosniff');
});

it('serves media without session cookies so shared caches can store it', function () {
    Storage::fake('public', ['url' => '/media']);

    $path = UploadedFile::fake()->image('galeri.jpg', 800, 600)->store('media/gallery', 'public');

    $response = $this->get(Storage::disk('public')->url($path))->assertOk();

    expect($response->headers->getCookies())->toBeEmpty()
        ->and($response->headers->get('cache-control'))->toContain('public')
        ->and($response->headers->get('cache-control'))->toContain('max-age=31536000');
});

it('answers conditional media requests with not modified', function () {
    Storage::fake('public', ['url' => '/media']);

    $path = UploadedFile::fake()->image('galeri.jpg', 800, 600)->store('media/gallery', 'public');
    $url = Storage::disk('public')->url($path);
    $lastModified = $this->get($url)->headers->get('last-modified');

    expect($lastModified)->not->toBeNull();

    $this->get($url, ['If-Modified-Since' => $lastModified])->assertStatus(304);
});

it('returns 404 when the requested media file does not exist', function () {
    $this->get('/media/hero/missing.jpg')->assertNotFound();
});

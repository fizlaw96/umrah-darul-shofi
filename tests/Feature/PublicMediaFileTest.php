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
        ->assertHeader('content-type', 'image/jpeg')
        ->assertHeader('x-content-type-options', 'nosniff');
});

it('returns 404 when the requested media file does not exist', function () {
    $this->get('/media/hero/missing.jpg')->assertNotFound();
});

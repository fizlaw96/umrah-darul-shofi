<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('returns 404 for the retired public guide page', function () {
    $this->get('/panduan')->assertNotFound();
});

it('does not publish guide URLs in the sitemap', function () {
    $this->get('/sitemap.xml')
        ->assertOk()
        ->assertDontSee('/panduan', false);
});

<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('lists every public static page in the sitemap', function () {
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('home'), false)
        ->assertSee(route('packages.index'), false)
        ->assertSee(route('about'), false)
        ->assertSee(route('gallery.index'), false)
        ->assertSee(route('faq'), false)
        ->assertSee(route('mutawifs.index'), false)
        ->assertSee(route('terms'), false);
});

<?php

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders the Darul Shofi homepage with its standalone assets', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertViewIs('darul-shofi.home')
        ->assertSee('darul-shofi/css/site.css', false)
        ->assertSee('darul-shofi/js/site.js', false);
});

it('renders the mobile menu, cinematic package carousel, and gallery lightbox', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-mobile-menu-toggle', false)
        ->assertSee('data-hero-carousel', false)
        ->assertSee('data-hero-video', false)
        ->assertSee('data-hero-slide', false)
        ->assertSee('mixkit-mecca-clock-tower-and-the-kaaba-at-night-42405-large.mp4', false)
        ->assertSee('Pakej Ekonomi')
        ->assertSee('Pakej Premium')
        ->assertSee('Pakej Ramadan')
        ->assertSee('Lihat Pakej')
        ->assertDontSee('data-hero-slider', false)
        ->assertDontSee('data-hero-prev', false)
        ->assertSee('id="darul-gallery-lightbox"', false);
});

it('falls back to the contact section when WhatsApp is not configured', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sila hubungi kami')
        ->assertDontSee('https://wa.me/', false);
});

it('uses the configured Darul Shofi WhatsApp number', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    config()->set('darul_shofi.whatsapp_display', '+60 12-345 6789');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('https://wa.me/60123456789?text=', false)
        ->assertSee('Hotline WhatsApp: +60 12-345 6789');
});

it('shows published packages and hides drafts from the catalogue', function () {
    $category = PackageCategory::factory()->create(['slug' => 'ekonomi']);
    $published = Package::factory()->published()->for($category, 'category')->create(['title' => 'Pakej Yang Dipaparkan']);
    $draft = Package::factory()->for($category, 'category')->create(['title' => 'Pakej Draf Rahsia']);

    $this->get(route('packages.index', ['category' => $category->slug]))
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title);
});

it('filters published packages by departure date', function () {
    $matchingPackage = Package::factory()->published()->create(['title' => 'Pakej Tarikh Sesuai']);
    $otherPackage = Package::factory()->published()->create(['title' => 'Pakej Tarikh Lain']);
    $date = now()->addMonths(2)->startOfDay();

    PackageDeparture::factory()->for($matchingPackage)->create(['departure_date' => $date, 'return_date' => $date->copy()->addDays(8)]);
    PackageDeparture::factory()->for($otherPackage)->create(['departure_date' => $date->copy()->subMonth(), 'return_date' => $date->copy()->subMonth()->addDays(8)]);

    $this->get(route('packages.index', ['departure_date' => $date->toDateString()]))
        ->assertOk()
        ->assertSee($matchingPackage->title)
        ->assertDontSee($otherPackage->title);
});

it('shows a published package by slug and returns 404 for an invalid slug', function () {
    $package = Package::factory()->published()->create();

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee($package->title);

    $this->get('/pakej-umrah/pakej-tidak-wujud')->assertNotFound();
});

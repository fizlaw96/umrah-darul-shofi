<?php

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('loads the homepage with published featured packages', function () {
    $package = Package::factory()->published()->create(['is_featured' => true]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($package->title);
});

it('only shows published packages in the homepage experience', function () {
    $publishedPackage = Package::factory()->published()->create([
        'is_featured' => true,
        'title' => 'Pakej Hero Diterbitkan',
    ]);
    $draftPackage = Package::factory()->create([
        'is_featured' => true,
        'title' => 'Pakej Hero Draf',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($publishedPackage->title)
        ->assertSee(route('packages.show', $publishedPackage))
        ->assertDontSee($draftPackage->title);
});

it('renders gallery media only when it is configured by the agency', function () {
    WebsiteSetting::query()->create([
        'key' => 'homepage_gallery_images',
        'value' => 'https://images.example.test/thumb.jpg | https://images.example.test/full.jpg | Jemaah di Masjidil Haram',
        'group' => 'homepage',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Galeri Perjalanan')
        ->assertSee('https://images.example.test/thumb.jpg', false)
        ->assertSee('https://images.example.test/full.jpg', false)
        ->assertSee('Jemaah di Masjidil Haram');
});

it('includes the selected package name in a WhatsApp enquiry link', function () {
    WebsiteSetting::query()->create([
        'key' => 'contact_whatsapp',
        'value' => '0123456789',
        'group' => 'contact',
    ]);
    $package = Package::factory()->published()->create([
        'is_featured' => true,
        'title' => 'Umrah Keluarga Pilihan',
    ]);
    $expectedUrl = 'https://wa.me/60123456789?text='.rawurlencode('Assalamualaikum, saya ingin bertanya tentang pakej '.$package->title.'.');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($expectedUrl, false);
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

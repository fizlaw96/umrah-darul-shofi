<?php

use App\Models\Package;
use App\Models\PackageDeparture;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('closes only departures inside the 30-day window and retains packages with a later departure', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create();
    $nearDeparture = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-10-30',
        'return_date' => '2026-11-07',
        'status' => 'open',
    ]);
    $laterDeparture = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-11-01',
        'return_date' => '2026-11-09',
        'status' => 'open',
    ]);

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('package_departures', ['id' => $nearDeparture->id, 'status' => 'closed']);
    $this->assertDatabaseHas('package_departures', ['id' => $laterDeparture->id, 'status' => 'open']);
    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'published']);
});

it('keeps a departure exactly 30 days away eligible for promotion', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create();
    $departure = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-10-31',
        'return_date' => '2026-11-08',
        'status' => 'open',
    ]);

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('package_departures', ['id' => $departure->id, 'status' => 'open']);
    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'published']);
});

it('keeps a published package with a future closed departure visible', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create();
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-11-01',
        'return_date' => '2026-11-09',
        'status' => 'closed',
    ]);

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'published']);
});

it('archives a published package only when every dated departure is ineligible', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create();
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-10-30',
        'return_date' => '2026-11-07',
        'status' => 'open',
    ]);

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'archived', 'published_at' => null]);
});

it('keeps a published package without departure records unchanged', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create();

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'published']);
});

it('keeps a flexible package published when its dated departures are ineligible', function () {
    $this->travelTo(CarbonImmutable::parse('2026-10-01 09:00:00', 'Asia/Kuala_Lumpur'));

    $package = Package::factory()->published()->create(['is_date_flexible' => true]);
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-10-30',
        'return_date' => '2026-11-07',
        'status' => 'open',
    ]);

    $this->artisan('packages:archive-ineligible-departures')->assertSuccessful();

    $this->assertDatabaseHas('packages', ['id' => $package->id, 'status' => 'published']);
});

<?php

use App\Models\Booking;
use App\Models\Package;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('redirects the legacy booking page to package information', function () {
    $package = Package::factory()->published()->create();

    $this->get(route('bookings.create', $package))
        ->assertRedirect(route('packages.show', $package))
        ->assertSessionHas('success');
});

it('does not create a booking from a legacy booking submission', function () {
    $package = Package::factory()->published()->create();

    $this->post(route('bookings.store'), ['package_id' => $package->id])
        ->assertRedirect(route('packages.show', $package))
        ->assertSessionHas('success');

    expect(Booking::query()->count())->toBe(0);
});

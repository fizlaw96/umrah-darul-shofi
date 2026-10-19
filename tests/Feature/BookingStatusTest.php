<?php

use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

it('records a status transition when staff confirms a booking', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->published()->create();
    $departure = PackageDeparture::factory()->for($package)->create(['capacity' => 5]);
    $booking = createPendingBooking($package, $departure);

    $this->actingAs($staff)
        ->put(route('admin.bookings.update', $booking), ['status' => 'confirmed', 'remarks' => 'Kapasiti disahkan.'])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'confirmed']);
    $this->assertDatabaseHas('booking_status_histories', [
        'booking_id' => $booking->id,
        'user_id' => $staff->id,
        'previous_status' => 'pending',
        'status' => 'confirmed',
    ]);
});

it('rejects a transition that skips from rejected to confirmed', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->published()->create();
    $departure = PackageDeparture::factory()->for($package)->create();
    $booking = createPendingBooking($package, $departure, ['status' => 'rejected']);

    $this->actingAs($staff)
        ->put(route('admin.bookings.update', $booking), ['status' => 'confirmed'])
        ->assertSessionHasErrors('status');
});

it('prevents staff from confirming a booking beyond departure capacity', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->published()->create();
    $departure = PackageDeparture::factory()->for($package)->create(['capacity' => 1]);
    createPendingBooking($package, $departure, ['status' => 'confirmed']);
    $pendingBooking = createPendingBooking($package, $departure);

    $this->actingAs($staff)
        ->put(route('admin.bookings.update', $pendingBooking), ['status' => 'confirmed'])
        ->assertSessionHasErrors('status');
});

/** @param array<string, string> $overrides */
function createPendingBooking(Package $package, PackageDeparture $departure, array $overrides = []): Booking
{
    return Booking::query()->create([
        'package_id' => $package->id,
        'package_departure_id' => $departure->id,
        'idempotency_key' => Str::uuid(),
        'customer_name' => 'Aminah Ahmad',
        'phone' => '0123456789',
        'email' => Str::uuid().'@example.test',
        'adults' => 1,
        'children' => 0,
        'travellers_count' => 1,
        'departure_date' => $departure->departure_date,
        'package_title' => $package->title,
        'price_per_traveller_sen' => $departure->price_sen,
        'total_quoted_sen' => $departure->price_sen,
        'status' => 'pending',
        'privacy_consented_at' => now(),
        ...$overrides,
    ]);
}

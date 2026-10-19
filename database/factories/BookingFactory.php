<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_id' => Package::factory(),
            'package_departure_id' => PackageDeparture::factory(),
            'reference' => 'JUM-'.fake()->unique()->numerify('########').'-'.Str::upper(Str::random(6)),
            'idempotency_key' => fake()->uuid(),
            'customer_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'adults' => 1,
            'children' => 0,
            'travellers_count' => 1,
            'departure_date' => now()->addMonth(),
            'package_title' => 'Pakej demo',
            'price_per_traveller_sen' => 650000,
            'total_quoted_sen' => 650000,
            'status' => 'pending',
            'privacy_consented_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageDeparture>
 */
class PackageDepartureFactory extends Factory
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
            'departure_date' => now()->addMonth()->toDateString(),
            'return_date' => now()->addMonth()->addDays(8)->toDateString(),
            'departure_airport' => 'KLIA',
            'price_sen' => 650000,
            'capacity' => 40,
            'status' => 'open',
        ];
    }
}

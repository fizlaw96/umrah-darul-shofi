<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageParticipant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageParticipant>
 */
class PackageParticipantFactory extends Factory
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
            'package_departure_id' => null,
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}

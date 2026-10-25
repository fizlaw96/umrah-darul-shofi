<?php

namespace Database\Factories;

use App\Models\PackageServiceOption;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PackageServiceOption>
 */
class PackageServiceOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => Str::headline($name),
            'sort_order' => fake()->numberBetween(0, 99),
        ];
    }
}

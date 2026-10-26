<?php

namespace Database\Factories;

use App\Models\AirlineCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AirlineCompany>
 */
class AirlineCompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Airlines',
            'logo' => 'airlines/'.fake()->unique()->uuid().'.png',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }
}

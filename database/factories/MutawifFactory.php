<?php

namespace Database\Factories;

use App\Models\Mutawif;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mutawif>
 */
class MutawifFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

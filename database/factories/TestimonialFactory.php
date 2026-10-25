<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
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
            'reviewer_name' => fake()->name(),
            'rating' => fake()->numberBetween(1, 5),
            'review' => fake()->paragraph(),
            'photo' => 'testimonials/'.fake()->uuid().'.jpg',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

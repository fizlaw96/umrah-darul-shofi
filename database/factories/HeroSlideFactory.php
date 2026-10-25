<?php

namespace Database\Factories;

use App\Models\HeroSlide;
use App\Models\PackageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HeroSlide>
 */
class HeroSlideFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'package_category_id' => PackageCategory::factory(),
            'title' => fake()->sentence(3),
            'subtitle' => fake()->sentence(4),
            'cta_label' => 'Lihat Pakej',
            'is_published' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }
}

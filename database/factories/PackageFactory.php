<?php

namespace Database\Factories;

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = 'Umrah '.fake()->unique()->words(2, true);

        return [
            'package_category_id' => PackageCategory::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'short_description' => fake()->sentence(12),
            'description' => fake()->paragraphs(3, true),
            'starting_price_sen' => fake()->numberBetween(550000, 1200000),
            'duration_days' => 9,
            'duration_nights' => 7,
            'makkah_nights' => 4,
            'madinah_nights' => 3,
            'inclusions' => ['Penginapan', 'Pengangkutan asas'],
            'exclusions' => ['Perbelanjaan peribadi'],
            'status' => 'draft',
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\PackageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PackageCategory>
 */
class PackageCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Ekonomi', 'Premium', 'Ramadan', 'Keluarga', 'Cuti Sekolah']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->sentence(),
            'sort_order' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\AqiqahKorbanService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AqiqahKorbanService>
 */
class AqiqahKorbanServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category' => AqiqahKorbanService::Aqiqah,
            'name' => 'Aqiqah Kambing — 1 Ekor',
            'animal_type' => 'Kambing',
            'quantity_description' => '1 Ekor',
            'service_location' => null,
            'price' => null,
            'full_description' => fake()->paragraph(),
            'featured_image' => null,
            'is_published' => true,
            'sort_order' => fake()->numberBetween(1, 20),
            'korban_season' => null,
            'registration_opens_on' => null,
            'registration_closes_on' => null,
            'registration_period' => null,
            'additional_terms' => null,
        ];
    }

    public function korban(): static
    {
        return $this->state(fn (): array => [
            'category' => AqiqahKorbanService::Korban,
            'name' => 'Korban Lembu — 1 Bahagian',
            'animal_type' => 'Lembu',
            'quantity_description' => '1 Bahagian',
        ]);
    }
}

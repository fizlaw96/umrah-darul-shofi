<?php

namespace Database\Factories;

use App\Models\ImageFocus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImageFocus>
 */
class ImageFocusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'media/gallery/'.fake()->uuid().'.jpg',
            'position_x' => 50,
            'position_y' => 50,
        ];
    }
}

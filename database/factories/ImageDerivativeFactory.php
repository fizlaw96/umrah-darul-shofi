<?php

namespace Database\Factories;

use App\Models\ImageDerivative;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImageDerivative>
 */
class ImageDerivativeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $path = 'media/gallery/'.fake()->unique()->uuid().'.jpg';

        return [
            'path' => $path,
            'width' => 1600,
            'height' => 900,
            'variants' => [
                640 => ImageDerivative::variantPath($path, 640),
                1280 => ImageDerivative::variantPath($path, 1280),
            ],
        ];
    }
}

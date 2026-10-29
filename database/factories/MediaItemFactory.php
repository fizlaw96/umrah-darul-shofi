<?php

namespace Database\Factories;

use App\Models\MediaItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MediaItem>
 */
class MediaItemFactory extends Factory
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
            'section' => MediaItem::FullGallerySection,
            'alt_text' => fake()->sentence(4),
            'caption' => fake()->sentence(),
            'is_published' => true,
            'show_on_homepage' => false,
            'sort_order' => fake()->numberBetween(0, 50),
        ];
    }
}

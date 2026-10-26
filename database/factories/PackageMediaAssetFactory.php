<?php

namespace Database\Factories;

use App\Models\PackageMediaAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PackageMediaAsset>
 */
class PackageMediaAssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'folder' => PackageMediaAsset::LandscapeFolder,
            'path' => 'packages/media/landscape/'.fake()->unique()->uuid().'.jpg',
            'checksum' => hash('sha256', fake()->unique()->uuid()),
            'original_name' => 'gambar-pakej.jpg',
            'mime_type' => 'image/jpeg',
            'width' => 1600,
            'height' => 900,
        ];
    }
}

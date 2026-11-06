<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\PackageImage;
use App\Models\PackageMediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DeletePackageMediaAsset
{
    public function handle(PackageMediaAsset $asset): void
    {
        $path = $asset->path;

        DB::transaction(function () use ($asset, $path): void {
            $this->removePackagePaths($path);
            PackageImage::query()->where('path', $path)->delete();
            $asset->delete();
        });

        if (! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    private function removePackagePaths(string $path): void
    {
        Package::query()
            ->select([
                'id',
                'cover_image',
                'poster_image',
                'flight_options',
                'hotel_options',
                'room_prices',
                'ziarah_places',
                'gifts',
            ])
            ->each(function (Package $package) use ($path): void {
                $updates = [];

                foreach (['cover_image', 'poster_image'] as $attribute) {
                    if ($package->{$attribute} === $path) {
                        $updates[$attribute] = null;
                    }
                }

                foreach (['flight_options', 'hotel_options'] as $attribute) {
                    $options = $this->removeTravelPath($package->{$attribute}, $path);

                    if ($options !== $package->{$attribute}) {
                        $updates[$attribute] = $options;
                    }
                }

                foreach (['room_prices', 'ziarah_places', 'gifts'] as $attribute) {
                    $items = $this->removeContentPath($package->{$attribute}, $path);

                    if ($items !== $package->{$attribute}) {
                        $updates[$attribute] = $items;
                    }
                }

                if ($updates !== []) {
                    $package->update($updates);
                }
            });
    }

    /**
     * @return array<int, mixed>|mixed
     */
    private function removeTravelPath(mixed $options, string $path): mixed
    {
        if (! is_array($options)) {
            return $options;
        }

        return array_map(function (mixed $option) use ($path): mixed {
            if (! is_array($option) || ! is_array($option['images'] ?? null)) {
                return $option;
            }

            $option['images'] = array_values(array_filter(
                $option['images'],
                static fn (mixed $imagePath): bool => $imagePath !== $path,
            ));

            return $option;
        }, $options);
    }

    /**
     * @return array<int, mixed>|mixed
     */
    private function removeContentPath(mixed $items, string $path): mixed
    {
        if (! is_array($items)) {
            return $items;
        }

        return array_map(function (mixed $item) use ($path): mixed {
            if (! is_array($item) || ($item['image'] ?? null) !== $path) {
                return $item;
            }

            $item['image'] = null;

            return $item;
        }, $items);
    }
}

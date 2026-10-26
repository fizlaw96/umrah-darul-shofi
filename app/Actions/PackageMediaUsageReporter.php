<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\PackageMediaAsset;
use Illuminate\Support\Collection;

class PackageMediaUsageReporter
{
    /**
     * @param  Collection<int, PackageMediaAsset>  $assets
     * @return Collection<string, Collection<int, array{package: Package, section: string}>>
     */
    public function usagesFor(Collection $assets): Collection
    {
        $assetPaths = $assets->pluck('path')->flip();
        $usages = $assets->mapWithKeys(fn (PackageMediaAsset $asset): array => [$asset->path => collect()]);

        if ($assetPaths->isEmpty()) {
            return $usages;
        }

        Package::query()
            ->with('images:id,package_id,path')
            ->get([
                'id',
                'title',
                'slug',
                'cover_image',
                'poster_image',
                'flight_options',
                'hotel_options',
                'room_prices',
                'ziarah_places',
                'gifts',
            ])
            ->each(function (Package $package) use ($assetPaths, $usages): void {
                $this->addUsage($usages, $assetPaths, $package->poster_image, $package, 'Poster');
                $this->addUsage($usages, $assetPaths, $package->cover_image, $package, 'Gambar utama landscape');
                $package->images->each(fn ($image): mixed => $this->addUsage($usages, $assetPaths, $image->path, $package, 'Landscape'));
                $this->addNestedImageUsages($usages, $assetPaths, $package, $package->flight_options, 'Perjalanan', true);
                $this->addNestedImageUsages($usages, $assetPaths, $package, $package->hotel_options, 'Penginapan Hotel', true);
                $this->addNestedImageUsages($usages, $assetPaths, $package, $package->room_prices, 'Harga Bilik');
                $this->addNestedImageUsages($usages, $assetPaths, $package, $package->ziarah_places, 'Tempat Ziarah');
                $this->addNestedImageUsages($usages, $assetPaths, $package, $package->gifts, 'Set Hadiah');
            });

        return $usages;
    }

    /**
     * @param  Collection<string, Collection<int, array{package: Package, section: string}>>  $usages
     * @param  Collection<string, string>  $assetPaths
     */
    private function addNestedImageUsages(Collection $usages, Collection $assetPaths, Package $package, mixed $items, string $section, bool $multiple = false): void
    {
        if (! is_array($items)) {
            return;
        }

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $paths = $multiple ? ($item['images'] ?? []) : [$item['image'] ?? null];

            if (! is_array($paths)) {
                continue;
            }

            foreach ($paths as $path) {
                $this->addUsage($usages, $assetPaths, $path, $package, $section);
            }
        }
    }

    /**
     * @param  Collection<string, Collection<int, array{package: Package, section: string}>>  $usages
     * @param  Collection<string, string>  $assetPaths
     */
    private function addUsage(Collection $usages, Collection $assetPaths, mixed $path, Package $package, string $section): void
    {
        if (! is_string($path) || ! $assetPaths->has($path)) {
            return;
        }

        $usages->get($path)?->push([
            'package' => $package,
            'section' => $section,
        ]);
    }
}

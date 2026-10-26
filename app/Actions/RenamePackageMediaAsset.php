<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\PackageImage;
use App\Models\PackageMediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class RenamePackageMediaAsset
{
    public function handle(PackageMediaAsset $asset, string $name): PackageMediaAsset
    {
        if (Str::startsWith($asset->path, ['http://', 'https://'])) {
            throw new RuntimeException('Media luaran tidak boleh dinamakan semula.');
        }

        $oldPath = $asset->path;
        $displayName = $this->displayName($name, $oldPath);
        $newPath = $this->availablePath($asset, $displayName);

        if ($newPath === $oldPath) {
            $asset->update(['original_name' => $displayName]);

            return $asset->fresh();
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($oldPath)) {
            throw new RuntimeException('Fail media asal tidak ditemui.');
        }

        if (! $disk->move($oldPath, $newPath)) {
            throw new RuntimeException('Fail media tidak dapat dinamakan semula.');
        }

        try {
            DB::transaction(function () use ($asset, $oldPath, $newPath, $displayName): void {
                $this->replacePackagePaths($oldPath, $newPath);
                PackageImage::query()->where('path', $oldPath)->update(['path' => $newPath]);
                $asset->update([
                    'path' => $newPath,
                    'original_name' => $displayName,
                ]);
            });
        } catch (Throwable $throwable) {
            $disk->move($newPath, $oldPath);

            throw $throwable;
        }

        return $asset->fresh();
    }

    private function displayName(string $name, string $path): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg';
        $baseName = Str::squish(pathinfo($name, PATHINFO_FILENAME)) ?: 'Media pakej';

        return Str::limit($baseName, 240 - mb_strlen($extension), '').'.'.$extension;
    }

    private function availablePath(PackageMediaAsset $asset, string $displayName): string
    {
        $directory = PackageMediaAsset::folders()[$asset->folder]['storage_directory'];
        $extension = pathinfo($displayName, PATHINFO_EXTENSION);
        $baseName = Str::slug(pathinfo($displayName, PATHINFO_FILENAME)) ?: 'media-pakej';
        $attempt = 1;

        do {
            $suffix = $attempt === 1 ? '' : '-'.$attempt;
            $candidate = $directory.'/'.$baseName.$suffix.'.'.$extension;
            $attempt++;
        } while ($candidate !== $asset->path
            && (Storage::disk('public')->exists($candidate)
                || PackageMediaAsset::query()->where('path', $candidate)->where('id', '!=', $asset->id)->exists()));

        return $candidate;
    }

    private function replacePackagePaths(string $oldPath, string $newPath): void
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
            ->each(function (Package $package) use ($oldPath, $newPath): void {
                $updates = [];

                foreach (['cover_image', 'poster_image'] as $attribute) {
                    if ($package->{$attribute} === $oldPath) {
                        $updates[$attribute] = $newPath;
                    }
                }

                foreach (['flight_options', 'hotel_options'] as $attribute) {
                    $options = $this->replaceTravelPath($package->{$attribute}, $oldPath, $newPath);

                    if ($options !== $package->{$attribute}) {
                        $updates[$attribute] = $options;
                    }
                }

                foreach (['room_prices', 'ziarah_places', 'gifts'] as $attribute) {
                    $items = $this->replaceContentPath($package->{$attribute}, $oldPath, $newPath);

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
    private function replaceTravelPath(mixed $options, string $oldPath, string $newPath): mixed
    {
        if (! is_array($options)) {
            return $options;
        }

        return array_map(function (mixed $option) use ($oldPath, $newPath): mixed {
            if (! is_array($option) || ! is_array($option['images'] ?? null)) {
                return $option;
            }

            $option['images'] = array_map(
                fn (mixed $path): mixed => $path === $oldPath ? $newPath : $path,
                $option['images'],
            );

            return $option;
        }, $options);
    }

    /**
     * @return array<int, mixed>|mixed
     */
    private function replaceContentPath(mixed $items, string $oldPath, string $newPath): mixed
    {
        if (! is_array($items)) {
            return $items;
        }

        return array_map(function (mixed $item) use ($oldPath, $newPath): mixed {
            if (! is_array($item) || ($item['image'] ?? null) !== $oldPath) {
                return $item;
            }

            $item['image'] = $newPath;

            return $item;
        }, $items);
    }
}

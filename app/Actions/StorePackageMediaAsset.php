<?php

namespace App\Actions;

use App\Models\Package;
use App\Models\PackageMediaAsset;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class StorePackageMediaAsset
{
    /**
     * Store an upload once and return the reusable media record.
     */
    public function handle(UploadedFile $uploadedImage, string $folder, ?string $name = null): PackageMediaAsset
    {
        $this->folderDetails($folder);

        $realPath = $uploadedImage->getRealPath();

        if ($realPath === false) {
            throw new RuntimeException('Fail gambar tidak dapat dibaca.');
        }

        $checksum = hash_file('sha256', $realPath);

        if ($checksum === false) {
            throw new RuntimeException('Fail gambar tidak dapat disahkan.');
        }

        $existingAsset = PackageMediaAsset::query()->where('checksum', $checksum)->first();

        if ($existingAsset instanceof PackageMediaAsset) {
            return $existingAsset;
        }

        $originalName = $this->originalName($uploadedImage, $name);
        $path = $this->availablePath($folder, $originalName);
        $uploadedImage->storeAs(dirname($path), basename($path), 'public');
        $dimensions = getimagesize($realPath) ?: null;

        try {
            return PackageMediaAsset::query()->create([
                'folder' => $folder,
                'path' => $path,
                'checksum' => $checksum,
                'original_name' => $originalName,
                'mime_type' => $uploadedImage->getMimeType(),
                'width' => is_array($dimensions) ? $dimensions[0] : null,
                'height' => is_array($dimensions) ? $dimensions[1] : null,
            ]);
        } catch (\Throwable $throwable) {
            Storage::disk('public')->delete($path);

            throw $throwable;
        }
    }

    public function registerExisting(string $path, string $folder): ?PackageMediaAsset
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://'])) {
            return null;
        }

        $this->folderDetails($folder);
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        try {
            $dimensions = getimagesize($disk->path($path)) ?: null;
        } catch (\Throwable) {
            $dimensions = null;
        }

        return PackageMediaAsset::query()->firstOrCreate(
            ['path' => $path],
            [
                'folder' => $folder,
                'checksum' => null,
                'original_name' => basename($path),
                'mime_type' => $disk->mimeType($path),
                'width' => is_array($dimensions) ? $dimensions[0] : null,
                'height' => is_array($dimensions) ? $dimensions[1] : null,
            ],
        );
    }

    /**
     * Register package files that existed before the reusable library was introduced.
     *
     * @param  Collection<int, Package>  $packages
     */
    public function registerLegacyPackageAssets(Collection $packages): void
    {
        foreach ($packages as $package) {
            if (filled($package->poster_image)) {
                $this->registerExisting($package->poster_image, PackageMediaAsset::PosterFolder);
            }

            if (filled($package->cover_image)) {
                $this->registerExisting($package->cover_image, PackageMediaAsset::LandscapeFolder);
            }

            $package->images->each(fn ($image): ?PackageMediaAsset => $this->registerExisting($image->path, PackageMediaAsset::LandscapeFolder));
            $this->registerNestedImages($package->flight_options, PackageMediaAsset::FlightFolder, true);
            $this->registerNestedImages($package->hotel_options, PackageMediaAsset::HotelFolder, true);
            $this->registerNestedImages($package->room_prices, PackageMediaAsset::RoomFolder);
            $this->registerNestedImages($package->ziarah_places, PackageMediaAsset::ZiarahFolder);
            $this->registerNestedImages($package->gifts, PackageMediaAsset::GiftFolder);
        }
    }

    /**
     * @param  array<int, int|string>  $assetIds
     * @return array<int, string>
     */
    public function pathsFor(array $assetIds): array
    {
        return PackageMediaAsset::query()
            ->whereKey($assetIds)
            ->orderBy('id')
            ->pluck('path')
            ->all();
    }

    public function pathForName(string $folder, string $name): ?string
    {
        $normalisedName = $this->normalisedName($name);

        if (blank($normalisedName)) {
            return null;
        }

        return PackageMediaAsset::query()
            ->where('folder', $folder)
            ->latest('id')
            ->get()
            ->first(fn (PackageMediaAsset $asset): bool => $this->normalisedName($asset->original_name) === $normalisedName)
            ?->path;
    }

    private function registerNestedImages(mixed $items, string $folder, bool $multiple = false): void
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
                if (is_string($path)) {
                    $this->registerExisting($path, $folder);
                }
            }
        }
    }

    /**
     * @return array{label: string, storage_directory: string}
     */
    private function folderDetails(string $folder): array
    {
        $folderDetails = PackageMediaAsset::folders()[$folder] ?? null;

        if ($folderDetails === null) {
            throw new RuntimeException('Folder media pakej tidak sah.');
        }

        return $folderDetails;
    }

    private function originalName(UploadedFile $uploadedImage, ?string $name): string
    {
        $extension = $uploadedImage->extension() ?: pathinfo($uploadedImage->getClientOriginalName(), PATHINFO_EXTENSION) ?: 'jpg';
        $baseName = Str::squish(pathinfo($name ?: $uploadedImage->getClientOriginalName(), PATHINFO_FILENAME));

        return Str::limit($baseName ?: 'Media pakej', 240 - mb_strlen($extension), '').'.'.$extension;
    }

    private function availablePath(string $folder, string $originalName): string
    {
        $directory = $this->folderDetails($folder)['storage_directory'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'media-pakej';
        $attempt = 1;

        do {
            $suffix = $attempt === 1 ? '' : '-'.$attempt;
            $path = $directory.'/'.$baseName.$suffix.'.'.$extension;
            $attempt++;
        } while (Storage::disk('public')->exists($path)
            || PackageMediaAsset::query()->where('path', $path)->exists());

        return $path;
    }

    private function normalisedName(string $name): string
    {
        return Str::slug(pathinfo($name, PATHINFO_FILENAME));
    }
}

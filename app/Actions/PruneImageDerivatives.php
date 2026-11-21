<?php

namespace App\Actions;

use App\Models\ImageDerivative;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PruneImageDerivatives
{
    /**
     * Remove the WebP copies of an original only after the original itself is gone.
     *
     * Originals are shared between records (the package media library reuses one file
     * across packages), so an existing original means it is still in use somewhere.
     */
    public function handle(string $path): bool
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', 'darul-shofi/', ImageDerivative::Directory.'/'])) {
            return false;
        }

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return false;
        }

        $derivative = ImageDerivative::query()->where('path', $path)->first();
        $variantPaths = collect(ImageDerivative::Widths)
            ->map(fn (int $width): string => ImageDerivative::variantPath($path, $width))
            ->merge(array_values($derivative?->variants ?? []))
            ->unique()
            ->values()
            ->all();

        $disk->delete($variantPaths);
        $derivative?->delete();

        return true;
    }

    /**
     * Remove derivative files and records whose original no longer exists.
     *
     * @return array{records: int, files: int}
     */
    public function orphans(bool $dryRun = false): array
    {
        $disk = Storage::disk('public');
        $removedRecords = 0;
        $knownVariantPaths = [];

        ImageDerivative::query()->orderBy('id')->each(function (ImageDerivative $derivative) use ($disk, $dryRun, &$removedRecords, &$knownVariantPaths): void {
            if ($disk->exists($derivative->path)) {
                foreach ($derivative->variants as $variantPath) {
                    $knownVariantPaths[$variantPath] = true;
                }

                return;
            }

            $removedRecords++;

            if (! $dryRun) {
                $this->handle($derivative->path);
            }
        });

        $orphanFiles = collect($disk->allFiles(ImageDerivative::Directory))
            ->reject(fn (string $file): bool => isset($knownVariantPaths[$file]))
            ->values();

        if (! $dryRun && $orphanFiles->isNotEmpty()) {
            $disk->delete($orphanFiles->all());
        }

        return ['records' => $removedRecords, 'files' => $orphanFiles->count()];
    }
}

<?php

namespace App\Console\Commands;

use App\Actions\GenerateImageDerivatives;
use App\Actions\PruneImageDerivatives;
use App\Models\AirlineCompany;
use App\Models\HeroSlide;
use App\Models\ImageDerivative;
use App\Models\MediaItem;
use App\Models\Mutawif;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\PackageMediaAsset;
use App\Models\Testimonial;
use App\Models\WebsiteSetting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;

#[Signature('images:derivatives
    {--dry-run : Senaraikan kerja tanpa menulis atau memadam fail}
    {--limit=0 : Hadkan bilangan imej yang diproses dalam satu larian}
    {--force : Jana semula walaupun salinan WebP sudah terkini}
    {--prune : Buang salinan WebP yang imej asalnya sudah tiada}
    {--bundled : Jana salinan WebP untuk imej tetap dalam public/darul-shofi/images}')]
#[Description('Jana salinan WebP 640px dan 1280px untuk imej sedia ada tanpa mengubah imej asal')]
class BackfillImageDerivatives extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(GenerateImageDerivatives $generateImageDerivatives, PruneImageDerivatives $pruneImageDerivatives): int
    {
        $isDryRun = (bool) $this->option('dry-run');

        if ($this->option('prune')) {
            $result = $pruneImageDerivatives->orphans($isDryRun);
            $verb = $isDryRun ? 'akan dibuang' : 'dibuang';
            $this->components->info("{$result['records']} rekod dan {$result['files']} fail WebP yatim {$verb}.");

            return self::SUCCESS;
        }

        if ($this->option('bundled')) {
            return $this->generateBundled($generateImageDerivatives, $isDryRun);
        }

        return $this->generateUploaded($generateImageDerivatives, $isDryRun);
    }

    private function generateUploaded(GenerateImageDerivatives $generateImageDerivatives, bool $isDryRun): int
    {
        $disk = Storage::disk('public');
        $limit = max(0, (int) $this->option('limit'));
        $existing = ImageDerivative::query()->get(['path', 'variants', 'updated_at'])->keyBy('path');

        $pending = $this->uploadedImagePaths()
            ->filter(fn (string $path): bool => $generateImageDerivatives->isProcessable($path) && $disk->exists($path))
            ->reject(function (string $path) use ($disk, $existing): bool {
                if ($this->option('force')) {
                    return false;
                }

                $derivative = $existing->get($path);

                return $derivative !== null
                    && $derivative->updated_at?->getTimestamp() >= $disk->lastModified($path)
                    && collect($derivative->variants)->every(fn (string $variantPath): bool => $disk->exists($variantPath));
            })
            ->values();

        $batch = $limit > 0 ? $pending->take($limit) : $pending;
        $originalBytes = $batch->sum(fn (string $path): int => $disk->size($path));

        $this->components->info(sprintf(
            '%d imej perlu diproses; %d dalam larian ini (%s imej asal).',
            $pending->count(),
            $batch->count(),
            $this->formatBytes($originalBytes),
        ));

        if ($isDryRun) {
            $batch->each(fn (string $path) => $this->line('  '.$path));

            return self::SUCCESS;
        }

        $failures = 0;
        $derivativeBytes = 0;

        foreach ($batch as $path) {
            try {
                $derivative = $generateImageDerivatives->handle($path);
                $derivativeBytes += collect($derivative?->variants ?? [])->sum(fn (string $variantPath): int => $disk->size($variantPath));
                $this->line('  ✓ '.$path);
            } catch (Throwable $throwable) {
                $failures++;
                $this->components->warn($path.': '.$throwable->getMessage());
            }
        }

        $this->components->info(sprintf(
            'Selesai: %d berjaya, %d gagal. Salinan WebP: %s.',
            $batch->count() - $failures,
            $failures,
            $this->formatBytes($derivativeBytes),
        ));

        return $failures === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function generateBundled(GenerateImageDerivatives $generateImageDerivatives, bool $isDryRun): int
    {
        $directory = public_path('darul-shofi/images');
        $manifest = [];

        $files = collect(File::allFiles($directory))
            ->reject(fn (SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.webp') && preg_match('/\.\d+\.webp$/', $file->getFilename()) === 1)
            ->filter(fn (SplFileInfo $file): bool => $generateImageDerivatives->isProcessable($file->getFilename()))
            ->sortBy(fn (SplFileInfo $file): string => $file->getRelativePathname())
            ->values();

        foreach ($files as $file) {
            $relativePath = 'darul-shofi/images/'.str_replace(DIRECTORY_SEPARATOR, '/', $file->getRelativePathname());

            if ($isDryRun) {
                $this->line('  '.$relativePath);

                continue;
            }

            $encoded = $generateImageDerivatives->encode((string) File::get($file->getPathname()), $relativePath);

            if ($encoded === null) {
                continue;
            }

            $variants = [];

            foreach ($encoded['variants'] as $width => $contents) {
                $variantPath = $relativePath.'.'.$width.'.webp';
                File::put(public_path($variantPath), $contents);
                $variants[$width] = $variantPath;
            }

            $manifest[$relativePath] = ['width' => $encoded['width'], 'height' => $encoded['height'], 'variants' => $variants];
            $this->line(sprintf('  ✓ %s (%s → %s)', $relativePath, $this->formatBytes($file->getSize()), $this->formatBytes(strlen((string) end($encoded['variants'])))));
        }

        if (! $isDryRun) {
            File::put(public_path(ImageDerivative::BundledManifest), json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
        }

        $this->components->info(count($files).' imej tetap '.($isDryRun ? 'akan diproses.' : 'diproses.'));

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, string>
     */
    private function uploadedImagePaths(): Collection
    {
        return collect()
            ->merge(MediaItem::query()->pluck('path'))
            ->merge(PackageMediaAsset::query()->pluck('path'))
            ->merge(PackageImage::query()->pluck('path'))
            ->merge(Package::query()->pluck('cover_image'))
            ->merge(Package::query()->pluck('poster_image'))
            ->merge(HeroSlide::query()->pluck('background_image'))
            ->merge(Testimonial::query()->pluck('photo'))
            ->merge(Mutawif::query()->pluck('photo'))
            ->merge(AirlineCompany::query()->pluck('logo'))
            ->merge(WebsiteSetting::query()->whereIn('key', WebsiteSetting::ImageSettingKeys)->pluck('value'))
            ->filter(fn (mixed $path): bool => is_string($path) && $path !== '')
            ->unique()
            ->values();
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? number_format($bytes / 1048576, 1).' MB'
            : number_format($bytes / 1024, 0).' KB';
    }
}

<?php

namespace App\Actions;

use App\Models\ImageDerivative;
use GdImage;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class GenerateImageDerivatives
{
    /** @var array<int, string> */
    private const ProcessableExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    private const MaximumSourcePixels = 50_000_000;

    /**
     * Create the 640px and 1280px WebP copies of an uploaded image, keeping the original untouched.
     */
    public function handle(string $path): ?ImageDerivative
    {
        if (! $this->isProcessable($path)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return null;
        }

        $encoded = $this->encode((string) $disk->get($path), $path);

        if ($encoded === null) {
            return null;
        }

        $previousVariants = ImageDerivative::query()->where('path', $path)->value('variants') ?? [];
        $writtenPaths = [];

        try {
            foreach ($encoded['variants'] as $width => $contents) {
                $variantPath = ImageDerivative::variantPath($path, $width);

                if (! $disk->put($variantPath, $contents, 'public')) {
                    throw new RuntimeException("Salinan WebP {$variantPath} tidak dapat disimpan.");
                }

                $writtenPaths[$width] = $variantPath;
            }

            $derivative = ImageDerivative::query()->updateOrCreate(
                ['path' => $path],
                ['width' => $encoded['width'], 'height' => $encoded['height'], 'variants' => $writtenPaths],
            );
        } catch (Throwable $throwable) {
            $disk->delete(array_diff($writtenPaths, (array) $previousVariants));

            throw $throwable;
        }

        $this->deleteStaleVariants($disk, (array) $previousVariants, $writtenPaths);

        return $derivative;
    }

    /**
     * Encode WebP variants from raw image bytes without touching storage.
     *
     * @return array{width: int, height: int, variants: array<int, string>}|null
     */
    public function encode(string $contents, string $path): ?array
    {
        if (! $this->isProcessable($path)) {
            return null;
        }

        $dimensions = @getimagesizefromstring($contents);

        if ($dimensions === false || $dimensions[0] < 1 || $dimensions[1] < 1) {
            return null;
        }

        if ($dimensions[0] * $dimensions[1] > self::MaximumSourcePixels) {
            Log::warning('Imej terlalu besar untuk dioptimumkan.', ['path' => $path, 'width' => $dimensions[0], 'height' => $dimensions[1]]);

            return null;
        }

        $source = @imagecreatefromstring($contents);

        if ($source === false) {
            return null;
        }

        $variants = [];
        $resizedImages = [];

        try {
            $source = $this->prepareSource($source);
            $source = $this->applyOrientation($source, $this->orientation($contents, $dimensions['mime'] ?? ''));
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);

            foreach (ImageDerivative::Widths as $maximumWidth) {
                $width = min($maximumWidth, $sourceWidth);

                if (array_key_exists($width, $variants)) {
                    continue;
                }

                $image = $width === $sourceWidth
                    ? $source
                    : $resizedImages[] = $this->resize($source, $width, max(1, (int) round($sourceHeight * $width / $sourceWidth)));

                $variants[$width] = $this->webp($image);
            }

            return ['width' => $sourceWidth, 'height' => $sourceHeight, 'variants' => $variants];
        } finally {
            imagedestroy($source);

            foreach ($resizedImages as $resizedImage) {
                imagedestroy($resizedImage);
            }
        }
    }

    public function isProcessable(?string $path): bool
    {
        if (blank($path) || Str::startsWith($path, ['http://', 'https://', ImageDerivative::Directory.'/'])) {
            return false;
        }

        return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), self::ProcessableExtensions, true);
    }

    private function prepareSource(GdImage $image): GdImage
    {
        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function orientation(string $contents, string $mimeType): int
    {
        if ($mimeType !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return 1;
        }

        $exif = @exif_read_data('data://image/jpeg;base64,'.base64_encode($contents));

        return is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
    }

    private function applyOrientation(GdImage $image, int $orientation): GdImage
    {
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, in_array($orientation, [2, 7], true) ? IMG_FLIP_HORIZONTAL : IMG_FLIP_VERTICAL);
        }

        $angle = match ($orientation) {
            3 => 180,
            5, 6, 7 => -90,
            8 => 90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, imagecolorallocatealpha($image, 0, 0, 0, 127));

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);

        return $this->prepareSource($rotated);
    }

    private function resize(GdImage $source, int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $canvas;
    }

    private function webp(GdImage $image): string
    {
        ob_start();
        $encoded = imagewebp($image, null, ImageDerivative::Quality);
        $contents = ob_get_clean();

        if (! $encoded || ! is_string($contents) || $contents === '') {
            throw new RuntimeException('Imej tidak dapat ditukar kepada WebP.');
        }

        return $contents;
    }

    /**
     * @param  array<int|string, string>  $previousVariants
     * @param  array<int, string>  $currentVariants
     */
    private function deleteStaleVariants(Filesystem $disk, array $previousVariants, array $currentVariants): void
    {
        $stalePaths = array_values(array_diff($previousVariants, $currentVariants));

        if ($stalePaths !== []) {
            $disk->delete($stalePaths);
        }
    }
}

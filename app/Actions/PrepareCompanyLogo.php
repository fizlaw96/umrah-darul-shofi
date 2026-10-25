<?php

namespace App\Actions;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class PrepareCompanyLogo
{
    /**
     * @return array{company_logo_path: string, company_favicon_path: string, company_apple_touch_icon_path: string}
     */
    public function handle(UploadedFile $uploadedLogo): array
    {
        $paths = [
            'company_logo_path' => 'branding/company-logo-'.Str::uuid().'.png',
            'company_favicon_path' => 'branding/company-favicon-'.Str::uuid().'.png',
            'company_apple_touch_icon_path' => 'branding/company-apple-touch-icon-'.Str::uuid().'.png',
        ];

        $source = $this->createSourceImage($uploadedLogo);
        $croppedLogo = null;
        $mainLogo = null;
        $favicon = null;
        $appleTouchIcon = null;

        try {
            $this->removeUniformBackground($source);
            $croppedLogo = $this->cropToVisibleArea($source);
            $mainLogo = $this->resizeWithin($croppedLogo, 512);
            $favicon = $this->createSquareIcon($croppedLogo, 64);
            $appleTouchIcon = $this->createSquareIcon($croppedLogo, 180);

            $this->storePng($mainLogo, $paths['company_logo_path']);
            $this->storePng($favicon, $paths['company_favicon_path']);
            $this->storePng($appleTouchIcon, $paths['company_apple_touch_icon_path']);

            return $paths;
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete(array_values($paths));

            throw $throwable;
        } finally {
            imagedestroy($source);

            if ($croppedLogo instanceof GdImage) {
                imagedestroy($croppedLogo);
            }

            if ($mainLogo instanceof GdImage) {
                imagedestroy($mainLogo);
            }

            if ($favicon instanceof GdImage) {
                imagedestroy($favicon);
            }

            if ($appleTouchIcon instanceof GdImage) {
                imagedestroy($appleTouchIcon);
            }
        }
    }

    private function createSourceImage(UploadedFile $uploadedLogo): GdImage
    {
        $contents = file_get_contents($uploadedLogo->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Fail logo tidak dapat dibaca.');
        }

        $source = imagecreatefromstring($contents);

        if ($source === false) {
            throw new RuntimeException('Fail logo tidak dapat diproses.');
        }

        if (! imageistruecolor($source)) {
            imagepalettetotruecolor($source);
        }

        imagealphablending($source, false);
        imagesavealpha($source, true);

        return $source;
    }

    private function removeUniformBackground(GdImage $image): void
    {
        $background = $this->uniformCornerColor($image);

        if ($background === null) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $pixel = $this->pixelAt($image, $x, $y);

                if ($pixel['alpha'] >= 127) {
                    continue;
                }

                $distance = $this->colorDistance($pixel, $background);

                if ($distance > 72) {
                    continue;
                }

                $alpha = $distance <= 44
                    ? 127
                    : max($pixel['alpha'], (int) round((72 - $distance) / 28 * 127));

                $transparentPixel = imagecolorallocatealpha($image, $pixel['red'], $pixel['green'], $pixel['blue'], $alpha);
                imagesetpixel($image, $x, $y, $transparentPixel);
            }
        }
    }

    /**
     * @return array{red: int, green: int, blue: int, alpha: int}|null
     */
    private function uniformCornerColor(GdImage $image): ?array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $corners = [
            $this->pixelAt($image, 0, 0),
            $this->pixelAt($image, $width - 1, 0),
            $this->pixelAt($image, 0, $height - 1),
            $this->pixelAt($image, $width - 1, $height - 1),
        ];

        if (collect($corners)->contains(fn (array $corner): bool => $corner['alpha'] >= 120)) {
            return null;
        }

        $red = (int) round(collect($corners)->avg('red'));
        $green = (int) round(collect($corners)->avg('green'));
        $blue = (int) round(collect($corners)->avg('blue'));
        $background = compact('red', 'green', 'blue');

        foreach ($corners as $corner) {
            if ($this->colorDistance($corner, $background) > 40) {
                return null;
            }
        }

        return $background + ['alpha' => 0];
    }

    /**
     * @return array{red: int, green: int, blue: int, alpha: int}
     */
    private function pixelAt(GdImage $image, int $x, int $y): array
    {
        $color = imagecolorat($image, $x, $y);

        return [
            'red' => ($color >> 16) & 0xFF,
            'green' => ($color >> 8) & 0xFF,
            'blue' => $color & 0xFF,
            'alpha' => ($color >> 24) & 0x7F,
        ];
    }

    /**
     * @param  array{red: int, green: int, blue: int, alpha: int}  $firstColor
     * @param  array{red: int, green: int, blue: int, alpha?: int}  $secondColor
     */
    private function colorDistance(array $firstColor, array $secondColor): float
    {
        return sqrt(
            (($firstColor['red'] - $secondColor['red']) ** 2)
            + (($firstColor['green'] - $secondColor['green']) ** 2)
            + (($firstColor['blue'] - $secondColor['blue']) ** 2),
        );
    }

    private function cropToVisibleArea(GdImage $image): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $left = $width;
        $top = $height;
        $right = -1;
        $bottom = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if ($this->pixelAt($image, $x, $y)['alpha'] >= 120) {
                    continue;
                }

                $left = min($left, $x);
                $top = min($top, $y);
                $right = max($right, $x);
                $bottom = max($bottom, $y);
            }
        }

        if ($right === -1 || $bottom === -1) {
            throw new RuntimeException('Logo tidak mempunyai grafik yang dapat digunakan selepas latar belakang dibuang.');
        }

        return $this->copyRegion($image, $left, $top, $right - $left + 1, $bottom - $top + 1);
    }

    private function resizeWithin(GdImage $image, int $maximumSize): GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maximumSize / max($width, $height));

        return $this->copyResizedImage(
            $image,
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        );
    }

    private function createSquareIcon(GdImage $image, int $size): GdImage
    {
        $canvas = $this->transparentCanvas($size, $size);
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $availableSize = $size - max(4, (int) round($size * 0.12));
        $scale = min($availableSize / $sourceWidth, $availableSize / $sourceHeight);
        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));
        $x = (int) floor(($size - $width) / 2);
        $y = (int) floor(($size - $height) / 2);

        imagecopyresampled($canvas, $image, $x, $y, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        return $canvas;
    }

    private function copyRegion(GdImage $image, int $sourceX, int $sourceY, int $width, int $height): GdImage
    {
        $copy = $this->transparentCanvas($width, $height);
        imagecopy($copy, $image, 0, 0, $sourceX, $sourceY, $width, $height);

        return $copy;
    }

    private function copyResizedImage(GdImage $image, int $width, int $height): GdImage
    {
        $copy = $this->transparentCanvas($width, $height);
        imagecopyresampled($copy, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));

        return $copy;
    }

    private function transparentCanvas(int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        return $canvas;
    }

    private function storePng(GdImage $image, string $path): void
    {
        ob_start();
        imagepng($image, null, 6);
        $contents = ob_get_clean();

        if (! is_string($contents) || ! Storage::disk('public')->put($path, $contents, 'public')) {
            throw new RuntimeException('Logo tidak dapat disimpan.');
        }
    }
}

<?php

namespace App\Actions;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class PrepareAirlineLogo
{
    private const CanvasWidth = 480;

    private const CanvasHeight = 200;

    public function handle(UploadedFile $uploadedLogo): string
    {
        $contents = file_get_contents($uploadedLogo->getRealPath());

        if ($contents === false) {
            throw new RuntimeException('Logo syarikat penerbangan tidak dapat dibaca.');
        }

        $source = imagecreatefromstring($contents);

        if (! $source instanceof GdImage) {
            throw new RuntimeException('Logo syarikat penerbangan tidak dapat diproses.');
        }

        $canvas = $this->transparentCanvas();
        $path = 'airlines/'.Str::uuid().'.png';

        try {
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $scale = min(
                (self::CanvasWidth - 48) / $sourceWidth,
                (self::CanvasHeight - 48) / $sourceHeight,
            );
            $width = max(1, (int) round($sourceWidth * $scale));
            $height = max(1, (int) round($sourceHeight * $scale));
            $x = (int) floor((self::CanvasWidth - $width) / 2);
            $y = (int) floor((self::CanvasHeight - $height) / 2);

            imagealphablending($source, true);
            imagecopyresampled($canvas, $source, $x, $y, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            ob_start();
            imagepng($canvas, null, 6);
            $encodedLogo = ob_get_clean();

            if (! is_string($encodedLogo) || ! Storage::disk('public')->put($path, $encodedLogo, 'public')) {
                throw new RuntimeException('Logo syarikat penerbangan tidak dapat disimpan.');
            }

            return $path;
        } finally {
            imagedestroy($source);
            imagedestroy($canvas);
        }
    }

    private function transparentCanvas(): GdImage
    {
        $canvas = imagecreatetruecolor(self::CanvasWidth, self::CanvasHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        imagefill($canvas, 0, 0, imagecolorallocatealpha($canvas, 0, 0, 0, 127));

        return $canvas;
    }
}

<?php

namespace App\Models;

use App\Models\Concerns\HasImageDerivatives;
use Database\Factories\PackageMediaAssetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['folder', 'path', 'checksum', 'original_name', 'mime_type', 'width', 'height'])]
class PackageMediaAsset extends Model
{
    public const FlightFolder = 'flight';

    public const HotelFolder = 'hotel';

    public const RoomFolder = 'room';

    public const ZiarahFolder = 'ziarah';

    public const GiftFolder = 'gift';

    public const PosterFolder = 'poster';

    public const LandscapeFolder = 'landscape';

    /** @use HasFactory<PackageMediaAssetFactory> */
    use HasFactory;

    use HasImageDerivatives;

    /**
     * @return array<string, array{label: string, storage_directory: string}>
     */
    public static function folders(): array
    {
        return [
            self::FlightFolder => ['label' => 'Perjalanan', 'storage_directory' => 'packages/media/perjalanan'],
            self::HotelFolder => ['label' => 'Penginapan Hotel', 'storage_directory' => 'packages/media/penginapan-hotel'],
            self::RoomFolder => ['label' => 'Harga Bilik', 'storage_directory' => 'packages/media/harga-bilik'],
            self::ZiarahFolder => ['label' => 'Tempat Ziarah', 'storage_directory' => 'packages/media/tempat-ziarah'],
            self::GiftFolder => ['label' => 'Set Hadiah', 'storage_directory' => 'packages/media/set-hadiah'],
            self::PosterFolder => ['label' => 'Poster', 'storage_directory' => 'packages/media/poster'],
            self::LandscapeFolder => ['label' => 'Landscape', 'storage_directory' => 'packages/media/landscape'],
        ];
    }

    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return Storage::disk('public')->url($this->path);
    }

    public function isPortrait(): bool
    {
        return $this->width !== null && $this->height !== null && $this->height > $this->width;
    }

    public function isLandscape(): bool
    {
        return $this->width !== null && $this->height !== null && $this->width > $this->height;
    }

    /**
     * @return array<int, string>
     */
    public function imageDerivativeAttributes(): array
    {
        return ['path'];
    }
}

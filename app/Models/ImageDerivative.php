<?php

namespace App\Models;

use Database\Factories\ImageDerivativeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['path', 'width', 'height', 'variants'])]
class ImageDerivative extends Model
{
    /** @use HasFactory<ImageDerivativeFactory> */
    use HasFactory;

    public const Directory = '_derivatives';

    /** @var array<int, int> */
    public const Widths = [640, 1280];

    public const Quality = 80;

    public const BundledManifest = 'darul-shofi/images/derivatives.json';

    /**
     * Storage path of the WebP copy generated for an original at a given width.
     */
    public static function variantPath(string $originalPath, int $width): string
    {
        return self::Directory.'/'.ltrim($originalPath, '/').'.'.$width.'.webp';
    }

    /**
     * Responsive attributes for an image URL, or null when no optimised copy exists yet
     * so the caller keeps rendering the original file unchanged.
     *
     * @return array{srcset: string, width: int, height: int, largest: string}|null
     */
    public static function responsiveAttributesFor(?string $url): ?array
    {
        if (blank($url)) {
            return null;
        }

        $bundledPath = static::bundledPathFromUrl($url);

        if ($bundledPath !== null) {
            $entry = static::bundledManifest()[$bundledPath] ?? null;

            return $entry === null ? null : static::buildAttributes($entry, fn (string $path): string => asset($path));
        }

        $storagePath = static::storagePathFromUrl($url);

        if ($storagePath === null) {
            return null;
        }

        $entry = static::uploadedMap()[$storagePath] ?? null;

        return $entry === null ? null : static::buildAttributes($entry, fn (string $path): string => Storage::disk('public')->url($path));
    }

    /**
     * @return array<string, array{width: int, height: int, variants: array<string, string>}>
     */
    public static function uploadedMap(): array
    {
        return once(fn (): array => static::query()
            ->get(['path', 'width', 'height', 'variants'])
            ->mapWithKeys(fn (self $derivative): array => [$derivative->path => [
                'width' => $derivative->width,
                'height' => $derivative->height,
                'variants' => $derivative->variants,
            ]])
            ->all());
    }

    /**
     * @return array<string, array{width: int, height: int, variants: array<string, string>}>
     */
    public static function bundledManifest(): array
    {
        return once(function (): array {
            $manifestPath = public_path(self::BundledManifest);

            if (! is_file($manifestPath)) {
                return [];
            }

            $manifest = json_decode((string) file_get_contents($manifestPath), true);

            return is_array($manifest) ? $manifest : [];
        });
    }

    private static function bundledPathFromUrl(string $url): ?string
    {
        $path = ltrim((string) parse_url($url, PHP_URL_PATH), '/');

        return Str::startsWith($path, 'darul-shofi/') ? $path : null;
    }

    private static function storagePathFromUrl(string $url): ?string
    {
        $diskUrlPrefix = rtrim(Storage::disk('public')->url(''), '/').'/';
        $candidates = [$url, (string) parse_url($url, PHP_URL_PATH)];

        foreach ($candidates as $candidate) {
            if (Str::startsWith($candidate, $diskUrlPrefix)) {
                return rawurldecode(Str::after($candidate, $diskUrlPrefix));
            }
        }

        return null;
    }

    /**
     * @param  array{width: int, height: int, variants: array<string, string>}  $entry
     * @param  callable(string): string  $toUrl
     * @return array{srcset: string, width: int, height: int, largest: string}|null
     */
    private static function buildAttributes(array $entry, callable $toUrl): ?array
    {
        $variants = collect($entry['variants'] ?? [])
            ->mapWithKeys(fn (string $path, int|string $width): array => [(int) $width => $path])
            ->sortKeys();

        if ($variants->isEmpty()) {
            return null;
        }

        return [
            'srcset' => $variants
                ->map(fn (string $path, int $width): string => $toUrl($path).' '.$width.'w')
                ->implode(', '),
            'width' => (int) $entry['width'],
            'height' => (int) $entry['height'],
            'largest' => $toUrl($variants->last()),
        ];
    }

    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'variants' => 'array',
        ];
    }
}

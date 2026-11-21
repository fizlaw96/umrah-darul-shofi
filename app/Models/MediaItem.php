<?php

namespace App\Models;

use App\Models\Concerns\HasImageDerivatives;
use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['path', 'section', 'package_category_id', 'alt_text', 'caption', 'is_published', 'show_on_homepage', 'sort_order'])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    use HasImageDerivatives;

    public const AboutSection = 'about';

    public const GallerySection = 'gallery';

    public const FullGallerySection = 'full_gallery';

    /**
     * @return array<string, array{label: string, description: string, limit: ?int}>
     */
    public static function homepageSections(): array
    {
        return [
            self::AboutSection => [
                'label' => 'Bersama Menuju Baitullah',
                'description' => 'Simpan koleksi gambar Tentang Kami dan pilih sehingga empat gambar untuk bahagian pengenalan laman utama.',
                'limit' => 4,
            ],
            self::GallerySection => [
                'label' => 'Kenangan Indah Jemaah Bersama SalamHaji',
                'description' => 'Simpan koleksi galeri dan pilih sehingga 18 gambar untuk galeri bergerak pada halaman utama.',
                'limit' => 18,
            ],
            self::FullGallerySection => [
                'label' => 'Galeri Keseluruhan',
                'description' => 'Muat naik semua kenangan mengikut kategori pakej untuk dipaparkan pada halaman Galeri.',
                'limit' => null,
            ],
        ];
    }

    public static function sectionLimit(string $section): ?int
    {
        return self::homepageSections()[$section]['limit'];
    }

    public static function homepageLimitForStorageSection(string $section): ?int
    {
        return match ($section) {
            self::AboutSection => self::sectionLimit(self::AboutSection),
            self::FullGallerySection => self::sectionLimit(self::GallerySection),
            default => null,
        };
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
    }

    public function scopeVisibleOnHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }

    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        if (Str::startsWith($this->path, 'darul-shofi/')) {
            return asset($this->path);
        }

        return Storage::disk('public')->url($this->path);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }

    /**
     * @return array<int, string>
     */
    public function imageDerivativeAttributes(): array
    {
        return ['path'];
    }
}

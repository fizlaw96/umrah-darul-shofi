<?php

namespace App\Models;

use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['path', 'section', 'alt_text', 'caption', 'is_published', 'sort_order'])]
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    public const AboutSection = 'about';

    public const GallerySection = 'gallery';

    /**
     * @return array<string, array{label: string, description: string, limit: int}>
     */
    public static function homepageSections(): array
    {
        return [
            self::AboutSection => [
                'label' => 'Bersama Menuju Baitullah',
                'description' => 'Gunakan gambar potret (menegak) untuk kolaj empat gambar pada bahagian pengenalan laman utama.',
                'limit' => 4,
            ],
            self::GallerySection => [
                'label' => 'Kenangan Indah Jemaah Bersama SalamHaji',
                'description' => 'Gunakan gambar landskap (melintang) untuk galeri bergerak jemaah pada laman utama.',
                'limit' => 18,
            ],
        ];
    }

    public static function sectionLimit(string $section): int
    {
        return self::homepageSections()[$section]['limit'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeForSection(Builder $query, string $section): Builder
    {
        return $query->where('section', $section);
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
        ];
    }
}

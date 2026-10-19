<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'title',
    'slug',
    'category',
    'excerpt',
    'body',
    'cover_image',
    'status',
    'seo_title',
    'seo_description',
    'published_at',
])]
class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function imageUrl(): ?string
    {
        if (blank($this->cover_image)) {
            return null;
        }

        if (Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        return Storage::disk('public')->url($this->cover_image);
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}

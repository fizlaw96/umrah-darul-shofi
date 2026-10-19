<?php

namespace App\Models;

use Database\Factories\PackageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable([
    'package_category_id',
    'title',
    'slug',
    'short_description',
    'description',
    'cover_image',
    'starting_price_sen',
    'duration_days',
    'duration_nights',
    'makkah_nights',
    'madinah_nights',
    'hotel_information',
    'flight_information',
    'inclusions',
    'exclusions',
    'is_featured',
    'status',
    'seo_title',
    'seo_description',
    'published_at',
])]
class Package extends Model
{
    /** @use HasFactory<PackageFactory> */
    use HasFactory;

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    public function departures(): HasMany
    {
        return $this->hasMany(PackageDeparture::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort_order');
    }

    public function itineraries(): HasMany
    {
        return $this->hasMany(PackageItinerary::class)->orderBy('day_number');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
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

    public function formattedStartingPrice(): string
    {
        return 'RM '.number_format($this->starting_price_sen / 100, 2);
    }

    protected function casts(): array
    {
        return [
            'inclusions' => 'array',
            'exclusions' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}

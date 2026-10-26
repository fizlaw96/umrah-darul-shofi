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
    'code',
    'slug',
    'short_description',
    'description',
    'cover_image',
    'poster_image',
    'starting_price_sen',
    'room_prices',
    'duration_days',
    'duration_nights',
    'season_label',
    'departure_airport',
    'arrival_airport',
    'airline',
    'proposed_departure_date',
    'makkah_nights',
    'madinah_nights',
    'makkah_hotel',
    'makkah_hotel_rating',
    'madinah_hotel',
    'madinah_hotel_rating',
    'walking_distance',
    'hotel_information',
    'flight_information',
    'flight_options',
    'hotel_options',
    'ziarah_places',
    'gifts',
    'highlights',
    'itinerary_pdf',
    'inclusions',
    'exclusions',
    'service_options',
    'is_featured',
    'display_order',
    'status',
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

    public function participants(): HasMany
    {
        return $this->hasMany(PackageParticipant::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query
            ->published()
            ->whereHas('category', fn (Builder $categoryQuery) => $categoryQuery->active())
            ->where(function (Builder $packageQuery): void {
                $packageQuery
                    ->doesntHave('departures')
                    ->orWhereHas('departures', fn (Builder $departureQuery) => $departureQuery->visibleForPromotion());
            });
    }

    public function scopeWithVisibleDepartures(Builder $query): Builder
    {
        return $query->with([
            'departures' => fn (HasMany $departureQuery) => $departureQuery
                ->visibleForPromotion()
                ->orderBy('departure_date'),
        ]);
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

    public function posterImageUrl(): ?string
    {
        $posterImage = $this->poster_image ?: $this->cover_image;

        if (blank($posterImage)) {
            return null;
        }

        if (Str::startsWith($posterImage, ['http://', 'https://'])) {
            return $posterImage;
        }

        return Storage::disk('public')->url($posterImage);
    }

    public function travelImageUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public function itineraryPdfUrl(): ?string
    {
        if (blank($this->itinerary_pdf)) {
            return null;
        }

        return Storage::disk('public')->url($this->itinerary_pdf);
    }

    public function formattedStartingPrice(): string
    {
        return 'RM '.number_format($this->starting_price_sen / 100, 2);
    }

    public function automaticSeoTitle(): string
    {
        $duration = filled($this->duration_days) && filled($this->duration_nights)
            ? "Umrah {$this->duration_days} Hari {$this->duration_nights} Malam"
            : 'Pakej Umrah';

        return Str::limit("{$this->title} | {$duration}", 60);
    }

    public function automaticSeoDescription(): string
    {
        $packageDetails = array_filter([
            filled($this->duration_days) && filled($this->duration_nights)
                ? "Pakej Umrah {$this->duration_days} Hari {$this->duration_nights} Malam"
                : 'Pakej Umrah',
            filled($this->departure_airport) ? "berlepas dari {$this->departure_airport}" : null,
            filled($this->airline) ? "dengan {$this->airline}" : null,
            $this->starting_price_sen > 0 ? "harga bermula {$this->formattedStartingPrice()} seorang" : null,
        ]);
        $shortDescription = Str::squish(strip_tags((string) $this->short_description));

        return Str::limit(
            Str::squish(implode(' ', [
                "{$this->title}.",
                implode(', ', $packageDetails).'.',
                $shortDescription,
            ])),
            160,
        );
    }

    public static function sanitizeDescription(string $description): string
    {
        $allowedTags = '<p><br><strong><b><em><i><ul><ol><li>';
        $description = strip_tags($description, $allowedTags);

        return trim((string) preg_replace(
            '/<(p|br|strong|b|em|i|ul|ol|li)\\b[^>]*>/i',
            '<$1>',
            $description,
        ));
    }

    public function descriptionForDisplay(): string
    {
        return static::sanitizeDescription($this->description);
    }

    protected function casts(): array
    {
        return [
            'room_prices' => 'array',
            'flight_options' => 'array',
            'hotel_options' => 'array',
            'ziarah_places' => 'array',
            'gifts' => 'array',
            'highlights' => 'array',
            'inclusions' => 'array',
            'exclusions' => 'array',
            'service_options' => 'array',
            'is_featured' => 'boolean',
            'proposed_departure_date' => 'date',
            'published_at' => 'datetime',
        ];
    }
}

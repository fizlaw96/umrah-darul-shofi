<?php

namespace App\Models;

use Database\Factories\PackageDepartureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'package_id',
    'departure_date',
    'return_date',
    'departure_airport',
    'price_sen',
    'capacity',
    'status',
])]
class PackageDeparture extends Model
{
    /** @use HasFactory<PackageDepartureFactory> */
    use HasFactory;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(PackageParticipant::class);
    }

    public function unassignedPackageParticipants(): HasMany
    {
        return $this->hasMany(PackageParticipant::class, 'package_id', 'package_id')
            ->whereNull('package_departure_id');
    }

    public function availableSeats(): int
    {
        $participants = $this->getAttribute('participants_count');

        if ($participants === null) {
            $participants = $this->participants()->count();
        }

        $unassignedParticipants = $this->getAttribute('unassigned_package_participants_count');

        if ($unassignedParticipants === null) {
            $unassignedParticipants = $this->unassignedPackageParticipants()->count();
        }

        return max(0, (int) $this->capacity - (int) $participants - (int) $unassignedParticipants);
    }

    public static function promotionCutoffDate(): string
    {
        return now('Asia/Kuala_Lumpur')->startOfDay()->addDays(30)->toDateString();
    }

    public function scopeVisibleForPromotion(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['open', 'closed'])
            ->whereDate('departure_date', '>=', self::promotionCutoffDate());
    }

    public function formattedPrice(): string
    {
        return 'RM '.number_format($this->price_sen / 100, 2);
    }

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
        ];
    }
}

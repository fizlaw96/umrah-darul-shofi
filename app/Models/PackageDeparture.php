<?php

namespace App\Models;

use Database\Factories\PackageDepartureFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function availableSeats(): int
    {
        $confirmedTravellers = $this->getAttribute('confirmed_travellers_count');

        if ($confirmedTravellers === null) {
            $confirmedTravellers = $this->bookings()
                ->where('status', 'confirmed')
                ->sum('travellers_count');
        }

        return max(0, $this->capacity - (int) $confirmedTravellers);
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

<?php

namespace App\Models;

use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'package_id',
    'package_departure_id',
    'reference',
    'idempotency_key',
    'customer_name',
    'phone',
    'email',
    'adults',
    'children',
    'travellers_count',
    'departure_date',
    'package_title',
    'price_per_traveller_sen',
    'total_quoted_sen',
    'status',
    'notes',
    'privacy_consented_at',
])]
class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    public const ConfirmedStatus = 'confirmed';

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(PackageDeparture::class, 'package_departure_id');
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(BookingStatusHistory::class);
    }

    public function formattedTotal(): string
    {
        return 'RM '.number_format($this->total_quoted_sen / 100, 2);
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if (blank($booking->reference)) {
                $booking->reference = 'JUM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'privacy_consented_at' => 'datetime',
        ];
    }
}

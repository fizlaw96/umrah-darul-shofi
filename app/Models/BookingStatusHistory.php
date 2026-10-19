<?php

namespace App\Models;

use Database\Factories\BookingStatusHistoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['booking_id', 'user_id', 'previous_status', 'status', 'remarks'])]
class BookingStatusHistory extends Model
{
    /** @use HasFactory<BookingStatusHistoryFactory> */
    use HasFactory;

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

<?php

namespace App\Models;

use Database\Factories\PackageItineraryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['package_id', 'day_number', 'title', 'description'])]
class PackageItinerary extends Model
{
    /** @use HasFactory<PackageItineraryFactory> */
    use HasFactory;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }
}

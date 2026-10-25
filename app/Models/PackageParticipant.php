<?php

namespace App\Models;

use Database\Factories\PackageParticipantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['package_id', 'package_departure_id', 'full_name', 'phone', 'email', 'notes'])]
class PackageParticipant extends Model
{
    /** @use HasFactory<PackageParticipantFactory> */
    use HasFactory;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function departure(): BelongsTo
    {
        return $this->belongsTo(PackageDeparture::class, 'package_departure_id');
    }
}

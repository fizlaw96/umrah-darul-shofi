<?php

namespace App\Models;

use Database\Factories\EnquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'package_id',
    'name',
    'phone',
    'email',
    'message',
    'status',
    'internal_notes',
    'privacy_consented_at',
])]
class Enquiry extends Model
{
    /** @use HasFactory<EnquiryFactory> */
    use HasFactory;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    protected function casts(): array
    {
        return [
            'privacy_consented_at' => 'datetime',
        ];
    }
}

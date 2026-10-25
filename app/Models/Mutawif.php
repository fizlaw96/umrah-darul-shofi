<?php

namespace App\Models;

use Database\Factories\MutawifFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['full_name', 'photo', 'is_published', 'sort_order'])]
class Mutawif extends Model
{
    /** @use HasFactory<MutawifFactory> */
    use HasFactory;

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function photoUrl(): ?string
    {
        if (blank($this->photo)) {
            return null;
        }

        if (Str::startsWith($this->photo, ['http://', 'https://'])) {
            return $this->photo;
        }

        return Storage::disk('public')->url($this->photo);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}

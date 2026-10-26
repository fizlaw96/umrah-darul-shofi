<?php

namespace App\Models;

use Database\Factories\AirlineCompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'logo', 'is_published', 'sort_order'])]
class AirlineCompany extends Model
{
    public const MaximumCompanies = 20;

    /** @use HasFactory<AirlineCompanyFactory> */
    use HasFactory;

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function logoUrl(): string
    {
        if (Str::startsWith($this->logo, ['http://', 'https://'])) {
            return $this->logo;
        }

        return Storage::disk('public')->url($this->logo);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}

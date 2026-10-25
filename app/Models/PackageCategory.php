<?php

namespace App\Models;

use Database\Factories\PackageCategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['name', 'slug', 'description', 'sort_order', 'is_active', 'show_on_homepage'])]
class PackageCategory extends Model
{
    /** @use HasFactory<PackageCategoryFactory> */
    use HasFactory;

    public function packages(): HasMany
    {
        return $this->hasMany(Package::class);
    }

    public function heroSlide(): HasOne
    {
        return $this->hasOne(HeroSlide::class)->whereNull('package_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeVisibleOnHomepage(Builder $query): Builder
    {
        return $query->where('show_on_homepage', true);
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'show_on_homepage' => 'boolean',
        ];
    }
}

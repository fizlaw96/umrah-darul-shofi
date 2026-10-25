<?php

namespace App\Models;

use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['category', 'question', 'answer', 'sort_order', 'is_published'])]
class Faq extends Model
{
    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    public const DefaultCategories = [
        'Umum',
        'Pertanyaan',
        'Pakej',
        'Persediaan',
        'Tempahan',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }
}

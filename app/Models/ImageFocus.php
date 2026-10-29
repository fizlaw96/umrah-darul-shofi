<?php

namespace App\Models;

use Database\Factories\ImageFocusFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['path', 'position_x', 'position_y'])]
class ImageFocus extends Model
{
    /** @use HasFactory<ImageFocusFactory> */
    use HasFactory;

    public function cssPosition(): string
    {
        return "{$this->position_x}% {$this->position_y}%";
    }

    /**
     * @return array<string, string>
     */
    public static function positions(): array
    {
        return static::query()
            ->get(['path', 'position_x', 'position_y'])
            ->mapWithKeys(fn (self $imageFocus): array => [$imageFocus->path => $imageFocus->cssPosition()])
            ->all();
    }

    protected function casts(): array
    {
        return [
            'position_x' => 'integer',
            'position_y' => 'integer',
        ];
    }
}

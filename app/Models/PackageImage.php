<?php

namespace App\Models;

use App\Models\Concerns\HasImageDerivatives;
use Database\Factories\PackageImageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['package_id', 'path', 'alt_text', 'sort_order'])]
class PackageImage extends Model
{
    /** @use HasFactory<PackageImageFactory> */
    use HasFactory;

    use HasImageDerivatives;

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function url(): string
    {
        if (Str::startsWith($this->path, ['http://', 'https://'])) {
            return $this->path;
        }

        return Storage::disk('public')->url($this->path);
    }

    /**
     * @return array<int, string>
     */
    public function imageDerivativeAttributes(): array
    {
        return ['path'];
    }
}

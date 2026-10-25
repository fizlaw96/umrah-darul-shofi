<?php

namespace App\Models;

use Database\Factories\PackageServiceOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'sort_order'])]
class PackageServiceOption extends Model
{
    /** @use HasFactory<PackageServiceOptionFactory> */
    use HasFactory;
}

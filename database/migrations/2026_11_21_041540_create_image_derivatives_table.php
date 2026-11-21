<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records the optimised WebP copies generated for each original upload.
 *
 * Rolling back drops only this index table. The original uploads are never
 * touched; leftover files under the derivatives directory can be removed with
 * `php artisan images:derivatives --prune` before rolling back.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('image_derivatives', function (Blueprint $table) {
            $table->id();
            $table->string('path')->unique();
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->json('variants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_derivatives');
    }
};

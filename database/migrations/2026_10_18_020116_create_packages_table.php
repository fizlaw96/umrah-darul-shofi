<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_category_id')->constrained()->restrictOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('short_description', 500);
            $table->longText('description');
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('starting_price_sen');
            $table->unsignedSmallInteger('duration_days');
            $table->unsignedSmallInteger('duration_nights');
            $table->unsignedSmallInteger('makkah_nights')->nullable();
            $table->unsignedSmallInteger('madinah_nights')->nullable();
            $table->text('hotel_information')->nullable();
            $table->text('flight_information')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft');
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 500)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'published_at']);
            $table->index(['is_featured', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};

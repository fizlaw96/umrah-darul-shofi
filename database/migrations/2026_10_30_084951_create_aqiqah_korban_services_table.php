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
        Schema::create('aqiqah_korban_services', function (Blueprint $table): void {
            $table->id();
            $table->string('category', 20);
            $table->string('name');
            $table->string('animal_type')->nullable();
            $table->string('quantity_description')->nullable();
            $table->string('service_location')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('korban_season')->nullable();
            $table->string('registration_period')->nullable();
            $table->text('additional_terms')->nullable();
            $table->timestamps();

            $table->index(['is_published', 'category', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aqiqah_korban_services');
    }
};

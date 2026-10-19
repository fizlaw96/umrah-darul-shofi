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
        Schema::create('package_departures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_id')->constrained()->cascadeOnDelete();
            $table->date('departure_date');
            $table->date('return_date');
            $table->string('departure_airport');
            $table->unsignedInteger('price_sen');
            $table->unsignedSmallInteger('capacity');
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->unique(['package_id', 'departure_date']);
            $table->index(['status', 'departure_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_departures');
    }
};

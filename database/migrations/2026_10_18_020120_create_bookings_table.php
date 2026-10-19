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
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_id')->constrained()->restrictOnDelete();
            $table->foreignId('package_departure_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->uuid('idempotency_key')->unique();
            $table->string('customer_name');
            $table->string('phone', 40);
            $table->string('email');
            $table->unsignedSmallInteger('adults');
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('travellers_count');
            $table->date('departure_date');
            $table->string('package_title');
            $table->unsignedInteger('price_per_traveller_sen');
            $table->unsignedInteger('total_quoted_sen');
            $table->string('status', 30)->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('privacy_consented_at');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['package_departure_id', 'status']);
            $table->index(['phone', 'email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};

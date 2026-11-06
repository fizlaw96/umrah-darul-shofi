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
        Schema::create('service_enquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('service_type', 30);
            $table->foreignId('aqiqah_korban_service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status', 20)->default('new');
            $table->string('full_name');
            $table->string('contact_number', 40);
            $table->string('summary');
            $table->json('details');
            $table->text('message');
            $table->text('staff_notes')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'status', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_enquiries');
    }
};

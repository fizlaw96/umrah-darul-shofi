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
        Schema::dropIfExists('enquiries');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('enquiries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('package_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 40);
            $table->string('email');
            $table->text('message');
            $table->string('status', 20)->default('new');
            $table->text('internal_notes')->nullable();
            $table->timestamp('privacy_consented_at');
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['phone', 'created_at']);
        });
    }
};

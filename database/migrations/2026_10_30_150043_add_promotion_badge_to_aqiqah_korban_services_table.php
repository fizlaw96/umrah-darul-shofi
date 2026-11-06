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
        Schema::table('aqiqah_korban_services', function (Blueprint $table): void {
            $table->string('promotion_badge', 24)->nullable()->after('is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aqiqah_korban_services', function (Blueprint $table): void {
            $table->dropColumn('promotion_badge');
        });
    }
};

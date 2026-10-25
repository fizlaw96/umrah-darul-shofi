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
        Schema::table('packages', function (Blueprint $table): void {
            $table->json('room_prices')->nullable()->after('starting_price_sen');
            $table->json('ziarah_places')->nullable()->after('flight_information');
            $table->json('gifts')->nullable()->after('ziarah_places');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table): void {
            $table->dropColumn(['room_prices', 'ziarah_places', 'gifts']);
        });
    }
};

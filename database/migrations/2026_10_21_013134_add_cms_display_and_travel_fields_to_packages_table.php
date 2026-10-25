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
        Schema::table('packages', function (Blueprint $table) {
            $table->string('code')->nullable()->unique()->after('title');
            $table->string('season_label')->nullable()->after('duration_nights');
            $table->string('departure_airport')->nullable()->after('season_label');
            $table->string('arrival_airport')->nullable()->after('departure_airport');
            $table->string('airline')->nullable()->after('arrival_airport');
            $table->date('proposed_departure_date')->nullable()->after('airline');
            $table->string('makkah_hotel')->nullable()->after('madinah_nights');
            $table->unsignedTinyInteger('makkah_hotel_rating')->nullable()->after('makkah_hotel');
            $table->string('madinah_hotel')->nullable()->after('makkah_hotel_rating');
            $table->unsignedTinyInteger('madinah_hotel_rating')->nullable()->after('madinah_hotel');
            $table->string('walking_distance')->nullable()->after('madinah_hotel_rating');
            $table->boolean('show_on_homepage')->default(true)->after('is_featured');
            $table->unsignedInteger('display_order')->default(0)->after('show_on_homepage');

            $table->index(['show_on_homepage', 'status', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropIndex(['show_on_homepage', 'status', 'display_order']);
            $table->dropUnique(['code']);
            $table->dropColumn([
                'code',
                'season_label',
                'departure_airport',
                'arrival_airport',
                'airline',
                'proposed_departure_date',
                'makkah_hotel',
                'makkah_hotel_rating',
                'madinah_hotel',
                'madinah_hotel_rating',
                'walking_distance',
                'show_on_homepage',
                'display_order',
            ]);
        });
    }
};

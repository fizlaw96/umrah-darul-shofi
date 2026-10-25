<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('package_departures', function (Blueprint $table): void {
            $table->string('departure_airport')->nullable()->change();
            $table->unsignedSmallInteger('capacity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('package_departures')
            ->whereNull('departure_airport')
            ->update(['departure_airport' => '']);
        DB::table('package_departures')
            ->whereNull('capacity')
            ->update(['capacity' => 1]);

        Schema::table('package_departures', function (Blueprint $table): void {
            $table->string('departure_airport')->nullable(false)->change();
            $table->unsignedSmallInteger('capacity')->nullable(false)->change();
        });
    }
};

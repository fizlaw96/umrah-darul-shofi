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
        DB::table('aqiqah_korban_services')->whereNull('animal_type')->update(['animal_type' => 'Kambing']);
        DB::table('aqiqah_korban_services')->whereNull('quantity_description')->update(['quantity_description' => '1 Ekor']);

        Schema::table('aqiqah_korban_services', function (Blueprint $table): void {
            $table->string('animal_type')->nullable(false)->change();
            $table->string('quantity_description')->nullable(false)->change();
            $table->date('registration_opens_on')->nullable()->after('korban_season');
            $table->date('registration_closes_on')->nullable()->after('registration_opens_on');
        });

        Schema::table('aqiqah_korban_services', function (Blueprint $table): void {
            $table->dropColumn('short_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aqiqah_korban_services', function (Blueprint $table): void {
            $table->text('short_description')->nullable()->after('price');
            $table->dropColumn(['registration_opens_on', 'registration_closes_on']);
            $table->string('animal_type')->nullable()->change();
            $table->string('quantity_description')->nullable()->change();
        });
    }
};

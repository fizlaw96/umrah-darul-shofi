<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('website_settings')
            ->where('key', 'company_name')
            ->whereIn('value', ['Darul Shofi', 'Salam Haji'])
            ->update(['value' => 'SalamHaji Travel & Tours']);

        DB::table('website_settings')
            ->where('key', 'company_tagline')
            ->whereIn('value', [
                'Perjalanan ibadah, diurus dengan tenang.',
                'Umrah haji : Salam Haji Umrahku & Hajimu Yang Teristimewa',
            ])
            ->update(['value' => 'Salam Haji Umrahku & Hajimu Yang Teristimewa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The previous public-facing brand values cannot be inferred safely.
    }
};

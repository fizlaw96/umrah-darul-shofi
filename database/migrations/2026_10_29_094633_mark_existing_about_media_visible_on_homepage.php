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
        DB::table('media_items')
            ->where('section', 'about')
            ->where('show_on_homepage', false)
            ->update(['show_on_homepage' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('media_items')
            ->where('section', 'about')
            ->update(['show_on_homepage' => false]);
    }
};

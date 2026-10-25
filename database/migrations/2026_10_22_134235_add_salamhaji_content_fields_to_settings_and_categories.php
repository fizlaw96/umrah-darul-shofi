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
        Schema::table('package_categories', function (Blueprint $table): void {
            $table->boolean('show_on_homepage')->default(true)->after('is_active');
            $table->index(['is_active', 'show_on_homepage', 'sort_order'], 'package_categories_homepage_index');
        });

        DB::table('website_settings')
            ->where('key', 'company_name')
            ->where('value', 'Darul Shofi')
            ->update(['value' => 'SalamHaji Travel & Tours']);

        DB::table('website_settings')
            ->where('key', 'company_tagline')
            ->where('value', 'Perjalanan ibadah, diurus dengan tenang.')
            ->update(['value' => 'Salam Haji Umrahku & Hajimu Yang Teristimewa']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_categories', function (Blueprint $table): void {
            $table->dropIndex('package_categories_homepage_index');
            $table->dropColumn('show_on_homepage');
        });
    }
};

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
        Schema::table('media_items', function (Blueprint $table) {
            $table->boolean('show_on_homepage')->default(false)->after('is_published');
            $table->index(
                ['section', 'show_on_homepage', 'is_published', 'sort_order'],
                'media_items_full_gallery_homepage_index',
            );
        });

        DB::table('media_items')
            ->where('section', 'gallery')
            ->update([
                'section' => 'full_gallery',
                'show_on_homepage' => true,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('media_items')
            ->where('section', 'full_gallery')
            ->where('show_on_homepage', true)
            ->update(['section' => 'gallery']);

        Schema::table('media_items', function (Blueprint $table) {
            $table->dropIndex('media_items_full_gallery_homepage_index');
            $table->dropColumn('show_on_homepage');
        });
    }
};

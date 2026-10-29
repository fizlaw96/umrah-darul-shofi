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
        Schema::table('media_items', function (Blueprint $table) {
            $table->foreignId('package_category_id')
                ->nullable()
                ->after('section')
                ->constrained()
                ->nullOnDelete();
            $table->index([
                'section',
                'package_category_id',
                'is_published',
                'sort_order',
            ], 'media_items_gallery_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_items', function (Blueprint $table) {
            $table->dropIndex('media_items_gallery_category_index');
            $table->dropConstrainedForeignId('package_category_id');
        });
    }
};

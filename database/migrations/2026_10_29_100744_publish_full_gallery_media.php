<?php

use App\Models\MediaItem;
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
            ->where('section', MediaItem::FullGallerySection)
            ->where('is_published', false)
            ->update(['is_published' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Publication state before this data correction cannot be recovered safely.
    }
};

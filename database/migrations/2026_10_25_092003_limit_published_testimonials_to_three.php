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
        $numberToUnpublish = DB::table('testimonials')
            ->where('is_published', true)
            ->count() - 3;

        if ($numberToUnpublish > 0) {
            $testimonialIds = DB::table('testimonials')
                ->where('is_published', true)
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($numberToUnpublish)
                ->pluck('id');

            DB::table('testimonials')
                ->whereIn('id', $testimonialIds)
                ->update(['is_published' => false]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * Previously published testimonials cannot be identified safely.
     */
    public function down(): void {}
};

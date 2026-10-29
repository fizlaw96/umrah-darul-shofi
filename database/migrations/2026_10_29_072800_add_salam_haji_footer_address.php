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
        $salamHajiAddress = "SALAM HAJI TRAVEL & TOURS\nUnit G -2-50, Jalan SP 11/3\nSeri Pristana\n47000 Sungai Buloh\nSelangor";
        $darulSoffiAddress = "DARULSOFFI PUNCAK JALIL\n1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan,\nSelangor.";
        $salamHajiMapUrl = 'https://www.google.com/maps/search/?api=1&query=Unit%20G%20-2-50%2C%20Jalan%20SP%2011%2F3%2C%20Seri%20Pristana%2C%2047000%20Sungai%20Buloh%2C%20Selangor';
        $darulSoffiMapUrl = 'https://www.google.com/maps/search/?api=1&query=1-79%20Jalan%20PUJ%203%2F9%2C%20Taman%20Puncak%20Jalil%2C%2043300%20Seri%20Kembangan%2C%20Selangor';
        $now = now();

        if (! DB::table('website_settings')->where('key', 'company_address')->exists()) {
            DB::table('website_settings')->insert([
                'key' => 'company_address',
                'value' => $salamHajiAddress,
                'group' => 'contact',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('website_settings')
            ->where('key', 'public_contact_address')
            ->whereIn('value', [
                $salamHajiAddress,
                str_replace("\n", "\r\n", $salamHajiAddress),
            ])
            ->update([
                'value' => $darulSoffiAddress,
                'updated_at' => $now,
            ]);

        DB::table('website_settings')
            ->where('key', 'map_url')
            ->where('value', $salamHajiMapUrl)
            ->update([
                'value' => $darulSoffiMapUrl,
                'updated_at' => $now,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The separate addresses cannot be safely merged without overwriting an admin update.
    }
};

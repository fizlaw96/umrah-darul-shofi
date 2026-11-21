<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Points the stored favicon and apple-touch icon at small pre-sized PNGs.
 *
 * Only rows that still reference the bundled 7493px logo are changed; icons an
 * admin uploaded are left alone. Rolling back restores the previous logo URL.
 */
return new class extends Migration
{
    /**
     * @var array<string, string>
     */
    private array $icons = [
        'company_favicon_url' => 'darul-shofi/images/branding/salam-haji-favicon-64.png',
        'company_apple_touch_icon_url' => 'darul-shofi/images/branding/salam-haji-apple-touch-icon-180.png',
    ];

    private string $bundledLogo = 'darul-shofi/images/branding/salam-haji-logo.png';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->icons as $key => $icon) {
            $this->replace($key, $this->bundledLogo, $icon);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->icons as $key => $icon) {
            $this->replace($key, $icon, $this->bundledLogo);
        }
    }

    private function replace(string $key, string $from, string $to): void
    {
        DB::table('website_settings')
            ->where('key', $key)
            ->where('value', 'like', '%/'.$from)
            ->get(['id', 'value'])
            ->each(fn (object $setting): int => DB::table('website_settings')
                ->where('id', $setting->id)
                ->update([
                    'value' => substr($setting->value, 0, -strlen($from)).$to,
                    'updated_at' => now(),
                ]));
    }
};

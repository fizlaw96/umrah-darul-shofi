<?php

namespace App\Models;

use Database\Factories\WebsiteSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['key', 'value', 'group'])]
class WebsiteSetting extends Model
{
    /** @use HasFactory<WebsiteSettingFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public static function siteDefaults(): array
    {
        return [
            'company_name' => 'SalamHaji Travel & Tours',
            'company_legal_name' => 'SALAM HAJI TRAVEL & TOURS SDN. BHD.',
            'company_tagline' => 'Salam Haji Umrahku & Hajimu Yang Teristimewa',
            'company_logo_url' => '',
            'company_logo_path' => '',
            'company_favicon_url' => asset('darul-shofi/images/branding/salam-haji-logo.png'),
            'company_favicon_path' => '',
            'company_apple_touch_icon_url' => asset('darul-shofi/images/branding/salam-haji-logo.png'),
            'company_apple_touch_icon_path' => '',
            'contact_phone' => '',
            'contact_email' => '',
            'contact_whatsapp' => '',
            'office_address' => '',
            'office_hours' => '',
            'registered_address' => '',
            'public_contact_address' => "DARULSOFFI PUNCAK JALIL\n1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan,\nSelangor.",
            'map_url' => 'https://www.google.com/maps/search/?api=1&query=1-79%20Jalan%20PUJ%203%2F9%2C%20Taman%20Puncak%20Jalil%2C%2043300%20Seri%20Kembangan%2C%20Selangor',
            'motac_license_number' => 'MOTAC L/N/10909',
            'motac_license_valid_until' => '2027-12-19',
            'license_document_url' => '',
            'footer_logo_path' => '',
            'featured_video_path' => '',
            'facebook_url' => '',
            'instagram_url' => '',
            'tiktok_url' => '',
            'threads_url' => '',
            'privacy_url' => '',
            'terms_url' => '',
            'homepage_company_intro' => '',
            'homepage_package_heading' => '',
            'about_eyebrow' => 'Bersama Menuju Baitullah',
            'about_heading' => 'Perjalanan suci, amanah kami',
            'about_description' => '',
            'about_cta_label' => 'Kenali Kami Lebih Dekat',
            'about_cta_url' => '',
            'featured_video_title' => '',
            'featured_video_url' => '',
            'terms_content' => '',
        ];
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function setValue(string $key, ?string $value, string $group = 'general'): void
    {
        static::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group],
        );
    }

    public static function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    public static function socialProfileName(?string $url): ?string
    {
        if (blank($url)) {
            return null;
        }

        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');
        $profileName = explode('/', $path)[0] ?? null;

        if (blank($profileName) || $profileName === 'profile.php') {
            return null;
        }

        return '@'.ltrim(rawurldecode($profileName), '@');
    }

    public static function threadsComUrl(?string $url): ?string
    {
        if (blank($url)) {
            return $url;
        }

        return preg_replace('#^https?://(?:www\.)?threads\.net(?=/|$)#i', 'https://www.threads.com', $url) ?: $url;
    }

    public static function featuredVideoEmbedUrl(?string $videoUrl): ?string
    {
        $videoId = static::youtubeVideoId($videoUrl);

        if ($videoId === null) {
            return null;
        }

        return "https://www.youtube-nocookie.com/embed/{$videoId}?rel=0";
    }

    private static function youtubeVideoId(?string $videoUrl): ?string
    {
        if (blank($videoUrl)) {
            return null;
        }

        $host = strtolower((string) parse_url($videoUrl, PHP_URL_HOST));
        $path = trim((string) parse_url($videoUrl, PHP_URL_PATH), '/');
        parse_str((string) parse_url($videoUrl, PHP_URL_QUERY), $query);

        $videoId = match ($host) {
            'youtu.be', 'www.youtu.be' => explode('/', $path)[0] ?? null,
            'youtube.com', 'www.youtube.com', 'm.youtube.com' => $query['v']
                ?? preg_replace('#^(embed|shorts)/#', '', $path),
            default => null,
        };

        if (! is_string($videoId) || preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId) !== 1) {
            return null;
        }

        return $videoId;
    }
}

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
            'company_logo_url' => asset('darul-shofi/images/branding/salam-haji-logo.png'),
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
            'company_address' => "SALAM HAJI TRAVEL & TOURS\nUnit G -2-50, Jalan SP 11/3\nSeri Pristana\n47000 Sungai Buloh\nSelangor",
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
            'transport_enabled' => '1',
            'transport_whatsapp' => '',
            'transport_intro' => 'Perjalanan anda, kami bantu uruskan. Kongsi lokasi dan keperluan perjalanan untuk semakan operator.',
            'transport_coverage_info' => '',
            'transport_operating_notes' => '',
            'transport_banner_path' => '',
            'transport_vehicle_options' => json_encode(static::transportVehicleDefaults()),
            'aqiqah_korban_whatsapp' => '',
            'aqiqah_korban_use_general_whatsapp' => '1',
        ];
    }

    public const MaximumTransportVehicleOptions = 8;

    /**
     * Site defaults merged with every stored value that overrides them.
     *
     * @return array<string, string|null>
     */
    public static function siteSettings(): array
    {
        $defaults = static::siteDefaults();

        return array_merge(
            $defaults,
            static::query()
                ->whereIn('key', array_keys($defaults))
                ->pluck('value', 'key')
                ->all(),
        );
    }

    /**
     * @return array<int, array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}>
     */
    public static function transportVehicleDefaults(): array
    {
        return [
            [
                'key' => 'sedan',
                'name' => 'Kereta Sedan',
                'description' => 'Sesuai untuk 1–3 penumpang',
                'image' => 'darul-shofi/images/transport/sedan-v1.png',
                'capacity' => 3,
                'enabled' => true,
            ],
            [
                'key' => 'mpv',
                'name' => 'MPV',
                'description' => 'Sesuai untuk keluarga kecil',
                'image' => 'darul-shofi/images/transport/mpv-v1.png',
                'capacity' => 6,
                'enabled' => true,
            ],
            [
                'key' => 'van',
                'name' => 'Van Penumpang',
                'description' => 'Sesuai untuk keluarga dan bagasi',
                'image' => 'darul-shofi/images/transport/van-v1.png',
                'capacity' => 12,
                'enabled' => true,
            ],
            [
                'key' => 'minibus',
                'name' => 'Bas Mini',
                'description' => 'Sesuai untuk kumpulan kecil',
                'image' => 'darul-shofi/images/transport/minibus-v1.png',
                'capacity' => 25,
                'enabled' => true,
            ],
        ];
    }

    /**
     * Preset vehicle images staff can attach to a vehicle option.
     *
     * @return array<string, string>
     */
    public static function transportVehicleImages(): array
    {
        return collect(static::transportVehicleDefaults())
            ->mapWithKeys(fn (array $vehicle): array => [$vehicle['image'] => $vehicle['name']])
            ->all();
    }

    /**
     * @return array<int, array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}>
     */
    public static function transportVehicleOptions(?string $encodedOptions): array
    {
        $options = json_decode((string) $encodedOptions, true);
        $normalizedOptions = is_array($options) ? static::normalizeTransportVehicleOptions($options) : [];

        return $normalizedOptions === [] ? static::transportVehicleDefaults() : $normalizedOptions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     */
    public static function encodeTransportVehicleOptions(array $options): string
    {
        return json_encode(static::normalizeTransportVehicleOptions($options), JSON_THROW_ON_ERROR);
    }

    /**
     * Drops removed or unnamed rows, keeps keys unique and fills capacities for options saved before capacity existed.
     *
     * @param  array<int|string, mixed>  $options
     * @return array<int, array{key: string, name: string, description: string, image: string, capacity: int|null, enabled: bool}>
     */
    public static function normalizeTransportVehicleOptions(array $options): array
    {
        $defaults = collect(static::transportVehicleDefaults())->keyBy('key');
        $allowedImages = array_keys(static::transportVehicleImages());
        $usedKeys = [];
        $normalized = [];

        foreach ($options as $option) {
            if (! is_array($option) || filter_var($option['remove'] ?? false, FILTER_VALIDATE_BOOLEAN) || blank($option['name'] ?? null)) {
                continue;
            }

            $name = trim((string) $option['name']);
            $key = Str::slug((string) ($option['key'] ?? '')) ?: (Str::slug($name) ?: 'kenderaan');
            $baseKey = $key;
            $suffix = 2;

            while (in_array($key, $usedKeys, true)) {
                $key = $baseKey.'-'.$suffix++;
            }

            $usedKeys[] = $key;
            $image = $option['image'] ?? null;
            $capacity = array_key_exists('capacity', $option)
                ? $option['capacity']
                : ($defaults->get($key)['capacity'] ?? null);

            $normalized[] = [
                'key' => $key,
                'name' => $name,
                'description' => trim((string) ($option['description'] ?? '')),
                'image' => in_array($image, $allowedImages, true) ? $image : $allowedImages[0],
                'capacity' => filled($capacity) && (int) $capacity > 0 ? (int) $capacity : null,
                'enabled' => filter_var($option['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ];

            if (count($normalized) === static::MaximumTransportVehicleOptions) {
                break;
            }
        }

        return $normalized;
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

    public static function normalizedWhatsAppNumber(?string $number): ?string
    {
        $normalized = preg_replace('/\D+/', '', (string) $number) ?? '';

        if (str_starts_with($normalized, '00')) {
            $normalized = substr($normalized, 2);
        }

        if (str_starts_with($normalized, '0')) {
            $normalized = '60'.ltrim($normalized, '0');
        }

        return preg_match('/^[1-9]\d{7,14}$/', $normalized) === 1 ? $normalized : null;
    }

    public static function whatsAppUrl(?string $number, string $message): ?string
    {
        $normalizedNumber = static::normalizedWhatsAppNumber($number);

        if ($normalizedNumber === null) {
            return null;
        }

        return 'https://wa.me/'.$normalizedNumber.'?text='.rawurlencode($message);
    }

    public static function publicUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
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

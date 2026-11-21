<?php

namespace Database\Seeders;

use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class CompanyWebsiteSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            'company_name' => ['value' => 'SalamHaji Travel & Tours', 'group' => 'identity'],
            'company_legal_name' => ['value' => 'SALAM HAJI TRAVEL & TOURS SDN. BHD.', 'group' => 'identity'],
            'company_tagline' => ['value' => 'Salam Haji Umrahku & Hajimu Yang Teristimewa', 'group' => 'identity'],
            'company_logo_url' => ['value' => asset('darul-shofi/images/branding/salam-haji-logo.png'), 'group' => 'identity'],
            'company_logo_path' => ['value' => null, 'group' => 'identity'],
            'company_favicon_url' => ['value' => asset('darul-shofi/images/branding/salam-haji-favicon-64.png'), 'group' => 'identity'],
            'company_favicon_path' => ['value' => null, 'group' => 'identity'],
            'company_apple_touch_icon_url' => ['value' => asset('darul-shofi/images/branding/salam-haji-apple-touch-icon-180.png'), 'group' => 'identity'],
            'company_apple_touch_icon_path' => ['value' => null, 'group' => 'identity'],
            'footer_logo_path' => ['value' => null, 'group' => 'identity'],
            'company_address' => ['value' => "SALAM HAJI TRAVEL & TOURS\nUnit G -2-50, Jalan SP 11/3\nSeri Pristana\n47000 Sungai Buloh\nSelangor", 'group' => 'contact'],
            'public_contact_address' => ['value' => "DARULSOFFI PUNCAK JALIL\n1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan,\nSelangor.", 'group' => 'contact'],
            'map_url' => ['value' => 'https://www.google.com/maps/search/?api=1&query=1-79%20Jalan%20PUJ%203%2F9%2C%20Taman%20Puncak%20Jalil%2C%2043300%20Seri%20Kembangan%2C%20Selangor', 'group' => 'contact'],
            'motac_license_number' => ['value' => 'MOTAC L/N/10909', 'group' => 'legal'],
            'motac_license_valid_until' => ['value' => '2027-12-19', 'group' => 'legal'],
        ] as $key => $setting) {
            WebsiteSetting::setValue($key, $setting['value'], $setting['group']);
        }
    }
}

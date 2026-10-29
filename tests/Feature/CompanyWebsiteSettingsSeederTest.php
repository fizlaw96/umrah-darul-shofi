<?php

use App\Models\WebsiteSetting;
use Database\Seeders\CompanyWebsiteSettingsSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('restores the SalamHaji company settings and its bundled logo', function () {
    $this->seed(CompanyWebsiteSettingsSeeder::class);

    expect(WebsiteSetting::getValue('company_name'))->toBe('SalamHaji Travel & Tours')
        ->and(WebsiteSetting::getValue('company_legal_name'))->toBe('SALAM HAJI TRAVEL & TOURS SDN. BHD.')
        ->and(WebsiteSetting::getValue('company_logo_url'))->toBe(asset('darul-shofi/images/branding/salam-haji-logo.png'))
        ->and(WebsiteSetting::getValue('company_address'))->toBe("SALAM HAJI TRAVEL & TOURS\nUnit G -2-50, Jalan SP 11/3\nSeri Pristana\n47000 Sungai Buloh\nSelangor")
        ->and(WebsiteSetting::getValue('public_contact_address'))->toBe("DARULSOFFI PUNCAK JALIL\n1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan,\nSelangor.")
        ->and(WebsiteSetting::getValue('motac_license_number'))->toBe('MOTAC L/N/10909');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(asset('darul-shofi/images/branding/salam-haji-logo.png'));
});

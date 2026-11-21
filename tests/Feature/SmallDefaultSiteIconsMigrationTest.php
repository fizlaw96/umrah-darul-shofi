<?php

use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

function smallSiteIconsMigration(): object
{
    return require database_path('migrations/2026_11_21_042837_use_small_default_site_icons.php');
}

it('swaps only the bundled logo icons for the small icon files and can roll back', function () {
    WebsiteSetting::setValue('company_favicon_url', 'https://salamhaji.com/darul-shofi/images/branding/salam-haji-logo.png', 'identity');
    WebsiteSetting::setValue('company_apple_touch_icon_url', 'https://cdn.example.test/ikon-sendiri.png', 'identity');

    smallSiteIconsMigration()->up();

    expect(WebsiteSetting::getValue('company_favicon_url'))->toBe('https://salamhaji.com/darul-shofi/images/branding/salam-haji-favicon-64.png')
        ->and(WebsiteSetting::getValue('company_apple_touch_icon_url'))->toBe('https://cdn.example.test/ikon-sendiri.png');

    smallSiteIconsMigration()->down();

    expect(WebsiteSetting::getValue('company_favicon_url'))->toBe('https://salamhaji.com/darul-shofi/images/branding/salam-haji-logo.png');
});

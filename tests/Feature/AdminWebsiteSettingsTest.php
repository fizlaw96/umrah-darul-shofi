<?php

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('uses the Salam Haji logo for site icons when no favicon is stored', function () {
    WebsiteSetting::setValue('company_favicon_url', '', 'identity');
    WebsiteSetting::setValue('company_apple_touch_icon_url', '', 'identity');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('rel="icon" type="image/png" sizes="64x64" href="'.asset('darul-shofi/images/branding/salam-haji-logo.png').'"', false)
        ->assertSee('rel="apple-touch-icon" sizes="180x180" href="'.asset('darul-shofi/images/branding/salam-haji-logo.png').'"', false);
});

it('stores an uploaded company logo and uses it on the public website', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $uploadedLogo = imagecreatetruecolor(160, 120);
    $white = imagecolorallocate($uploadedLogo, 255, 255, 255);
    $emerald = imagecolorallocate($uploadedLogo, 0, 84, 65);
    imagefill($uploadedLogo, 0, 0, $white);
    imagefilledrectangle($uploadedLogo, 40, 30, 120, 90, $emerald);
    ob_start();
    imagepng($uploadedLogo);
    $logoContents = ob_get_clean();
    imagedestroy($uploadedLogo);

    $this->actingAs($staff)
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'Darul Shofi Travel',
            'company_tagline' => 'Ibadah dengan tenang',
            'company_logo' => UploadedFile::fake()->createWithContent('darul-shofi-logo.png', $logoContents),
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']));

    $logoPath = WebsiteSetting::getValue('company_logo_path');
    $faviconPath = WebsiteSetting::getValue('company_favicon_path');
    $appleTouchIconPath = WebsiteSetting::getValue('company_apple_touch_icon_path');

    expect($logoPath)
        ->toStartWith('branding/company-logo-')
        ->toEndWith('.png');
    Storage::disk('public')->assertExists($logoPath);
    Storage::disk('public')->assertExists($faviconPath);
    Storage::disk('public')->assertExists($appleTouchIconPath);

    $processedLogo = imagecreatefromstring(Storage::disk('public')->get($logoPath));
    $favicon = imagecreatefromstring(Storage::disk('public')->get($faviconPath));
    $appleTouchIcon = imagecreatefromstring(Storage::disk('public')->get($appleTouchIconPath));

    expect([imagesx($processedLogo), imagesy($processedLogo)])
        ->toBe([81, 61]);
    expect([imagesx($favicon), imagesy($favicon)])
        ->toBe([64, 64]);
    expect((imagecolorat($favicon, 0, 0) >> 24) & 0x7F)
        ->toBe(127);
    expect([imagesx($appleTouchIcon), imagesy($appleTouchIcon)])
        ->toBe([180, 180]);

    imagedestroy($processedLogo);
    imagedestroy($favicon);
    imagedestroy($appleTouchIcon);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Darul Shofi Travel')
        ->assertSee($logoPath)
        ->assertSee($faviconPath)
        ->assertSee($appleTouchIconPath);
});

it('accepts a high-resolution company logo and prepares a smaller display asset', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $uploadedLogo = imagecreatetruecolor(2049, 100);
    $white = imagecolorallocate($uploadedLogo, 255, 255, 255);
    $emerald = imagecolorallocate($uploadedLogo, 0, 84, 65);
    imagefill($uploadedLogo, 0, 0, $white);
    imagefilledrectangle($uploadedLogo, 900, 20, 1150, 80, $emerald);
    ob_start();
    imagepng($uploadedLogo);
    $logoContents = ob_get_clean();
    imagedestroy($uploadedLogo);

    $this->actingAs($staff)
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'SalamHaji Travel & Tours',
            'company_logo' => UploadedFile::fake()->createWithContent('salam-haji-logo.png', $logoContents),
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']))
        ->assertSessionHasNoErrors('company_logo');

    Storage::disk('public')->assertExists(WebsiteSetting::getValue('company_logo_path'));
});

it('deletes every generated logo asset when the company logo is removed', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $paths = [
        'company_logo_path' => 'branding/previous-logo.png',
        'company_favicon_path' => 'branding/previous-favicon.png',
        'company_apple_touch_icon_path' => 'branding/previous-apple-touch-icon.png',
    ];

    foreach ($paths as $key => $path) {
        Storage::disk('public')->put($path, 'previous logo');
        WebsiteSetting::setValue($key, $path, 'identity');
    }

    $this->actingAs($staff)
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'Darul Shofi Travel',
            'company_tagline' => 'Ibadah dengan tenang',
            'remove_company_logo' => true,
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']));

    foreach ($paths as $key => $path) {
        Storage::disk('public')->assertMissing($path);
        expect(WebsiteSetting::getValue($key))->toBeNull();
    }
});

it('uses the bundled Darul Soffi logo when no valid custom footer logo exists', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    WebsiteSetting::setValue('footer_logo_path', 'branding/missing-footer-logo.jpg', 'identity');

    $this->actingAs($staff)
        ->get(route('admin.settings.edit'))
        ->assertOk()
        ->assertSee(asset('darul-shofi/images/branding/darul-soffi-logo.jpg'), false)
        ->assertDontSee('/storage/branding/missing-footer-logo.jpg', false);
});

it('renders the grouped website settings form', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.settings.edit', ['tab' => 'contact']))
        ->assertOk()
        ->assertSee('Hubungan, alamat &amp; lesen', false)
        ->assertSee('Kandungan homepage')
        ->assertSee('data-logo-remove', false)
        ->assertDontSee('type="checkbox" name="remove_company_logo"', false)
        ->assertDontSee('type="checkbox" name="remove_footer_logo"', false);
});

it('defaults the About CTA URL to the About page', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.settings.edit', ['tab' => 'homepage']))
        ->assertOk()
        ->assertSee('name="about_cta_url" value="'.route('about').'"', false);
});

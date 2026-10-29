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

it('accepts an 8MB company logo and prepares smaller display assets', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $uploadedLogo = imagecreatetruecolor(3000, 300);
    $white = imagecolorallocate($uploadedLogo, 255, 255, 255);
    $emerald = imagecolorallocate($uploadedLogo, 0, 84, 65);
    imagefill($uploadedLogo, 0, 0, $white);
    imagefilledrectangle($uploadedLogo, 800, 60, 2200, 240, $emerald);
    ob_start();
    imagepng($uploadedLogo);
    $logoContents = ob_get_clean();
    imagedestroy($uploadedLogo);

    $uploadedFile = UploadedFile::fake()
        ->createWithContent('salam-haji-logo.png', $logoContents)
        ->size(8192);

    $this->actingAs($staff)
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'SalamHaji Travel & Tours',
            'company_logo' => $uploadedFile,
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']))
        ->assertSessionHasNoErrors('company_logo');

    $logoPath = WebsiteSetting::getValue('company_logo_path');
    Storage::disk('public')->assertExists($logoPath);

    $processedLogo = imagecreatefromstring(Storage::disk('public')->get($logoPath));

    expect(max(imagesx($processedLogo), imagesy($processedLogo)))->toBe(512);

    imagedestroy($processedLogo);
});

it('rejects a company logo that exceeds the supported dimensions', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->from(route('admin.settings.edit', ['tab' => 'identity']))
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'SalamHaji Travel & Tours',
            'company_logo' => UploadedFile::fake()->image('oversized-logo.png', 4097, 100),
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']))
        ->assertSessionHasErrors([
            'company_logo' => 'Logo utama tidak boleh melebihi 4,096 × 4,096 piksel.',
        ]);

    expect(WebsiteSetting::getValue('company_logo_path'))->toBeNull();
});

it('explains when a company logo exceeds the upload size limit', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->from(route('admin.settings.edit', ['tab' => 'identity']))
        ->put(route('admin.settings.update'), [
            'tab' => 'identity',
            'company_name' => 'SalamHaji Travel & Tours',
            'company_logo' => UploadedFile::fake()
                ->image('large-logo.png', 100, 100)
                ->size(8193),
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'identity']))
        ->assertSessionHasErrors([
            'company_logo' => 'Logo utama mesti tidak melebihi 8 MB.',
        ]);

    expect(WebsiteSetting::getValue('company_logo_path'))->toBeNull();
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
        ->assertDontSee('type="checkbox" name="remove_footer_logo"', false)
        ->assertSee('sehingga 8MB dan maksimum 4,096 × 4,096 piksel', false)
        ->assertSee('favicon (64px) dan ikon telefon Apple (180px)', false)
        ->assertSee('Logo ini digunakan pada footer sahaja', false)
        ->assertSee('Alamat Salam Haji')
        ->assertSee('Alamat Darul Soffi / cawangan');
});

it('allows staff to manage separate Salam Haji and Darul Soffi footer addresses', function () {
    $staff = User::factory()->staff()->create();
    $companyAddress = "SALAM HAJI TRAVEL & TOURS\nUnit G -2-50, Jalan SP 11/3\nSeri Pristana\n47000 Sungai Buloh\nSelangor";
    $publicContactAddress = "DARULSOFFI PUNCAK JALIL\n1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan,\nSelangor.";
    $mapUrl = 'https://www.google.com/maps/search/?api=1&query=1-79%20Jalan%20PUJ%203%2F9%2C%20Taman%20Puncak%20Jalil%2C%2043300%20Seri%20Kembangan%2C%20Selangor';

    $this->actingAs($staff)
        ->put(route('admin.settings.update'), [
            'tab' => 'contact',
            'company_name' => 'SalamHaji Travel & Tours',
            'company_address' => $companyAddress,
            'public_contact_address' => $publicContactAddress,
            'map_url' => $mapUrl,
        ])
        ->assertRedirect(route('admin.settings.edit', ['tab' => 'contact']));

    expect(WebsiteSetting::getValue('company_address'))->toBe($companyAddress)
        ->and(WebsiteSetting::getValue('public_contact_address'))->toBe($publicContactAddress)
        ->and(WebsiteSetting::getValue('map_url'))->toBe($mapUrl);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('SALAM HAJI TRAVEL &amp; TOURS', false)
        ->assertSee('Unit G -2-50, Jalan SP 11/3')
        ->assertSeeInOrder([
            'Unit G -2-50, Jalan SP 11/3',
            'DARULSOFFI PUNCAK JALIL',
        ])
        ->assertSee(e($mapUrl), false);
});

it('defaults the About CTA URL to the About page', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.settings.edit', ['tab' => 'homepage']))
        ->assertOk()
        ->assertSee('name="about_cta_url" value="'.route('about').'"', false);
});

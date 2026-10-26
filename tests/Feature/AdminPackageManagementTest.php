<?php

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\PackageMediaAsset;
use App\Models\PackageServiceOption;
use App\Models\User;
use App\Notifications\PackageArchived;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

function packagePayload(PackageCategory $category, array $overrides = []): array
{
    return array_merge([
        'package_category_id' => $category->id,
        'title' => 'Pakej Safwah Premium',
        'code' => 'DS-UMR-01',
        'slug' => 'pakej-safwah-premium',
        'short_description' => 'Pakej Umrah eksklusif dengan penginapan berhampiran Masjidil Haram.',
        'description' => '<p>Pakej dengan <strong>bimbingan</strong> mutawwif.</p>',
        'starting_price' => 8490,
        'duration_days' => 9,
        'duration_nights' => 7,
        'season_label' => (string) now()->year,
        'departure_airport' => 'KLIA Terminal 1 (KUL)',
        'arrival_airport' => 'Jeddah (JED)',
        'airline' => 'Saudia Airlines',
        'proposed_departure_date' => now()->addMonth()->toDateString(),
        'makkah_hotel' => 'Safwah Royal Orchid',
        'makkah_hotel_rating' => 5,
        'madinah_hotel' => 'Frontel Al Harithia',
        'madinah_hotel_rating' => 5,
        'makkah_nights' => 4,
        'madinah_nights' => 3,
        'walking_distance' => '5 minit berjalan kaki ke Masjidil Haram',
        'intent' => 'draft',
    ], $overrides);
}

it('saves a package as a draft with its travel settings', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $returnFlight = PackageServiceOption::factory()->create([
        'name' => 'Tiket penerbangan pergi balik',
        'sort_order' => 1,
    ]);
    $umrahVisa = PackageServiceOption::factory()->create([
        'name' => 'Visa Umrah',
        'sort_order' => 2,
    ]);
    $personalExpenses = PackageServiceOption::factory()->create([
        'name' => 'Perbelanjaan peribadi',
        'sort_order' => 3,
    ]);

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'service_options_present' => '1',
            'package_services' => [
                ['name' => $returnFlight->name, 'included' => '1'],
                ['name' => $umrahVisa->name, 'included' => '1'],
                ['name' => $personalExpenses->name, 'excluded' => '1'],
            ],
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('packages', [
        'title' => 'Pakej Safwah Premium',
        'status' => 'draft',
        'makkah_hotel' => 'Safwah Royal Orchid',
    ]);

    $package = Package::query()->firstOrFail();

    expect($package->inclusions)->toBe(['Tiket penerbangan pergi balik', 'Visa Umrah']);
    expect($package->exclusions)->toBe(['Perbelanjaan peribadi']);
    expect($package->service_options)->toBe(['Tiket penerbangan pergi balik', 'Visa Umrah', 'Perbelanjaan peribadi']);
    expect($package->code)->toBe('DS-'.str_pad((string) $package->id, 5, '0', STR_PAD_LEFT));
    expect($package->slug)->toBe('pakej-safwah-premium');
    expect($package->description)->toContain('<strong>bimbingan</strong>');
    expect($package->display_order)->toBe(1);
});

it('generates the package code and URL from the package name', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'title' => 'Pakej Umrah Barakah 2026',
            'code' => 'MANUAL-CODE',
            'slug' => 'url-manual-yang-diabaikan',
        ]))
        ->assertRedirect(route('admin.packages.edit', 'pakej-umrah-barakah-2026'));

    $package = Package::query()->sole();

    expect($package->code)->toBe('DS-'.str_pad((string) $package->id, 5, '0', STR_PAD_LEFT));
    expect($package->slug)->toBe('pakej-umrah-barakah-2026');
    expect($package->short_description)->toBe('Pakej dengan bimbingan mutawwif.');
});

it('redirects to the new package edit URL after its title changes', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $package = Package::factory()->for($category, 'category')->create([
        'title' => 'Pakej Umrah Asal',
        'slug' => 'pakej-umrah-asal',
    ]);

    $this->actingAs($staff)
        ->from(route('admin.packages.edit', $package))
        ->put(route('admin.packages.update', $package), packagePayload($category, [
            'title' => 'Pakej Umrah Baharu',
        ]))
        ->assertRedirect(route('admin.packages.edit', 'pakej-umrah-baharu'));

    $this->assertDatabaseHas('packages', [
        'id' => $package->id,
        'title' => 'Pakej Umrah Baharu',
        'slug' => 'pakej-umrah-baharu',
    ]);
});

it('stores an automatic short description within the database column limit', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $description = str_repeat('A', 501);

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'description' => $description,
        ]))
        ->assertRedirect();

    expect(Package::query()->sole()->short_description)->toBe(str_repeat('A', 500));
});

it('stores departure dates and one itinerary entry for every travel day', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'duration_days' => 3,
            'duration_nights' => 2,
            'itinerary_days' => [
                ['title' => 'Bertolak ke Jeddah', 'description' => 'Berkumpul di KLIA dan bertolak ke Jeddah.'],
                ['title' => 'Ibadah Umrah', 'description' => 'Melaksanakan ibadah Umrah bersama mutawwif.'],
                ['title' => 'Ziarah Madinah', 'description' => 'Ziarah tempat bersejarah di Madinah.'],
            ],
            'departure_rows' => [[
                'departure_date' => '2026-03-01',
                'return_date' => '2026-03-09',
                'departure_airport' => 'KLIA Terminal 1',
                'price' => '8490.00',
                'capacity' => 40,
                'status' => 'open',
            ]],
        ]))
        ->assertRedirect();

    $package = Package::query()->sole();

    expect($package->itineraries()->pluck('title')->all())->toBe([
        'Bertolak ke Jeddah',
        'Ibadah Umrah',
        'Ziarah Madinah',
    ]);
    $departure = $package->departures()->sole();

    expect($departure->departure_date->toDateString())->toBe('2026-03-01');
    expect($departure->return_date->toDateString())->toBe('2026-03-09');
    expect($departure->price_sen)->toBe(849_000);
    expect($departure->capacity)->toBe(40);
    expect($departure->status)->toBe('open');
});

it('stores poster departure details as a draft when airport and capacity are unknown', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'departure_rows' => [[
                'departure_date' => '2026-12-18',
                'return_date' => '2026-12-29',
                'price' => '11500.00',
            ]],
        ]))
        ->assertRedirect();

    $departure = Package::query()->sole()->departures()->sole();

    expect($departure->price_sen)->toBe(1_150_000)
        ->and($departure->departure_airport)->toBeNull()
        ->and($departure->capacity)->toBeNull()
        ->and($departure->status)->toBe('draft');
});

it('requires a capacity before a departure can be opened to customers', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'departure_rows' => [[
                'departure_date' => '2026-12-18',
                'return_date' => '2026-12-29',
                'price' => '11500.00',
                'status' => 'open',
            ]],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'departure_rows.0.capacity' => 'Kapasiti diperlukan untuk membuka jadual berlepas kepada pelanggan.',
        ]);

    $this->assertDatabaseCount('packages', 0);
});

it('requires a selectable season year', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'season_label' => (string) now()->addYears(3)->year,
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors('season_label');

    $this->assertDatabaseCount('packages', 0);
});

it('opens the tab containing the first package validation error', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->followingRedirects()
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'departure_rows' => [[
                'departure_date' => '2026-03-09',
                'return_date' => '2026-03-01',
                'departure_airport' => 'KLIA Terminal 1',
                'price' => '8490.00',
                'capacity' => 40,
                'status' => 'open',
            ]],
        ]))
        ->assertOk()
        ->assertSee('data-default-tab="departures"', false);
});

it('automatically saves valid package changes without changing a published package status', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $package = Package::factory()->published()->for($category, 'category')->create([
        'title' => 'Pakej Autosimpan Asal',
        'slug' => 'pakej-autosimpan-asal',
    ]);

    $this->actingAs($staff)
        ->postJson(route('admin.packages.autosave', $package), packagePayload($category, [
            'title' => 'Pakej Dimas Kini Automatik',
            'service_options_present' => '1',
            'package_services' => [
                ['name' => 'Bimbingan mutawwif', 'included' => '1'],
                ['name' => 'Caj lebihan bagasi', 'excluded' => '1'],
            ],
        ]))
        ->assertOk()
        ->assertJsonPath('message', 'Perubahan disimpan secara automatik.');

    expect($package->fresh()->title)->toBe('Pakej Dimas Kini Automatik');
    expect($package->fresh()->slug)->toBe('pakej-autosimpan-asal');
    expect($package->fresh()->status)->toBe('published');
    expect($package->fresh()->service_options)->toBe(['Bimbingan mutawwif', 'Caj lebihan bagasi']);
    expect($package->fresh()->inclusions)->toBe(['Bimbingan mutawwif']);
    expect($package->fresh()->exclusions)->toBe(['Caj lebihan bagasi']);
});

it('stores an optional itinerary PDF', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'itinerary_pdf' => UploadedFile::fake()->create('atur-cara.pdf', 120, 'application/pdf'),
        ]))
        ->assertRedirect();

    $package = Package::query()->firstOrFail();

    expect($package->itinerary_pdf)->toStartWith('packages/itineraries/');
    Storage::disk('public')->assertExists($package->itinerary_pdf);
});

it('rejects a service selected as both included and excluded', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'service_options_present' => '1',
            'package_services' => [[
                'name' => 'Perkhidmatan yang sama',
                'included' => '1',
                'excluded' => '1',
            ]],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'package_services.0.included' => 'Satu perkhidmatan hanya boleh dipilih sebagai termasuk atau tidak termasuk.',
        ]);

    $this->assertDatabaseCount('packages', 0);
});

it('limits a package to twenty service rows', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $packageServices = array_map(
        fn (int $number): array => ['name' => "Perkhidmatan {$number}", 'included' => '1'],
        range(1, 21),
    );

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'service_options_present' => '1',
            'package_services' => $packageServices,
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'package_services' => 'The package services field must not have more than 20 items.',
        ]);

    $this->assertDatabaseCount('packages', 0);
});

it('publishes a package when the publish form intent is submitted', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'title' => 'Pakej Safwah Diterbitkan',
            'code' => 'DS-UMR-PUBLISH',
            'slug' => 'pakej-safwah-diterbitkan',
            'poster_image' => UploadedFile::fake()->image('poster.jpg', 900, 1400),
            'intent' => 'publish',
        ]))
        ->assertRedirect(route('admin.packages.edit', 'pakej-safwah-diterbitkan'))
        ->assertSessionHas('success', 'Pakej berjaya disimpan dan diterbitkan.');

    $package = Package::query()->where('slug', 'pakej-safwah-diterbitkan')->firstOrFail();

    expect($package->status)->toBe('published');
    expect($package->published_at)->not->toBeNull();
});

it('shows the AI import and poster prompt assistants between the menu and form when creating and editing a package', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();
    PackageServiceOption::factory()->create([
        'name' => 'Bagasi daftar masuk',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.packages.create'))
        ->assertOk()
        ->assertSeeInOrder([
            'Cipta Pakej Umrah',
            'Isi keseluruhan pakej dengan AI',
            'Jana poster pakej dengan AI',
            '11. Gambar & Media',
            'Maklumat Asas Pakej',
        ])
        ->assertSee('<details class="group border-t border-[#e8eee9]" data-ai-assistant-collapse>', false)
        ->assertSee('Buka pembantu')
        ->assertSee('Import keseluruhan pakej dengan AI')
        ->assertSee('PERKHIDMATAN SEDIA ADA DALAM BORANG (1/20)')
        ->assertSee('Bagasi daftar masuk')
        ->assertSee('xl:grid-cols-2', false)
        ->assertSee('data-ai-import-dialog', false)
        ->assertSee('data-ai-import-response', false)
        ->assertSee('data-ai-import-sync', false)
        ->assertSee('Prompt poster sedia untuk disalin')
        ->assertSee('Isi sekurang-kurangnya 50% maklumat pakej untuk jana prompt poster.')
        ->assertSee('data-poster-prompt-dialog', false)
        ->assertSee('data-poster-prompt-open', false)
        ->assertSee('data-poster-prompt-copy', false)
        ->assertSee('Buka ChatGPT, tampal prompt ini, kemudian jana visual poster.')
        ->assertSee('novalidate', false);

    $this->get(route('admin.packages.edit', $package))
        ->assertOk()
        ->assertSee('Isi keseluruhan pakej dengan AI')
        ->assertSee('data-ai-import-sync', false)
        ->assertSee('Jana poster pakej dengan AI')
        ->assertSee('data-poster-prompt-copy', false);
});

it('hides automatic Google search information from the package form', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();

    $this->actingAs($staff)
        ->get(route('admin.packages.edit', $package))
        ->assertOk()
        ->assertDontSee('Maklumat Carian Google')
        ->assertDontSee('name="seo_title"', false)
        ->assertDontSee('name="seo_description"', false);
});

it('publishes selected packages and displays every published package on the public home page', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $visiblePackage = Package::factory()->for($category, 'category')->create([
        'title' => 'Pakej Homepage Terbit',
        'status' => 'draft',
        'show_on_homepage' => true,
        'poster_image' => 'packages/media/poster/homepage-terbit.jpg',
    ]);
    $automaticPackage = Package::factory()->published()->for($category, 'category')->create([
        'title' => 'Pakej Terbit Automatik',
        'show_on_homepage' => false,
    ]);

    $this->actingAs($staff)
        ->post(route('admin.packages.bulk'), [
            'action' => 'publish',
            'package_ids' => [$visiblePackage->id],
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('packages', [
        'id' => $visiblePackage->id,
        'status' => 'published',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($visiblePackage->title)
        ->assertSee($automaticPackage->title);
});

it('shows the earliest departure month in the package table without old image or homepage columns', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create(['title' => 'Pakej Musim Disember']);
    $departure = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2026-12-18',
        'return_date' => '2026-12-28',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.packages.index'))
        ->assertOk()
        ->assertSee('Musim')
        ->assertSee($departure->departure_date->translatedFormat('F Y'))
        ->assertDontSee('Paparan Homepage')
        ->assertDontSee('>Gambar</th>', false);
});

it('searches package records by code and filters by category and status', function () {
    $staff = User::factory()->staff()->create();
    $matchingCategory = PackageCategory::factory()->create();
    $otherCategory = PackageCategory::factory()->create();
    $matchingPackage = Package::factory()->for($matchingCategory, 'category')->create([
        'title' => 'Pakej Mencari Kod',
        'code' => 'DS-CARI-01',
        'status' => 'draft',
    ]);
    $otherPackage = Package::factory()->for($otherCategory, 'category')->create([
        'title' => 'Pakej Lain',
        'code' => 'DS-LAIN-02',
        'status' => 'published',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.packages.index', [
            'q' => 'DS-CARI-01',
            'category' => $matchingCategory->id,
            'status' => 'draft',
        ]))
        ->assertOk()
        ->assertSee($matchingPackage->title)
        ->assertDontSee($otherPackage->title);
});

it('uses automatic filters in the package list without a filter button', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.packages.index'))
        ->assertOk()
        ->assertSee('data-auto-submit-filters', false)
        ->assertDontSee('>Tapis<', false)
        ->assertDontSee('Pratonton');
});

it('renders package actions without pricing and accommodation columns', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->published()->create();

    $this->actingAs($staff)
        ->get(route('admin.packages.index'))
        ->assertOk()
        ->assertSee($package->title)
        ->assertSee(route('admin.packages.destroy', $package), false)
        ->assertSee(route('packages.show', $package), false)
        ->assertSee('target="_blank"', false)
        ->assertSee('aria-label="Buka halaman awam '.$package->title.'"', false)
        ->assertSee('aria-label="Arkibkan '.$package->title.'"', false)
        ->assertDontSee('Harga Bermula')
        ->assertDontSee('Tempoh &amp; Hotel', false);
});

it('archives and restores package records without deleting them', function () {
    $staff = User::factory()->staff()->create();
    $otherStaff = User::factory()->superAdmin()->create();
    $customer = User::factory()->create(['role' => 'customer']);
    $package = Package::factory()->published()->create();

    $this->actingAs($staff)
        ->delete(route('admin.packages.destroy', $package))
        ->assertRedirect(route('admin.packages.index'));

    $this->assertDatabaseHas('packages', [
        'id' => $package->id,
        'status' => 'archived',
        'published_at' => null,
    ]);
    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $staff->id,
        'type' => PackageArchived::class,
    ]);
    $this->assertDatabaseHas('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $otherStaff->id,
        'type' => PackageArchived::class,
    ]);
    $this->assertDatabaseMissing('notifications', [
        'notifiable_type' => User::class,
        'notifiable_id' => $customer->id,
        'type' => PackageArchived::class,
    ]);

    $this->actingAs($staff)
        ->put(route('admin.packages.restore', $package))
        ->assertRedirect();

    $this->assertDatabaseHas('packages', [
        'id' => $package->id,
        'status' => 'draft',
    ]);
});

it('opens a package archive notification in the package editor and marks it as read', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->published()->create();

    $this->actingAs($staff)
        ->delete(route('admin.packages.destroy', $package));

    $notification = $staff->notifications()->firstOrFail();

    $this->actingAs($staff)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Pemberitahuan')
        ->assertSee("Pakej {$package->title} telah diarkibkan.")
        ->assertSee(route('admin.package-notifications.show', $notification->id), false);

    $this->actingAs($staff)
        ->get(route('admin.package-notifications.show', $notification->id))
        ->assertRedirect(route('admin.packages.edit', $package));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

it('stores valid images and rejects invalid package uploads', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
            'gallery' => [UploadedFile::fake()->image('gallery.webp', 800, 600)],
        ]))
        ->assertRedirect();

    $package = Package::query()->firstOrFail();

    Storage::disk('public')->assertExists($package->cover_image);
    expect($package->images)->toHaveCount(1);
    Storage::disk('public')->assertExists($package->images->first()->path);

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'title' => 'Pakej Fail Tidak Sah',
            'code' => 'DS-INVALID-01',
            'slug' => 'pakej-fail-tidak-sah',
            'cover_image' => UploadedFile::fake()->create('fail.pdf', 100, 'application/pdf'),
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors('cover_image');
});

it('limits a package to three gallery images and lets an uploaded image become the main image', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'gallery' => [
                UploadedFile::fake()->image('pertama.jpg', 1200, 800),
                UploadedFile::fake()->image('utama.jpg', 1200, 800),
            ],
            'gallery_primary_index' => '1',
        ]))
        ->assertRedirect();

    $package = Package::query()->firstOrFail();
    $packageImages = $package->images()->get();

    expect($packageImages)->toHaveCount(2);
    expect($package->cover_image)->toBe($packageImages->get(1)->path);

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'title' => 'Pakej Dengan Empat Gambar',
            'slug' => 'pakej-dengan-empat-gambar',
            'cover_image' => UploadedFile::fake()->image('cover.jpg', 1200, 800),
            'gallery' => [
                UploadedFile::fake()->image('satu.jpg', 1200, 800),
                UploadedFile::fake()->image('dua.jpg', 1200, 800),
                UploadedFile::fake()->image('tiga.jpg', 1200, 800),
            ],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'gallery' => 'Setiap pakej hanya boleh mempunyai maksimum 3 gambar.',
        ]);

    $this->actingAs($staff)
        ->from(route('admin.packages.edit', $package))
        ->put(route('admin.packages.update', $package), packagePayload($category, [
            'gallery' => [
                UploadedFile::fake()->image('melebihi-had.jpg', 1200, 800),
                UploadedFile::fake()->image('melebihi-had-lagi.jpg', 1200, 800),
            ],
        ]))
        ->assertRedirect(route('admin.packages.edit', $package))
        ->assertSessionHasErrors('gallery');
});

it('uses a newly uploaded gallery image as the main image when it is selected', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $package = Package::factory()->for($category, 'category')->create([
        'cover_image' => 'packages/gallery/semasa.jpg',
    ]);
    $package->images()->create([
        'path' => $package->cover_image,
        'sort_order' => 1,
    ]);
    Storage::disk('public')->put($package->cover_image, 'imej semasa');

    $this->actingAs($staff)
        ->put(route('admin.packages.update', $package), packagePayload($category, [
            'gallery' => [UploadedFile::fake()->image('baharu.jpg', 1200, 800)],
            'gallery_primary_index' => '0',
        ]))
        ->assertRedirect();

    $newImagePath = $package->fresh('images')->images->sortByDesc('sort_order')->first()?->path;

    expect($newImagePath)->not->toBeNull();
    expect($package->fresh()->cover_image)->toBe($newImagePath);
});

it('returns to the media tab after removing a package image', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create([
        'cover_image' => 'packages/gallery/utama.jpg',
    ]);
    $mainImage = $package->images()->create([
        'path' => 'packages/gallery/utama.jpg',
        'sort_order' => 1,
    ]);
    $replacementImage = $package->images()->create([
        'path' => 'packages/gallery/gantian.jpg',
        'sort_order' => 2,
    ]);
    Storage::disk('public')->put($mainImage->path, 'imej utama');
    Storage::disk('public')->put($replacementImage->path, 'imej gantian');

    $this->actingAs($staff)
        ->delete(route('admin.packages.images.destroy', [$package, $mainImage]))
        ->assertRedirect(route('admin.packages.edit', [$package, 'tab' => 'media']));

    $this->assertModelMissing($mainImage);
    expect($package->fresh()->cover_image)->toBe($replacementImage->path);
});

it('stores structured room prices, ziarah places, and gifts for a package', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $hotelKey = '4c5b9edc-3154-4c43-8bf3-4a8b0c149f73';

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => $hotelKey,
                'location' => 'Makkah',
                'name' => 'Safwah Royal Orchid',
                'rating' => 5,
                'nights' => 4,
            ]],
            'room_prices' => [
                ['hotel_key' => $hotelKey, 'room_type' => 'Bilik Double', 'price' => '6990.00'],
                ['hotel_key' => $hotelKey, 'room_type' => 'Bilik Triple', 'price' => '6590.00'],
            ],
            'ziarah_places' => [
                [
                    'name' => 'Masjid Quba',
                    'location' => 'Madinah',
                    'description' => 'Masjid pertama dalam sejarah Islam.',
                ],
            ],
            'gifts' => [
                [
                    'title' => 'Beg Sandang Umrah',
                    'description' => 'Satu unit untuk setiap jemaah.',
                ],
            ],
        ]))
        ->assertRedirect();

    $package = Package::query()->firstOrFail();

    expect($package->room_prices)->toBe([
        ['room_type' => 'Bilik Double', 'price_sen' => 699000, 'hotel_key' => $hotelKey],
        ['room_type' => 'Bilik Triple', 'price_sen' => 659000, 'hotel_key' => $hotelKey],
    ]);
    expect($package->ziarah_places)->toBe([
        [
            'name' => 'Masjid Quba',
            'location' => 'Madinah',
            'description' => 'Masjid pertama dalam sejarah Islam.',
        ],
    ]);
    expect($package->gifts)->toBe([
        [
            'title' => 'Beg Sandang Umrah',
            'description' => 'Satu unit untuk setiap jemaah.',
        ],
    ]);
});

it('automatically attaches named package media and permits manual removal', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $hotelKey = 'f6f50e3f-286f-45f2-acbf-5fe0dfbbc0f6';
    $asset = PackageMediaAsset::factory()->create([
        'folder' => PackageMediaAsset::HotelFolder,
        'path' => 'packages/media/penginapan-hotel/hotel-safwah-royal.jpg',
        'original_name' => 'Hotel Safwah Royal.jpg',
    ]);
    Storage::disk('public')->put($asset->path, 'hotel-image');

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => $hotelKey,
                'location' => 'Makkah',
                'name' => 'Hotel Safwah Royal',
                'rating' => 5,
                'nights' => 4,
            ]],
        ]))
        ->assertRedirect();

    $package = Package::query()->sole();

    expect($package->hotel_options[0]['images'])->toBe([$asset->path]);

    $this->actingAs($staff)
        ->put(route('admin.packages.update', $package), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => $hotelKey,
                'location' => 'Makkah',
                'name' => 'Hotel Safwah Royal',
                'rating' => 5,
                'nights' => 4,
                'removed_existing_images' => [$asset->path],
            ]],
        ]))
        ->assertRedirect();

    expect($package->fresh()->hotel_options[0]['images'])->toBe([]);
});

it('stores room images with their room prices instead of the hotel carousel', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $hotelKey = 'b2c877e8-a10a-47f2-8b37-78e95f5c6984';

    $this->actingAs($staff)
        ->post(route('admin.packages.store'), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => $hotelKey,
                'location' => 'Makkah',
                'name' => 'Safwah Royal Orchid',
                'images' => [UploadedFile::fake()->image('bilik-double.jpg', 720, 480)],
            ]],
            'room_prices' => [[
                'hotel_key' => $hotelKey,
                'room_type' => 'Bilik Double',
                'price' => '6990.00',
                'image' => UploadedFile::fake()->image('bilik-double.jpg', 720, 480),
            ]],
            'ziarah_places' => [[
                'name' => 'Masjid Quba',
                'location' => 'Madinah',
                'description' => 'Masjid pertama dalam sejarah Islam.',
                'image' => UploadedFile::fake()->image('masjid-quba.jpg', 720, 480),
            ]],
            'gifts' => [[
                'title' => 'Beg Sandang Umrah',
                'description' => 'Satu unit untuk setiap jemaah.',
                'image' => UploadedFile::fake()->image('beg-sandang.jpg', 720, 480),
            ]],
        ]))
        ->assertRedirect();

    $package = Package::query()->firstOrFail();
    $hotelImages = $package->hotel_options[0]['images'];
    $roomImage = $package->room_prices[0]['image'];
    $ziarahImage = $package->ziarah_places[0]['image'];
    $giftImage = $package->gifts[0]['image'];

    expect($package->room_prices[0]['hotel_key'])->toBe($hotelKey);
    expect($hotelImages)->toHaveCount(1);
    expect($hotelImages[0])->toStartWith('packages/media/');
    expect($roomImage)->toStartWith('packages/media/');
    expect($ziarahImage)->toStartWith('packages/media/');
    expect($giftImage)->toStartWith('packages/media/');
    Storage::disk('public')->assertExists($hotelImages[0]);
    Storage::disk('public')->assertExists($roomImage);
    Storage::disk('public')->assertExists($ziarahImage);
    Storage::disk('public')->assertExists($giftImage);
});

it('rejects non-image uploads for ziarah places and gifts', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'ziarah_places' => [[
                'name' => 'Masjid Quba',
                'image' => UploadedFile::fake()->create('masjid-quba.pdf', 100, 'application/pdf'),
            ]],
            'gifts' => [[
                'title' => 'Beg Sandang Umrah',
                'image' => UploadedFile::fake()->create('beg-sandang.pdf', 100, 'application/pdf'),
            ]],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors(['ziarah_places.0.image', 'gifts.0.image']);

    $this->assertDatabaseCount('packages', 0);
});

it('requires the key details when an additional package information row is started', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'room_prices' => [
                ['room_type' => '', 'price' => '6990.00'],
            ],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'room_prices.0.room_type' => 'Jenis bilik diperlukan apabila satu baris diisi.',
        ]);
});

it('requires each room price to select a hotel created for the package', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $hotelKey = '4d6ce270-c8ea-4a13-a7b1-0f3d8636981f';

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => $hotelKey,
                'location' => 'Makkah',
                'name' => 'Safwah Royal Orchid',
            ]],
            'room_prices' => [[
                'room_type' => 'Bilik Double',
                'price' => '6990.00',
            ]],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'room_prices.0.hotel_key' => 'Hotel penginapan diperlukan apabila satu baris diisi.',
        ]);
});

it('rejects a room price assigned to a hotel that is not in the package', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    $this->actingAs($staff)
        ->from(route('admin.packages.create'))
        ->post(route('admin.packages.store'), packagePayload($category, [
            'hotel_options_present' => '1',
            'hotel_options' => [[
                'key' => '4d6ce270-c8ea-4a13-a7b1-0f3d8636981f',
                'location' => 'Makkah',
                'name' => 'Safwah Royal Orchid',
            ]],
            'room_prices' => [[
                'hotel_key' => 'ec1236d2-e764-4f4a-a3b1-07ec7e84e329',
                'room_type' => 'Bilik Double',
                'price' => '6990.00',
            ]],
        ]))
        ->assertRedirect(route('admin.packages.create'))
        ->assertSessionHasErrors([
            'room_prices.0.hotel_key' => 'Hotel penginapan yang dipilih tidak sah.',
        ]);
});

it('renders the package information sections in the admin form', function () {
    $staff = User::factory()->staff()->create();
    $serviceOption = PackageServiceOption::factory()->create([
        'name' => 'Lawatan ziarah',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.packages.create'))
        ->assertOk()
        ->assertSee('2. Tarikh Berlepas')
        ->assertSee('3. Perjalanan')
        ->assertSee('4. Penginapan Hotel')
        ->assertSee('6. Itinerari')
        ->assertSee('Harga Bilik')
        ->assertSee('Hotel penginapan')
        ->assertSee('data-room-hotel-select', false)
        ->assertSee('data-package-media-upload-input', false)
        ->assertSee('data-collection-image-input', false)
        ->assertSee('admin-package-media-dialog', false)
        ->assertSee('admin-package-media-preview-dialog', false)
        ->assertSee('data-room-image-input', false)
        ->assertSee('Jana poster pakej dengan AI')
        ->assertSee('Tarikh Berlepas')
        ->assertSee('Tempat Ziarah')
        ->assertSee('Set Hadiah Jemaah')
        ->assertSee('Gambar lokasi')
        ->assertSee('Gambar hadiah')
        ->assertSee($serviceOption->name)
        ->assertSee('name="package_services[0][included]"', false)
        ->assertSee('name="package_services[0][excluded]"', false)
        ->assertSee('Tambah perkhidmatan')
        ->assertSee('aria-label="Buang Lawatan ziarah"', false)
        ->assertSee('Ringkasan pada hero halaman pakej dikemas kini secara automatik')
        ->assertDontSee('Sorotan Pakej')
        ->assertDontSee('name="short_description"', false)
        ->assertDontSee('name="slug"', false)
        ->assertDontSee('name="code"', false);
});

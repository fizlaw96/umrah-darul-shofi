<?php

use App\Models\MediaItem;
use App\Models\PackageCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('allows staff to upload multiple, update, and delete gallery media', function () {
    Storage::fake('public', ['url' => '/media']);

    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::GallerySection,
            'images' => [
                UploadedFile::fake()->image('jemaah-pertama.jpg', 1200, 800),
                UploadedFile::fake()->image('jemaah-kedua.jpg', 1200, 800),
            ],
            'is_published' => false,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::GallerySection]));

    $mediaItems = MediaItem::query()->orderBy('sort_order')->get();
    $mediaItem = $mediaItems->firstOrFail();

    expect($mediaItems)->toHaveCount(2);
    expect($mediaItem->is_published)->toBeTrue();
    expect($mediaItem->section)->toBe(MediaItem::FullGallerySection);
    expect($mediaItem->show_on_homepage)->toBeTrue();
    expect($mediaItem->alt_text)->toBe('Foto Kenangan Indah Jemaah Bersama SalamHaji: jemaah pertama');

    foreach ($mediaItems as $item) {
        Storage::disk('public')->assertExists($item->path);
    }

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::GallerySection]))
        ->assertOk()
        ->assertSee('Bersama Menuju Baitullah')
        ->assertSee('Kenangan Indah Jemaah Bersama SalamHaji')
        ->assertSee('Gambar ini disimpan dalam Galeri Keseluruhan, dipilih terus untuk Kenangan Indah, dan menggantikan pilihan paling lama apabila penuh.')
        ->assertSee('Simpan semua perubahan')
        ->assertSee('data-media-edit-grid', false)
        ->assertSee('data-media-edit-dialog', false)
        ->assertSee('Dipaparkan · Kenangan')
        ->assertDontSee('Papar pada halaman Galeri')
        ->assertSee('Senarai')
        ->assertSee('Seret dan lepas gambar di sini');

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::AboutSection]))
        ->assertOk()
        ->assertSee('Simpan koleksi gambar Tentang Kami dan pilih sehingga empat gambar untuk bahagian pengenalan laman utama.');

    $this->actingAs($staff)
        ->put(route('admin.media.update', $mediaItem), [
            'is_published' => false,
            'show_on_homepage' => true,
            'return_section' => MediaItem::GallerySection,
            'sort_order' => 99,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::GallerySection]));

    $this->assertDatabaseHas('media_items', [
        'id' => $mediaItem->id,
        'alt_text' => 'Foto Kenangan Indah Jemaah Bersama SalamHaji: jemaah pertama',
        'caption' => null,
        'is_published' => true,
        'show_on_homepage' => true,
        'sort_order' => 1,
    ]);

    $path = $mediaItem->path;

    $this->actingAs($staff)
        ->delete(route('admin.media.destroy', $mediaItem))
        ->assertRedirect();

    $this->assertDatabaseMissing('media_items', ['id' => $mediaItem->id]);
    Storage::disk('public')->assertMissing($path);
});

it('replaces the oldest About image when an upload fills the homepage selection', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $oldestMedia = MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'show_on_homepage' => true,
        'created_at' => now()->subMinutes(4),
    ]);
    MediaItem::factory()
        ->count(MediaItem::sectionLimit(MediaItem::AboutSection) - 1)
        ->create([
            'section' => MediaItem::AboutSection,
            'show_on_homepage' => true,
        ]);

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::AboutSection,
            'images' => [UploadedFile::fake()->image('jemaah.jpg', 1200, 800)],
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::AboutSection]));

    $newMedia = MediaItem::query()->latest('id')->firstOrFail();

    expect($oldestMedia->fresh()->show_on_homepage)->toBeFalse()
        ->and($newMedia->section)->toBe(MediaItem::AboutSection)
        ->and($newMedia->show_on_homepage)->toBeTrue()
        ->and(MediaItem::query()->forSection(MediaItem::AboutSection)->visibleOnHomepage()->count())->toBe(4);
});

it('returns gallery validation errors to asynchronous uploads', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->postJson(route('admin.media.store'), [
            'section' => MediaItem::GallerySection,
            'images' => [],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('images');
});

it('replaces the oldest gallery image when an upload fills the homepage selection', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $oldestMedia = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'show_on_homepage' => true,
        'created_at' => now()->subMinutes(18),
    ]);
    MediaItem::factory()
        ->count(MediaItem::sectionLimit(MediaItem::GallerySection) - 1)
        ->create([
            'section' => MediaItem::FullGallerySection,
            'show_on_homepage' => true,
        ]);

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::GallerySection,
            'images' => [UploadedFile::fake()->image('jemaah.jpg', 1200, 800)],
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::GallerySection]));

    $newMedia = MediaItem::query()->latest('id')->firstOrFail();

    expect($oldestMedia->fresh()->show_on_homepage)->toBeFalse()
        ->and($newMedia->show_on_homepage)->toBeTrue()
        ->and(MediaItem::query()->forSection(MediaItem::FullGallerySection)->visibleOnHomepage()->count())->toBe(18);
});

it('allows batch uploads to the full gallery with a package category', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create(['name' => 'Pakej Turkiye']);

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::FullGallerySection]))
        ->assertOk()
        ->assertSee('name="gallery_destination"', false)
        ->assertSee('value="about"', false)
        ->assertSee('Tentang Kami')
        ->assertSee('Pakej Turkiye');

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::FullGallerySection,
            'package_category_id' => $category->id,
            'images' => [
                UploadedFile::fake()->image('turkiye-pertama.jpg', 1200, 800),
                UploadedFile::fake()->image('turkiye-kedua.jpg', 1200, 800),
            ],
            'is_published' => false,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::FullGallerySection]));

    expect(MediaItem::query()
        ->where('section', MediaItem::FullGallerySection)
        ->where('package_category_id', $category->id)
        ->count())->toBe(2);
    expect(MediaItem::query()
        ->where('section', MediaItem::FullGallerySection)
        ->where('package_category_id', $category->id)
        ->where('is_published', true)
        ->count())->toBe(2);
    expect(MediaItem::query()->forSection(MediaItem::FullGallerySection)->visibleOnHomepage()->count())->toBe(0);
});

it('stores the About category separately from the public full gallery', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::FullGallerySection,
            'gallery_destination' => MediaItem::AboutSection,
            'images' => [UploadedFile::fake()->image('tentang-kami.jpg', 1200, 800)],
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::AboutSection]));

    $mediaItem = MediaItem::query()->latest('id')->firstOrFail();

    expect($mediaItem->section)->toBe(MediaItem::AboutSection)
        ->and($mediaItem->package_category_id)->toBeNull()
        ->and($mediaItem->show_on_homepage)->toBeFalse();
});

it('filters the full gallery by package category, About collection, and unassigned media', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create(['name' => 'Pakej Turkiye']);
    $inactiveCategory = PackageCategory::factory()->create([
        'name' => 'Pakej Lama',
        'is_active' => false,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Foto Baitullah Ditapis',
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Foto Umum Ditapis',
        'package_category_id' => null,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Foto Turkiye Ditapis',
        'package_category_id' => $category->id,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Foto Lama Ditapis',
        'package_category_id' => $inactiveCategory->id,
    ]);

    $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'section' => MediaItem::FullGallerySection,
            'gallery_filter' => 'all',
        ]))
        ->assertOk()
        ->assertSee('Tapis Galeri Keseluruhan')
        ->assertSee('data-gallery-filter="about"', false)
        ->assertSee('data-gallery-filter="general"', false)
        ->assertSee("data-gallery-filter=\"category-{$category->id}\"", false)
        ->assertSee("data-gallery-filter=\"category-{$inactiveCategory->id}\"", false)
        ->assertSee('Foto Baitullah Ditapis')
        ->assertSee('Foto Umum Ditapis')
        ->assertSee('Foto Turkiye Ditapis')
        ->assertSee('Foto Lama Ditapis');

    $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'section' => MediaItem::FullGallerySection,
            'gallery_filter' => 'about',
        ]))
        ->assertOk()
        ->assertSee('Foto Baitullah Ditapis')
        ->assertDontSee('Foto Umum Ditapis')
        ->assertDontSee('Foto Turkiye Ditapis')
        ->assertDontSee('Foto Lama Ditapis');

    $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'section' => MediaItem::FullGallerySection,
            'gallery_filter' => 'general',
        ]))
        ->assertOk()
        ->assertDontSee('Foto Baitullah Ditapis')
        ->assertSee('Foto Umum Ditapis')
        ->assertDontSee('Foto Turkiye Ditapis')
        ->assertDontSee('Foto Lama Ditapis');

    $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'section' => MediaItem::FullGallerySection,
            'gallery_filter' => "category-{$category->id}",
        ]))
        ->assertOk()
        ->assertDontSee('Foto Baitullah Ditapis')
        ->assertDontSee('Foto Umum Ditapis')
        ->assertSee('Foto Turkiye Ditapis')
        ->assertDontSee('Foto Lama Ditapis');

    $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'section' => MediaItem::FullGallerySection,
            'gallery_filter' => "category-{$inactiveCategory->id}",
        ]))
        ->assertOk()
        ->assertDontSee('Foto Baitullah Ditapis')
        ->assertDontSee('Foto Umum Ditapis')
        ->assertDontSee('Foto Turkiye Ditapis')
        ->assertSee('Foto Lama Ditapis');
});

it('moves a full gallery image to the About collection when selected during editing', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $mediaItem = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'package_category_id' => $category->id,
        'is_published' => true,
        'show_on_homepage' => false,
    ]);

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::FullGallerySection]))
        ->assertOk()
        ->assertSee('name="gallery_destination"', false)
        ->assertSee('Tentang Kami');

    $this->actingAs($staff)
        ->put(route('admin.media.update', $mediaItem), [
            'gallery_destination' => MediaItem::AboutSection,
            'is_published' => true,
            'show_on_homepage' => false,
            'return_section' => MediaItem::FullGallerySection,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::AboutSection]));

    $this->assertDatabaseHas('media_items', [
        'id' => $mediaItem->id,
        'section' => MediaItem::AboutSection,
        'package_category_id' => null,
        'is_published' => true,
        'show_on_homepage' => false,
    ]);
});

it('saves changes to all gallery cards in one request', function () {
    $staff = User::factory()->staff()->create();
    $firstCategory = PackageCategory::factory()->create();
    $secondCategory = PackageCategory::factory()->create();
    $featuredMedia = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'package_category_id' => $firstCategory->id,
        'show_on_homepage' => true,
    ]);
    $galleryMedia = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'package_category_id' => $firstCategory->id,
        'show_on_homepage' => false,
    ]);

    $this->actingAs($staff)
        ->putJson(route('admin.media.bulk-update'), [
            'section' => MediaItem::FullGallerySection,
            'items' => [
                [
                    'id' => $featuredMedia->id,
                    'package_category_id' => $secondCategory->id,
                    'is_published' => true,
                    'show_on_homepage' => false,
                ],
                [
                    'id' => $galleryMedia->id,
                    'package_category_id' => $secondCategory->id,
                    'is_published' => false,
                    'show_on_homepage' => true,
                ],
            ],
        ])
        ->assertOk()
        ->assertJsonPath('redirect_url', route('admin.media.index', ['section' => MediaItem::FullGallerySection]));

    $this->assertDatabaseHas('media_items', [
        'id' => $featuredMedia->id,
        'package_category_id' => $secondCategory->id,
        'is_published' => true,
        'show_on_homepage' => false,
    ]);
    $this->assertDatabaseHas('media_items', [
        'id' => $galleryMedia->id,
        'package_category_id' => $secondCategory->id,
        'is_published' => true,
        'show_on_homepage' => true,
    ]);
});

it('replaces the oldest featured gallery image when staff ticks another gallery card', function () {
    $staff = User::factory()->staff()->create();
    $oldestMedia = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'show_on_homepage' => true,
        'created_at' => now()->subMinutes(18),
    ]);
    MediaItem::factory()
        ->count(MediaItem::sectionLimit(MediaItem::GallerySection) - 1)
        ->create([
            'section' => MediaItem::FullGallerySection,
            'show_on_homepage' => true,
        ]);
    $candidateMedia = MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'show_on_homepage' => false,
    ]);

    $this->actingAs($staff)
        ->putJson(route('admin.media.bulk-update'), [
            'section' => MediaItem::FullGallerySection,
            'items' => [[
                'id' => $candidateMedia->id,
                'is_published' => true,
                'show_on_homepage' => true,
            ]],
        ])
        ->assertOk();

    expect($oldestMedia->fresh()->show_on_homepage)->toBeFalse()
        ->and($candidateMedia->fresh()->show_on_homepage)->toBeTrue()
        ->and(MediaItem::query()->forSection(MediaItem::FullGallerySection)->visibleOnHomepage()->count())->toBe(18);
});

it('requires a package category for the full gallery', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::FullGallerySection,
            'images' => [UploadedFile::fake()->image('tanpa-kategori.jpg', 1200, 800)],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors('gallery_destination');
});

it('only displays published media from each homepage section on the public website', function () {
    MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Foto Bersama Menuju Baitullah',
        'is_published' => true,
        'show_on_homepage' => true,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Foto Kenangan Jemaah',
        'is_published' => true,
        'show_on_homepage' => true,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Foto disorokkan',
        'is_published' => false,
        'show_on_homepage' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Foto Bersama Menuju Baitullah')
        ->assertSee('Foto Kenangan Jemaah')
        ->assertDontSee('Foto disorokkan');
});

it('serves uploaded public media through the application media endpoint', function () {
    Storage::fake('public', ['url' => '/media']);

    $path = UploadedFile::fake()->image('poster-umrah.jpg', 1200, 800)->store('hero', 'public');
    $url = Storage::disk('public')->url($path);

    expect($url)->toStartWith('/media/');

    $this->get($url)
        ->assertOk()
        ->assertHeader('content-type', 'image/jpeg');
});

it('forbids non-staff users from managing gallery media', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.media.index'))
        ->assertForbidden();
});

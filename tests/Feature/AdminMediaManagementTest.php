<?php

use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('allows staff to upload multiple, update, and delete gallery media', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::GallerySection,
            'images' => [
                UploadedFile::fake()->image('jemaah-pertama.jpg', 1200, 800),
                UploadedFile::fake()->image('jemaah-kedua.jpg', 1200, 800),
            ],
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::GallerySection]));

    $mediaItems = MediaItem::query()->orderBy('sort_order')->get();
    $mediaItem = $mediaItems->firstOrFail();

    expect($mediaItems)->toHaveCount(2);
    expect($mediaItem->is_published)->toBeTrue();
    expect($mediaItem->section)->toBe(MediaItem::GallerySection);
    expect($mediaItem->alt_text)->toBe('Foto Kenangan Indah Jemaah Bersama SalamHaji: jemaah pertama');

    foreach ($mediaItems as $item) {
        Storage::disk('public')->assertExists($item->path);
    }

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::GallerySection]))
        ->assertOk()
        ->assertSee('Bersama Menuju Baitullah')
        ->assertSee('Kenangan Indah Jemaah Bersama SalamHaji')
        ->assertSee('Gunakan gambar landskap (melintang) untuk galeri bergerak jemaah pada laman utama.')
        ->assertSee('Seret dan lepas gambar di sini');

    $this->actingAs($staff)
        ->get(route('admin.media.index', ['section' => MediaItem::AboutSection]))
        ->assertOk()
        ->assertSee('Gunakan gambar potret (menegak) untuk kolaj empat gambar pada bahagian pengenalan laman utama.');

    $this->actingAs($staff)
        ->put(route('admin.media.update', $mediaItem), [
            'is_published' => false,
            'sort_order' => 99,
        ])
        ->assertRedirect(route('admin.media.index', ['section' => MediaItem::GallerySection]));

    $this->assertDatabaseHas('media_items', [
        'id' => $mediaItem->id,
        'alt_text' => 'Foto Kenangan Indah Jemaah Bersama SalamHaji: jemaah pertama',
        'caption' => null,
        'is_published' => false,
        'sort_order' => 1,
    ]);

    $path = $mediaItem->path;

    $this->actingAs($staff)
        ->delete(route('admin.media.destroy', $mediaItem))
        ->assertRedirect();

    $this->assertDatabaseMissing('media_items', ['id' => $mediaItem->id]);
    Storage::disk('public')->assertMissing($path);
});

it('prevents uploads beyond a homepage section image limit', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    MediaItem::factory()
        ->count(MediaItem::sectionLimit(MediaItem::AboutSection))
        ->create(['section' => MediaItem::AboutSection]);

    $this->actingAs($staff)
        ->post(route('admin.media.store'), [
            'section' => MediaItem::AboutSection,
            'images' => [UploadedFile::fake()->image('jemaah.jpg', 1200, 800)],
        ])
        ->assertRedirect()
        ->assertSessionHasErrors([
            'images' => 'Bahagian Bersama Menuju Baitullah hanya boleh memaparkan maksimum 4 gambar. Baki slot: 0.',
        ]);

    expect(MediaItem::query()->forSection(MediaItem::AboutSection)->count())->toBe(4);
});

it('only displays published media from each homepage section on the public website', function () {
    MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Foto Bersama Menuju Baitullah',
        'is_published' => true,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::GallerySection,
        'alt_text' => 'Foto Kenangan Jemaah',
        'is_published' => true,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::GallerySection,
        'alt_text' => 'Foto disorokkan',
        'is_published' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Foto Bersama Menuju Baitullah')
        ->assertSee('Foto Kenangan Jemaah')
        ->assertDontSee('Foto disorokkan');
});

it('forbids non-staff users from managing gallery media', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.media.index'))
        ->assertForbidden();
});

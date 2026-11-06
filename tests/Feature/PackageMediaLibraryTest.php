<?php

use App\Actions\PackageMediaUsageReporter;
use App\Actions\StorePackageMediaAsset;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageMediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('stores an identical package upload once in the reusable media library', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('masjid.jpg', 1600, 900);
    $mediaLibrary = app(StorePackageMediaAsset::class);

    $firstAsset = $mediaLibrary->handle($image, PackageMediaAsset::LandscapeFolder);
    $secondAsset = $mediaLibrary->handle($image, PackageMediaAsset::LandscapeFolder);

    expect($secondAsset->id)->toBe($firstAsset->id)
        ->and(PackageMediaAsset::query()->count())->toBe(1);
    Storage::disk('public')->assertExists($firstAsset->path);
});

it('names a package upload after its related item and can find it again by that name', function () {
    Storage::fake('public');
    $mediaLibrary = app(StorePackageMediaAsset::class);

    $asset = $mediaLibrary->handle(
        UploadedFile::fake()->image('kamera-lama.jpg', 1600, 900),
        PackageMediaAsset::HotelFolder,
        'Hotel Al Safwah',
    );

    expect(pathinfo($asset->original_name, PATHINFO_FILENAME))->toBe('Hotel Al Safwah')
        ->and($mediaLibrary->pathForName(PackageMediaAsset::HotelFolder, 'hotel al safwah'))->toBe($asset->path)
        ->and($asset->path)->toContain('hotel-al-safwah');
});

it('reports where package media is used and exposes the package media tab to staff', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $asset = PackageMediaAsset::factory()->create([
        'folder' => PackageMediaAsset::PosterFolder,
        'path' => 'packages/media/poster/pakej-umrah.jpg',
        'width' => 900,
        'height' => 1400,
    ]);
    Storage::disk('public')->put($asset->path, 'test-image');
    $category = PackageCategory::factory()->create();
    $package = Package::factory()->for($category, 'category')->create([
        'title' => 'Pakej Media Bersama',
        'poster_image' => $asset->path,
    ]);
    Package::factory()->for($category, 'category')->count(10)->create(['poster_image' => $asset->path]);

    $usages = app(PackageMediaUsageReporter::class)->usagesFor(collect([$asset]));

    expect($usages->get($asset->path))->toHaveCount(11)
        ->and($usages->get($asset->path)->first()['package']->is($package))->toBeTrue()
        ->and($usages->get($asset->path)->first()['section'])->toBe('Poster');

    $response = $this->actingAs($staff)
        ->get(route('admin.media.index', [
            'tab' => 'package-media',
            'folder' => PackageMediaAsset::PosterFolder,
        ]));

    $response
        ->assertOk()
        ->assertSee('Media Pakej')
        ->assertSee('Pakej Media Bersama')
        ->assertSee('digunakan 11 kali')
        ->assertSee('Lihat penggunaan')
        ->assertSee('Penggunaan media')
        ->assertSee('aria-label="Padam media"', false)
        ->assertSee('data-package-media-usage-pagination', false);

    expect(substr_count($response->getContent(), 'data-package-media-usage-row'))->toBe(11);
});

it('renames package media and keeps every current package reference intact', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $oldPath = 'packages/media/penginapan-hotel/hotel-lama.jpg';
    $asset = PackageMediaAsset::factory()->create([
        'folder' => PackageMediaAsset::HotelFolder,
        'path' => $oldPath,
        'original_name' => 'Hotel Lama.jpg',
    ]);
    Storage::disk('public')->put($oldPath, 'hotel-image');
    $package = Package::factory()->create([
        'cover_image' => $oldPath,
        'poster_image' => $oldPath,
        'hotel_options' => [['name' => 'Hotel Lama', 'images' => [$oldPath]]],
        'room_prices' => [['room_type' => 'Double', 'image' => $oldPath]],
        'ziarah_places' => [['name' => 'Masjid', 'image' => $oldPath]],
        'gifts' => [['title' => 'Beg', 'image' => $oldPath]],
    ]);
    $package->images()->create(['path' => $oldPath, 'sort_order' => 1]);

    $this->actingAs($staff)
        ->put(route('admin.media.package-assets.rename', $asset), ['name' => 'Hotel Baharu'])
        ->assertRedirect(route('admin.media.index', [
            'tab' => 'package-media',
            'folder' => PackageMediaAsset::HotelFolder,
        ]));

    $asset->refresh();
    $package->refresh();

    expect($asset->original_name)->toBe('Hotel Baharu.jpg')
        ->and($asset->path)->toBe('packages/media/penginapan-hotel/hotel-baharu.jpg')
        ->and($package->cover_image)->toBe($asset->path)
        ->and($package->poster_image)->toBe($asset->path)
        ->and($package->hotel_options[0]['images'])->toBe([$asset->path])
        ->and($package->room_prices[0]['image'])->toBe($asset->path)
        ->and($package->ziarah_places[0]['image'])->toBe($asset->path)
        ->and($package->gifts[0]['image'])->toBe($asset->path)
        ->and($package->images()->sole()->path)->toBe($asset->path);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($asset->path);
});

it('deletes a package media asset and removes every package reference', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    $path = 'packages/media/penginapan-hotel/hotel-lama.jpg';
    $asset = PackageMediaAsset::factory()->create([
        'folder' => PackageMediaAsset::HotelFolder,
        'path' => $path,
    ]);
    Storage::disk('public')->put($path, 'hotel-image');
    $package = Package::factory()->create([
        'cover_image' => $path,
        'poster_image' => $path,
        'flight_options' => [['images' => [$path]]],
        'hotel_options' => [['images' => [$path]]],
        'room_prices' => [['image' => $path]],
        'ziarah_places' => [['image' => $path]],
        'gifts' => [['image' => $path]],
    ]);
    $package->images()->create(['path' => $path, 'sort_order' => 1]);

    $this->actingAs($staff)
        ->delete(route('admin.media.package-assets.destroy', $asset))
        ->assertRedirect(route('admin.media.index', [
            'tab' => 'package-media',
            'folder' => PackageMediaAsset::HotelFolder,
        ]));

    $this->assertModelMissing($asset);
    Storage::disk('public')->assertMissing($path);

    $package->refresh();

    expect($package->cover_image)->toBeNull()
        ->and($package->poster_image)->toBeNull()
        ->and($package->flight_options[0]['images'])->toBe([])
        ->and($package->hotel_options[0]['images'])->toBe([])
        ->and($package->room_prices[0]['image'])->toBeNull()
        ->and($package->ziarah_places[0]['image'])->toBeNull()
        ->and($package->gifts[0]['image'])->toBeNull()
        ->and($package->images()->count())->toBe(0);
});

<?php

use App\Actions\GenerateImageDerivatives;
use App\Actions\PruneImageDerivatives;
use App\Jobs\ProcessImageDerivatives;
use App\Models\ImageDerivative;
use App\Models\MediaItem;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Once;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public', ['url' => '/media']);
});

function storeFakeImage(string $name, int $width, int $height, string $directory = 'media/gallery'): string
{
    return UploadedFile::fake()->image($name, $width, $height)->store($directory, 'public');
}

/**
 * A JPEG whose EXIF Orientation tag says it must be rotated 90° clockwise to display upright.
 */
function jpegWithOrientation(int $orientation, int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    ob_start();
    imagejpeg($image);
    $jpeg = (string) ob_get_clean();

    $tiff = 'MM'."\x00\x2A".pack('N', 8).pack('n', 1).pack('nnN', 0x0112, 3, 1).pack('n', $orientation)."\x00\x00".pack('N', 0);
    $exif = 'Exif'."\x00\x00".$tiff;
    $segment = "\xFF\xE1".pack('n', strlen($exif) + 2).$exif;

    return substr($jpeg, 0, 2).$segment.substr($jpeg, 2);
}

it('generates 640px and 1280px webp copies and keeps the original', function () {
    $path = storeFakeImage('jemaah.jpg', 2000, 1000);

    $derivative = app(GenerateImageDerivatives::class)->handle($path);

    Storage::disk('public')->assertExists($path);

    expect($derivative->width)->toBe(2000)
        ->and($derivative->height)->toBe(1000)
        ->and(array_keys($derivative->variants))->toBe([640, 1280]);

    $small = imagecreatefromstring(Storage::disk('public')->get($derivative->variants[640]));
    $large = imagecreatefromstring(Storage::disk('public')->get($derivative->variants[1280]));

    expect([imagesx($small), imagesy($small)])->toBe([640, 320])
        ->and([imagesx($large), imagesy($large)])->toBe([1280, 640])
        ->and(Storage::disk('public')->mimeType($derivative->variants[640]))->toBe('image/webp');
});

it('never upscales images smaller than the target widths', function () {
    $path = storeFakeImage('kecil.jpg', 500, 300);

    $derivative = app(GenerateImageDerivatives::class)->handle($path);
    $variant = imagecreatefromstring(Storage::disk('public')->get($derivative->variants[500]));

    expect(array_keys($derivative->variants))->toBe([500])
        ->and([imagesx($variant), imagesy($variant)])->toBe([500, 300]);
});

it('keeps transparency when converting png images', function () {
    $image = imagecreatetruecolor(800, 400);
    imagealphablending($image, false);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    ob_start();
    imagepng($image);
    Storage::disk('public')->put('branding/logo.png', (string) ob_get_clean());

    $derivative = app(GenerateImageDerivatives::class)->handle('branding/logo.png');
    $variant = imagecreatefromstring(Storage::disk('public')->get($derivative->variants[640]));

    expect((imagecolorat($variant, 10, 10) >> 24) & 0x7F)->toBe(127);
});

it('applies exif orientation before resizing', function () {
    Storage::disk('public')->put('media/gallery/sisi.jpg', jpegWithOrientation(6, 40, 20));

    $derivative = app(GenerateImageDerivatives::class)->handle('media/gallery/sisi.jpg');

    expect([$derivative->width, $derivative->height])->toBe([20, 40]);
})->skip(! function_exists('exif_read_data'), 'Sambungan exif tidak dipasang.');

it('skips svg and other non raster files', function () {
    Storage::disk('public')->put('airlines/logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>');

    expect(app(GenerateImageDerivatives::class)->handle('airlines/logo.svg'))->toBeNull()
        ->and(ImageDerivative::query()->count())->toBe(0);
});

it('keeps derivatives while the shared original still exists', function () {
    $path = storeFakeImage('kongsi.jpg', 1400, 700);
    $derivative = app(GenerateImageDerivatives::class)->handle($path);

    expect(app(PruneImageDerivatives::class)->handle($path))->toBeFalse();

    Storage::disk('public')->assertExists($derivative->variants[640]);
    expect(ImageDerivative::query()->where('path', $path)->exists())->toBeTrue();
});

it('removes derivatives once the original has been deleted', function () {
    $path = storeFakeImage('lama.jpg', 1400, 700);
    $derivative = app(GenerateImageDerivatives::class)->handle($path);
    Storage::disk('public')->delete($path);

    expect(app(PruneImageDerivatives::class)->handle($path))->toBeTrue();

    Storage::disk('public')->assertMissing($derivative->variants[640]);
    Storage::disk('public')->assertMissing($derivative->variants[1280]);
    expect(ImageDerivative::query()->where('path', $path)->exists())->toBeFalse();
});

it('queues generation and pruning when image attributes change', function () {
    Bus::fake();

    $firstPath = storeFakeImage('pertama.jpg', 1000, 500);
    $mediaItem = MediaItem::factory()->create(['path' => $firstPath]);

    Bus::assertDispatched(ProcessImageDerivatives::class, fn (ProcessImageDerivatives $job): bool => $job->pathsToGenerate === [$firstPath] && $job->pathsToPrune === []);

    $secondPath = storeFakeImage('kedua.jpg', 1000, 500);
    $mediaItem->update(['path' => $secondPath]);

    Bus::assertDispatched(ProcessImageDerivatives::class, fn (ProcessImageDerivatives $job): bool => $job->pathsToGenerate === [$secondPath] && $job->pathsToPrune === [$firstPath]);

    $mediaItem->delete();

    Bus::assertDispatched(ProcessImageDerivatives::class, fn (ProcessImageDerivatives $job): bool => $job->pathsToGenerate === [] && $job->pathsToPrune === [$secondPath]);
});

it('does not queue work when unrelated attributes change', function () {
    $mediaItem = MediaItem::factory()->create(['path' => storeFakeImage('sedia.jpg', 800, 400)]);
    Bus::fake();

    $mediaItem->update(['caption' => 'Kapsyen baharu']);

    Bus::assertNotDispatched(ProcessImageDerivatives::class);
});

it('creates derivatives after an admin upload and removes them after replacement', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->post(route('admin.media.store'), [
        'section' => MediaItem::GallerySection,
        'images' => [UploadedFile::fake()->image('jemaah.jpg', 1600, 900)],
    ])->assertRedirect();

    $mediaItem = MediaItem::query()->sole();
    $originalPath = $mediaItem->path;
    $originalVariants = ImageDerivative::query()->where('path', $originalPath)->sole()->variants;

    Storage::disk('public')->assertExists($originalVariants[640]);

    $this->actingAs($staff)->put(route('admin.media.update', $mediaItem), [
        'section' => $mediaItem->section,
        'image' => UploadedFile::fake()->image('ganti.jpg', 1600, 900),
        'alt_text' => 'Gambar ganti',
        'is_published' => true,
    ])->assertRedirect();

    $mediaItem->refresh();

    expect($mediaItem->path)->not->toBe($originalPath)
        ->and(ImageDerivative::query()->where('path', $mediaItem->path)->exists())->toBeTrue()
        ->and(ImageDerivative::query()->where('path', $originalPath)->exists())->toBeFalse();

    Storage::disk('public')->assertMissing($originalVariants[640]);
});

it('renders srcset and dimensions when derivatives exist and the plain original otherwise', function () {
    $path = storeFakeImage('poster.jpg', 1600, 900);
    $url = Storage::disk('public')->url($path);

    $fallback = Blade::render('<x-responsive-image :src="$src" sizes="50vw" alt="Poster" loading="lazy" />', ['src' => $url]);

    expect($fallback)->toContain('src="'.$url.'"')
        ->not->toContain('srcset=')
        ->toContain('loading="lazy"');

    app(GenerateImageDerivatives::class)->handle($path);
    Once::flush();

    $responsive = Blade::render('<x-responsive-image :src="$src" sizes="50vw" alt="Poster" :priority="true" />', ['src' => $url]);

    expect($responsive)->toContain('src="'.$url.'"')
        ->toContain('srcset="/media/_derivatives/'.$path.'.640.webp 640w, /media/_derivatives/'.$path.'.1280.webp 1280w"')
        ->toContain('sizes="50vw"')
        ->toContain('width="1600"')
        ->toContain('height="900"')
        ->toContain('fetchpriority="high"');
});

it('uses the committed webp copies for bundled site images', function () {
    $html = Blade::render('<x-responsive-image :src="$src" alt="" />', ['src' => asset('darul-shofi/images/hero/umrah-ekonomi-madinah-hero.png')]);

    expect($html)->toContain('umrah-ekonomi-madinah-hero.png.640.webp 640w')
        ->toContain('width="1672"');
});

it('backfills existing uploads resumably and supports a dry run', function () {
    Bus::fake();
    MediaItem::factory()->create(['path' => storeFakeImage('a.jpg', 900, 600)]);
    MediaItem::factory()->create(['path' => storeFakeImage('b.jpg', 900, 600)]);

    $this->artisan('images:derivatives', ['--dry-run' => true])
        ->expectsOutputToContain('2 imej perlu diproses')
        ->assertSuccessful();

    expect(ImageDerivative::query()->count())->toBe(0);

    $this->artisan('images:derivatives', ['--limit' => 1])->assertSuccessful();
    expect(ImageDerivative::query()->count())->toBe(1);

    $this->artisan('images:derivatives')->expectsOutputToContain('1 imej perlu diproses')->assertSuccessful();
    $this->artisan('images:derivatives')->expectsOutputToContain('0 imej perlu diproses')->assertSuccessful();
});

it('prunes orphaned derivative files and records', function () {
    $path = storeFakeImage('yatim.jpg', 900, 600);
    app(GenerateImageDerivatives::class)->handle($path);
    Storage::disk('public')->put(ImageDerivative::Directory.'/sesat.jpg.640.webp', 'x');
    Storage::disk('public')->delete($path);

    $this->artisan('images:derivatives', ['--prune' => true, '--dry-run' => true])->assertSuccessful();
    expect(ImageDerivative::query()->count())->toBe(1);

    $this->artisan('images:derivatives', ['--prune' => true])
        ->expectsOutputToContain('1 rekod dan 1 fail')
        ->assertSuccessful();

    expect(ImageDerivative::query()->count())->toBe(0)
        ->and(Storage::disk('public')->allFiles(ImageDerivative::Directory))->toBe([]);
});

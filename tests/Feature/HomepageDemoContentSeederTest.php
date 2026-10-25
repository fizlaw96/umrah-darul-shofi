<?php

use App\Models\Article;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Enquiry;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\MediaItem;
use App\Models\Mutawif;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageImage;
use App\Models\PackageItinerary;
use App\Models\PackageServiceOption;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteSetting;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoContentSeeder;
use Database\Seeders\HeroSlideSeeder;
use Database\Seeders\MediaItemSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('seeds four editable category heroes and four About images without duplicating them', function () {
    $this->seed(DemoContentSeeder::class);
    $this->seed(HeroSlideSeeder::class);
    $this->seed(MediaItemSeeder::class);
    $this->seed(HeroSlideSeeder::class);
    $this->seed(MediaItemSeeder::class);

    expect(HeroSlide::query()->published()->count())->toBe(4);
    expect(MediaItem::query()->forSection(MediaItem::AboutSection)->count())->toBe(4);
    expect(MediaItem::query()->forSection(MediaItem::AboutSection)->firstOrFail()->url())
        ->toContain('darul-shofi/images/about/');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Pakej Umrah')
        ->assertSee('storage/demo/branding/company-logo.png', false)
        ->assertSee('storage/demo/branding/darul-soffi-logo.jpg', false)
        ->assertSee('darul-shofi/images/branding/salam-haji-logo.png', false)
        ->assertSee('SalamHaji Travel & Tours membantu jemaah merancang perjalanan Umrah');
});

it('uses visible package categories as the hero fallback and limits the homepage to four categories', function () {
    foreach (range(1, 5) as $index) {
        $category = PackageCategory::factory()->create([
            'name' => "Kategori {$index}",
            'slug' => "kategori-{$index}",
            'sort_order' => $index,
            'show_on_homepage' => true,
        ]);

        Package::factory()->published()->for($category, 'category')->create([
            'show_on_homepage' => true,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="pakej-kategori-1"', false)
        ->assertSee('id="pakej-kategori-4"', false)
        ->assertDontSee('id="pakej-kategori-5"', false);
});

it('seeds complete development data and local images for public and admin checks', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('role', 'super_admin')->exists())->toBeTrue()
        ->and(PackageCategory::query()->orderBy('sort_order')->pluck('name')->all())->toBe([
            'Pakej Umrah',
            'Pakej Haji',
            'Pakej Badal Haji',
            'Pakej Pelancongan',
        ])
        ->and(Package::query()->count())->toBe(7)
        ->and(Package::query()->published()->count())->toBe(5)
        ->and(Package::query()->where('status', 'draft')->count())->toBe(1)
        ->and(Package::query()->where('status', 'archived')->count())->toBe(1)
        ->and(PackageImage::query()->count())->toBe(21)
        ->and(PackageItinerary::query()->count())->toBe(67)
        ->and(PackageServiceOption::query()->count())->toBe(12)
        ->and(Booking::query()->count())->toBe(4)
        ->and(BookingStatusHistory::query()->count())->toBe(11)
        ->and(Enquiry::query()->count())->toBe(3)
        ->and(Article::query()->count())->toBe(3)
        ->and(Faq::query()->count())->toBe(7)
        ->and(Testimonial::query()->count())->toBe(4)
        ->and(Mutawif::query()->count())->toBe(4)
        ->and(HeroSlide::query()->published()->count())->toBe(4)
        ->and(MediaItem::query()->forSection(MediaItem::GallerySection)->count())->toBe(9)
        ->and(WebsiteSetting::getValue('company_logo_path'))->toBe('demo/branding/company-logo.png')
        ->and(WebsiteSetting::getValue('footer_logo_path'))->toBe('demo/branding/darul-soffi-logo.jpg')
        ->and(WebsiteSetting::getValue('contact_whatsapp'))->toBe('60133212212')
        ->and(Storage::disk('public')->exists('demo/branding/company-logo.png'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/branding/darul-soffi-logo.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/umrah-ekonomi.png'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/umrah-cuti-sekolah.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/gallery/tilawah.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/people/mutawif-1.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/umrah-poster.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/haji-poster.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/badal-haji-poster.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/packages/pelancongan-poster.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/travel/hotels/makkah-arrival.jpg'))->toBeTrue()
        ->and(Storage::disk('public')->exists('demo/content/room-prices/double.jpg'))->toBeTrue();

    $packages = Package::query()
        ->whereIn('slug', [
            'pakej-umrah-ekonomi-9h-7m-demo',
            'pakej-haji-9h-7m-demo',
            'pakej-badal-haji-demo',
            'pakej-pelancongan-muslim-12h-10m-demo',
        ])
        ->get()
        ->keyBy('slug');
    $umrahPackage = $packages->get('pakej-umrah-ekonomi-9h-7m-demo');

    expect($packages)->toHaveCount(4)
        ->and($packages->pluck('cover_image')->every(fn (string $path): bool => str_ends_with($path, '-poster.jpg')))->toBeTrue()
        ->and($packages->every(fn (Package $package): bool => count($package->hotel_options ?? []) === 3))->toBeTrue()
        ->and($packages->every(fn (Package $package): bool => count($package->room_prices ?? []) === 5))->toBeTrue()
        ->and($packages->every(fn (Package $package): bool => count($package->flight_options ?? []) === 2))->toBeTrue()
        ->and($packages->every(fn (Package $package): bool => count($package->ziarah_places ?? []) === 3))->toBeTrue()
        ->and($packages->every(fn (Package $package): bool => count($package->gifts ?? []) === 3))->toBeTrue()
        ->and($umrahPackage?->room_prices[0]['image'] ?? null)->toBe('demo/content/room-prices/quad.jpg')
        ->and($umrahPackage?->hotel_options[0]['images'] ?? null)->toContain('demo/travel/hotels/makkah-arrival.jpg');

    $this->get(route('packages.show', $umrahPackage))
        ->assertOk()
        ->assertSee('/storage/demo/packages/umrah-poster.jpg', false)
        ->assertSee('/storage/demo/travel/hotels/makkah-arrival.jpg', false)
        ->assertSee('/storage/demo/content/room-prices/quad.jpg', false)
        ->assertSee('Al Kiswah Towers Hotel');
});

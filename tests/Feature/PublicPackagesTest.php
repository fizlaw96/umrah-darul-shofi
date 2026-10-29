<?php

use App\Models\HeroSlide;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\PackageParticipant;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders the SalamHaji homepage with its standalone assets', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertViewIs('darul-shofi.home')
        ->assertSee('darul-shofi/css/site.css', false)
        ->assertSee('darul-shofi/js/site.js', false)
        ->assertSee('darul-shofi/images/branding/darul-soffi-logo.jpg', false)
        ->assertSee('ds-global-footer', false)
        ->assertSee('Maklumat &amp; Panduan', false)
        ->assertSee('Buka dalam Google Maps', false);
});

it('renders the shared footer on catalogue pages', function () {
    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSee('darul-shofi/css/footer.css', false)
        ->assertSee('ds-global-footer--spaced', false)
        ->assertSee('Kami komited membimbing perjalanan ibadah anda ke Tanah Suci', false);
});

it('renders the mobile menu, cinematic package carousel, and gallery lightbox', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-mobile-menu-toggle', false)
        ->assertSee('data-hero-carousel', false)
        ->assertSee('data-hero-video', false)
        ->assertSee('data-hero-slide', false)
        ->assertSee('Lihat Semua Pakej')
        ->assertDontSee('data-hero-slider', false)
        ->assertDontSee('data-hero-prev', false)
        ->assertSee('id="darul-gallery-lightbox"', false)
        ->assertDontSee('data-gallery-caption', false);
});

it('only includes homepage categories that have a public package', function () {
    $umrah = PackageCategory::factory()->create([
        'name' => 'Pakej Umrah',
        'slug' => 'pakej-umrah',
        'description' => 'Pilihan perjalanan Umrah.',
        'sort_order' => 1,
        'is_active' => true,
        'show_on_homepage' => true,
    ]);
    $haji = PackageCategory::factory()->create([
        'name' => 'Pakej Haji',
        'slug' => 'pakej-haji',
        'description' => 'Pilihan perjalanan Haji.',
        'sort_order' => 2,
        'is_active' => true,
        'show_on_homepage' => true,
    ]);
    HeroSlide::factory()->for($umrah, 'category')->create([
        'title' => $umrah->name,
        'subtitle' => $umrah->description,
        'is_published' => true,
        'sort_order' => 1,
    ]);
    Package::factory()->published()->for($umrah, 'category')->create([
        'starting_price_sen' => 650000,
    ]);

    $response = $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-hero-package-name="Pakej Umrah"', false)
        ->assertDontSee('data-hero-package-name="Pakej Haji"', false);

    expect(substr_count($response->getContent(), 'data-hero-slide'))->toBe(1);
});

it('falls back to the contact section when WhatsApp is not configured', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Sila hubungi kami')
        ->assertDontSee('https://wa.me/', false);
});

it('uses the configured Darul Shofi WhatsApp number', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    config()->set('darul_shofi.whatsapp_display', '+60 12-345 6789');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('https://wa.me/60123456789?text=', false)
        ->assertSee('Hotline WhatsApp: +60 12-345 6789');
});

it('shows published packages and hides drafts from the catalogue', function () {
    $category = PackageCategory::factory()->create(['slug' => 'ekonomi']);
    $published = Package::factory()->published()->for($category, 'category')->create(['title' => 'Pakej Yang Dipaparkan']);
    $draft = Package::factory()->for($category, 'category')->create(['title' => 'Pakej Draf Rahsia']);

    $this->get(route('packages.index', ['category' => $category->slug]))
        ->assertOk()
        ->assertSee($published->title)
        ->assertSee('Logo Pusat Pendidikan dan Bimbingan Darul Soffi')
        ->assertDontSee($draft->title);
});

it('places recommended packages first and shows the selected promotion badge', function () {
    $category = PackageCategory::factory()->create();
    $recommendedPackage = Package::factory()->published()->for($category, 'category')->create([
        'title' => 'Pakej Recommendation',
        'is_featured' => true,
        'promotion_badge' => Package::RecommendationBadge,
        'published_at' => now(),
    ]);
    $superDealPackage = Package::factory()->published()->for($category, 'category')->create([
        'title' => 'Pakej Super Deal',
        'is_featured' => true,
        'promotion_badge' => Package::SuperDealBadge,
        'published_at' => now()->subWeek(),
    ]);

    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSeeInOrder([$recommendedPackage->title, $superDealPackage->title])
        ->assertSee('Recommendation')
        ->assertSee('Super Deal')
        ->assertSee('package-card__badge--recommendation', false)
        ->assertSee('package-card__badge--super-deal', false);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('ds-package-badge--super-deal', false);
});

it('shows closed departures publicly but hides packages with draft-only departures', function () {
    $closedPackage = Package::factory()->published()->create(['title' => 'Pakej Jadual Ditutup']);
    $draftOnlyPackage = Package::factory()->published()->create(['title' => 'Pakej Jadual Draf']);
    $departureDate = now()->addDays(31)->startOfDay();

    PackageDeparture::factory()->for($closedPackage)->create([
        'departure_date' => $departureDate,
        'return_date' => $departureDate->copy()->addDays(8),
        'status' => 'closed',
    ]);
    PackageDeparture::factory()->for($draftOnlyPackage)->create([
        'departure_date' => $departureDate,
        'return_date' => $departureDate->copy()->addDays(8),
        'status' => 'draft',
    ]);

    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSee($closedPackage->title)
        ->assertDontSee($draftOnlyPackage->title);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee($closedPackage->title)
        ->assertDontSee($draftOnlyPackage->title);

    $this->get(route('packages.show', $closedPackage))
        ->assertOk()
        ->assertSee('Ditutup')
        ->assertSee('Tarikh ini tidak lagi tersedia.')
        ->assertDontSee('>Pilih <span', false);

    $this->get(route('packages.show', $draftOnlyPackage))->assertNotFound();
});

it('directs a catalogue card to its package detail instead of WhatsApp', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    $package = Package::factory()->published()->create(['title' => 'Pakej WhatsApp']);

    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSee(route('packages.show', $package), false)
        ->assertSee('aria-label="Lihat pakej Pakej WhatsApp"', false)
        ->assertDontSee('https://wa.me/', false);
});

it('renders catalogue posters as lightbox controls and titles as package links', function () {
    $package = Package::factory()->published()->create([
        'title' => 'Pakej Poster Katalog',
        'poster_image' => 'packages/media/poster/pakej-poster-katalog.jpg',
    ]);

    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSee('data-package-poster-lightbox', false)
        ->assertSee('data-package-poster', false)
        ->assertSee('aria-label="Lihat poster Pakej Poster Katalog"', false)
        ->assertSee('max-h-full max-w-full object-contain', false)
        ->assertSee(route('packages.show', $package), false)
        ->assertSee('snap-x', false)
        ->assertDontSee('data-package-scroll', false);
});

it('includes the package link and main image in WhatsApp enquiries', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    $package = Package::factory()->published()->create([
        'title' => 'Pakej Haji 9H 7M',
        'cover_image' => 'packages/haji-utama.jpg',
    ]);
    $packageUrl = route('packages.show', $package);
    $whatsAppUrl = 'https://wa.me/60123456789?text='.rawurlencode(
        'Assalamualaikum, saya ingin bertanya tentang pakej Pakej Haji 9H 7M.'."\n\n".$packageUrl,
    );

    $this->get($packageUrl)
        ->assertOk()
        ->assertSee('WhatsApp Kami')
        ->assertSee($whatsAppUrl, false)
        ->assertDontSee('Pasukan kami sedia membantu semak pilihan perjalanan anda.')
        ->assertSee('<meta property="og:image" content="'.url('/media/packages/haji-utama.jpg').'">', false)
        ->assertDontSee('/hubungi-kami', false);
});

it('shows a short confirmation note for a flexible package', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    $package = Package::factory()->published()->create([
        'title' => 'Pakej Umrah Fleksibel',
        'is_date_flexible' => true,
    ]);
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => now()->subMonth()->toDateString(),
        'return_date' => now()->subMonth()->addDays(8)->toDateString(),
        'status' => 'closed',
    ]);

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('Tarikh perjalanan akan disahkan oleh agensi. Hubungi kami untuk memilih bulan yang sesuai.')
        ->assertSee('WhatsApp Kami')
        ->assertDontSee('Tarikh fleksibel')
        ->assertDontSee('data-flexible-departure-picker', false)
        ->assertDontSee('data-flexible-departure-date', false)
        ->assertDontSee('Pilih tarikh &amp; WhatsApp', false)
        ->assertDontSee('Tarikh baharu akan dikemas kini oleh agensi.');
});

it('renders the floating WhatsApp button without a mobile bottom navigation bar', function () {
    config()->set('darul_shofi.whatsapp_number', '60 12-345 6789');
    $package = Package::factory()->published()->create();

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('fixed bottom-6 right-5', false)
        ->assertDontSee('aria-label="Navigasi bawah"', false);
});

it('filters published packages by departure month', function () {
    $matchingPackage = Package::factory()->published()->create(['title' => 'Pakej Tarikh Sesuai']);
    $otherPackage = Package::factory()->published()->create(['title' => 'Pakej Tarikh Lain']);
    $date = now()->addMonths(2)->startOfDay();

    PackageDeparture::factory()->for($matchingPackage)->create(['departure_date' => $date, 'return_date' => $date->copy()->addDays(8)]);
    PackageDeparture::factory()->for($otherPackage)->create(['departure_date' => $date->copy()->subMonth(), 'return_date' => $date->copy()->subMonth()->addDays(8)]);

    $this->get(route('packages.index', ['month' => $date->format('Y-m')]))
        ->assertOk()
        ->assertSee($matchingPackage->title)
        ->assertDontSee($otherPackage->title);
});

it('includes flexible packages when a departure month is selected', function () {
    $package = Package::factory()->published()->create([
        'title' => 'Pakej Fleksibel Setiap Bulan',
        'is_date_flexible' => true,
    ]);
    $selectedMonth = now()->addMonths(2)->format('Y-m');

    $this->get(route('packages.index', ['month' => $selectedMonth]))
        ->assertOk()
        ->assertSee($package->title)
        ->assertSee('Tarikh akan disahkan');
});

it('filters published packages by the selected budget range', function () {
    $lowerBudgetPackage = Package::factory()->published()->create([
        'title' => 'Pakej Bajet Rendah',
        'starting_price_sen' => 450_000,
    ]);
    $matchingBudgetPackage = Package::factory()->published()->create([
        'title' => 'Pakej Bajet Sesuai',
        'starting_price_sen' => 650_000,
    ]);
    $higherBudgetPackage = Package::factory()->published()->create([
        'title' => 'Pakej Bajet Tinggi',
        'starting_price_sen' => 850_000,
    ]);

    $this->get(route('packages.index', ['min_price' => 6000, 'max_price' => 7000]))
        ->assertOk()
        ->assertSee($matchingBudgetPackage->title)
        ->assertDontSee($lowerBudgetPackage->title)
        ->assertDontSee($higherBudgetPackage->title);
});

it('renders automatic package filters without a keyword field', function () {
    Package::factory()->published()->create(['starting_price_sen' => 650_000]);

    $this->get(route('packages.index'))
        ->assertOk()
        ->assertSee('data-package-filters', false)
        ->assertSee('data-budget-minimum', false)
        ->assertSee('data-budget-maximum', false)
        ->assertSee('data-auto-submit', false)
        ->assertDontSee('name="q"', false);
});

it('shows a published package by slug and returns 404 for an invalid slug', function () {
    $package = Package::factory()->published()->create([
        'title' => 'Pakej Safwah Premium',
        'short_description' => 'Penginapan berhampiran Masjidil Haram dengan bimbingan mutawwif.',
        'duration_days' => 9,
        'duration_nights' => 7,
        'departure_airport' => 'KLIA Terminal 1',
        'airline' => 'Saudia Airlines',
        'starting_price_sen' => 849_000,
        'seo_title' => 'Tajuk SEO lama yang tidak digunakan',
        'seo_description' => 'Penerangan SEO lama yang tidak digunakan.',
    ]);

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee($package->title)
        ->assertSee('<title>Pakej Safwah Premium | Umrah 9 Hari 7 Malam ·', false)
        ->assertSee('content="Pakej Safwah Premium. Pakej Umrah 9 Hari 7 Malam, berlepas dari KLIA Terminal 1, dengan Saudia Airlines, harga bermula RM 8,490.00 seorang.', false)
        ->assertDontSee('Tajuk SEO lama yang tidak digunakan')
        ->assertDontSee('Penerangan SEO lama yang tidak digunakan.');

    $this->get('/pakej-umrah/pakej-tidak-wujud')->assertNotFound();
});

it('renders no more than three unique images in the package hero', function () {
    $package = Package::factory()->published()->create([
        'cover_image' => 'packages/cover.jpg',
    ]);

    $package->images()->createMany([
        ['path' => 'packages/cover.jpg', 'sort_order' => 1],
        ['path' => 'packages/galeri-satu.jpg', 'sort_order' => 2],
        ['path' => 'packages/galeri-dua.jpg', 'sort_order' => 3],
        ['path' => 'packages/galeri-tiga.jpg', 'sort_order' => 4],
    ]);

    $response = $this->get(route('packages.show', $package));

    $response
        ->assertOk()
        ->assertSee('3 foto galeri')
        ->assertSee('/media/packages/galeri-satu.jpg', false)
        ->assertSee('/media/packages/galeri-dua.jpg', false)
        ->assertDontSee('/media/packages/galeri-tiga.jpg', false);

    expect(substr_count($response->getContent(), '/media/packages/cover.jpg'))->toBe(2);
});

it('shows the return date for each available departure', function () {
    $package = Package::factory()->published()->create();
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2027-04-26',
        'return_date' => '2027-05-04',
    ]);

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('Pulang 04 May 2027');
});

it('subtracts added participants from the available seats for their departure', function () {
    $package = Package::factory()->published()->create();
    $firstDeparture = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2027-04-26',
        'return_date' => '2027-05-04',
        'capacity' => 10,
    ]);
    $secondDeparture = PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2027-06-26',
        'return_date' => '2027-07-04',
        'capacity' => 10,
    ]);

    PackageParticipant::factory()->for($package)->for($firstDeparture, 'departure')->count(2)->create();
    PackageParticipant::factory()->for($package)->for($secondDeparture, 'departure')->create();
    PackageParticipant::factory()->for($package)->create();
    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('7 tempat')
        ->assertSee('8 tempat');
});

it('shows hotel and attached room media in the same carousel without repeating the cover image', function () {
    $coverImageUrl = 'https://images.example.test/umrah-cover.jpg';
    $hotelKey = 'a7bf1944-4e2c-4ab0-afcd-4907213027cb';
    $package = Package::factory()->published()->create([
        'cover_image' => $coverImageUrl,
        'hotel_options' => [[
            'key' => $hotelKey,
            'location' => 'Makkah',
            'name' => 'Anjum Hotel Makkah',
            'rating' => 5,
            'walking_distance' => '5 minit ke Masjidil Haram',
            'images' => [
                'packages/travel/hotels/anjum-hotel.jpg',
                'packages/travel/hotels/anjum-room.jpg',
            ],
        ]],
        'room_prices' => [[
            'hotel_key' => $hotelKey,
            'room_type' => 'Bilik Double',
            'price_sen' => 699000,
            'image' => 'packages/content/room-prices/anjum-double.jpg',
        ]],
        'flight_options' => [[
            'airline' => 'Saudia Airlines',
            'departure_airport' => 'KLIA Terminal 1',
            'arrival_airport' => 'Jeddah',
            'images' => ['packages/travel/flights/saudia-airlines.jpg'],
        ]],
    ]);

    $response = $this->get(route('packages.show', $package));

    $response
        ->assertOk()
        ->assertSee('data-travel-media', false)
        ->assertSee('Anjum Hotel Makkah')
        ->assertSee('Saudia Airlines')
        ->assertSee('/media/packages/travel/hotels/anjum-hotel.jpg', false)
        ->assertSee('/media/packages/travel/hotels/anjum-room.jpg', false)
        ->assertSee('/media/packages/content/room-prices/anjum-double.jpg', false)
        ->assertSee('Gambar penginapan seterusnya')
        ->assertSee('data-hotel-lightbox-trigger', false)
        ->assertSee('data-hotel-lightbox', false)
        ->assertSee('/media/packages/travel/flights/saudia-airlines.jpg', false);

    expect(substr_count($response->getContent(), $coverImageUrl))->toBe(2);
});

it('shows ziarah and gift images alongside their package details', function () {
    $package = Package::factory()->published()->create([
        'ziarah_places' => [[
            'name' => 'Masjid Quba',
            'location' => 'Madinah',
            'description' => 'Masjid pertama dalam sejarah Islam.',
            'image' => 'packages/content/ziarah/masjid-quba.jpg',
        ]],
        'gifts' => [[
            'title' => 'Beg Sandang Umrah',
            'description' => 'Satu unit untuk setiap jemaah.',
            'image' => 'packages/content/gifts/beg-sandang.jpg',
        ]],
    ]);

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('Masjid Quba')
        ->assertSee('Beg Sandang Umrah')
        ->assertSee('/media/packages/content/ziarah/masjid-quba.jpg', false)
        ->assertSee('/media/packages/content/gifts/beg-sandang.jpg', false);
});

it('renders an itinerary download without displaying deprecated manual highlights', function () {
    $package = Package::factory()->published()->create([
        'highlights' => [[
            'title' => 'Bimbingan mutawif',
            'description' => 'Panduan sepanjang perjalanan ibadah.',
            'icon' => 'support_agent',
        ]],
        'itinerary_pdf' => 'packages/itineraries/atur-cara.pdf',
    ]);

    $this->get(route('packages.show', $package))
        ->assertOk()
        ->assertSee('Pengenalan Pakej')
        ->assertDontSee('Bimbingan mutawif')
        ->assertSee('Muat Turun Itinerari (PDF)')
        ->assertSee('/media/packages/itineraries/atur-cara.pdf', false);
});

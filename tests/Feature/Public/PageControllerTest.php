<?php

use App\Models\Faq;
use App\Models\MediaItem;
use App\Models\PackageCategory;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders all published FAQs in latest insertion order', function () {
    Faq::factory()->create(['question' => 'Soalan FAQ Lama']);
    Faq::factory()->create(['question' => 'Soalan FAQ Terkini']);
    Faq::factory()->create([
        'question' => 'Soalan FAQ Draf',
        'is_published' => false,
    ]);

    $this->get(route('faq'))
        ->assertOk()
        ->assertSeeInOrder(['Soalan FAQ Terkini', 'Soalan FAQ Lama'])
        ->assertDontSee('Soalan FAQ Draf');
});

it('renders the About page with managed copy in its hero and its own gallery', function () {
    WebsiteSetting::setValue('about_eyebrow', 'Bersama Menuju Baitullah', 'homepage');
    WebsiteSetting::setValue('about_heading', 'Amanah dalam setiap perjalanan', 'homepage');
    WebsiteSetting::setValue('about_description', 'Kami membimbing jemaah dari persediaan hingga pulang.', 'homepage');
    WebsiteSetting::setValue('about_cta_url', '', 'homepage');

    $aboutMedia = MediaItem::factory()->create([
        'path' => 'darul-shofi/images/branding/salam-haji-logo.png',
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Jemaah SalamHaji',
        'sort_order' => 1,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Gambar About Draf',
        'is_published' => false,
    ]);

    $this->get(route('about'))
        ->assertOk()
        ->assertViewIs('darul-shofi.about')
        ->assertSee('Bersama Menuju Baitullah')
        ->assertSee('Amanah dalam setiap perjalanan')
        ->assertSee('<h1>Amanah dalam setiap perjalanan</h1>', false)
        ->assertSee('Kami membimbing jemaah dari persediaan hingga pulang.')
        ->assertSee($aboutMedia->url(), false)
        ->assertSee('alt="Jemaah SalamHaji"', false)
        ->assertSee('ds-about-page__gallery-divider', false)
        ->assertSee('<em>Tentang Kami</em>', false)
        ->assertDontSee('Galeri Tentang Kami')
        ->assertSee('data-full-gallery-lightbox', false)
        ->assertDontSee('data-about-cta class=', false)
        ->assertDontSee('Gambar About Draf');
});

it('renders published gallery media and filters for linked package categories', function () {
    $category = PackageCategory::factory()->create(['name' => 'Pakej Turkiye']);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Kenangan homepage',
        'show_on_homepage' => true,
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'package_category_id' => $category->id,
        'alt_text' => 'Kenangan Turkiye',
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::AboutSection,
        'alt_text' => 'Kenangan Tentang Kami',
    ]);
    MediaItem::factory()->create([
        'section' => MediaItem::FullGallerySection,
        'alt_text' => 'Kenangan draf',
        'is_published' => false,
    ]);

    $this->get(route('gallery.index'))
        ->assertOk()
        ->assertViewIs('darul-shofi.gallery')
        ->assertSee('Kenangan homepage')
        ->assertSee('Kenangan Turkiye')
        ->assertSee('Pakej Turkiye')
        ->assertSee('data-full-gallery-lightbox', false)
        ->assertDontSee('ds-full-gallery-item__detail', false)
        ->assertDontSee('Kenangan Tentang Kami')
        ->assertDontSee('Kenangan draf');
});

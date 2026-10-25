<?php

use App\Models\Faq;
use App\Models\MediaItem;
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

it('renders the About page with the homepage About content', function () {
    WebsiteSetting::setValue('about_eyebrow', 'Ibadah dengan tenang', 'homepage');
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
        ->assertSee('Ibadah dengan tenang')
        ->assertSee('Amanah dalam setiap perjalanan')
        ->assertSee('<h1 class="font-serif-luxury', false)
        ->assertSee('Kami membimbing jemaah dari persediaan hingga pulang.')
        ->assertSee($aboutMedia->url(), false)
        ->assertSee('alt="Jemaah SalamHaji"', false)
        ->assertSee('data-about-cta class=', false)
        ->assertDontSee('Gambar About Draf');
});

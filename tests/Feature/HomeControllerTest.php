<?php

use App\Models\Faq;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders only the first three published testimonials with profile images', function () {
    $firstTestimonial = Testimonial::factory()->create([
        'reviewer_name' => 'Jemaah Pertama',
        'review' => 'Urusan kami dipermudahkan dari awal hingga akhir.',
        'photo' => 'testimonials/jemaah-pertama.jpg',
        'sort_order' => 1,
    ]);
    $secondTestimonial = Testimonial::factory()->create([
        'reviewer_name' => 'Jemaah Kedua',
        'sort_order' => 2,
    ]);
    $thirdTestimonial = Testimonial::factory()->create([
        'reviewer_name' => 'Jemaah Ketiga',
        'sort_order' => 3,
    ]);
    $fourthTestimonial = Testimonial::factory()->create([
        'reviewer_name' => 'Jemaah Keempat',
        'sort_order' => 4,
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee($firstTestimonial->reviewer_name)
        ->assertSee($secondTestimonial->reviewer_name)
        ->assertSee($thirdTestimonial->reviewer_name)
        ->assertDontSee($fourthTestimonial->reviewer_name)
        ->assertSee($firstTestimonial->photoUrl(), false);

    expect(substr_count($response->getContent(), 'data-testimonial-card'))->toBe(3);
});

it('renders the five latest published FAQs with a link to the full FAQ page', function () {
    Faq::factory()->create(['question' => 'Soalan FAQ 1']);
    Faq::factory()->create(['question' => 'Soalan FAQ 2']);
    Faq::factory()->create(['question' => 'Soalan FAQ 3']);
    Faq::factory()->create(['question' => 'Soalan FAQ 4']);
    Faq::factory()->create(['question' => 'Soalan FAQ 5']);
    Faq::factory()->create(['question' => 'Soalan FAQ 6']);
    Faq::factory()->create(['question' => 'Soalan FAQ 7']);
    Faq::factory()->create([
        'question' => 'Soalan FAQ Draf',
        'is_published' => false,
    ]);

    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSeeInOrder([
            'Soalan FAQ 7',
            'Soalan FAQ 6',
            'Soalan FAQ 5',
            'Soalan FAQ 4',
            'Soalan FAQ 3',
        ])
        ->assertDontSee('Soalan FAQ 2')
        ->assertDontSee('Soalan FAQ 1')
        ->assertDontSee('Soalan FAQ Draf')
        ->assertSee(route('faq'), false);
});

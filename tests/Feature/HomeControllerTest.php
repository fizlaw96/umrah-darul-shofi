<?php

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

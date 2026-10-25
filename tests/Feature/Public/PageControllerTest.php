<?php

use App\Models\Faq;
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

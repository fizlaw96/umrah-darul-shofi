<?php

use App\Models\Enquiry;
use App\Models\Package;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders the public enquiry form', function () {
    $this->get(route('contact.create'))
        ->assertOk()
        ->assertSee('Hantar pertanyaan');
});

it('stores a validated public enquiry', function () {
    $package = Package::factory()->published()->create();

    $this->post(route('contact.store'), [
        'package_id' => $package->id,
        'name' => 'Siti Hajar',
        'phone' => '0191234567',
        'email' => 'siti@example.test',
        'message' => 'Saya ingin mengetahui tarikh yang sesuai.',
        'privacy_consent' => '1',
    ])->assertSessionHasNoErrors();

    $this->assertDatabaseHas('enquiries', [
        'package_id' => $package->id,
        'name' => 'Siti Hajar',
        'status' => 'new',
    ]);

    expect(Enquiry::query()->count())->toBe(1);
});

it('requires privacy consent for a public enquiry', function () {
    $this->post(route('contact.store'), [
        'name' => 'Siti Hajar',
        'phone' => '0191234567',
        'email' => 'siti@example.test',
        'message' => 'Saya ingin mengetahui tarikh yang sesuai.',
    ])->assertSessionHasErrors('privacy_consent');
});

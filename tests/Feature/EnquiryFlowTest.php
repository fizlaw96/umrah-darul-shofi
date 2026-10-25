<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('returns 404 for the retired public contact page', function () {
    $this->get('/hubungi-kami')->assertNotFound();
    $this->post('/hubungi-kami')->assertNotFound();
});

it('does not expose the retired admin enquiry module', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get('/admin/enquiries')
        ->assertNotFound();
});

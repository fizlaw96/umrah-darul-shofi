<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('redirects guests away from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids a non-staff user from the admin dashboard', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

it('allows staff into the admin dashboard', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)->get(route('admin.dashboard'))->assertOk();
});

it('forbids non-staff users from creating packages', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->post(route('admin.packages.store'), [])
        ->assertForbidden();
});

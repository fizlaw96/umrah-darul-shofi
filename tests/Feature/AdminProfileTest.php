<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('shows the authenticated staff profile and links to it from the admin header', function () {
    $staff = User::factory()->staff()->create([
        'name' => 'Pentadbir Salam Haji',
        'email' => 'admin@example.test',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.profile.edit'))
        ->assertOk()
        ->assertSee('Profil Saya')
        ->assertSee('Pentadbir Salam Haji')
        ->assertSee('admin@example.test')
        ->assertSee('href="'.route('admin.profile.edit').'"', false);
});

it('updates the authenticated staff profile and password', function () {
    $staff = User::factory()->staff()->create([
        'name' => 'Nama Asal',
        'email' => 'asal@example.test',
        'password' => 'password',
    ]);

    $this->actingAs($staff)
        ->put(route('admin.profile.update'), [
            'name' => 'Nama Baharu',
            'email' => 'baharu@example.test',
            'current_password' => 'password',
            'password' => 'kata-laluan-baharu',
            'password_confirmation' => 'kata-laluan-baharu',
        ])
        ->assertRedirect(route('admin.profile.edit'))
        ->assertSessionHas('success', 'Profil anda berjaya dikemas kini.');

    $staff->refresh();

    expect($staff->name)->toBe('Nama Baharu')
        ->and($staff->email)->toBe('baharu@example.test')
        ->and(Hash::check('kata-laluan-baharu', $staff->password))->toBeTrue();
});

it('requires the current password before changing it', function () {
    $staff = User::factory()->staff()->create(['password' => 'password']);
    $passwordHash = $staff->password;

    $this->actingAs($staff)
        ->from(route('admin.profile.edit'))
        ->put(route('admin.profile.update'), [
            'name' => $staff->name,
            'email' => $staff->email,
            'current_password' => 'password-yang-salah',
            'password' => 'kata-laluan-baharu',
            'password_confirmation' => 'kata-laluan-baharu',
        ])
        ->assertRedirect(route('admin.profile.edit'))
        ->assertSessionHasErrors('current_password');

    expect($staff->fresh()->password)->toBe($passwordHash);
});

it('forbids non-staff users from viewing their profile', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->get(route('admin.profile.edit'))
        ->assertForbidden();
});

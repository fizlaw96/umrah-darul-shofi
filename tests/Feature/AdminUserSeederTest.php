<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('seeds a verified super administrator without resetting an existing password', function () {
    config()->set('admin_seeder.name', 'Pentadbir Ujian');
    config()->set('admin_seeder.email', 'pentadbir@example.test');
    config()->set('admin_seeder.password', 'kata-laluan-ujian');

    $this->seed(AdminUserSeeder::class);

    $administrator = User::query()->where('email', 'pentadbir@example.test')->sole();

    expect($administrator->name)->toBe('Pentadbir Ujian')
        ->and($administrator->role)->toBe('super_admin')
        ->and($administrator->email_verified_at)->not->toBeNull()
        ->and(Hash::check('kata-laluan-ujian', $administrator->password))->toBeTrue();

    $passwordHash = $administrator->password;

    $this->seed(AdminUserSeeder::class);

    expect(User::query()->where('email', 'pentadbir@example.test')->sole()->password)->toBe($passwordHash);
});

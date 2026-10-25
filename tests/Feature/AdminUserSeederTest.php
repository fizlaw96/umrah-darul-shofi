<?php

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

it('seeds a verified super administrator without resetting an existing password', function () {
    config()->set('admin_seeder.name', 'Pentadbir Ujian');
    config()->set('admin_seeder.email', 'pentadbir@example.test');

    $this->seed(AdminUserSeeder::class);

    $administrator = User::query()->where('email', 'pentadbir@example.test')->sole();

    expect($administrator->name)->toBe('Pentadbir Ujian')
        ->and($administrator->role)->toBe('super_admin')
        ->and($administrator->email_verified_at)->not->toBeNull()
        ->and(Hash::check('password', $administrator->password))->toBeTrue();

    $passwordHash = $administrator->password;

    $this->seed(AdminUserSeeder::class);

    expect(User::query()->where('email', 'pentadbir@example.test')->sole()->password)->toBe($passwordHash);
});

it('uses only the administrator seeder by default', function () {
    config()->set('admin_seeder.email', 'admin@example.test');

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(1)
        ->and(User::query()->sole()->email)->toBe('admin@example.test');
});

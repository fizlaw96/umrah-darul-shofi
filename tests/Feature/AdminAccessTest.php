<?php

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Notification;

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

it('shows departure months and paginates dashboard packages', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $pageTwoPackage = Package::factory()->for($category, 'category')->create([
        'title' => 'Pakej Halaman Kedua',
    ]);

    Package::factory()
        ->count(9)
        ->for($category, 'category')
        ->create();

    $departurePackage = Package::factory()->for($category, 'category')->create([
        'title' => 'Pakej Berlepas Disember',
    ]);

    PackageDeparture::factory()->for($departurePackage)->create([
        'departure_date' => '2099-12-10',
        'return_date' => '2099-12-19',
        'status' => 'open',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('Paparan homepage')
        ->assertSee('Jadual berlepas aktif')
        ->assertSeeInOrder(['Jadual berlepas aktif', '1'])
        ->assertSee('Bulan berlepas')
        ->assertSee('Pakej Berlepas Disember')
        ->assertSee('2099')
        ->assertDontSee($pageTwoPackage->title)
        ->assertSee('Menunjukkan 1 hingga 10 daripada 11 rekod pakej')
        ->assertSee('page=2', false);

    $this->actingAs($staff)
        ->get(route('admin.dashboard', ['page' => 2]))
        ->assertOk()
        ->assertSee($pageTwoPackage->title)
        ->assertDontSee('Pakej Berlepas Disember')
        ->assertSee('Menunjukkan 11 hingga 11 daripada 11 rekod pakej');
});

it('authenticates a staff member and regenerates the administrator session', function () {
    $staff = User::factory()->staff()->create([
        'email' => 'admin@darulshofi.test',
        'password' => 'password',
    ]);

    $this->post(route('admin.login.store'), [
        'email' => $staff->email,
        'password' => 'password',
        'remember' => true,
    ])->assertRedirect(route('admin.dashboard'));

    $this->assertAuthenticatedAs($staff);
});

it('selects persistent login by default for the administrator sign-in form', function () {
    $this->get(route('admin.login'))
        ->assertOk()
        ->assertSee('name="remember"', false)
        ->assertSee('checked', false)
        ->assertSee('Kekal log masuk pada peranti ini');
});

it('rejects invalid administrator credentials', function () {
    $staff = User::factory()->staff()->create();

    $this->from(route('admin.login'))
        ->post(route('admin.login.store'), [
            'email' => $staff->email,
            'password' => 'not-the-password',
        ])
        ->assertRedirect(route('admin.login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('sends password reset links only to staff accounts', function () {
    Notification::fake();

    $staff = User::factory()->staff()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $this->post(route('admin.password.email'), ['email' => $staff->email])
        ->assertSessionHas('status');

    Notification::assertSentTo($staff, ResetPassword::class);

    $this->post(route('admin.password.email'), ['email' => $customer->email])
        ->assertSessionHas('status');

    Notification::assertNotSentTo($customer, ResetPassword::class);
});

it('forbids non-staff users from creating packages', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($user)
        ->post(route('admin.packages.store'), [])
        ->assertForbidden();
});

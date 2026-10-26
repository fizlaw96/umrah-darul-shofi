<?php

use App\Models\AirlineCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('lets staff create a normalised airline logo that is shown on the homepage', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.airline-companies.store'), [
            'name' => 'Salam Airways',
            'logo' => UploadedFile::fake()->image('salam-airways.png', 900, 300),
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.airline-companies.index'));

    $airlineCompany = AirlineCompany::query()->sole();

    expect($airlineCompany->logo)->toStartWith('airlines/')->toEndWith('.png');
    Storage::disk('public')->assertExists($airlineCompany->logo);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Salam Airways')
        ->assertSee($airlineCompany->logoUrl(), false);
});

it('limits the airline directory to twenty companies', function () {
    $staff = User::factory()->staff()->create();
    AirlineCompany::factory()->count(AirlineCompany::MaximumCompanies)->create();

    $this->actingAs($staff)
        ->post(route('admin.airline-companies.store'), [
            'name' => 'Syarikat Ke-21',
            'logo' => UploadedFile::fake()->image('too-many.png', 900, 300),
        ])
        ->assertSessionHasErrors('name');

    expect(AirlineCompany::query()->count())->toBe(AirlineCompany::MaximumCompanies);
});

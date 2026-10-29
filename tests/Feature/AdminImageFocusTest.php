<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('allows staff to save a non-destructive image focus position', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $path = UploadedFile::fake()->image('jemaah.jpg', 1200, 800)->store('media/gallery', 'public');

    $this->actingAs($staff)
        ->putJson(route('admin.image-focus.update'), [
            'path' => $path,
            'position_x' => 34,
            'position_y' => 72,
        ])
        ->assertOk()
        ->assertJsonPath('position', '34% 72%');

    $this->assertDatabaseHas('image_foci', [
        'path' => $path,
        'position_x' => 34,
        'position_y' => 72,
    ]);
});

it('does not allow a focus position to be saved for a missing image', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->putJson(route('admin.image-focus.update'), [
            'path' => 'media/gallery/missing.jpg',
            'position_x' => 50,
            'position_y' => 50,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('path');
});

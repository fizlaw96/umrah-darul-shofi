<?php

use App\Models\Faq;
use App\Models\PackageCategory;
use App\Models\User;
use Database\Seeders\DefaultContentSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('seeds package categories for the package dropdown and five published FAQs', function () {
    $this->seed(DefaultContentSeeder::class);

    expect(PackageCategory::query()->whereIn('slug', [
        'pakej-umrah',
        'pakej-haji',
        'pakej-badal-haji',
        'pakej-pelancongan',
    ])->count())->toBe(4)
        ->and(Faq::query()->published()->count())->toBe(5);

    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.packages.create'))
        ->assertOk()
        ->assertSee('Pakej Umrah')
        ->assertSee('Pakej Haji')
        ->assertSee('Pakej Badal Haji')
        ->assertSee('Pakej Pelancongan');

    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('Bagaimanakah saya bertanya tentang pakej?')
        ->assertSee('Bagaimanakah status tempahan dikemas kini?');
});

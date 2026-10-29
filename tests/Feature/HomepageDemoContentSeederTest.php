<?php

use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('uses visible package categories as the hero fallback and limits the homepage to four categories', function () {
    foreach (range(1, 5) as $index) {
        $category = PackageCategory::factory()->create([
            'name' => "Kategori {$index}",
            'slug' => "kategori-{$index}",
            'sort_order' => $index,
            'show_on_homepage' => true,
        ]);

        Package::factory()->published()->for($category, 'category')->create([
            'show_on_homepage' => true,
        ]);
    }

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="pakej-kategori-1"', false)
        ->assertSee('id="pakej-kategori-4"', false)
        ->assertDontSee('id="pakej-kategori-5"', false);
});

it('does not show an active homepage category when it has no public packages', function () {
    PackageCategory::factory()->create([
        'name' => 'Pakej Menanti',
        'slug' => 'pakej-menanti',
        'description' => 'Pakej baharu akan diumumkan.',
        'sort_order' => 1,
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    PackageCategory::factory()->create([
        'name' => 'Kategori Tersembunyi',
        'slug' => 'kategori-tersembunyi',
        'sort_order' => 2,
        'is_active' => true,
        'show_on_homepage' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('id="pakej-pakej-menanti"', false)
        ->assertDontSee('id="pakej-kategori-tersembunyi"', false);
});

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

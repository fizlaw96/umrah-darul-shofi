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

it('uses the active status as the only homepage visibility control', function () {
    $activeCategory = PackageCategory::factory()->create([
        'name' => 'Pakej Aktif',
        'slug' => 'pakej-aktif',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $activeCategory->forceFill(['show_on_homepage' => false])->save();
    $inactiveCategory = PackageCategory::factory()->create([
        'name' => 'Pakej Tidak Aktif',
        'slug' => 'pakej-tidak-aktif',
        'sort_order' => 2,
        'is_active' => false,
    ]);

    Package::factory()->published()->for($activeCategory, 'category')->create();
    Package::factory()->published()->for($inactiveCategory, 'category')->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="pakej-pakej-aktif"', false)
        ->assertDontSee('id="pakej-pakej-tidak-aktif"', false);
});

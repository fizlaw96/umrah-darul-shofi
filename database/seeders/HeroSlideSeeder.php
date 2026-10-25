<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        $posters = [
            'darul-shofi/images/hero/umrah-ekonomi-madinah-hero.png',
            'darul-shofi/images/hero/umrah-premium-makkah-hero.png',
            'darul-shofi/images/hero/umrah-ramadan-makkah-hero.png',
            'darul-shofi/images/hero/umrah-premium-makkah-hero.png',
        ];

        PackageCategory::query()
            ->active()
            ->visibleOnHomepage()
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->each(function (PackageCategory $category, int $index) use ($posters): void {
                HeroSlide::query()->updateOrCreate(
                    ['package_category_id' => $category->id],
                    [
                        'package_id' => null,
                        'title' => $category->name,
                        'subtitle' => $category->description ?: 'Terokai pilihan perjalanan ibadah bersama SalamHaji.',
                        'cta_label' => 'Terokai Pakej',
                        'background_image' => $posters[$index],
                        'is_published' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            });
    }
}

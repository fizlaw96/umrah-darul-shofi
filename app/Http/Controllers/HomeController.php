<?php

namespace App\Http\Controllers;

use App\Models\AirlineCompany;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\MediaItem;
use App\Models\PackageCategory;
use App\Models\Testimonial;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('darul-shofi.home', [
            'heroSlides' => HeroSlide::query()
                ->whereHas('category', fn ($categoryQuery) => $categoryQuery
                    ->active()
                    ->whereHas('packages', fn ($packageQuery) => $packageQuery->publiclyVisible()))
                ->with(['category', 'package'])
                ->orderBy('sort_order')
                ->get(),
            'homepageCategories' => PackageCategory::query()
                ->active()
                ->whereHas('packages', fn ($packageQuery) => $packageQuery->publiclyVisible())
                ->with(['packages' => fn ($query) => $query
                    ->publiclyVisible()
                    ->with('category')
                    ->withVisibleDepartures()
                    ->orderByDesc('is_featured')
                    ->orderBy('display_order')
                    ->latest('published_at')
                    ->limit(8)])
                ->orderBy('sort_order')
                ->limit(4)
                ->get(),
            'testimonials' => Testimonial::query()
                ->published()
                ->with('package')
                ->orderBy('sort_order')
                ->latest('id')
                ->limit(Testimonial::MaximumPublished)
                ->get(),
            'homeFaqs' => Faq::query()->published()->latest('id')->limit(5)->get(),
            'aboutMedia' => MediaItem::query()
                ->published()
                ->forSection(MediaItem::AboutSection)
                ->visibleOnHomepage()
                ->orderBy('sort_order')
                ->latest('id')
                ->take(MediaItem::sectionLimit(MediaItem::AboutSection))
                ->get(),
            'galleryMedia' => MediaItem::query()
                ->published()
                ->forSection(MediaItem::FullGallerySection)
                ->visibleOnHomepage()
                ->orderBy('sort_order')
                ->latest('id')
                ->take(MediaItem::sectionLimit(MediaItem::GallerySection))
                ->get(),
            'airlineCompanies' => AirlineCompany::query()
                ->published()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }
}

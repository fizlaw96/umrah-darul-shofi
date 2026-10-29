<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\MediaItem;
use App\Models\PackageCategory;
use App\Models\WebsiteSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('darul-shofi.about', [
            'aboutMedia' => MediaItem::query()
                ->published()
                ->forSection(MediaItem::AboutSection)
                ->orderBy('sort_order')
                ->latest('id')
                ->get(),
        ]);
    }

    public function faq(): View
    {
        return view('faq', [
            'faqs' => Faq::query()->published()->latest('id')->get()->groupBy('category'),
        ]);
    }

    public function gallery(): View
    {
        $galleryMedia = MediaItem::query()
            ->published()
            ->forSection(MediaItem::FullGallerySection)
            ->with('category:id,name,slug')
            ->orderBy('section')
            ->orderBy('sort_order')
            ->latest('id')
            ->get();

        return view('darul-shofi.gallery', [
            'galleryMedia' => $galleryMedia,
            'galleryCategories' => PackageCategory::query()
                ->active()
                ->whereKey($galleryMedia->pluck('package_category_id')->filter()->unique())
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
        ]);
    }

    public function terms(): View
    {
        return view('terms', [
            'termsContent' => WebsiteSetting::getValue('terms_content'),
        ]);
    }
}

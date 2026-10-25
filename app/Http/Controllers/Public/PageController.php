<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\MediaItem;
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
                ->take(MediaItem::sectionLimit(MediaItem::AboutSection))
                ->get(),
        ]);
    }

    public function faq(): View
    {
        return view('faq', [
            'faqs' => Faq::query()->published()->latest('id')->get()->groupBy('category'),
        ]);
    }

    public function terms(): View
    {
        return view('terms', [
            'termsContent' => WebsiteSetting::getValue('terms_content'),
        ]);
    }
}

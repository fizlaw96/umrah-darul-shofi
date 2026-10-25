<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\WebsiteSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function about(): View
    {
        return view('about');
    }

    public function faq(): View
    {
        return view('faq', [
            'faqs' => Faq::query()->published()->orderBy('sort_order')->latest('id')->get()->groupBy('category'),
        ]);
    }

    public function terms(): View
    {
        return view('terms', [
            'termsContent' => WebsiteSetting::getValue('terms_content'),
        ]);
    }
}

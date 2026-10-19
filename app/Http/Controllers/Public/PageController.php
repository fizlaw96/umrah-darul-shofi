<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Faq;
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
            'faqs' => Faq::query()->published()->orderBy('category')->orderBy('sort_order')->get()->groupBy('category'),
        ]);
    }
}

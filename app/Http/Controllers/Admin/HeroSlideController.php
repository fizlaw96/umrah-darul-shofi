<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;

class HeroSlideController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('admin.package-categories.index');
    }

    public function edit(HeroSlide $heroSlide): RedirectResponse
    {
        if ($heroSlide->package_category_id === null || $heroSlide->package_id !== null) {
            return redirect()->route('admin.package-categories.index');
        }

        return redirect()->route('admin.package-categories.edit', $heroSlide->package_category_id);
    }
}

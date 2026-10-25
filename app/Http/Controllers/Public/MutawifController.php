<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Mutawif;
use Illuminate\View\View;

class MutawifController extends Controller
{
    public function index(): View
    {
        return view('mutawifs.index', [
            'mutawifs' => Mutawif::query()->published()->orderBy('sort_order')->orderBy('full_name')->get(),
        ]);
    }
}

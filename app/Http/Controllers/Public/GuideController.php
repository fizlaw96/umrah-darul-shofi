<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\View\View;

class GuideController extends Controller
{
    public function index(): View
    {
        return view('guides.index', [
            'articles' => Article::query()->published()->latest('published_at')->paginate(9),
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->status === 'published' && $article->published_at?->isPast(), 404);

        return view('guides.show', compact('article'));
    }
}

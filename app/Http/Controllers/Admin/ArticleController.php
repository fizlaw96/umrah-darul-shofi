<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ArticleRequest;
use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $articles = Article::query()
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.articles.create', ['article' => new Article]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleRequest $request): RedirectResponse
    {
        $article = new Article;
        $this->saveArticle($request, $article);

        return redirect()->route('admin.articles.edit', $article)->with('success', 'Artikel berjaya dicipta.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article): RedirectResponse
    {
        return redirect()->route('admin.articles.edit', $article);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article): View
    {
        return view('admin.articles.edit', compact('article'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleRequest $request, Article $article): RedirectResponse
    {
        $this->saveArticle($request, $article);

        return back()->with('success', 'Artikel berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $article): RedirectResponse
    {
        $article->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return redirect()->route('admin.articles.index')->with('success', 'Artikel dipindahkan ke draf.');
    }

    private function saveArticle(ArticleRequest $request, Article $article): void
    {
        $validated = $request->validated();
        $values = [
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'category' => $validated['category'] ?? null,
            'excerpt' => $validated['excerpt'],
            'body' => $validated['body'],
            'status' => $validated['status'],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
            'published_at' => $validated['status'] === 'published' ? ($article->published_at ?? now()) : null,
        ];

        if ($request->hasFile('cover_image')) {
            $values['cover_image'] = $request->file('cover_image')->store('articles', 'public');
        }

        $article->fill($values);
        $article->save();
    }
}

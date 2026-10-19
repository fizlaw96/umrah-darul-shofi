<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::query()->orderBy('category')->orderBy('sort_order')->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.faqs.create', ['faq' => new Faq]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request): RedirectResponse
    {
        Faq::query()->create([
            ...$request->safe()->except('is_published'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'Soalan lazim berjaya ditambah.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Faq $faq): RedirectResponse
    {
        return redirect()->route('admin.faqs.edit', $faq);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $faq->update([
            ...$request->safe()->except('is_published'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('success', 'Soalan lazim berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'Soalan lazim berjaya dipadamkan.');
    }
}

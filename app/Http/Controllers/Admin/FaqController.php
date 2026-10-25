<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FaqRequest;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::query()->orderBy('sort_order')->latest('id')->paginate(20),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.faqs.create', $this->formViewData(new Faq));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FaqRequest $request): RedirectResponse
    {
        $values = [
            ...$request->safe()->except(['is_published', 'placement']),
            'is_published' => $request->boolean('is_published'),
        ];
        $placement = $request->validated('placement', 'top');

        DB::transaction(function () use ($placement, $values): void {
            if ($placement === 'bottom') {
                $sortOrder = (int) Faq::query()->max('sort_order') + 1;
            } else {
                Faq::query()->increment('sort_order');
                $sortOrder = 1;
            }

            Faq::query()->create([
                ...$values,
                'sort_order' => $sortOrder,
            ]);
        });

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
        return view('admin.faqs.edit', $this->formViewData($faq));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FaqRequest $request, Faq $faq): RedirectResponse
    {
        $values = [
            ...$request->safe()->except(['is_published', 'placement']),
            'is_published' => $request->boolean('is_published'),
        ];
        $placement = $request->validated('placement', 'keep');

        DB::transaction(function () use ($faq, $placement, $values): void {
            $sortOrder = $faq->sort_order;

            if ($placement === 'top' && $faq->sort_order > 1) {
                Faq::query()
                    ->whereKeyNot($faq->id)
                    ->where('sort_order', '<', $faq->sort_order)
                    ->increment('sort_order');

                $sortOrder = 1;
            } elseif ($placement === 'bottom') {
                $maximumSortOrder = (int) Faq::query()->max('sort_order');

                if ($faq->sort_order < $maximumSortOrder) {
                    Faq::query()
                        ->whereKeyNot($faq->id)
                        ->where('sort_order', '>', $faq->sort_order)
                        ->decrement('sort_order');

                    $sortOrder = $maximumSortOrder;
                }
            }

            $faq->update([
                ...$values,
                'sort_order' => $sortOrder,
            ]);
        });

        return back()->with('success', 'Soalan lazim berjaya dikemas kini.');
    }

    /**
     * @return array{faq: Faq, categories: Collection<int, string>}
     */
    private function formViewData(Faq $faq): array
    {
        return [
            'faq' => $faq,
            'categories' => Faq::query()
                ->select('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ];
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

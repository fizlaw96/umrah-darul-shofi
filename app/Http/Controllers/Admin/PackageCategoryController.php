<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageCategoryRequest;
use App\Models\HeroSlide;
use App\Models\PackageCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.package-categories.index', [
            'categories' => PackageCategory::query()
                ->with('heroSlide')
                ->withCount('packages')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            'category' => new PackageCategory,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.package-categories.index');
    }

    public function store(PackageCategoryRequest $request): RedirectResponse
    {
        $values = $this->values($request);
        $values['sort_order'] = ((int) PackageCategory::query()->max('sort_order')) + 1;

        $category = PackageCategory::query()->create($values);
        $this->syncHeroSlide($category);

        return back()->with('success', 'Kategori pakej dan slaid hero asas berjaya ditambah.');
    }

    public function show(PackageCategory $packageCategory): RedirectResponse
    {
        return redirect()->route('admin.package-categories.edit', $packageCategory);
    }

    public function edit(PackageCategory $packageCategory): View
    {
        $packageCategory->load('heroSlide');

        return view('admin.package-categories.edit', compact('packageCategory'));
    }

    public function update(PackageCategoryRequest $request, PackageCategory $packageCategory): RedirectResponse
    {
        $packageCategory->update($this->values($request));
        $heroSlide = $this->syncHeroSlide($packageCategory);
        $this->saveHeroMedia($request, $heroSlide);

        return redirect()->route('admin.package-categories.index')->with('success', 'Kategori pakej berjaya dikemas kini.');
    }

    public function destroy(PackageCategory $packageCategory): RedirectResponse
    {
        if ($packageCategory->packages()->exists()) {
            return back()->with('error', 'Kategori ini tidak boleh dipadamkan kerana masih digunakan oleh pakej sedia ada.');
        }

        $heroSlide = $packageCategory->heroSlide;

        if ($heroSlide instanceof HeroSlide) {
            $this->deleteFile($heroSlide->background_image);
            $this->deleteFile($heroSlide->background_video);
            $heroSlide->delete();
        }

        $packageCategory->delete();

        return redirect()->route('admin.package-categories.index')->with('success', 'Kategori pakej berjaya dipadamkan.');
    }

    /** @return array{name: string, slug: string, description: ?string, is_active: bool} */
    private function values(PackageCategoryRequest $request): array
    {
        $validated = $request->validated();

        return [
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncHeroSlide(PackageCategory $category): HeroSlide
    {
        $values = [
            'package_category_id' => $category->id,
            'package_id' => null,
            'title' => $category->name,
            'subtitle' => $category->description,
            'cta_label' => 'Terokai Pakej',
            'cta_url' => null,
            'is_published' => $category->is_active,
            'sort_order' => $category->sort_order,
        ];

        $heroSlide = HeroSlide::query()->firstOrNew([
            'package_category_id' => $category->id,
            'package_id' => null,
        ]);
        $heroSlide->fill($values);
        $heroSlide->save();

        return $heroSlide;
    }

    private function saveHeroMedia(PackageCategoryRequest $request, HeroSlide $heroSlide): void
    {
        $oldImage = $heroSlide->background_image;
        $oldVideo = $heroSlide->background_video;
        $values = [];
        $storedPaths = [];

        if ($request->hasFile('background_image')) {
            $values['background_image'] = $request->file('background_image')->store('hero', 'public');
            $storedPaths[] = $values['background_image'];
        } elseif ($request->boolean('remove_background_image')) {
            $values['background_image'] = null;
        }

        if ($request->hasFile('background_video')) {
            $values['background_video'] = $request->file('background_video')->store('hero', 'public');
            $storedPaths[] = $values['background_video'];
        } elseif ($request->boolean('remove_background_video')) {
            $values['background_video'] = null;
        }

        if ($values === []) {
            return;
        }

        try {
            $heroSlide->update($values);
        } catch (\Throwable $throwable) {
            Storage::disk('public')->delete($storedPaths);

            throw $throwable;
        }

        if ($oldImage !== $heroSlide->background_image) {
            $this->deleteFile($oldImage);
        }

        if ($oldVideo !== $heroSlide->background_video) {
            $this->deleteFile($oldVideo);
        }
    }

    private function deleteFile(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://', 'darul-shofi/'])) {
            Storage::disk('public')->delete($path);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Package;
use App\Models\PackageCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $packages = Package::query()
            ->with('category')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where('title', 'like', '%'.$request->string('q')->toString().'%');
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.packages.create', [
            'categories' => PackageCategory::query()->orderBy('sort_order')->get(),
            'package' => new Package,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PackageRequest $request): RedirectResponse
    {
        $package = new Package;
        $this->savePackage($request, $package);

        return redirect()->route('admin.packages.edit', $package)->with('success', 'Pakej berjaya dicipta.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package): RedirectResponse
    {
        return redirect()->route('admin.packages.edit', $package);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package): View
    {
        $package->load('images', 'itineraries');

        return view('admin.packages.edit', [
            'categories' => PackageCategory::query()->orderBy('sort_order')->get(),
            'package' => $package,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $this->savePackage($request, $package);

        return back()->with('success', 'Pakej berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $package->update([
            'status' => 'archived',
            'published_at' => null,
        ]);

        return redirect()->route('admin.packages.index')->with('success', 'Pakej telah diarkibkan.');
    }

    private function savePackage(PackageRequest $request, Package $package): void
    {
        $validated = $request->validated();
        $values = [
            'package_category_id' => $validated['package_category_id'],
            'title' => $validated['title'],
            'slug' => Str::slug($validated['slug']),
            'short_description' => $validated['short_description'],
            'description' => $validated['description'],
            'starting_price_sen' => (int) round((float) $validated['starting_price'] * 100),
            'duration_days' => $validated['duration_days'],
            'duration_nights' => $validated['duration_nights'],
            'makkah_nights' => $validated['makkah_nights'] ?? null,
            'madinah_nights' => $validated['madinah_nights'] ?? null,
            'hotel_information' => $validated['hotel_information'] ?? null,
            'flight_information' => $validated['flight_information'] ?? null,
            'inclusions' => $this->lines($validated['inclusions'] ?? null),
            'exclusions' => $this->lines($validated['exclusions'] ?? null),
            'is_featured' => $request->boolean('is_featured'),
            'status' => $validated['status'],
            'seo_title' => $validated['seo_title'] ?? null,
            'seo_description' => $validated['seo_description'] ?? null,
        ];

        if ($request->hasFile('cover_image')) {
            $values['cover_image'] = $request->file('cover_image')->store('packages', 'public');
        }

        if ($values['status'] === 'published') {
            $values['published_at'] = $package->published_at ?? now();
        } else {
            $values['published_at'] = null;
        }

        $package->fill($values);
        $package->save();

        if ($request->hasFile('gallery')) {
            $sortOrder = ((int) $package->images()->max('sort_order')) + 1;

            foreach ($request->file('gallery', []) as $image) {
                $package->images()->create([
                    'path' => $image->store('packages/gallery', 'public'),
                    'sort_order' => $sortOrder++,
                ]);
            }
        }

        if (array_key_exists('itinerary', $validated)) {
            $package->itineraries()->delete();

            foreach ($this->lines($validated['itinerary']) as $index => $line) {
                [$title, $description] = array_pad(explode('|', $line, 2), 2, '');

                $package->itineraries()->create([
                    'day_number' => $index + 1,
                    'title' => filled($title) ? trim($title) : 'Hari '.($index + 1),
                    'description' => filled($description) ? trim($description) : trim($title),
                ]);
            }
        }
    }

    /** @return array<int, string> */
    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value))));
    }
}

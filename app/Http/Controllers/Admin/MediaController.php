<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaItemRequest;
use App\Models\MediaItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $sections = MediaItem::homepageSections();
        $requestedSection = $request->string('section')->toString();
        $selectedSection = array_key_exists($requestedSection, $sections)
            ? $requestedSection
            : MediaItem::GallerySection;

        $allMediaItems = MediaItem::query()
            ->orderBy('section')
            ->orderBy('sort_order')
            ->latest('id')
            ->get();
        $mediaItems = $allMediaItems->where('section', $selectedSection)->values();

        return view('admin.media.index', [
            'mediaItems' => $mediaItems,
            'selectedSection' => $selectedSection,
            'mediaSections' => collect($sections)
                ->map(function (array $details, string $section) use ($allMediaItems): array {
                    $items = $allMediaItems->where('section', $section);

                    return [
                        ...$details,
                        'key' => $section,
                        'items' => $items,
                        'count' => $items->count(),
                    ];
                }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $section = $validated['section'];
        $sortOrder = ((int) MediaItem::query()->forSection($section)->max('sort_order')) + 1;

        foreach ($request->file('images', []) as $image) {
            MediaItem::query()->create([
                'path' => $image->store('media/gallery', 'public'),
                'section' => $section,
                'alt_text' => $this->generatedAltText($image, $section, $sortOrder),
                'is_published' => $request->boolean('is_published'),
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()
            ->route('admin.media.index', ['section' => $section])
            ->with('success', 'Media berjaya dimuat naik.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MediaItemRequest $request, MediaItem $medium): RedirectResponse
    {
        $validated = $request->validated();
        $previousPath = $medium->path;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $medium->path = $image->store('media/gallery', 'public');
            $medium->alt_text = $this->generatedAltText($image, $medium->section, $medium->sort_order);
        }

        $medium->fill([
            'caption' => null,
            'is_published' => $request->boolean('is_published'),
        ]);
        $medium->save();

        if ($previousPath !== $medium->path) {
            $this->deleteStoredFile($previousPath);
        }

        return redirect()
            ->route('admin.media.index', ['section' => $medium->section])
            ->with('success', 'Media berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MediaItem $medium): RedirectResponse
    {
        $path = $medium->path;
        $section = $medium->section;
        $medium->delete();
        $this->deleteStoredFile($path);

        return redirect()
            ->route('admin.media.index', ['section' => $section])
            ->with('success', 'Media galeri telah dibuang.');
    }

    private function generatedAltText(UploadedFile $image, string $section, int $position): string
    {
        $name = Str::limit(
            Str::squish(str_replace(['_', '-'], ' ', pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME))),
            180,
            '',
        );
        $label = MediaItem::homepageSections()[$section]['label'];

        return filled($name)
            ? "Foto {$label}: {$name}"
            : "Foto {$label} {$position}";
    }

    private function deleteStoredFile(string $path): void
    {
        if (! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}

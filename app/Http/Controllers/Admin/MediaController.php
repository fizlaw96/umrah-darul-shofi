<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PackageMediaUsageReporter;
use App\Actions\RenamePackageMediaAsset;
use App\Actions\StorePackageMediaAsset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MediaItemRequest;
use App\Http\Requests\Admin\RenamePackageMediaAssetRequest;
use App\Models\MediaItem;
use App\Models\Package;
use App\Models\PackageMediaAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(
        private readonly StorePackageMediaAsset $packageMediaAssets,
        private readonly PackageMediaUsageReporter $packageMediaUsageReporter,
        private readonly RenamePackageMediaAsset $renamePackageMediaAsset,
    ) {}

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
        $this->packageMediaAssets->registerLegacyPackageAssets(
            Package::query()->with('images:id,package_id,path')->get(),
        );
        $packageMediaAssets = PackageMediaAsset::query()->orderBy('folder')->latest('id')->get();
        $packageMediaUsages = $this->packageMediaUsageReporter->usagesFor($packageMediaAssets);
        $requestedPackageFolder = $request->string('folder')->toString();
        $selectedPackageFolder = array_key_exists($requestedPackageFolder, PackageMediaAsset::folders())
            ? $requestedPackageFolder
            : PackageMediaAsset::FlightFolder;
        $packageMediaAssets = $packageMediaAssets
            ->sortByDesc(fn (PackageMediaAsset $asset): int => $packageMediaUsages->get($asset->path, collect())->count())
            ->values();

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
            'selectedTab' => $request->string('tab')->toString() === 'package-media' ? 'package-media' : 'gallery',
            'packageMediaAssets' => $packageMediaAssets,
            'selectedPackageFolder' => $selectedPackageFolder,
            'selectedPackageMediaAssets' => $packageMediaAssets->where('folder', $selectedPackageFolder)->values(),
            'packageMediaUsages' => $packageMediaUsages,
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

    public function destroyPackageAsset(PackageMediaAsset $packageMediaAsset): RedirectResponse
    {
        $usages = $this->packageMediaUsageReporter->usagesFor(collect([$packageMediaAsset]));

        if (($usages->get($packageMediaAsset->path)?->isNotEmpty()) === true) {
            return back()->with('error', 'Media ini masih digunakan oleh pakej dan tidak boleh dipadamkan.');
        }

        $path = $packageMediaAsset->path;
        $folder = $packageMediaAsset->folder;
        $packageMediaAsset->delete();
        $this->deleteStoredFile($path);

        return redirect()->route('admin.media.index', [
            'tab' => 'package-media',
            'folder' => $folder,
        ])->with('success', 'Media pakej berjaya dipadamkan.');
    }

    public function renamePackageAsset(RenamePackageMediaAssetRequest $request, PackageMediaAsset $packageMediaAsset): RedirectResponse
    {
        $packageMediaAsset = $this->renamePackageMediaAsset->handle(
            $packageMediaAsset,
            $request->string('name')->toString(),
        );

        return redirect()
            ->route('admin.media.index', ['tab' => 'package-media', 'folder' => $packageMediaAsset->folder])
            ->with('success', 'Nama media pakej berjaya dikemas kini.');
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

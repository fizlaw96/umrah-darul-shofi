<?php

namespace App\Http\Controllers\Admin;

use App\Actions\PackageMediaUsageReporter;
use App\Actions\RenamePackageMediaAsset;
use App\Actions\StorePackageMediaAsset;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkMediaItemUpdateRequest;
use App\Http\Requests\Admin\MediaItemRequest;
use App\Http\Requests\Admin\RenamePackageMediaAssetRequest;
use App\Models\MediaItem;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageMediaAsset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
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

        $aboutMediaItems = MediaItem::query()
            ->forSection(MediaItem::AboutSection)
            ->orderBy('sort_order')
            ->latest('id')
            ->get();
        $fullGalleryMediaItems = MediaItem::query()
            ->forSection(MediaItem::FullGallerySection)
            ->orderBy('sort_order')
            ->latest('id')
            ->get();
        $allGalleryMediaItems = $aboutMediaItems
            ->concat($fullGalleryMediaItems)
            ->sortBy('sort_order')
            ->values();
        $galleryFilterCategories = PackageCategory::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
        $packageCategories = $galleryFilterCategories
            ->where('is_active', true)
            ->values();
        $galleryFilters = collect([
            'all' => [
                'label' => 'Semua',
                'count' => $allGalleryMediaItems->count(),
            ],
            'about' => [
                'label' => 'Tentang Kami',
                'count' => $aboutMediaItems->count(),
            ],
            'general' => [
                'label' => 'Umum',
                'count' => $fullGalleryMediaItems->whereNull('package_category_id')->count(),
            ],
        ])->merge($galleryFilterCategories->mapWithKeys(fn (PackageCategory $packageCategory): array => [
            "category-{$packageCategory->id}" => [
                'label' => $packageCategory->name,
                'count' => $fullGalleryMediaItems->where('package_category_id', $packageCategory->id)->count(),
            ],
        ]));
        $requestedGalleryFilter = $request->string('gallery_filter')->toString();
        $selectedGalleryFilter = $galleryFilters->has($requestedGalleryFilter)
            ? $requestedGalleryFilter
            : 'all';
        $mediaItems = match ($selectedSection) {
            MediaItem::AboutSection => $aboutMediaItems,
            MediaItem::GallerySection => $fullGalleryMediaItems
                ->where('show_on_homepage', true)
                ->values(),
            MediaItem::FullGallerySection => match ($selectedGalleryFilter) {
                'about' => $aboutMediaItems,
                'general' => $fullGalleryMediaItems->whereNull('package_category_id')->values(),
                'all' => $allGalleryMediaItems,
                default => $fullGalleryMediaItems
                    ->where('package_category_id', (int) Str::after($selectedGalleryFilter, 'category-'))
                    ->values(),
            },
        };
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
            'galleryFilters' => $galleryFilters,
            'selectedGalleryFilter' => $selectedGalleryFilter,
            'galleryFilterCategories' => $galleryFilterCategories,
            'mediaSections' => collect($sections)
                ->map(function (array $details, string $section) use ($aboutMediaItems, $allGalleryMediaItems, $fullGalleryMediaItems): array {
                    [$items, $count] = match ($section) {
                        MediaItem::AboutSection => [
                            $aboutMediaItems,
                            $aboutMediaItems->where('show_on_homepage', true)->count(),
                        ],
                        MediaItem::GallerySection => [
                            $fullGalleryMediaItems->where('show_on_homepage', true),
                            $fullGalleryMediaItems->where('show_on_homepage', true)->count(),
                        ],
                        default => [$allGalleryMediaItems, $allGalleryMediaItems->count()],
                    };

                    return [
                        ...$details,
                        'key' => $section,
                        'items' => $items,
                        'count' => $count,
                    ];
                }),
            'selectedTab' => $request->string('tab')->toString() === 'package-media' ? 'package-media' : 'gallery',
            'packageMediaAssets' => $packageMediaAssets,
            'selectedPackageFolder' => $selectedPackageFolder,
            'selectedPackageMediaAssets' => $packageMediaAssets->where('folder', $selectedPackageFolder)->values(),
            'packageMediaUsages' => $packageMediaUsages,
            'packageCategories' => $packageCategories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MediaItemRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $section = $validated['section'];
        $storageSection = $this->storageSectionFor($section, $validated['gallery_destination'] ?? null);
        $packageCategoryId = $this->packageCategoryIdFor($section, $validated);
        $sortOrder = ((int) MediaItem::query()->forSection($storageSection)->max('sort_order')) + 1;
        $showOnHomepage = in_array($section, [MediaItem::AboutSection, MediaItem::GallerySection], true);
        $isPublished = $storageSection === MediaItem::FullGallerySection
            || $showOnHomepage
            || $request->boolean('is_published');

        DB::transaction(function () use ($request, $storageSection, $packageCategoryId, $section, $isPublished, $showOnHomepage, &$sortOrder): void {
            foreach ($request->file('images', []) as $image) {
                $mediaItem = MediaItem::query()->create([
                    'path' => $image->store('media/gallery', 'public'),
                    'section' => $storageSection,
                    'package_category_id' => $packageCategoryId,
                    'alt_text' => $this->generatedAltText($image, $section, $sortOrder),
                    'is_published' => $isPublished,
                    'show_on_homepage' => $showOnHomepage,
                    'sort_order' => $sortOrder++,
                ]);

                if ($showOnHomepage) {
                    $this->keepHomepageSelectionWithinLimit($mediaItem);
                }
            }
        });

        $returnSection = $section === MediaItem::FullGallerySection && $storageSection === MediaItem::AboutSection
            ? MediaItem::AboutSection
            : $section;

        return redirect()
            ->route('admin.media.index', ['section' => $returnSection])
            ->with('success', 'Media berjaya dimuat naik.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MediaItemRequest $request, MediaItem $medium): RedirectResponse
    {
        $validated = $request->validated();
        $storageSection = $this->storageSectionFor($medium->section, $validated['gallery_destination'] ?? null);
        $returnSection = $storageSection === MediaItem::AboutSection && $medium->section !== MediaItem::AboutSection
            ? MediaItem::AboutSection
            : ($validated['return_section'] ?? ($medium->show_on_homepage
            ? MediaItem::GallerySection
            : $medium->section));

        $this->updateMediaItem(
            medium: $medium,
            storageSection: $storageSection,
            packageCategoryId: $this->packageCategoryIdFor($medium->section, $validated),
            isPublished: $storageSection === MediaItem::FullGallerySection
                || $request->boolean('is_published'),
            showOnHomepage: in_array($storageSection, [MediaItem::AboutSection, MediaItem::FullGallerySection], true)
                ? $request->boolean('show_on_homepage')
                : null,
            image: $request->file('image'),
            altTextSection: $storageSection,
        );

        return redirect()
            ->route('admin.media.index', ['section' => $returnSection])
            ->with('success', 'Media berjaya dikemas kini.');
    }

    public function bulkUpdate(BulkMediaItemUpdateRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();
        $mediaItems = MediaItem::query()
            ->whereKey(collect($validated['items'])->pluck('id'))
            ->get()
            ->keyBy('id');

        foreach ($validated['items'] as $item) {
            $medium = $mediaItems->get((int) $item['id']);

            if (! $medium instanceof MediaItem) {
                continue;
            }

            $this->updateMediaItem(
                medium: $medium,
                storageSection: $medium->section,
                packageCategoryId: $item['package_category_id'] ?? null,
                isPublished: $medium->section === MediaItem::FullGallerySection
                    || filter_var($item['is_published'] ?? false, FILTER_VALIDATE_BOOLEAN),
                showOnHomepage: in_array($medium->section, [MediaItem::AboutSection, MediaItem::FullGallerySection], true)
                    ? filter_var($item['show_on_homepage'] ?? false, FILTER_VALIDATE_BOOLEAN)
                    : null,
                image: $item['image'] ?? null,
                altTextSection: $medium->section,
            );
        }

        $redirectUrl = route('admin.media.index', ['section' => $validated['section']]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Semua perubahan media berjaya disimpan.',
                'redirect_url' => $redirectUrl,
            ]);
        }

        return redirect($redirectUrl)->with('success', 'Semua perubahan media berjaya disimpan.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MediaItem $medium): RedirectResponse
    {
        $path = $medium->path;
        $section = $request->string('return_section')->toString() ?: ($medium->show_on_homepage
            ? MediaItem::GallerySection
            : $medium->section);
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

    private function updateMediaItem(
        MediaItem $medium,
        string $storageSection,
        ?int $packageCategoryId,
        bool $isPublished,
        ?bool $showOnHomepage,
        ?UploadedFile $image,
        string $altTextSection,
    ): void {
        $previousPath = $medium->path;

        DB::transaction(function () use ($medium, $storageSection, $packageCategoryId, $isPublished, $showOnHomepage, $image, $altTextSection): void {
            if ($medium->section !== $storageSection) {
                $medium->section = $storageSection;
                $medium->sort_order = ((int) MediaItem::query()->forSection($storageSection)->max('sort_order')) + 1;
            }

            if ($image instanceof UploadedFile) {
                $medium->path = $image->store('media/gallery', 'public');
                $medium->alt_text = $this->generatedAltText($image, $altTextSection, $medium->sort_order);
            }

            $medium->fill([
                'caption' => null,
                'package_category_id' => $packageCategoryId,
                'is_published' => $isPublished,
            ]);

            if ($showOnHomepage !== null) {
                $medium->show_on_homepage = $showOnHomepage && $isPublished;
            }

            $medium->save();

            if ($medium->show_on_homepage) {
                $this->keepHomepageSelectionWithinLimit($medium);
            }
        });

        if ($previousPath !== $medium->path) {
            $this->deleteStoredFile($previousPath);
        }
    }

    private function deleteStoredFile(string $path): void
    {
        if (! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @param array<string, mixed> $validated */
    private function packageCategoryIdFor(string $section, array $validated): ?int
    {
        if ($section !== MediaItem::FullGallerySection || ($validated['gallery_destination'] ?? null) === MediaItem::AboutSection) {
            return null;
        }

        return filled($validated['gallery_destination'] ?? null)
            ? (int) $validated['gallery_destination']
            : ($validated['package_category_id'] ?? null);
    }

    private function storageSectionFor(string $section, ?string $galleryDestination): string
    {
        if ($section === MediaItem::GallerySection) {
            return MediaItem::FullGallerySection;
        }

        if ($section === MediaItem::FullGallerySection && $galleryDestination === MediaItem::AboutSection) {
            return MediaItem::AboutSection;
        }

        return $section;
    }

    /** Keep the most recently chosen image when a homepage collection reaches its limit. */
    private function keepHomepageSelectionWithinLimit(MediaItem $mediaItem): void
    {
        $limit = MediaItem::homepageLimitForStorageSection($mediaItem->section);

        if ($limit === null) {
            return;
        }

        $featuredMedia = MediaItem::query()
            ->forSection($mediaItem->section)
            ->visibleOnHomepage();
        $overflow = $featuredMedia->count() - $limit;

        if ($overflow < 1) {
            return;
        }

        $mediaItemIds = $featuredMedia
            ->where('id', '!=', $mediaItem->id)
            ->oldest('created_at')
            ->orderBy('id')
            ->limit($overflow)
            ->pluck('id');

        MediaItem::query()
            ->whereKey($mediaItemIds)
            ->update(['show_on_homepage' => false]);
    }
}

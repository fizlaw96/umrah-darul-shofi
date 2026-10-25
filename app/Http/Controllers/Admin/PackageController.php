<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ArchivePackage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PackageRequest;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\PackageImage;
use App\Models\PackageServiceOption;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PackageController extends Controller
{
    public function __construct(private readonly ArchivePackage $archivePackage) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $packages = Package::query()
            ->with('category')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $search = '%'.$request->string('q')->toString().'%';

                $query->where(function ($packageQuery) use ($search): void {
                    $packageQuery
                        ->where('title', 'like', $search)
                        ->orWhere('code', 'like', $search)
                        ->orWhere('season_label', 'like', $search)
                        ->orWhere('makkah_hotel', 'like', $search)
                        ->orWhere('madinah_hotel', 'like', $search)
                        ->orWhere('hotel_information', 'like', $search);
                });
            })
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->where('package_category_id', $request->integer('category'));
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->latest('updated_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.packages.index', [
            'packages' => $packages,
            'categories' => PackageCategory::query()->active()->orderBy('sort_order')->get(),
            'statusCounts' => [
                'published' => Package::query()->where('status', 'published')->count(),
                'draft' => Package::query()->where('status', 'draft')->count(),
                'archived' => Package::query()->where('status', 'archived')->count(),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.packages.create', [
            'categories' => PackageCategory::query()->orderBy('sort_order')->get(),
            'package' => new Package,
            'serviceOptions' => $this->serviceOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PackageRequest $request): RedirectResponse
    {
        $package = new Package;
        $status = $this->requestedStatus($request, $package);
        $this->savePackage($request, $package, $status);

        return redirect()->route('admin.packages.edit', $package)->with('success', $this->statusMessage($status));
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
        $package->load([
            'images',
            'itineraries',
            'departures' => fn ($query) => $query->orderBy('departure_date'),
        ]);

        return view('admin.packages.edit', [
            'categories' => PackageCategory::query()->orderBy('sort_order')->get(),
            'package' => $package,
            'serviceOptions' => $this->serviceOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackageRequest $request, Package $package): RedirectResponse
    {
        $status = $this->requestedStatus($request, $package);
        $this->savePackage($request, $package, $status);

        return redirect()->route('admin.packages.edit', $package)->with('success', $this->statusMessage($status));
    }

    public function autosave(PackageRequest $request, Package $package): JsonResponse
    {
        $this->savePackage($request, $package, $package->status ?: 'draft', regenerateSlug: false);

        return response()->json([
            'message' => 'Perubahan disimpan secara automatik.',
            'saved_at' => now()->format('H:i'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package): RedirectResponse
    {
        $this->archivePackage->handle($package);

        return redirect()->route('admin.packages.index')->with('success', 'Pakej telah diarkibkan.');
    }

    public function restore(Package $package): RedirectResponse
    {
        $package->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return back()->with('success', 'Pakej dipulihkan sebagai draf.');
    }

    public function preview(Package $package): View
    {
        $package->load([
            'category',
            'images',
            'itineraries',
            'departures' => fn ($query) => $query
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->orderBy('departure_date'),
        ]);

        return view('packages.show', [
            'package' => $package,
            'whatsappNumber' => null,
        ]);
    }

    public function destroyImage(Package $package, PackageImage $image): RedirectResponse
    {
        abort_unless($image->package_id === $package->id, 404);

        $path = $image->path;
        $image->delete();

        if ($package->cover_image === $path) {
            $package->update([
                'cover_image' => $package->images()->orderBy('sort_order')->value('path'),
            ]);
        }

        if (! $package->images()->where('path', $path)->exists()) {
            $this->deleteStoredFile($path);
        }

        return redirect()
            ->route('admin.packages.edit', [$package, 'tab' => 'media'])
            ->with('success', 'Gambar galeri telah dibuang.');
    }

    public function destroyCoverImage(Package $package): RedirectResponse
    {
        $coverImage = $package->cover_image;

        if (blank($coverImage)) {
            return back();
        }

        $replacementImage = $package->images()
            ->where('path', '!=', $coverImage)
            ->orderBy('sort_order')
            ->value('path');

        $package->update(['cover_image' => $replacementImage]);

        if (! $package->images()->where('path', $coverImage)->exists()) {
            $this->deleteStoredFile($coverImage);
        }

        return redirect()
            ->route('admin.packages.edit', [$package, 'tab' => 'media'])
            ->with('success', 'Gambar utama telah dibuang.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'in:publish,archive'],
            'package_ids' => ['required', 'array', 'min:1', 'max:100'],
            'package_ids.*' => ['integer', 'distinct', 'exists:packages,id'],
        ]);

        DB::transaction(function () use ($validated): void {
            $packages = Package::query()
                ->whereKey($validated['package_ids'])
                ->lockForUpdate()
                ->get();

            foreach ($packages as $package) {
                if ($validated['action'] === 'publish') {
                    $package->update([
                        'status' => 'published',
                        'published_at' => $package->published_at ?? now(),
                    ]);
                } else {
                    $this->archivePackage->handle($package);
                }
            }
        });

        $message = $validated['action'] === 'publish'
            ? 'Pakej terpilih telah diterbitkan.'
            : 'Pakej terpilih telah diarkibkan.';

        return back()->with('success', $message);
    }

    private function savePackage(
        PackageRequest $request,
        Package $package,
        string $status,
        bool $regenerateSlug = true,
    ): void {
        $validated = $request->validated();
        $wasArchived = $package->status === 'archived';
        $previousCoverImage = $package->cover_image;
        $previousItineraryPdf = $package->itinerary_pdf;
        $storedPaths = [];
        $removedTravelImagePaths = [];
        $removedContentImagePaths = [];
        $description = Package::sanitizeDescription($validated['description']);

        $values = [
            'package_category_id' => $validated['package_category_id'],
            'title' => $validated['title'],
            'code' => $package->code,
            'slug' => $regenerateSlug
                ? $this->uniqueSlug($validated['title'], $package)
                : $package->slug,
            'short_description' => Str::limit(Str::squish(strip_tags($description)), 500, ''),
            'description' => $description,
            'starting_price_sen' => (int) round((float) $validated['starting_price'] * 100),
            'duration_days' => $validated['duration_days'],
            'duration_nights' => $validated['duration_nights'],
            'season_label' => $validated['season_label'] ?? null,
            'departure_airport' => $validated['departure_airport'] ?? null,
            'arrival_airport' => $validated['arrival_airport'] ?? null,
            'airline' => $validated['airline'] ?? null,
            'proposed_departure_date' => $validated['proposed_departure_date'] ?? null,
            'makkah_nights' => $validated['makkah_nights'] ?? null,
            'madinah_nights' => $validated['madinah_nights'] ?? null,
            'makkah_hotel' => $validated['makkah_hotel'] ?? null,
            'makkah_hotel_rating' => $validated['makkah_hotel_rating'] ?? null,
            'madinah_hotel' => $validated['madinah_hotel'] ?? null,
            'madinah_hotel_rating' => $validated['madinah_hotel_rating'] ?? null,
            'walking_distance' => $validated['walking_distance'] ?? null,
            'hotel_information' => $validated['hotel_information'] ?? null,
            'flight_information' => $validated['flight_information'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'show_on_homepage' => $request->boolean('show_on_homepage'),
            'display_order' => $package->exists
                ? $package->display_order
                : ((int) Package::query()->max('display_order')) + 1,
            'status' => $status,
        ];

        if ($request->boolean('service_options_present')) {
            $packageServices = $this->packageServices($validated['package_services'] ?? []);

            $values['service_options'] = array_column($packageServices, 'name');
            $values['inclusions'] = array_values(array_column(array_filter(
                $packageServices,
                fn (array $packageService): bool => $packageService['included'],
            ), 'name'));
            $values['exclusions'] = array_values(array_column(array_filter(
                $packageServices,
                fn (array $packageService): bool => $packageService['excluded'],
            ), 'name'));
        }

        if ($status === 'published') {
            $values['published_at'] = $package->published_at ?? now();
        } else {
            $values['published_at'] = null;
        }

        try {
            if ($request->hasFile('cover_image')) {
                $values['cover_image'] = $request->file('cover_image')->store('packages', 'public');
                $storedPaths[] = $values['cover_image'];
            } elseif ($request->boolean('remove_cover_image')) {
                $values['cover_image'] = null;
            }

            if ($request->hasFile('itinerary_pdf')) {
                $values['itinerary_pdf'] = $request->file('itinerary_pdf')->store('packages/itineraries', 'public');
                $storedPaths[] = $values['itinerary_pdf'];
            } elseif ($request->boolean('remove_itinerary_pdf')) {
                $values['itinerary_pdf'] = null;
            }

            $currentFlightOptions = $package->flight_options ?? [];
            $currentHotelOptions = $package->hotel_options ?? [];
            $currentRoomPrices = $package->room_prices ?? [];
            $currentZiarahPlaces = $package->ziarah_places ?? [];
            $currentGifts = $package->gifts ?? [];

            $values['flight_options'] = $request->boolean('flight_options_present')
                ? $this->travelOptions(
                    $validated['flight_options'] ?? [],
                    $request->file('flight_options', []),
                    $currentFlightOptions,
                    'packages/travel/flights',
                    ['airline', 'departure_airport', 'arrival_airport', 'proposed_departure_date', 'notes'],
                    $storedPaths,
                )
                : $currentFlightOptions;
            $values['hotel_options'] = $request->boolean('hotel_options_present')
                ? $this->travelOptions(
                    $validated['hotel_options'] ?? [],
                    $request->file('hotel_options', []),
                    $currentHotelOptions,
                    'packages/travel/hotels',
                    ['location', 'name', 'rating', 'nights', 'walking_distance', 'notes'],
                    $storedPaths,
                    true,
                )
                : $currentHotelOptions;
            $values['room_prices'] = $this->roomPrices(
                $validated['room_prices'] ?? [],
                $request->file('room_prices', []),
                $currentRoomPrices,
                $values['hotel_options'],
                $storedPaths,
            );
            if ($request->boolean('flight_options_present') || $request->boolean('hotel_options_present')) {
                $values = [...$values, ...$this->legacyTravelValues(
                    $values['flight_options'],
                    $values['hotel_options'],
                )];
            }
            $values['ziarah_places'] = $this->itemsWithImages(
                $validated['ziarah_places'] ?? [],
                $request->file('ziarah_places', []),
                $currentZiarahPlaces,
                'packages/content/ziarah',
                'name',
                ['location', 'description'],
                $storedPaths,
            );
            $values['gifts'] = $this->itemsWithImages(
                $validated['gifts'] ?? [],
                $request->file('gifts', []),
                $currentGifts,
                'packages/content/gifts',
                'title',
                ['description'],
                $storedPaths,
            );

            $removedTravelImagePaths = array_values(array_unique(array_diff(
                [...$this->travelImagePaths($currentFlightOptions), ...$this->travelImagePaths($currentHotelOptions)],
                [...$this->travelImagePaths($values['flight_options']), ...$this->travelImagePaths($values['hotel_options'])],
            )));
            $removedContentImagePaths = array_values(array_unique(array_diff(
                [...$this->contentImagePaths($currentRoomPrices), ...$this->contentImagePaths($currentZiarahPlaces), ...$this->contentImagePaths($currentGifts)],
                [...$this->contentImagePaths($values['room_prices']), ...$this->contentImagePaths($values['ziarah_places']), ...$this->contentImagePaths($values['gifts'])],
            )));

            $existingImagePaths = $package->images()->pluck('path')->all();
            $coverImagePath = $values['cover_image'] ?? $package->cover_image;

            if (filled($coverImagePath) && ! in_array($coverImagePath, $existingImagePaths, true)) {
                $existingImagePaths[] = $coverImagePath;
            }

            $uploadedGalleryImages = $request->file('gallery', []);

            if (count(array_unique($existingImagePaths)) + count($uploadedGalleryImages) > 3) {
                throw ValidationException::withMessages([
                    'gallery' => 'Setiap pakej hanya boleh mempunyai maksimum 3 gambar.',
                ]);
            }

            $galleryPaths = [];

            foreach ($uploadedGalleryImages as $image) {
                $galleryPaths[] = $image->store('packages/gallery', 'public');
            }

            $storedPaths = [...$storedPaths, ...$galleryPaths];

            DB::transaction(function () use ($package, $values, $galleryPaths, $validated): void {
                $package->fill($values);
                $package->save();

                if (blank($package->code)) {
                    $package->update([
                        'code' => $this->automaticCode($package),
                    ]);
                }

                $sortOrder = ((int) $package->images()->max('sort_order')) + 1;

                foreach ($galleryPaths as $galleryPath) {
                    $package->images()->create([
                        'path' => $galleryPath,
                        'sort_order' => $sortOrder++,
                    ]);
                }

                $primaryImagePath = $this->requestedPrimaryImagePath($validated, $package, $galleryPaths);

                if (filled($primaryImagePath)) {
                    $package->update(['cover_image' => $primaryImagePath]);
                }

                $this->syncItineraries($package, $validated);

                if (array_key_exists('departure_rows', $validated)) {
                    $this->syncDepartures($package, $validated['departure_rows'] ?? []);
                }
            });
        } catch (Throwable $throwable) {
            Storage::disk('public')->delete($storedPaths);

            throw $throwable;
        }

        if ($previousCoverImage !== $package->cover_image
            && ! $package->images()->where('path', $previousCoverImage)->exists()) {
            $this->deleteStoredFile($previousCoverImage);
        }

        if ($previousItineraryPdf !== $package->itinerary_pdf) {
            $this->deleteStoredFile($previousItineraryPdf);
        }

        foreach ($removedTravelImagePaths as $path) {
            $this->deleteStoredFile($path);
        }

        foreach ($removedContentImagePaths as $path) {
            $this->deleteStoredFile($path);
        }

        if (! $wasArchived && $package->status === 'archived') {
            $this->archivePackage->notify($package);
        }
    }

    private function requestedStatus(PackageRequest $request, Package $package): string
    {
        return match ($request->string('intent')->toString()) {
            'publish' => 'published',
            'draft' => 'draft',
            default => $request->string('status')->toString() ?: ($package->status ?: 'draft'),
        };
    }

    private function statusMessage(string $status): string
    {
        return match ($status) {
            'published' => 'Pakej berjaya disimpan dan diterbitkan.',
            'archived' => 'Pakej berjaya diarkibkan.',
            default => 'Pakej berjaya disimpan sebagai draf.',
        };
    }

    /** @return array<int, string> */
    private function lines(?string $value): array
    {
        if (blank($value)) {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $value))));
    }

    /**
     * @param  array<int, array<string, mixed>>  $roomPrices
     * @param  array<int, array<string, mixed>>  $uploads
     * @param  array<int, array<string, mixed>>  $currentRoomPrices
     * @param  array<int, array<string, mixed>>  $hotelOptions
     * @param  array<int, string>  $storedPaths
     * @return array<int, array{room_type: string, price_sen: int, hotel_key: string, image?: string}>
     */
    private function roomPrices(
        array $roomPrices,
        array $uploads,
        array $currentRoomPrices,
        array $hotelOptions,
        array &$storedPaths,
    ): array {
        $availableImagePaths = $this->contentImagePaths($currentRoomPrices);
        $availableHotelKeys = collect($hotelOptions)
            ->filter(fn (mixed $hotel): bool => is_array($hotel))
            ->pluck('key')
            ->filter(fn (mixed $key): bool => is_string($key) && filled($key))
            ->all();
        $prices = [];

        foreach ($roomPrices as $index => $roomPrice) {
            if (! is_array($roomPrice)) {
                continue;
            }

            $roomType = trim((string) ($roomPrice['room_type'] ?? ''));
            $price = $roomPrice['price'] ?? null;
            $hotelKey = $roomPrice['hotel_key'] ?? null;

            if (blank($roomType) || blank($price) || ! is_string($hotelKey) || ! in_array($hotelKey, $availableHotelKeys, true)) {
                continue;
            }

            $roomPriceValues = [
                'room_type' => $roomType,
                'price_sen' => (int) round((float) $price * 100),
                'hotel_key' => $hotelKey,
            ];
            $imagePath = $roomPrice['existing_image'] ?? null;

            if (! is_string($imagePath) || ! in_array($imagePath, $availableImagePaths, true)) {
                $imagePath = null;
            }

            $upload = $uploads[$index]['image'] ?? null;

            if ($upload instanceof UploadedFile) {
                $imagePath = $upload->store('packages/content/room-prices', 'public');
                $storedPaths[] = $imagePath;
            }

            if (filled($imagePath)) {
                $roomPriceValues['image'] = $imagePath;
            }

            $prices[] = $roomPriceValues;
        }

        return $prices;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $optionalFields
     * @return array<int, array<string, string>>
     */
    private function namedItems(array $rows, string $nameField, array $optionalFields): array
    {
        $items = [];

        foreach ($rows as $row) {
            $name = trim((string) ($row[$nameField] ?? ''));

            if (blank($name)) {
                continue;
            }

            $item = [$nameField => $name];

            foreach ($optionalFields as $field) {
                $item[$field] = trim((string) ($row[$field] ?? ''));
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, mixed>  $uploads
     * @param  array<int, array<string, mixed>>  $currentItems
     * @param  array<int, string>  $optionalFields
     * @param  array<int, string>  $storedPaths
     * @return array<int, array<string, string>>
     */
    private function itemsWithImages(
        array $items,
        array $uploads,
        array $currentItems,
        string $storageDirectory,
        string $nameField,
        array $optionalFields,
        array &$storedPaths,
    ): array {
        $availableImagePaths = $this->contentImagePaths($currentItems);
        $normalisedItems = [];

        foreach ($items as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $name = trim((string) ($item[$nameField] ?? ''));

            if (blank($name)) {
                continue;
            }

            $normalisedItem = [$nameField => $name];

            foreach ($optionalFields as $field) {
                $normalisedItem[$field] = trim((string) ($item[$field] ?? ''));
            }

            $imagePath = $item['existing_image'] ?? null;

            if (! is_string($imagePath) || ! in_array($imagePath, $availableImagePaths, true)) {
                $imagePath = null;
            }

            $upload = $uploads[$index]['image'] ?? null;

            if ($upload instanceof UploadedFile) {
                $imagePath = $upload->store($storageDirectory, 'public');
                $storedPaths[] = $imagePath;
            }

            if (filled($imagePath)) {
                $normalisedItem['image'] = $imagePath;
            }

            $normalisedItems[] = $normalisedItem;
        }

        return $normalisedItems;
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     * @param  array<int, array<string, mixed>>  $uploads
     * @param  array<int, array<string, mixed>>  $currentOptions
     * @param  array<int, string>  $fields
     * @param  array<int, string>  $storedPaths
     * @return array<int, array<string, int|string|null|array<int, string>>>
     */
    private function travelOptions(
        array $options,
        array $uploads,
        array $currentOptions,
        string $storageDirectory,
        array $fields,
        array &$storedPaths,
        bool $includesHotelKey = false,
    ): array {
        $availableImagePaths = $this->travelImagePaths($currentOptions);
        $normalisedOptions = [];

        foreach ($options as $index => $option) {
            if (! is_array($option)) {
                continue;
            }

            $nameField = in_array('name', $fields, true) ? 'name' : 'airline';

            if (blank($option[$nameField] ?? null)) {
                continue;
            }

            $images = array_values(array_intersect(
                $this->stringItems($option['existing_images'] ?? []),
                $availableImagePaths,
            ));
            $optionUploads = $uploads[$index]['images'] ?? [];

            if (is_array($optionUploads)) {
                foreach ($optionUploads as $upload) {
                    if (! $upload instanceof UploadedFile) {
                        continue;
                    }

                    $path = $upload->store($storageDirectory, 'public');
                    $images[] = $path;
                    $storedPaths[] = $path;
                }
            }

            $normalisedOption = ['images' => array_values(array_unique($images))];

            if ($includesHotelKey) {
                $key = $option['key'] ?? null;
                $normalisedOption['key'] = filled($key) ? (string) $key : (string) Str::uuid();
            }

            foreach ($fields as $field) {
                $value = $option[$field] ?? null;

                $normalisedOption[$field] = in_array($field, ['rating', 'nights'], true) && filled($value)
                    ? (int) $value
                    : (filled($value) ? trim((string) $value) : null);
            }

            $normalisedOptions[] = $normalisedOption;
        }

        return $normalisedOptions;
    }

    /**
     * @param  array<int, array<string, mixed>>  $options
     * @return array<int, string>
     */
    private function travelImagePaths(array $options): array
    {
        return collect($options)
            ->filter(fn (mixed $option): bool => is_array($option))
            ->flatMap(fn (array $option): array => $this->stringItems($option['images'] ?? []))
            ->filter(fn (string $path): bool => ! Str::startsWith($path, ['http://', 'https://']))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, string>
     */
    private function contentImagePaths(array $items): array
    {
        return collect($items)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(fn (array $item): mixed => $item['image'] ?? null)
            ->filter(fn (mixed $path): bool => is_string($path) && filled($path) && ! Str::startsWith($path, ['http://', 'https://']))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function stringItems(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        return array_values(array_filter(
            $items,
            fn (mixed $item): bool => is_string($item) && filled($item),
        ));
    }

    private function deleteStoredFile(?string $path): void
    {
        if (filled($path) && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }

    /** @return Collection<int, PackageServiceOption> */
    private function serviceOptions(): Collection
    {
        return PackageServiceOption::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $packageServices
     * @return array<int, array{name: string, included: bool, excluded: bool}>
     */
    private function packageServices(array $packageServices): array
    {
        return collect($packageServices)
            ->filter(fn (mixed $packageService): bool => is_array($packageService) && filled($packageService['name'] ?? null))
            ->map(fn (array $packageService): array => [
                'name' => trim((string) $packageService['name']),
                'included' => filter_var($packageService['included'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'excluded' => filter_var($packageService['excluded'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ])
            ->unique(fn (array $packageService): string => mb_strtolower($packageService['name']))
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $validated
     * @param  array<int, string>  $galleryPaths
     */
    private function requestedPrimaryImagePath(array $validated, Package $package, array $galleryPaths): ?string
    {
        $availableImagePaths = $package->images()->pluck('path')->all();

        if (filled($package->cover_image)) {
            $availableImagePaths[] = $package->cover_image;
        }

        $primaryImageIndex = $validated['gallery_primary_index'] ?? null;

        if (is_numeric($primaryImageIndex)
            && (int) $primaryImageIndex >= 0
            && array_key_exists((int) $primaryImageIndex, $galleryPaths)) {
            return $galleryPaths[(int) $primaryImageIndex];
        }

        $primaryImagePath = $validated['primary_image_path'] ?? null;

        if (is_string($primaryImagePath) && in_array($primaryImagePath, $availableImagePaths, true)) {
            return $primaryImagePath;
        }

        return blank($package->cover_image) ? ($galleryPaths[0] ?? null) : null;
    }

    /**
     * Keep existing public-package and search metadata in step with the first
     * flight and hotel selected in the structured tabs.
     *
     * @param  array<int, array<string, mixed>>  $flightOptions
     * @param  array<int, array<string, mixed>>  $hotelOptions
     * @return array<string, int|string|null>
     */
    private function legacyTravelValues(array $flightOptions, array $hotelOptions): array
    {
        $firstFlight = $flightOptions[0] ?? [];
        $makkahHotel = collect($hotelOptions)->first(
            fn (mixed $hotel): bool => is_array($hotel) && ($hotel['location'] ?? null) === 'Makkah',
            [],
        );
        $madinahHotel = collect($hotelOptions)->first(
            fn (mixed $hotel): bool => is_array($hotel) && ($hotel['location'] ?? null) === 'Madinah',
            [],
        );

        return [
            'departure_airport' => $firstFlight['departure_airport'] ?? null,
            'arrival_airport' => $firstFlight['arrival_airport'] ?? null,
            'airline' => $firstFlight['airline'] ?? null,
            'proposed_departure_date' => $firstFlight['proposed_departure_date'] ?? null,
            'flight_information' => $firstFlight['notes'] ?? null,
            'makkah_nights' => $makkahHotel['nights'] ?? null,
            'makkah_hotel' => $makkahHotel['name'] ?? null,
            'makkah_hotel_rating' => $makkahHotel['rating'] ?? null,
            'madinah_nights' => $madinahHotel['nights'] ?? null,
            'madinah_hotel' => $madinahHotel['name'] ?? null,
            'madinah_hotel_rating' => $madinahHotel['rating'] ?? null,
            'walking_distance' => $makkahHotel['walking_distance'] ?? $madinahHotel['walking_distance'] ?? null,
            'hotel_information' => $makkahHotel['notes'] ?? $madinahHotel['notes'] ?? null,
        ];
    }

    private function automaticCode(Package $package): string
    {
        return 'DS-'.str_pad((string) $package->id, 5, '0', STR_PAD_LEFT);
    }

    private function uniqueSlug(string $title, Package $package): string
    {
        $baseSlug = Str::slug($title) ?: 'pakej';
        $slug = $baseSlug;
        $suffix = 2;

        while (Package::query()
            ->where('slug', $slug)
            ->when($package->exists, fn ($query) => $query->where('id', '!=', $package->id))
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function syncItineraries(Package $package, array $validated): void
    {
        if (array_key_exists('itinerary_days', $validated) && is_array($validated['itinerary_days'])) {
            $package->itineraries()->delete();

            for ($dayNumber = 1; $dayNumber <= $package->duration_days; $dayNumber++) {
                $itinerary = $validated['itinerary_days'][$dayNumber - 1] ?? [];
                $title = is_array($itinerary) ? trim((string) ($itinerary['title'] ?? '')) : '';
                $description = is_array($itinerary) ? trim((string) ($itinerary['description'] ?? '')) : '';

                $package->itineraries()->create([
                    'day_number' => $dayNumber,
                    'title' => filled($title) ? $title : "Hari {$dayNumber}",
                    'description' => $description,
                ]);
            }

            return;
        }

        if (! array_key_exists('itinerary', $validated)) {
            return;
        }

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

    /**
     * @param  array<int, array<string, mixed>>  $departureRows
     */
    private function syncDepartures(Package $package, array $departureRows): void
    {
        $existingDepartures = PackageDeparture::query()
            ->whereBelongsTo($package)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
        $submittedDepartureIds = [];

        foreach ($departureRows as $departureRow) {
            if (! is_array($departureRow) || blank($departureRow['departure_date'] ?? null)) {
                continue;
            }

            $departureValues = [
                'departure_date' => $departureRow['departure_date'],
                'return_date' => $departureRow['return_date'],
                'departure_airport' => filled($departureRow['departure_airport'] ?? null)
                    ? $departureRow['departure_airport']
                    : null,
                'price_sen' => (int) round((float) $departureRow['price'] * 100),
                'capacity' => filled($departureRow['capacity'] ?? null)
                    ? (int) $departureRow['capacity']
                    : null,
                'status' => filled($departureRow['status'] ?? null)
                    ? $departureRow['status']
                    : 'draft',
            ];
            $departureId = (int) ($departureRow['id'] ?? 0);

            if ($departureId === 0) {
                $package->departures()->create($departureValues);

                continue;
            }

            $departure = $existingDepartures->get($departureId);

            if (! $departure instanceof PackageDeparture) {
                throw ValidationException::withMessages([
                    'departure_rows' => 'Salah satu jadual berlepas tidak tergolong dalam pakej ini.',
                ]);
            }

            $participantCount = $departure->participants()->count();

            if ($departureValues['capacity'] === null || $departureValues['capacity'] < $participantCount) {
                throw ValidationException::withMessages([
                    'departure_rows' => 'Kapasiti tidak boleh lebih rendah daripada peserta yang telah direkodkan.',
                ]);
            }

            $departure->update($departureValues);
            $submittedDepartureIds[] = $departureId;
        }

        $existingDepartures
            ->reject(fn (PackageDeparture $departure): bool => in_array($departure->id, $submittedDepartureIds, true))
            ->each(fn (PackageDeparture $departure) => $departure->update(['status' => 'cancelled']));
    }
}

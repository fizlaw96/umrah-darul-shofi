<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\WebsiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PackageController extends Controller
{
    public function index(Request $request): View
    {
        $sort = $request->string('sort')->toString();
        $allowedSorts = ['price_asc', 'price_desc', 'departure', 'newest'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'newest';
        }

        $packages = Package::query()
            ->publiclyVisible()
            ->with('category')
            ->withVisibleDepartures()
            ->when($request->filled('category'), function ($query) use ($request): void {
                $query->whereHas('category', function ($categoryQuery) use ($request): void {
                    $categoryQuery->where('slug', $request->string('category')->toString());
                });
            })
            ->when($request->filled('min_price'), function ($query) use ($request): void {
                $query->where('starting_price_sen', '>=', (int) round($request->float('min_price') * 100));
            })
            ->when($request->filled('max_price'), function ($query) use ($request): void {
                $query->where('starting_price_sen', '<=', (int) round($request->float('max_price') * 100));
            })
            ->when($request->filled('duration'), function ($query) use ($request): void {
                $query->where('duration_days', $request->integer('duration'));
            })
            ->when($request->filled('month') && preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $request->string('month')->toString()) === 1, function ($query) use ($request): void {
                $month = CarbonImmutable::createFromFormat('Y-m', $request->string('month')->toString(), 'Asia/Kuala_Lumpur');

                $query->where(function ($packageQuery) use ($month): void {
                    $packageQuery
                        ->where('is_date_flexible', true)
                        ->orWhereHas('departures', function ($departureQuery) use ($month): void {
                            $departureQuery
                                ->visibleForPromotion()
                                ->whereBetween('departure_date', [$month->startOfMonth()->toDateString(), $month->endOfMonth()->toDateString()]);
                        });
                });
            });

        $packages->orderByDesc('is_featured');

        match ($sort) {
            'price_asc' => $packages->orderBy('starting_price_sen'),
            'price_desc' => $packages->orderByDesc('starting_price_sen'),
            'departure' => $packages->orderBy(PackageDeparture::query()
                ->select('departure_date')
                ->whereColumn('package_id', 'packages.id')
                ->visibleForPromotion()
                ->orderBy('departure_date')
                ->limit(1)),
            default => $packages->latest('published_at'),
        };

        $priceBounds = Package::query()
            ->publiclyVisible()
            ->selectRaw('MIN(starting_price_sen) as minimum_price_sen, MAX(starting_price_sen) as maximum_price_sen')
            ->first();

        $priceRangeMinimum = intdiv((int) ($priceBounds?->minimum_price_sen ?? 0), 10_000) * 100;
        $priceRangeMaximum = (int) ceil((int) ($priceBounds?->maximum_price_sen ?? 0) / 10_000) * 100;

        if ($priceRangeMaximum <= $priceRangeMinimum) {
            $priceRangeMaximum = $priceRangeMinimum + 100;
        }

        $selectedMinimumPrice = $request->filled('min_price')
            ? max($priceRangeMinimum, min((int) round($request->float('min_price')), $priceRangeMaximum))
            : $priceRangeMinimum;
        $selectedMaximumPrice = $request->filled('max_price')
            ? max($priceRangeMinimum, min((int) round($request->float('max_price')), $priceRangeMaximum))
            : $priceRangeMaximum;

        if ($selectedMinimumPrice > $selectedMaximumPrice) {
            $selectedMinimumPrice = $selectedMaximumPrice;
        }

        return view('packages.index', [
            'categories' => PackageCategory::query()->active()->orderBy('sort_order')->get(),
            'departureMonths' => PackageDeparture::query()
                ->visibleForPromotion()
                ->whereHas('package', fn ($packageQuery) => $packageQuery->publiclyVisible())
                ->orderBy('departure_date')
                ->get(['departure_date'])
                ->map(fn (PackageDeparture $departure) => $departure->departure_date->format('Y-m'))
                ->unique()
                ->values(),
            'packages' => $packages->paginate(9)->withQueryString(),
            'priceRange' => [
                'minimum' => $priceRangeMinimum,
                'maximum' => $priceRangeMaximum,
                'selectedMinimum' => $selectedMinimumPrice,
                'selectedMaximum' => $selectedMaximumPrice,
            ],
            'selectedSort' => $sort,
        ]);
    }

    public function show(Package $package): View
    {
        abort_unless(Package::query()->whereKey($package->getKey())->publiclyVisible()->exists(), 404);

        $package->load([
            'category',
            'images',
            'itineraries',
            'departures' => fn ($query) => $query
                ->visibleForPromotion()
                ->withCount(['participants', 'unassignedPackageParticipants'])
                ->orderBy('departure_date'),
        ]);

        return view('packages.show', [
            'package' => $package,
            'whatsappNumber' => $this->whatsappNumber(),
        ]);
    }

    private function whatsappNumber(): string
    {
        return WebsiteSetting::getValue('contact_whatsapp') ?: (string) config('darul_shofi.whatsapp_number', '');
    }
}

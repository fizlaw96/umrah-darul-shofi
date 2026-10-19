<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\WebsiteSetting;
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
            ->published()
            ->with([
                'category',
                'departures' => fn ($query) => $query
                    ->where('status', 'open')
                    ->whereDate('departure_date', '>=', today())
                    ->orderBy('departure_date')
                    ->limit(1),
            ])
            ->when($request->filled('q'), function ($query) use ($request): void {
                $query->where(function ($subQuery) use ($request): void {
                    $search = '%'.$request->string('q')->toString().'%';

                    $subQuery->where('title', 'like', $search)
                        ->orWhere('short_description', 'like', $search);
                });
            })
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
            ->when($request->filled('departure_date'), function ($query) use ($request): void {
                $query->whereHas('departures', function ($departureQuery) use ($request): void {
                    $departureQuery
                        ->where('status', 'open')
                        ->whereDate('departure_date', '>=', $request->date('departure_date'));
                });
            });

        match ($sort) {
            'price_asc' => $packages->orderBy('starting_price_sen'),
            'price_desc' => $packages->orderByDesc('starting_price_sen'),
            'departure' => $packages->orderBy('published_at'),
            default => $packages->latest('published_at'),
        };

        return view('packages.index', [
            'categories' => PackageCategory::query()->active()->orderBy('sort_order')->get(),
            'packages' => $packages->paginate(9)->withQueryString(),
            'selectedSort' => $sort,
            'whatsappNumber' => WebsiteSetting::getValue('contact_whatsapp'),
        ]);
    }

    public function show(Package $package): View
    {
        abort_unless($package->status === 'published' && $package->published_at?->isPast(), 404);

        $package->load([
            'category',
            'images',
            'itineraries',
            'departures' => fn ($query) => $query
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->withSum([
                    'bookings as confirmed_travellers_count' => fn ($bookingQuery) => $bookingQuery->where('status', 'confirmed'),
                ], 'travellers_count')
                ->orderBy('departure_date'),
        ]);

        return view('packages.show', [
            'package' => $package,
            'whatsappNumber' => WebsiteSetting::getValue('contact_whatsapp'),
        ]);
    }
}

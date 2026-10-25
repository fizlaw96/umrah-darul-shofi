<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = now('Asia/Kuala_Lumpur')->startOfDay();
        $publishedPackages = Package::query()->where('status', 'published')->count();

        return view('admin.dashboard', [
            'packageCount' => Package::query()->count(),
            'publishedPackageCount' => $publishedPackages,
            'draftPackageCount' => Package::query()->where('status', 'draft')->count(),
            'upcomingDepartureCount' => PackageDeparture::query()
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', $today)
                ->count(),
            'recentPackages' => Package::query()
                ->with('category')
                ->withMin([
                    'departures as next_departure_date' => fn ($query) => $query
                        ->where('status', 'open')
                        ->whereDate('departure_date', '>=', $today),
                ], 'departure_date')
                ->withCasts(['next_departure_date' => 'date'])
                ->latest('updated_at')
                ->orderByDesc('id')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }
}

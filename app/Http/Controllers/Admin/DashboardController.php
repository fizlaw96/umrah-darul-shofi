<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Enquiry;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'activePackages' => Package::query()->published()->count(),
            'upcomingDepartures' => PackageDeparture::query()
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->count(),
            'totalBookings' => Booking::query()->count(),
            'pendingBookings' => Booking::query()->whereIn('status', ['pending', 'reviewing'])->count(),
            'confirmedBookings' => Booking::query()->where('status', 'confirmed')->count(),
            'totalEnquiries' => Enquiry::query()->count(),
            'recentBookings' => Booking::query()->with('departure.package')->latest()->take(5)->get(),
            'recentEnquiries' => Enquiry::query()->with('package')->latest()->take(5)->get(),
        ]);
    }
}

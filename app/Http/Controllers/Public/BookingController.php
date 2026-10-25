<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function create(Package $package): RedirectResponse
    {
        abort_unless($package->status === 'published' && $package->published_at?->isPast(), 404);

        return redirect()
            ->route('packages.show', $package)
            ->with('success', 'Sila semak butiran pakej dan hubungi agensi untuk pertanyaan.');
    }

    public function store(Request $request): RedirectResponse
    {
        $package = Package::query()
            ->published()
            ->find($request->integer('package_id'));

        if ($package !== null) {
            return redirect()
                ->route('packages.show', $package)
                ->with('success', 'Tempahan dalam talian tidak disediakan. Sila hubungi agensi untuk pertanyaan pakej.');
        }

        return redirect()
            ->route('packages.index')
            ->with('success', 'Tempahan dalam talian tidak disediakan. Sila pilih pakej untuk melihat maklumat lanjut.');
    }

    public function confirmation(string $bookingReference): RedirectResponse
    {
        return redirect()
            ->route('packages.index')
            ->with('success', 'Untuk maklumat pakej, sila hubungi agensi melalui saluran pertanyaan yang disediakan.');
    }
}

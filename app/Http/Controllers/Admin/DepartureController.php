<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DepartureRequest;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DepartureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $selectedPackageId = $request->integer('package_id') ?: null;

        $departures = PackageDeparture::query()
            ->with('package')
            ->when($selectedPackageId, function ($query) use ($selectedPackageId): void {
                $query->where('package_id', $selectedPackageId);
            })
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->orderBy('departure_date')
            ->paginate(15)
            ->withQueryString();

        return view('admin.departures.index', [
            'departures' => $departures,
            'packages' => Package::query()->orderBy('title')->get(['id', 'title']),
            'selectedPackageId' => $selectedPackageId,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.departures.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartureRequest $request): RedirectResponse
    {
        PackageDeparture::query()->create($this->departureValues($request));

        return back()->with('success', 'Jadual berlepas berjaya ditambah.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PackageDeparture $departure): RedirectResponse
    {
        return redirect()->route('admin.departures.edit', $departure);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PackageDeparture $departure): View
    {
        return view('admin.departures.edit', [
            'departure' => $departure,
            'packages' => Package::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DepartureRequest $request, PackageDeparture $departure): RedirectResponse
    {
        $this->ensureCapacityIsValid($departure, $request->integer('capacity'));

        if ($departure->participants()->exists() && $departure->package_id !== $request->integer('package_id')) {
            throw ValidationException::withMessages([
                'package_id' => 'Pakej tidak boleh ditukar selepas terdapat rekod peserta.',
            ]);
        }

        $departure->update($this->departureValues($request));

        return redirect()->route('admin.departures.index')->with('success', 'Jadual berlepas berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackageDeparture $departure): RedirectResponse
    {
        $departure->update(['status' => 'cancelled']);

        return back()->with('success', 'Jadual telah dibatalkan tanpa memadam sejarah tempahan.');
    }

    /** @return array{package_id: int, departure_date: string, return_date: string, departure_airport: string, price_sen: int, capacity: int, status: string} */
    private function departureValues(DepartureRequest $request): array
    {
        $validated = $request->validated();

        return [
            'package_id' => $validated['package_id'],
            'departure_date' => $validated['departure_date'],
            'return_date' => $validated['return_date'],
            'departure_airport' => $validated['departure_airport'],
            'price_sen' => (int) round((float) $validated['price'] * 100),
            'capacity' => $validated['capacity'],
            'status' => $validated['status'],
        ];
    }

    private function ensureCapacityIsValid(PackageDeparture $departure, int $capacity): void
    {
        $participantCount = $departure->participants()->count();

        if ($capacity < $participantCount) {
            throw ValidationException::withMessages([
                'capacity' => 'Kapasiti tidak boleh lebih rendah daripada peserta yang telah direkodkan.',
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BookingStatusRequest;
use App\Models\Booking;
use App\Models\Package;
use App\Models\PackageDeparture;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $bookings = Booking::query()
            ->with(['package', 'departure'])
            ->when($request->filled('status'), function ($query) use ($request): void {
                $query->where('status', $request->string('status')->toString());
            })
            ->when($request->filled('package'), function ($query) use ($request): void {
                $query->where('package_id', $request->integer('package'));
            })
            ->when($request->filled('departure_date'), function ($query) use ($request): void {
                $query->whereDate('departure_date', $request->date('departure_date'));
            })
            ->when($request->filled('created_from'), function ($query) use ($request): void {
                $query->whereDate('created_at', '>=', $request->date('created_from'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'packages' => Package::query()->orderBy('title')->get(['id', 'title']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.bookings.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        abort(405);
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['package', 'departure', 'statusHistory.actor']);

        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking): RedirectResponse
    {
        return redirect()->route('admin.bookings.show', $booking);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BookingStatusRequest $request, Booking $booking): RedirectResponse
    {
        $newStatus = $request->string('status')->toString();

        DB::transaction(function () use ($booking, $newStatus, $request): void {
            $lockedBooking = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            $currentStatus = $lockedBooking->status;

            if ($currentStatus === $newStatus) {
                return;
            }

            if (! $this->canTransition($currentStatus, $newStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'Peralihan status ini tidak dibenarkan.',
                ]);
            }

            if ($newStatus === Booking::ConfirmedStatus) {
                $departure = PackageDeparture::query()->lockForUpdate()->findOrFail($lockedBooking->package_departure_id);
                $confirmedTravellers = $departure->bookings()
                    ->where('status', Booking::ConfirmedStatus)
                    ->sum('travellers_count');

                if ($confirmedTravellers + $lockedBooking->travellers_count > $departure->capacity) {
                    throw ValidationException::withMessages([
                        'status' => 'Tempahan ini tidak boleh disahkan kerana kapasiti berlepas telah penuh.',
                    ]);
                }
            }

            $lockedBooking->update(['status' => $newStatus]);
            $lockedBooking->statusHistory()->create([
                'user_id' => $request->user()?->id,
                'previous_status' => $currentStatus,
                'status' => $newStatus,
                'remarks' => $request->string('remarks')->toString() ?: null,
            ]);
        });

        return back()->with('success', 'Status tempahan berjaya dikemas kini.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        abort(405);
    }

    private function canTransition(string $from, string $to): bool
    {
        return in_array($to, [
            'pending' => ['reviewing', 'awaiting_payment', 'confirmed', 'cancelled', 'rejected'],
            'reviewing' => ['awaiting_payment', 'confirmed', 'cancelled', 'rejected'],
            'awaiting_payment' => ['confirmed', 'cancelled', 'rejected'],
            'confirmed' => ['completed', 'cancelled'],
            'cancelled' => [],
            'rejected' => [],
            'completed' => [],
        ][$from] ?? [], true);
    }
}

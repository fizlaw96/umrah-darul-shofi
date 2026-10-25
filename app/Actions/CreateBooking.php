<?php

namespace App\Actions;

use App\Models\Booking;
use App\Models\PackageDeparture;
use App\Models\User;
use App\Notifications\BookingRequestSubmitted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class CreateBooking
{
    /**
     * @param  array{package_id: int, package_departure_id: int, customer_name: string, phone: string, email: string, adults: int, children?: int, notes?: string|null, idempotency_key: string}  $attributes
     */
    public function handle(array $attributes): Booking
    {
        return DB::transaction(function () use ($attributes): Booking {
            $existingBooking = Booking::query()
                ->where('idempotency_key', $attributes['idempotency_key'])
                ->first();

            if ($existingBooking !== null) {
                return $existingBooking;
            }

            $departure = PackageDeparture::query()
                ->with('package')
                ->lockForUpdate()
                ->findOrFail($attributes['package_departure_id']);

            $travellersCount = $attributes['adults'] + ($attributes['children'] ?? 0);
            if ($departure->package_id !== $attributes['package_id'] || $departure->status !== 'open') {
                throw ValidationException::withMessages([
                    'package_departure_id' => 'Tarikh berlepas ini tidak lagi dibuka untuk permohonan.',
                ]);
            }

            if ($departure->availableSeats() < $travellersCount) {
                throw ValidationException::withMessages([
                    'package_departure_id' => 'Maaf, baki tempat untuk tarikh ini tidak mencukupi.',
                ]);
            }

            $booking = Booking::query()->create([
                'package_id' => $departure->package_id,
                'package_departure_id' => $departure->id,
                'idempotency_key' => $attributes['idempotency_key'],
                'customer_name' => $attributes['customer_name'],
                'phone' => $attributes['phone'],
                'email' => $attributes['email'],
                'adults' => $attributes['adults'],
                'children' => $attributes['children'] ?? 0,
                'travellers_count' => $travellersCount,
                'departure_date' => $departure->departure_date,
                'package_title' => $departure->package->title,
                'price_per_traveller_sen' => $departure->price_sen,
                'total_quoted_sen' => $departure->price_sen * $travellersCount,
                'status' => 'pending',
                'notes' => $attributes['notes'] ?? null,
                'privacy_consented_at' => now(),
            ]);

            $booking->statusHistory()->create([
                'status' => 'pending',
                'remarks' => 'Permohonan tempahan diterima.',
            ]);

            DB::afterCommit(function () use ($booking): void {
                Notification::send(User::staff()->get(), new BookingRequestSubmitted($booking));
            });

            return $booking;
        });
    }
}

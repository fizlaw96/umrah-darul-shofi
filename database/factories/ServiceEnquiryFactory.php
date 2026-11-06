<?php

namespace Database\Factories;

use App\Models\AqiqahKorbanService;
use App\Models\ServiceEnquiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceEnquiry>
 */
class ServiceEnquiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_type' => ServiceEnquiry::TransportType,
            'aqiqah_korban_service_id' => null,
            'status' => ServiceEnquiry::NewStatus,
            'full_name' => fake()->name(),
            'contact_number' => '+60 12 345 6789',
            'summary' => 'Hotel Contoh → Masjidil Haram',
            'details' => ['pickup_place' => 'Hotel Contoh', 'destination_place' => 'Masjidil Haram'],
            'message' => 'SALAMHAJI TRANSPORT',
            'staff_notes' => null,
            'contacted_at' => null,
        ];
    }

    public function aqiqahKorban(): static
    {
        return $this->state(fn (): array => [
            'service_type' => ServiceEnquiry::AqiqahKorbanType,
            'aqiqah_korban_service_id' => AqiqahKorbanService::factory(),
            'summary' => 'Aqiqah Kambing — 1 Ekor × 1',
            'details' => ['quantity' => 1],
            'message' => 'SALAMHAJI — AQIQAH & KORBAN',
        ]);
    }

    public function contacted(): static
    {
        return $this->state(fn (): array => [
            'status' => ServiceEnquiry::ContactedStatus,
            'contacted_at' => now(),
        ]);
    }
}

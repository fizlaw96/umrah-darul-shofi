<?php

namespace App\Http\Controllers\Public;

use App\GoogleMapsLocation;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransportEnquiryRequest;
use App\Models\ServiceEnquiry;
use App\Models\WebsiteSetting;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransportController extends Controller
{
    public function show(): View
    {
        $settings = WebsiteSetting::siteSettings();

        return view('services.transport', [
            'transportSettings' => $settings,
            'transportBannerUrl' => WebsiteSetting::publicUrl($settings['transport_banner_path'] ?? null),
            'transportIsEnabled' => $settings['transport_enabled'] === '1',
            'transportWhatsAppConfigured' => WebsiteSetting::normalizedWhatsAppNumber($settings['transport_whatsapp'] ?? null) !== null,
            'transportVehicleOptions' => WebsiteSetting::transportVehicleOptions($settings['transport_vehicle_options'] ?? null),
        ]);
    }

    public function prepare(TransportEnquiryRequest $request): View
    {
        $settings = WebsiteSetting::siteSettings();
        abort_unless($settings['transport_enabled'] === '1', 404);

        $enquiry = $request->validated();
        $distanceInKilometres = $this->distanceInKilometres($enquiry);
        $message = $this->message($enquiry, $distanceInKilometres);

        ServiceEnquiry::query()->create([
            'service_type' => ServiceEnquiry::TransportType,
            'full_name' => $enquiry['full_name'],
            'contact_number' => $enquiry['contact_number'],
            'summary' => Str::limit($enquiry['pickup_place'].' → '.$enquiry['destination_place'], 250),
            'details' => [
                ...collect($enquiry)->except(['important_information_acknowledged', 'pickup_confirmed', 'destination_confirmed'])->all(),
                'distance_km' => $distanceInKilometres === null ? null : round($distanceInKilometres, 1),
            ],
            'message' => $message,
        ]);

        return view('services.transport-prepared', [
            'transportSettings' => $settings,
            'whatsAppUrl' => WebsiteSetting::whatsAppUrl($settings['transport_whatsapp'] ?? null, $message),
            'message' => $message,
        ]);
    }

    /**
     * @param  array<string, mixed>  $enquiry
     */
    private function message(array $enquiry, ?float $distanceInKilometres): string
    {
        $isScheduled = $enquiry['request_type'] === 'scheduled';

        return implode("\n", array_filter([
            '🚐 SALAMHAJI TRANSPORT',
            '',
            'Assalamualaikum, saya ingin membuat permintaan pengangkutan.',
            '',
            'MAKLUMAT PELANGGAN',
            'Nama: '.$enquiry['full_name'],
            'Telefon: '.$enquiry['contact_number'],
            '',
            'JENIS PERJALANAN',
            $isScheduled ? 'Jadualkan Perjalanan' : 'Perlu Sekarang',
            'Tarikh: '.($isScheduled ? $enquiry['pickup_date'] : 'Segera'),
            'Masa: '.($isScheduled ? $enquiry['pickup_time'] : 'Segera'),
            'Zon waktu pengguna: '.($enquiry['user_timezone'] ?? 'Tidak dapat dikesan'),
            '',
            ...$this->locationLines('PICKUP (A)', 'pickup', $enquiry),
            '',
            ...$this->locationLines('DESTINASI (B)', 'destination', $enquiry),
            '',
            ...($distanceInKilometres === null ? [] : [
                'ANGGARAN JARAK',
                'Lebih kurang '.number_format($distanceInKilometres, 1).' km (garis lurus, jarak jalan raya mungkin lebih jauh)',
                '',
            ]),
            'PENUMPANG',
            'Dewasa: '.($enquiry['adults'] ?? 0),
            'Kanak-kanak: '.($enquiry['children'] ?? 0),
            'Bagasi: '.($enquiry['luggage_items'] ?? 0),
            '',
            'JENIS KENDERAAN',
            ($enquiry['vehicle_type'] ?? null) ?: 'Tiada pilihan khusus',
            '',
            'ARAHAN PICKUP',
            ($enquiry['pickup_instructions'] ?? null) ?: 'Tiada',
            '',
            'Mohon semakan kawasan liputan, ketersediaan kenderaan dan sebut harga.',
            'Terima kasih.',
        ], fn (?string $line): bool => $line !== null));
    }

    /**
     * @param  array<string, mixed>  $enquiry
     */
    private function distanceInKilometres(array $enquiry): ?float
    {
        $pickup = $this->coordinates('pickup', $enquiry);
        $destination = $this->coordinates('destination', $enquiry);

        if ($pickup === null || $destination === null) {
            return null;
        }

        return GoogleMapsLocation::distanceInKilometres($pickup, $destination);
    }

    /**
     * Coordinates from the pin, or from the Google Maps link when no pin was set.
     *
     * @param  array<string, mixed>  $enquiry
     * @return array{latitude: float, longitude: float}|null
     */
    private function coordinates(string $prefix, array $enquiry): ?array
    {
        if (isset($enquiry[$prefix.'_latitude'], $enquiry[$prefix.'_longitude'])) {
            return [
                'latitude' => (float) $enquiry[$prefix.'_latitude'],
                'longitude' => (float) $enquiry[$prefix.'_longitude'],
            ];
        }

        return GoogleMapsLocation::coordinatesFromUrl($enquiry[$prefix.'_google_maps_url'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $enquiry
     * @return array<int, string>
     */
    private function locationLines(string $heading, string $prefix, array $enquiry): array
    {
        $coordinates = isset($enquiry[$prefix.'_latitude'], $enquiry[$prefix.'_longitude'])
            ? $enquiry[$prefix.'_latitude'].', '.$enquiry[$prefix.'_longitude']
            : null;
        $mapsLink = ($enquiry[$prefix.'_google_maps_url'] ?? null)
            ?? ($coordinates === null ? null : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($coordinates));

        return array_values(array_filter([
            'LOKASI '.$heading,
            $enquiry[$prefix.'_place'],
            ($enquiry[$prefix.'_address'] ?? null) ?: null,
            $mapsLink,
        ]));
    }
}

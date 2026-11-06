<?php

use App\Models\ServiceEnquiry;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('renders transport with manual location, pin, and Google Maps-link fallbacks', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');

    $this->get(route('transport.show'))
        ->assertOk()
        ->assertSee('Destinasi')
        ->assertSee('Lokasi anda')
        ->assertSee('Butiran Penumpang')
        ->assertSee('Perjalanan &amp; Hubungi', false)
        ->assertSee('Guna Lokasi Saya')
        ->assertSee('Link Google Maps (pilihan)')
        ->assertSee('Semak sebelum ke WhatsApp')
        ->assertSee('Semak Semua Maklumat')
        ->assertSee('data-transport-vehicle-picker', false)
        ->assertSee('important_information_acknowledged', false)
        ->assertDontSee('<details class="transport-step-card"', false)
        ->assertDontSee('Catatan tambahan')
        ->assertDontSee('Maklumat anda akan disediakan untuk WhatsApp.')
        ->assertSee('transport-hero-v1.png', false)
        ->assertSee('data-location-apply-link', false)
        ->assertSee('data-transport-next-step', false)
        ->assertSee('data-transport-review', false)
        ->assertSee('data-transport-map', false);
});

it('prepares a scheduled transport WhatsApp message and records the enquiry for follow-up', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');
    $pickupDate = now()->addDay()->toDateString();

    $this->post(route('transport.prepare'), [
        'full_name' => 'Aminah Binti Abdullah',
        'contact_number' => '+60 19 999 9999',
        'user_timezone' => 'Asia/Kuala_Lumpur',
        'request_type' => 'scheduled',
        'pickup_date' => $pickupDate,
        'pickup_time' => '09:30',
        'pickup_place' => 'Hotel Contoh',
        'pickup_address' => 'Jalan Contoh',
        'pickup_latitude' => '21.422510',
        'pickup_longitude' => '39.826168',
        'pickup_google_maps_url' => 'https://www.google.com/maps/search/?api=1&query=21.422510,39.826168',
        'pickup_confirmed' => '1',
        'destination_place' => 'Lapangan Terbang Contoh',
        'destination_address' => 'Terminal 1',
        'destination_confirmed' => '1',
        'adults' => 2,
        'children' => 1,
        'luggage_items' => 3,
        'vehicle_type' => 'Van Penumpang',
        'important_information_acknowledged' => '1',
    ])
        ->assertOk()
        ->assertSee('Permintaan anda sedia dihantar.')
        ->assertSee('https://wa.me/60123456789?text=', false)
        ->assertSee('Hotel Contoh')
        ->assertDontSee('Belum dapat dikira')
        ->assertDontSee('ANGGARAN JARAK')
        ->assertSee('Zon waktu pengguna: Asia/Kuala_Lumpur')
        ->assertSee('Sila tekan Send di WhatsApp')
        ->assertDontSee('CATATAN');

    $this->assertDatabaseHas('service_enquiries', [
        'service_type' => ServiceEnquiry::TransportType,
        'status' => ServiceEnquiry::NewStatus,
        'full_name' => 'Aminah Binti Abdullah',
        'summary' => 'Hotel Contoh → Lapangan Terbang Contoh',
    ]);
});

it('accepts an immediate transport enquiry without a scheduled date or time', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');

    $this->post(route('transport.prepare'), [
        'full_name' => 'Hassan',
        'contact_number' => '+60 17 111 1111',
        'request_type' => 'immediate',
        'pickup_place' => 'Hotel A',
        'pickup_confirmed' => '1',
        'destination_place' => 'Hotel B',
        'destination_confirmed' => '1',
        'adults' => 1,
        'important_information_acknowledged' => '1',
    ])
        ->assertOk()
        ->assertSee('Perlu Sekarang')
        ->assertSee('Tarikh: Segera')
        ->assertSee('Masa: Segera');
});

it('rejects a non Google Maps location link', function () {
    $this->from(route('transport.show'))
        ->post(route('transport.prepare'), [
            'full_name' => 'Hassan',
            'contact_number' => '+60 17 111 1111',
            'request_type' => 'immediate',
            'pickup_place' => 'Hotel A',
            'pickup_google_maps_url' => 'https://example.com/location',
            'pickup_confirmed' => '1',
            'destination_place' => 'Hotel B',
            'destination_confirmed' => '1',
            'adults' => 1,
            'important_information_acknowledged' => '1',
        ])
        ->assertRedirect(route('transport.show'))
        ->assertSessionHasErrors('pickup_google_maps_url');
});

it('does not use the general WhatsApp number when transport has no dedicated operator number', function () {
    WebsiteSetting::setValue('contact_whatsapp', '60 12-111 2222', 'contact');

    $this->post(route('transport.prepare'), [
        'full_name' => 'Hassan',
        'contact_number' => '+60 17 111 1111',
        'request_type' => 'immediate',
        'pickup_place' => 'Hotel A',
        'pickup_confirmed' => '1',
        'destination_place' => 'Hotel B',
        'destination_confirmed' => '1',
        'adults' => 1,
        'important_information_acknowledged' => '1',
    ])
        ->assertOk()
        ->assertSee('Nombor WhatsApp Operator Transport belum dikonfigurasi.')
        ->assertDontSee('https://wa.me/60121112222', false);
});

it('requires acknowledgement of important information before preparing WhatsApp', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');

    $this->from(route('transport.show'))
        ->post(route('transport.prepare'), [
            'full_name' => 'Hassan',
            'contact_number' => '+60 17 111 1111',
            'request_type' => 'immediate',
            'pickup_place' => 'Hotel A',
            'pickup_confirmed' => '1',
            'destination_place' => 'Hotel B',
            'destination_confirmed' => '1',
            'adults' => 1,
        ])
        ->assertRedirect(route('transport.show'))
        ->assertSessionHasErrors('important_information_acknowledged');
});

it('lets staff configure the public vehicle choices', function () {
    $staff = User::factory()->staff()->create();
    $vehicleOptions = WebsiteSetting::transportVehicleDefaults();
    $vehicleOptions[0]['name'] = 'Sedan Eksekutif';
    $vehicleOptions[0]['description'] = 'Sesuai untuk 1–3 penumpang dengan bagasi ringan';
    $vehicleOptions[3]['enabled'] = false;

    $this->actingAs($staff)
        ->put(route('admin.transport-settings.update'), [
            'transport_enabled' => true,
            'transport_vehicle_options' => $vehicleOptions,
        ])
        ->assertRedirect(route('admin.transport-settings.edit'));

    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');
    $savedOptions = WebsiteSetting::transportVehicleOptions(WebsiteSetting::getValue('transport_vehicle_options'));

    expect($savedOptions[0]['name'])->toBe('Sedan Eksekutif')
        ->and($savedOptions[0]['description'])->toBe('Sesuai untuk 1–3 penumpang dengan bagasi ringan')
        ->and($savedOptions[3]['enabled'])->toBeFalse();

    $this->get(route('transport.show'))
        ->assertOk()
        ->assertSee('Sedan Eksekutif')
        ->assertDontSee('Bas Mini');
});

it('requires staff access to transport settings', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.transport-settings.edit'))
        ->assertForbidden();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function transportEnquiryPayload(array $overrides = []): array
{
    return [
        'full_name' => 'Hassan',
        'contact_number' => '+60 17 111 1111',
        'request_type' => 'immediate',
        'pickup_place' => 'Hotel A',
        'pickup_confirmed' => '1',
        'destination_place' => 'Hotel B',
        'destination_confirmed' => '1',
        'adults' => 1,
        'important_information_acknowledged' => '1',
        ...$overrides,
    ];
}

it('shows an unavailable notice instead of the form when no transport operator number is set', function () {
    $this->get(route('transport.show'))
        ->assertOk()
        ->assertSee('Operator Transport belum tersedia')
        ->assertDontSee('data-transport-form', false);
});

it('includes the straight-line distance when both locations have coordinates', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');

    $this->post(route('transport.prepare'), transportEnquiryPayload([
        'pickup_latitude' => '21.422510',
        'pickup_longitude' => '39.826168',
        'destination_google_maps_url' => 'https://www.google.com/maps/@24.467200,39.611100,15z',
    ]))
        ->assertOk()
        ->assertSee('ANGGARAN JARAK')
        ->assertSee('Lebih kurang 339.3 km')
        ->assertSee('garis lurus');

    expect(ServiceEnquiry::query()->sole()->details['distance_km'])->toBeGreaterThan(330)->toBeLessThan(345);
});

it('rejects a scheduled pickup that has already passed in the customer timezone', function () {
    $this->travelTo(now('Asia/Riyadh')->setTime(15, 0));

    $this->from(route('transport.show'))
        ->post(route('transport.prepare'), transportEnquiryPayload([
            'request_type' => 'scheduled',
            'user_timezone' => 'Asia/Riyadh',
            'pickup_date' => now('Asia/Riyadh')->toDateString(),
            'pickup_time' => '14:00',
        ]))
        ->assertRedirect(route('transport.show'))
        ->assertSessionHasErrors('pickup_time');

    $this->assertDatabaseCount('service_enquiries', 0);
});

it('accepts a scheduled pickup later today in the customer timezone', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');
    $this->travelTo(now('Asia/Riyadh')->setTime(9, 0));

    $this->post(route('transport.prepare'), transportEnquiryPayload([
        'request_type' => 'scheduled',
        'user_timezone' => 'Asia/Riyadh',
        'pickup_date' => now('Asia/Riyadh')->toDateString(),
        'pickup_time' => '18:30',
    ]))
        ->assertOk()
        ->assertSee('Masa: 18:30');
});

it('requires at least one adult passenger', function () {
    $this->from(route('transport.show'))
        ->post(route('transport.prepare'), transportEnquiryPayload(['adults' => 0]))
        ->assertRedirect(route('transport.show'))
        ->assertSessionHasErrors('adults');
});

it('rejects more passengers than the selected vehicle can carry', function () {
    $this->from(route('transport.show'))
        ->post(route('transport.prepare'), transportEnquiryPayload([
            'vehicle_type' => 'Kereta Sedan',
            'adults' => 3,
            'children' => 2,
        ]))
        ->assertRedirect(route('transport.show'))
        ->assertSessionHasErrors('vehicle_type');
});

it('lets staff add, remove, and set capacity for vehicle choices', function () {
    $staff = User::factory()->staff()->create();
    $vehicleOptions = WebsiteSetting::transportVehicleDefaults();
    $vehicleOptions[1]['remove'] = '1';
    $vehicleOptions[0]['capacity'] = 4;
    $vehicleOptions[] = [
        'key' => '',
        'name' => 'Van VIP',
        'description' => 'Tempat duduk kulit',
        'image' => 'darul-shofi/images/transport/van-v1.png',
        'capacity' => 8,
        'enabled' => '1',
    ];

    $this->actingAs($staff)
        ->put(route('admin.transport-settings.update'), [
            'transport_enabled' => true,
            'transport_vehicle_options' => $vehicleOptions,
        ])
        ->assertRedirect(route('admin.transport-settings.edit'))
        ->assertSessionHasNoErrors();

    $savedOptions = collect(WebsiteSetting::transportVehicleOptions(WebsiteSetting::getValue('transport_vehicle_options')));

    expect($savedOptions)->toHaveCount(4)
        ->and($savedOptions->pluck('name')->all())->toBe(['Kereta Sedan', 'Van Penumpang', 'Bas Mini', 'Van VIP'])
        ->and($savedOptions->firstWhere('key', 'sedan')['capacity'])->toBe(4)
        ->and($savedOptions->firstWhere('name', 'Van VIP')['key'])->toBe('van-vip')
        ->and($savedOptions->firstWhere('name', 'Van VIP')['capacity'])->toBe(8);
});

it('keeps default capacities for vehicle options saved before capacity existed', function () {
    $legacyOptions = collect(WebsiteSetting::transportVehicleDefaults())
        ->map(fn (array $vehicle): array => collect($vehicle)->except('capacity')->all())
        ->all();
    WebsiteSetting::setValue('transport_vehicle_options', json_encode($legacyOptions), 'transport');

    $savedOptions = WebsiteSetting::transportVehicleOptions(WebsiteSetting::getValue('transport_vehicle_options'));

    expect(array_column($savedOptions, 'capacity'))->toBe([3, 6, 12, 25]);
});

it('requires staff to keep at least one vehicle choice', function () {
    $staff = User::factory()->staff()->create();
    $vehicleOptions = collect(WebsiteSetting::transportVehicleDefaults())
        ->map(fn (array $vehicle): array => [...$vehicle, 'remove' => '1'])
        ->all();

    $this->actingAs($staff)
        ->from(route('admin.transport-settings.edit'))
        ->put(route('admin.transport-settings.update'), [
            'transport_enabled' => true,
            'transport_vehicle_options' => $vehicleOptions,
        ])
        ->assertRedirect(route('admin.transport-settings.edit'))
        ->assertSessionHasErrors('transport_vehicle_options');
});

it('shows staff an empty row for adding a vehicle and the capacity fields', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.transport-settings.edit'))
        ->assertOk()
        ->assertSee('Tambah kenderaan baharu')
        ->assertSee('transport_vehicle_options[4][name]', false)
        ->assertSee('transport_vehicle_options[0][capacity]', false)
        ->assertSee('transport_vehicle_options[0][remove]', false);
});

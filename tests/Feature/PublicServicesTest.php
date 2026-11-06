<?php

use App\Models\AqiqahKorbanService;
use App\Models\ServiceEnquiry;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('shows configured prices and the contact-for-price fallback while hiding drafts', function () {
    AqiqahKorbanService::factory()->create([
        'name' => 'Aqiqah Dengan Harga',
        'price' => '725.00',
        'is_published' => true,
    ]);
    AqiqahKorbanService::factory()->create([
        'name' => 'Aqiqah Tanpa Harga',
        'price' => null,
        'is_published' => true,
    ]);
    AqiqahKorbanService::factory()->create([
        'name' => 'Perkhidmatan Draf',
        'is_published' => false,
    ]);

    $this->get(route('aqiqah-korban.show'))
        ->assertOk()
        ->assertSee('Aqiqah Dengan Harga')
        ->assertSee('RM 725.00')
        ->assertSee('Aqiqah Tanpa Harga')
        ->assertSee('Hubungi Untuk Harga')
        ->assertSee('aqiqah-korban-hero-v1.png', false)
        ->assertSee('data-aqiqah-korban-select-service', false)
        ->assertSee('Mulakan Servis')
        ->assertSee('Pilih tawaran')
        ->assertSee('Guna Lokasi Saya')
        ->assertSee('important_information_acknowledged', false)
        ->assertDontSee('Semak dan Hantar WhatsApp')
        ->assertDontSee('Perkhidmatan Draf');
});

it('prepares an Aqiqah WhatsApp enquiry for the selected published service', function () {
    WebsiteSetting::setValue('aqiqah_korban_use_general_whatsapp', '0', 'aqiqah-korban');
    WebsiteSetting::setValue('aqiqah_korban_whatsapp', '60 12-345 6789', 'aqiqah-korban');
    $service = AqiqahKorbanService::factory()->create([
        'name' => 'Aqiqah Pilihan',
        'category' => AqiqahKorbanService::Aqiqah,
        'animal_type' => 'Kambing',
        'price' => null,
    ]);

    $response = $this->post(route('aqiqah-korban.prepare'), [
        'category' => AqiqahKorbanService::Aqiqah,
        'service_id' => $service->id,
        'quantity' => 2,
        'child_name' => 'Nur Aisyah',
        'child_gender' => 'Perempuan',
        'father_name' => 'Ahmad',
        'full_name' => 'Siti',
        'contact_number' => '+60 12 345 6789',
        'user_location' => 'Kuala Lumpur, Malaysia',
        'user_latitude' => '3.139000',
        'user_longitude' => '101.686900',
        'user_timezone' => 'Asia/Kuala_Lumpur',
        'requested_execution_date' => now()->addDays(2)->toDateString(),
        'important_information_acknowledged' => '1',
    ]);

    $response->assertOk()
        ->assertSee('Pertanyaan anda sedia dihantar.')
        ->assertSee('https://wa.me/60123456789?text=', false)
        ->assertSee('Nama anak: Nur Aisyah')
        ->assertSee('Jantina: Perempuan')
        ->assertSee('Nama bapa: Ahmad')
        ->assertSee('2 × 1 Ekor');

    $this->assertDatabaseHas('service_enquiries', [
        'service_type' => ServiceEnquiry::AqiqahKorbanType,
        'aqiqah_korban_service_id' => $service->id,
        'full_name' => 'Siti',
        'status' => ServiceEnquiry::NewStatus,
    ]);
});

it('rejects a service from the wrong category', function () {
    $service = AqiqahKorbanService::factory()->create([
        'category' => AqiqahKorbanService::Korban,
    ]);

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), [
            'category' => AqiqahKorbanService::Aqiqah,
            'service_id' => $service->id,
            'quantity' => 1,
            'child_name' => 'Nur Aisyah',
            'child_gender' => 'Perempuan',
            'full_name' => 'Siti',
            'contact_number' => '+60 12 345 6789',
            'user_location' => 'Kuala Lumpur, Malaysia',
            'important_information_acknowledged' => '1',
        ])
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors('service_id');
});

it('requires acknowledgement of important information before opening WhatsApp', function () {
    $service = AqiqahKorbanService::factory()->create();

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), [
            'category' => $service->category,
            'service_id' => $service->id,
            'quantity' => 1,
            'child_name' => 'Nur Aisyah',
            'child_gender' => 'Perempuan',
            'full_name' => 'Siti',
            'contact_number' => '+60 12 345 6789',
            'user_location' => 'Kuala Lumpur, Malaysia',
        ])
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors('important_information_acknowledged');
});

it('uses the official WhatsApp contact when selected by the administrator', function () {
    WebsiteSetting::setValue('contact_whatsapp', '60 11-222 3333', 'contact');
    WebsiteSetting::setValue('aqiqah_korban_use_general_whatsapp', '1', 'aqiqah-korban');
    $service = AqiqahKorbanService::factory()->create();

    $response = $this->post(route('aqiqah-korban.prepare'), [
        'category' => $service->category,
        'service_id' => $service->id,
        'quantity' => 1,
        'child_name' => 'Nur Aisyah',
        'child_gender' => 'Perempuan',
        'full_name' => 'Siti',
        'contact_number' => '+60 12 345 6789',
        'user_location' => 'Kuala Lumpur, Malaysia',
        'important_information_acknowledged' => '1',
    ]);

    $response->assertOk()->assertSee('https://wa.me/60112223333?text=', false);
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function korbanEnquiryPayload(AqiqahKorbanService $service, array $overrides = []): array
{
    return [
        'category' => AqiqahKorbanService::Korban,
        'service_id' => $service->id,
        'quantity' => 1,
        'participant_names' => "Ahmad bin Ali\nFatimah binti Ali",
        'full_name' => 'Siti',
        'contact_number' => '+60 12 345 6789',
        'user_location' => 'Kuala Lumpur, Malaysia',
        'important_information_acknowledged' => '1',
        ...$overrides,
    ];
}

it('starts the wizard form that the page script looks for', function () {
    AqiqahKorbanService::factory()->create();

    $this->get(route('aqiqah-korban.show'))
        ->assertOk()
        ->assertSee('data-aqiqah-korban-enquiry>', false)
        ->assertSee('name="child_name"', false)
        ->assertSee('name="participant_names"', false)
        ->assertDontSee('name="beneficiary_name"', false);
});

it('lists every korban participant and the estimated total', function () {
    WebsiteSetting::setValue('contact_whatsapp', '60 11-222 3333', 'contact');
    $service = AqiqahKorbanService::factory()->korban()->create([
        'quantity_description' => '1 Bahagian',
        'price' => '850.00',
    ]);

    $this->post(route('aqiqah-korban.prepare'), korbanEnquiryPayload($service, ['quantity' => 2]))
        ->assertOk()
        ->assertSee('NAMA PESERTA KORBAN (2)')
        ->assertSee('1. Ahmad bin Ali')
        ->assertSee('2. Fatimah binti Ali')
        ->assertSee('Anggaran jumlah: RM 1,700.00 (2 × RM 850.00)');

    expect(ServiceEnquiry::query()->sole()->details['participant_names'])->toBe(['Ahmad bin Ali', 'Fatimah binti Ali']);
});

it('rejects more korban participants than the package covers', function () {
    $service = AqiqahKorbanService::factory()->korban()->create(['quantity_description' => '1 Bahagian']);

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), korbanEnquiryPayload($service))
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors('participant_names');
});

it('allows seven participants for one whole cow', function () {
    WebsiteSetting::setValue('contact_whatsapp', '60 11-222 3333', 'contact');
    $service = AqiqahKorbanService::factory()->korban()->create(['quantity_description' => '1 Ekor']);

    $this->post(route('aqiqah-korban.prepare'), korbanEnquiryPayload($service, [
        'participant_names' => implode("\n", ['A', 'B', 'C', 'D', 'E', 'F', 'G']),
    ]))
        ->assertOk()
        ->assertSee('NAMA PESERTA KORBAN (7)');
});

it('requires child details for an aqiqah enquiry', function () {
    $service = AqiqahKorbanService::factory()->create();

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), [
            'category' => AqiqahKorbanService::Aqiqah,
            'service_id' => $service->id,
            'quantity' => 1,
            'full_name' => 'Siti',
            'contact_number' => '+60 12 345 6789',
            'user_location' => 'Kuala Lumpur, Malaysia',
            'important_information_acknowledged' => '1',
        ])
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors(['child_name', 'child_gender']);
});

it('hides korban offers outside their registration dates and refuses enquiries for them', function () {
    AqiqahKorbanService::factory()->korban()->create([
        'name' => 'Korban Dibuka',
        'registration_opens_on' => today()->subDay(),
        'registration_closes_on' => today()->addWeek(),
    ]);
    $closedService = AqiqahKorbanService::factory()->korban()->create([
        'name' => 'Korban Ditutup',
        'registration_closes_on' => today()->subDay(),
    ]);
    AqiqahKorbanService::factory()->korban()->create([
        'name' => 'Korban Belum Dibuka',
        'registration_opens_on' => today()->addDay(),
    ]);

    $this->get(route('aqiqah-korban.show'))
        ->assertOk()
        ->assertSee('Korban Dibuka')
        ->assertSee('Pendaftaran:')
        ->assertDontSee('Korban Ditutup')
        ->assertDontSee('Korban Belum Dibuka');

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), korbanEnquiryPayload($closedService, ['participant_names' => 'Ahmad']))
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors('service_id');
});

it('lists services by sort order, then newest first', function () {
    AqiqahKorbanService::factory()->create(['name' => 'Pakej Lama Susunan Satu', 'sort_order' => 1]);
    AqiqahKorbanService::factory()->create(['name' => 'Pakej Baru Susunan Dua', 'sort_order' => 2]);
    AqiqahKorbanService::factory()->create(['name' => 'Pakej Paling Baru Susunan Satu', 'sort_order' => 1]);

    $this->get(route('aqiqah-korban.show'))
        ->assertOk()
        ->assertSeeInOrder(['Pakej Paling Baru Susunan Satu', 'Pakej Lama Susunan Satu', 'Pakej Baru Susunan Dua']);
});

it('rejects an aqiqah execution date earlier than today in the customer timezone', function () {
    $service = AqiqahKorbanService::factory()->create();

    $this->from(route('aqiqah-korban.show'))
        ->post(route('aqiqah-korban.prepare'), [
            'category' => AqiqahKorbanService::Aqiqah,
            'service_id' => $service->id,
            'quantity' => 1,
            'child_name' => 'Nur Aisyah',
            'child_gender' => 'Perempuan',
            'full_name' => 'Siti',
            'contact_number' => '+60 12 345 6789',
            'user_location' => 'Kuala Lumpur, Malaysia',
            'user_timezone' => 'Asia/Kuala_Lumpur',
            'requested_execution_date' => now('Asia/Kuala_Lumpur')->subDay()->toDateString(),
            'important_information_acknowledged' => '1',
        ])
        ->assertRedirect(route('aqiqah-korban.show'))
        ->assertSessionHasErrors('requested_execution_date');
});

it('renders the prepared pages without stray markup before the document', function () {
    WebsiteSetting::setValue('transport_whatsapp', '60 12-345 6789', 'transport');

    $response = $this->post(route('transport.prepare'), [
        'full_name' => 'Hassan',
        'contact_number' => '+60 17 111 1111',
        'request_type' => 'immediate',
        'pickup_place' => 'Hotel A',
        'pickup_confirmed' => '1',
        'destination_place' => 'Hotel B',
        'destination_confirmed' => '1',
        'adults' => 1,
        'important_information_acknowledged' => '1',
    ]);

    expect(ltrim($response->getContent()))->toStartWith('<!DOCTYPE html>');
});

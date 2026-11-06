<?php

use App\Models\ServiceEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

it('lists service enquiries for staff with status counts', function () {
    $staff = User::factory()->staff()->create();
    ServiceEnquiry::factory()->create(['full_name' => 'Pelanggan Transport']);
    ServiceEnquiry::factory()->aqiqahKorban()->contacted()->create(['full_name' => 'Pelanggan Korban']);

    $this->actingAs($staff)
        ->get(route('admin.service-enquiries.index'))
        ->assertOk()
        ->assertSee('Pelanggan Transport')
        ->assertSee('Pelanggan Korban')
        ->assertSee('Semua (2)')
        ->assertSee('Baru (1)')
        ->assertSee('Dihubungi (1)');
});

it('filters service enquiries by service type, status, and search', function () {
    $staff = User::factory()->staff()->create();
    ServiceEnquiry::factory()->create(['full_name' => 'Pelanggan Transport']);
    ServiceEnquiry::factory()->aqiqahKorban()->create(['full_name' => 'Pelanggan Korban']);
    ServiceEnquiry::factory()->aqiqahKorban()->contacted()->create(['full_name' => 'Pelanggan Dihubungi']);

    $this->actingAs($staff)
        ->get(route('admin.service-enquiries.index', ['type' => ServiceEnquiry::AqiqahKorbanType, 'status' => ServiceEnquiry::NewStatus]))
        ->assertOk()
        ->assertSee('Pelanggan Korban')
        ->assertDontSee('Pelanggan Transport')
        ->assertDontSee('Pelanggan Dihubungi');

    $this->actingAs($staff)
        ->get(route('admin.service-enquiries.index', ['q' => 'Dihubungi']))
        ->assertOk()
        ->assertSee('Pelanggan Dihubungi')
        ->assertDontSee('Pelanggan Transport');
});

it('shows the prepared message and a WhatsApp link to the customer', function () {
    $staff = User::factory()->staff()->create();
    $enquiry = ServiceEnquiry::factory()->create([
        'contact_number' => '+60 12 345 6789',
        'message' => 'MESEJ UJIAN TRANSPORT',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.service-enquiries.show', $enquiry))
        ->assertOk()
        ->assertSee('MESEJ UJIAN TRANSPORT')
        ->assertSee('https://wa.me/60123456789', false);
});

it('lets staff update the follow-up status and notes', function () {
    $staff = User::factory()->staff()->create();
    $enquiry = ServiceEnquiry::factory()->create();

    $this->actingAs($staff)
        ->put(route('admin.service-enquiries.update', $enquiry), [
            'status' => ServiceEnquiry::ContactedStatus,
            'staff_notes' => 'Sebut harga dihantar.',
        ])
        ->assertRedirect(route('admin.service-enquiries.show', $enquiry));

    $enquiry->refresh();

    expect($enquiry->status)->toBe(ServiceEnquiry::ContactedStatus)
        ->and($enquiry->staff_notes)->toBe('Sebut harga dihantar.')
        ->and($enquiry->contacted_at)->not->toBeNull();
});

it('rejects an unknown follow-up status', function () {
    $staff = User::factory()->staff()->create();
    $enquiry = ServiceEnquiry::factory()->create();

    $this->actingAs($staff)
        ->put(route('admin.service-enquiries.update', $enquiry), ['status' => 'unknown'])
        ->assertSessionHasErrors('status');
});

it('lets staff delete an enquiry', function () {
    $staff = User::factory()->staff()->create();
    $enquiry = ServiceEnquiry::factory()->create();

    $this->actingAs($staff)
        ->delete(route('admin.service-enquiries.destroy', $enquiry))
        ->assertRedirect(route('admin.service-enquiries.index'));

    $this->assertModelMissing($enquiry);
});

it('requires staff access to service enquiries', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $enquiry = ServiceEnquiry::factory()->create();

    $this->actingAs($customer)->get(route('admin.service-enquiries.index'))->assertForbidden();
    $this->actingAs($customer)->get(route('admin.service-enquiries.show', $enquiry))->assertForbidden();
});

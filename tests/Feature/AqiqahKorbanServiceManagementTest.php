<?php

use App\Models\AqiqahKorbanService;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('allows staff to create a published service with an approved price and image', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.aqiqah-korban-services.store'), [
            'category' => AqiqahKorbanService::Aqiqah,
            'name' => 'Aqiqah Kambing — 1 Ekor',
            'animal_type' => 'Kambing',
            'quantity_description' => '1 Ekor',
            'price' => '725.00',
            'full_description' => 'Perkhidmatan yang perlu disahkan oleh operator.',
            'promotion_badge' => AqiqahKorbanService::RecommendationBadge,
            'featured_image' => UploadedFile::fake()->image('aqiqah.jpg', 1200, 800),
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.index'));

    $service = AqiqahKorbanService::query()->where('name', 'Aqiqah Kambing — 1 Ekor')->firstOrFail();

    $this->assertDatabaseHas('aqiqah_korban_services', [
        'id' => $service->id,
        'category' => AqiqahKorbanService::Aqiqah,
        'price' => '725.00',
        'is_published' => true,
        'promotion_badge' => AqiqahKorbanService::RecommendationBadge,
    ]);
    Storage::disk('public')->assertExists($service->featured_image);
});

it('allows staff to update, unpublish, and delete a service', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $service = AqiqahKorbanService::factory()->create([
        'featured_image' => 'aqiqah-korban/lama.jpg',
        'is_published' => true,
    ]);
    Storage::disk('public')->put($service->featured_image, 'image');

    $this->actingAs($staff)
        ->put(route('admin.aqiqah-korban-services.update', $service), [
            'category' => AqiqahKorbanService::Korban,
            'name' => 'Korban Lembu — 1 Bahagian',
            'animal_type' => 'Lembu',
            'quantity_description' => '1 Bahagian',
            'korban_season' => '1448H',
            'registration_period' => 'Tertakluk kepada pengesahan',
            'promotion_badge' => AqiqahKorbanService::SuperDealBadge,
            'is_published' => false,
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.index'));

    $this->assertDatabaseHas('aqiqah_korban_services', [
        'id' => $service->id,
        'name' => 'Korban Lembu — 1 Bahagian',
        'is_published' => false,
        'korban_season' => '1448H',
        'promotion_badge' => AqiqahKorbanService::SuperDealBadge,
    ]);

    $this->actingAs($staff)
        ->delete(route('admin.aqiqah-korban-services.destroy', $service))
        ->assertRedirect();

    $this->assertDatabaseMissing('aqiqah_korban_services', ['id' => $service->id]);
    Storage::disk('public')->assertMissing('aqiqah-korban/lama.jpg');
});

it('requires staff access for Aqiqah and Korban administration', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->get(route('admin.aqiqah-korban-services.index'))
        ->assertForbidden();
});

it('shows admin-managed service totals on the Aqiqah and Korban dashboard', function () {
    $staff = User::factory()->staff()->create();

    AqiqahKorbanService::factory()->count(2)->create([
        'category' => AqiqahKorbanService::Aqiqah,
        'is_published' => true,
    ]);
    AqiqahKorbanService::factory()->create([
        'category' => AqiqahKorbanService::Korban,
        'is_published' => false,
    ]);

    $this->actingAs($staff)
        ->get(route('admin.aqiqah-korban-services.index'))
        ->assertOk()
        ->assertSee('Jumlah Perkhidmatan')
        ->assertSee('Semua (3)')
        ->assertSee('Aqiqah (2)')
        ->assertSee('Korban (1)')
        ->assertSee('Dipaparkan');
});

it('lets staff choose a dedicated WhatsApp recipient for Aqiqah and Korban', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->put(route('admin.aqiqah-korban-services.contact.update'), [
            'aqiqah_korban_use_general_whatsapp' => false,
            'aqiqah_korban_whatsapp' => '+60 12 345 6789',
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.index'));

    expect(WebsiteSetting::getValue('aqiqah_korban_use_general_whatsapp'))->toBe('0')
        ->and(WebsiteSetting::getValue('aqiqah_korban_whatsapp'))->toBe('60123456789');
});

it('saves korban registration dates and sort order', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.aqiqah-korban-services.store'), [
            'category' => AqiqahKorbanService::Korban,
            'name' => 'Korban Lembu — 1 Bahagian',
            'animal_type' => 'Lembu',
            'quantity_description' => '1 Bahagian',
            'registration_opens_on' => '2027-04-01',
            'registration_closes_on' => '2027-05-20',
            'sort_order' => 3,
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.index'));

    $service = AqiqahKorbanService::query()->sole();

    expect($service->registration_opens_on->toDateString())->toBe('2027-04-01')
        ->and($service->registration_closes_on->toDateString())->toBe('2027-05-20')
        ->and($service->sort_order)->toBe(3);
});

it('rejects a registration close date before the open date', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->from(route('admin.aqiqah-korban-services.create'))
        ->post(route('admin.aqiqah-korban-services.store'), [
            'category' => AqiqahKorbanService::Korban,
            'name' => 'Korban Lembu — 1 Bahagian',
            'animal_type' => 'Lembu',
            'quantity_description' => '1 Bahagian',
            'registration_opens_on' => '2027-05-20',
            'registration_closes_on' => '2027-04-01',
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.create'))
        ->assertSessionHasErrors('registration_closes_on');
});

it('clears korban registration dates when a service becomes aqiqah', function () {
    $staff = User::factory()->staff()->create();
    $service = AqiqahKorbanService::factory()->korban()->create([
        'registration_opens_on' => '2027-04-01',
        'registration_closes_on' => '2027-05-20',
    ]);

    $this->actingAs($staff)
        ->put(route('admin.aqiqah-korban-services.update', $service), [
            'category' => AqiqahKorbanService::Aqiqah,
            'name' => 'Aqiqah Kambing — 1 Ekor',
            'animal_type' => 'Kambing',
            'quantity_description' => '1 Ekor',
            'registration_opens_on' => '2027-04-01',
        ])
        ->assertRedirect(route('admin.aqiqah-korban-services.index'));

    expect($service->refresh()->registration_opens_on)->toBeNull()
        ->and($service->registration_closes_on)->toBeNull();
});

it('shows staff the sort order and registration date fields', function () {
    $staff = User::factory()->staff()->create();
    $service = AqiqahKorbanService::factory()->korban()->create(['registration_closes_on' => today()->subDay()]);

    $this->actingAs($staff)
        ->get(route('admin.aqiqah-korban-services.edit', $service))
        ->assertOk()
        ->assertSee('name="sort_order"', false)
        ->assertSee('name="registration_opens_on"', false)
        ->assertSee('name="registration_closes_on"', false);

    $this->actingAs($staff)
        ->get(route('admin.aqiqah-korban-services.index'))
        ->assertOk()
        ->assertSee('Di luar tarikh pendaftaran');
});

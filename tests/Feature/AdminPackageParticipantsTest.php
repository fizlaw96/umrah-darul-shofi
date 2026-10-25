<?php

use App\Models\Package;
use App\Models\PackageDeparture;
use App\Models\PackageParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Str;

uses(LazilyRefreshDatabase::class);

it('lets staff add package participants and export their list as an Excel workbook', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create(['title' => 'Pakej Peserta Musim Sejuk']);
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2027-01-15',
        'return_date' => '2027-01-23',
    ]);
    PackageDeparture::factory()->for($package)->create([
        'departure_date' => '2027-01-22',
        'return_date' => '2027-01-30',
    ]);

    $this->actingAs($staff)
        ->get(route('admin.packages.show', $package))
        ->assertOk()
        ->assertSee('Tambah peserta')
        ->assertSee('Eksport Excel')
        ->assertDontSee('Urus Pakej')
        ->assertDontSee('Pratonton')
        ->assertSee('Tarikh berlepas')
        ->assertSee('15 Jan 2027')
        ->assertDontSee('E-mel');

    $this->post(route('admin.packages.participants.store', $package), [
        'package_departure_id' => $package->departures()->oldest('departure_date')->value('id'),
        'full_name' => 'Nur Aisyah Ahmad',
        'phone' => '0123456789',
        'notes' => 'Bilik berdua.',
    ])->assertRedirect(route('admin.packages.show', $package));

    $participant = PackageParticipant::query()->firstOrFail();

    $this->assertDatabaseHas('package_participants', [
        'id' => $participant->id,
        'package_id' => $package->id,
        'package_departure_id' => $package->departures()->oldest('departure_date')->value('id'),
        'full_name' => 'Nur Aisyah Ahmad',
        'phone' => '0123456789',
        'notes' => 'Bilik berdua.',
    ]);

    $response = $this->get(route('admin.packages.participants.export', $package));

    $response
        ->assertOk()
        ->assertDownload(Str::slug($package->title).'-senarai-peserta.xlsx');

    $spreadsheetPath = tempnam(sys_get_temp_dir(), 'participant-export-test-');
    file_put_contents($spreadsheetPath, $response->streamedContent());

    $spreadsheet = new ZipArchive;

    try {
        expect($spreadsheet->open($spreadsheetPath))->toBeTrue();

        $worksheetXml = $spreadsheet->getFromName('xl/worksheets/sheet1.xml');
        $stylesXml = $spreadsheet->getFromName('xl/styles.xml');
    } finally {
        $spreadsheet->close();
        unlink($spreadsheetPath);
    }

    expect($worksheetXml)
        ->toContain('Senarai Peserta')
        ->toContain('Pakej Peserta Musim Sejuk')
        ->toContain('Tarikh penerbangan')
        ->toContain('15 Jan 2027, 22 Jan 2027')
        ->toContain('Bil.')
        ->toContain('<c r="B3" s="3"')
        ->toContain('<c r="C3" s="2"')
        ->toContain('<mergeCell ref="B1:D1"/>')
        ->not->toContain('r="A3"')
        ->toContain('Nama peserta')
        ->toContain('Telefon')
        ->toContain('Catatan')
        ->toContain('Nur Aisyah Ahmad')
        ->toContain('<c r="A6" s="6" t="inlineStr"><is><t>1</t></is></c>')
        ->toContain('bestFit="1"')
        ->toContain('showGridLines="0"')
        ->not->toContain('E-mel');

    expect($stylesXml)
        ->toContain('<sz val="18"/>')
        ->toContain('<sz val="12"/>')
        ->toContain('wrapText="1"');

    expect((new DOMDocument)->loadXML($worksheetXml))->toBeTrue();
    expect((new DOMDocument)->loadXML($stylesXml))->toBeTrue();
});

it('only stores a participant against a departure belonging to the selected package', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();
    $departure = PackageDeparture::factory()->for($package)->create();
    $otherDeparture = PackageDeparture::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.packages.participants.store', $package), [
            'full_name' => 'Peserta Ringkas',
            'phone' => '0139876543',
            'notes' => 'Perlu dihubungi selepas Asar.',
            'package_departure_id' => $otherDeparture->id,
            'email' => 'tidak-direkodkan@example.test',
        ])
        ->assertSessionHasErrors('package_departure_id');

    expect(PackageParticipant::query()->count())->toBe(0);

    $this->post(route('admin.packages.participants.store', $package), [
        'full_name' => 'Peserta Ringkas',
        'phone' => '0139876543',
        'notes' => 'Perlu dihubungi selepas Asar.',
        'package_departure_id' => $departure->id,
        'email' => 'tidak-direkodkan@example.test',
    ])->assertRedirect(route('admin.packages.show', $package));

    $this->assertDatabaseHas('package_participants', [
        'package_id' => $package->id,
        'package_departure_id' => $departure->id,
        'full_name' => 'Peserta Ringkas',
        'phone' => '0139876543',
        'notes' => 'Perlu dihubungi selepas Asar.',
        'email' => null,
    ]);
});

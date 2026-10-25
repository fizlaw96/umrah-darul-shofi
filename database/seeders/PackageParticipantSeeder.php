<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\PackageParticipant;
use Illuminate\Database\Seeder;

class PackageParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Package::query()
            ->with('departures')
            ->lazyById()
            ->each(function (Package $package): void {
                PackageParticipant::query()->firstOrCreate(
                    [
                        'package_id' => $package->id,
                        'email' => "peserta-contoh-{$package->id}@example.test",
                    ],
                    [
                        'package_departure_id' => $package->departures->first()?->id,
                        'full_name' => "Peserta Contoh {$package->id}",
                        'phone' => '0100000000',
                        'notes' => 'Rekod contoh untuk demonstrasi senarai peserta.',
                    ],
                );
            });
    }
}

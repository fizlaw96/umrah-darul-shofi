<?php

namespace Database\Seeders;

use App\Models\PackageServiceOption;
use Illuminate\Database\Seeder;

class PackageServiceOptionSeeder extends Seeder
{
    /** @var array<int, string> */
    private const ServiceOptions = [
        'Tiket penerbangan pergi dan balik',
        'Bagasi daftar masuk',
        'Penginapan di Makkah dan Madinah',
        'Pengangkutan bas berhawa dingin',
        'Visa Umrah',
        'Makan mengikut atur cara',
        'Ziarah di Makkah dan Madinah',
        'Bimbingan mutawif',
        'Insurans perjalanan',
        'Set hadiah jemaah',
        'Caj lebihan bagasi',
        'Perbelanjaan peribadi',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(self::ServiceOptions)->each(function (string $name, int $index): void {
            PackageServiceOption::query()->updateOrCreate(
                ['name' => $name],
                ['sort_order' => $index + 1],
            );
        });
    }
}

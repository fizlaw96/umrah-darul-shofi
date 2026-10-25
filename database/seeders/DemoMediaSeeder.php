<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;

class DemoMediaSeeder extends Seeder
{
    /**
     * @var array<int, array{source: string, destination: string}>
     */
    private const Images = [
        ['source' => 'darul-shofi/images/packages/umrah-poster.jpg', 'destination' => 'demo/packages/umrah-poster.jpg'],
        ['source' => 'darul-shofi/images/packages/haji-poster.jpg', 'destination' => 'demo/packages/haji-poster.jpg'],
        ['source' => 'darul-shofi/images/packages/badal-haji-poster.jpg', 'destination' => 'demo/packages/badal-haji-poster.jpg'],
        ['source' => 'darul-shofi/images/packages/pelancongan-poster.jpg', 'destination' => 'demo/packages/pelancongan-poster.jpg'],
        ['source' => 'darul-shofi/images/hero/umrah-ekonomi-madinah-hero.png', 'destination' => 'demo/packages/umrah-ekonomi.png'],
        ['source' => 'darul-shofi/images/hero/umrah-premium-makkah-hero.png', 'destination' => 'demo/packages/umrah-premium.png'],
        ['source' => 'darul-shofi/images/hero/umrah-ramadan-makkah-hero.png', 'destination' => 'demo/packages/umrah-ramadan.png'],
        ['source' => 'darul-shofi/images/about/jemaah-menuju-kaabah.jpg', 'destination' => 'demo/packages/umrah-keluarga.jpg'],
        ['source' => 'darul-shofi/images/about/tilawah-di-makkah.jpg', 'destination' => 'demo/packages/umrah-cuti-sekolah.jpg'],
        ['source' => 'darul-shofi/images/about/tawaf-di-makkah.jpg', 'destination' => 'demo/gallery/jemaah-tawaf.jpg'],
        ['source' => 'darul-shofi/images/about/dua-di-kaabah.jpg', 'destination' => 'demo/gallery/doa-di-kaabah.jpg'],
        ['source' => 'darul-shofi/images/about/jemaah-menuju-kaabah.jpg', 'destination' => 'demo/gallery/menuju-kaabah.jpg'],
        ['source' => 'darul-shofi/images/about/tilawah-di-makkah.jpg', 'destination' => 'demo/gallery/tilawah.jpg'],
        ['source' => 'darul-shofi/images/about/tawaf-di-makkah.jpg', 'destination' => 'demo/people/mutawif-1.jpg'],
        ['source' => 'darul-shofi/images/about/dua-di-kaabah.jpg', 'destination' => 'demo/people/mutawif-2.jpg'],
        ['source' => 'darul-shofi/images/about/jemaah-menuju-kaabah.jpg', 'destination' => 'demo/people/mutawif-3.jpg'],
        ['source' => 'darul-shofi/images/about/tilawah-di-makkah.jpg', 'destination' => 'demo/people/mutawif-4.jpg'],
        ['source' => 'darul-shofi/images/about/tawaf-di-makkah.jpg', 'destination' => 'demo/people/jemaah-1.jpg'],
        ['source' => 'darul-shofi/images/about/dua-di-kaabah.jpg', 'destination' => 'demo/people/jemaah-2.jpg'],
        ['source' => 'darul-shofi/images/about/jemaah-menuju-kaabah.jpg', 'destination' => 'demo/people/jemaah-3.jpg'],
        ['source' => 'darul-shofi/images/about/tilawah-di-makkah.jpg', 'destination' => 'demo/articles/persiapan-umrah.jpg'],
        ['source' => 'darul-shofi/images/hero/umrah-premium-makkah-hero.png', 'destination' => 'demo/articles/ibadah-tenang.png'],
        ['source' => 'darul-shofi/images/branding/salam-haji-logo.png', 'destination' => 'demo/branding/company-logo.png'],
        ['source' => 'darul-shofi/images/branding/darul-soffi-logo.jpg', 'destination' => 'demo/branding/darul-soffi-logo.jpg'],
        ['source' => 'darul-shofi/images/hotels/makkah-arrival.jpg', 'destination' => 'demo/travel/hotels/makkah-arrival.jpg'],
        ['source' => 'darul-shofi/images/hotels/madinah-arrival.jpg', 'destination' => 'demo/travel/hotels/madinah-arrival.jpg'],
        ['source' => 'darul-shofi/images/hotels/saudi-lobby.jpg', 'destination' => 'demo/travel/hotels/saudi-lobby.jpg'],
        ['source' => 'darul-shofi/images/rooms/quad.jpg', 'destination' => 'demo/content/room-prices/quad.jpg'],
        ['source' => 'darul-shofi/images/rooms/triple.jpg', 'destination' => 'demo/content/room-prices/triple.jpg'],
        ['source' => 'darul-shofi/images/rooms/double.jpg', 'destination' => 'demo/content/room-prices/double.jpg'],
        ['source' => 'darul-shofi/images/partners/saudia.svg', 'destination' => 'demo/airlines/saudia.svg'],
        ['source' => 'darul-shofi/images/partners/qatar-airways.svg', 'destination' => 'demo/airlines/qatar-airways.svg'],
        ['source' => 'darul-shofi/images/partners/malaysia-airlines.svg', 'destination' => 'demo/airlines/malaysia-airlines.svg'],
        ['source' => 'darul-shofi/images/partners/airasia-x.svg', 'destination' => 'demo/airlines/airasia-x.svg'],
        ['source' => 'darul-shofi/images/partners/turkish-airlines.svg', 'destination' => 'demo/airlines/turkish-airlines.svg'],
        ['source' => 'darul-shofi/images/partners/emirates.svg', 'destination' => 'demo/airlines/emirates.svg'],
    ];

    public function run(): void
    {
        $filesystem = app(Filesystem::class);

        foreach (self::Images as $image) {
            $source = public_path($image['source']);

            if (! $filesystem->exists($source)) {
                continue;
            }

            $destination = storage_path('app/public/'.$image['destination']);
            $filesystem->ensureDirectoryExists(dirname($destination));
            $filesystem->copy($source, $destination);
        }
    }
}

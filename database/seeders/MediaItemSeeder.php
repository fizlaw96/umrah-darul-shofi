<?php

namespace Database\Seeders;

use App\Models\MediaItem;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class MediaItemSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedAboutCopy();

        $this->seedSection(MediaItem::AboutSection, [
            [
                'path' => 'darul-shofi/images/about/tawaf-di-makkah.jpg',
                'alt_text' => 'Jemaah menunaikan tawaf di hadapan Kaabah',
                'caption' => 'Langkah pertama menuju Baitullah',
            ],
            [
                'path' => 'darul-shofi/images/about/dua-di-kaabah.jpg',
                'alt_text' => 'Jemaah berdoa di Masjidil Haram',
                'caption' => 'Setiap doa diraikan dengan penuh tertib',
            ],
            [
                'path' => 'darul-shofi/images/about/jemaah-menuju-kaabah.jpg',
                'alt_text' => 'Jemaah wanita menuju Kaabah di Makkah',
                'caption' => 'Sahabat perjalanan yang saling menguatkan',
            ],
            [
                'path' => 'darul-shofi/images/about/tilawah-di-makkah.jpg',
                'alt_text' => 'Jemaah membaca al-Quran di Masjidil Haram',
                'caption' => 'Menghayati ibadah dalam suasana yang tenang',
            ],
        ]);

        $this->seedSection(MediaItem::GallerySection, [
            [
                'path' => 'demo/packages/umrah-ekonomi.png',
                'alt_text' => 'Suasana perjalanan Umrah bersama jemaah',
                'caption' => 'Persediaan perjalanan dengan tenang dan teratur',
            ],
            [
                'path' => 'demo/packages/umrah-premium.png',
                'alt_text' => 'Pemandangan Masjidil Haram pada waktu malam',
                'caption' => 'Malam yang penuh syukur di Tanah Suci',
            ],
            [
                'path' => 'demo/packages/umrah-ramadan.png',
                'alt_text' => 'Ibadah Ramadan di hadapan Kaabah',
                'caption' => 'Menghayati Ramadan bersama sahabat perjalanan',
            ],
            [
                'path' => 'demo/packages/umrah-keluarga.jpg',
                'alt_text' => 'Jemaah menunaikan ibadah bersama keluarga',
                'caption' => 'Membina kenangan ibadah bersama insan tersayang',
            ],
            [
                'path' => 'demo/packages/umrah-cuti-sekolah.jpg',
                'alt_text' => 'Jemaah membaca al-Quran di Masjidil Haram',
                'caption' => 'Masa yang bermakna untuk mendekatkan diri kepada Allah',
            ],
            [
                'path' => 'demo/gallery/jemaah-tawaf.jpg',
                'alt_text' => 'Jemaah melakukan tawaf di Makkah',
                'caption' => 'Setiap langkah mengiringi doa dan harapan',
            ],
            [
                'path' => 'demo/gallery/doa-di-kaabah.jpg',
                'alt_text' => 'Jemaah berdoa di Masjidil Haram',
                'caption' => 'Doa-doa yang dipanjatkan dengan penuh pengharapan',
            ],
            [
                'path' => 'demo/gallery/menuju-kaabah.jpg',
                'alt_text' => 'Jemaah menuju Kaabah di Makkah',
                'caption' => 'Melangkah bersama menuju Baitullah',
            ],
            [
                'path' => 'demo/gallery/tilawah.jpg',
                'alt_text' => 'Jemaah bertilawah di Makkah',
                'caption' => 'Menenangkan jiwa dengan ayat-ayat suci',
            ],
        ]);
    }

    /**
     * @param  array<int, array{path: string, alt_text: string, caption: string}>  $mediaItems
     */
    private function seedSection(string $section, array $mediaItems): void
    {
        collect($mediaItems)->each(function (array $media, int $index) use ($section): void {
            MediaItem::query()->updateOrCreate(
                ['section' => $section, 'path' => $media['path']],
                [
                    ...$media,
                    'is_published' => true,
                    'sort_order' => $index + 1,
                ],
            );
        });
    }

    private function seedAboutCopy(): void
    {
        if (blank(WebsiteSetting::getValue('about_description'))) {
            WebsiteSetting::setValue(
                'about_description',
                "SalamHaji Travel & Tours membantu jemaah merancang perjalanan Umrah dengan susunan yang jelas, bimbingan yang mudah diikuti dan layanan yang penuh hormat.\n\nDaripada persediaan awal hingga kepulangan ke tanah air, kami mahu setiap langkah ibadah terasa lebih tenang supaya jemaah dapat memberi tumpuan kepada perkara yang paling utama.",
                'homepage',
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\PackageCategory;
use Illuminate\Database\Seeder;

class DefaultContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ([
            [
                'name' => 'Pakej Umrah',
                'slug' => 'pakej-umrah',
                'description' => 'Pilihan perjalanan Umrah dengan susunan yang jelas.',
                'sort_order' => 1,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
            [
                'name' => 'Pakej Haji',
                'slug' => 'pakej-haji',
                'description' => 'Pilihan perjalanan Haji dengan bimbingan menyeluruh.',
                'sort_order' => 2,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
            [
                'name' => 'Pakej Badal Haji',
                'slug' => 'pakej-badal-haji',
                'description' => 'Pilihan perkhidmatan Badal Haji yang diurus dengan amanah.',
                'sort_order' => 3,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
            [
                'name' => 'Pakej Pelancongan',
                'slug' => 'pakej-pelancongan',
                'description' => 'Pilihan pelancongan Muslim ke destinasi terpilih.',
                'sort_order' => 4,
                'is_active' => true,
                'show_on_homepage' => true,
            ],
        ] as $category) {
            PackageCategory::query()->updateOrCreate(
                ['slug' => $category['slug']],
                $category,
            );
        }

        foreach ([
            [
                'category' => 'Pertanyaan',
                'question' => 'Bagaimanakah saya bertanya tentang pakej?',
                'answer' => 'Pilih pakej yang diminati dan gunakan WhatsApp atau borang pertanyaan untuk berkongsi keperluan anda dengan agensi.',
                'sort_order' => 1,
                'is_published' => true,
            ],
            [
                'category' => 'Pakej',
                'question' => 'Adakah harga yang dipaparkan muktamad?',
                'answer' => 'Harga dan maklumat pakej perlu disahkan oleh agensi melalui saluran pertanyaan.',
                'sort_order' => 1,
                'is_published' => true,
            ],
            [
                'category' => 'Pakej',
                'question' => 'Apakah yang termasuk dalam harga pakej?',
                'answer' => 'Setiap pakej memaparkan senarai perkhidmatan yang termasuk dan tidak termasuk pada halaman butirannya.',
                'sort_order' => 2,
                'is_published' => true,
            ],
            [
                'category' => 'Persediaan',
                'question' => 'Bilakah saya perlu membuat persediaan dokumen?',
                'answer' => 'Sediakan pasport dan dokumen berkaitan sebaik sahaja memilih tarikh perjalanan untuk semakan pihak agensi.',
                'sort_order' => 1,
                'is_published' => true,
            ],
            [
                'category' => 'Tempahan',
                'question' => 'Bagaimanakah status tempahan dikemas kini?',
                'answer' => 'Pasukan agensi akan menyemak butiran anda dan mengemas kini status selepas tindakan susulan dibuat.',
                'sort_order' => 1,
                'is_published' => true,
            ],
        ] as $faq) {
            Faq::query()->updateOrCreate(
                ['question' => $faq['question']],
                $faq,
            );
        }
    }
}

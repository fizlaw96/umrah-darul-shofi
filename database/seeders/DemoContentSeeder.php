<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Faq;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\PackageItinerary;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect([
            ['name' => 'Ekonomi', 'slug' => 'ekonomi', 'description' => 'Pilihan praktikal dengan susunan asas yang jelas.', 'sort_order' => 1],
            ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Lebih keselesaan untuk perjalanan anda.', 'sort_order' => 2],
            ['name' => 'Ramadan', 'slug' => 'ramadan', 'description' => 'Pilihan perjalanan bagi bulan Ramadan.', 'sort_order' => 3],
            ['name' => 'Keluarga', 'slug' => 'keluarga', 'description' => 'Sesuai untuk perjalanan bersama keluarga.', 'sort_order' => 4],
            ['name' => 'Cuti Sekolah', 'slug' => 'cuti-sekolah', 'description' => 'Jadual yang dirancang sekitar musim cuti.', 'sort_order' => 5],
        ])->mapWithKeys(function (array $attributes): array {
            $category = PackageCategory::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [...$attributes, 'is_active' => true],
            );

            return [$attributes['slug'] => $category];
        });

        $packages = [
            ['title' => 'Umrah Ekonomi 9H 7M', 'slug' => 'umrah-ekonomi-9h-7m-demo', 'category' => 'ekonomi', 'price' => 599000, 'days' => 9, 'nights' => 7, 'image' => 'https://images.unsplash.com/photo-1564769625905-50e93615e769?auto=format&fit=crop&w=1200&q=80'],
            ['title' => 'Umrah Premium 9H 7M', 'slug' => 'umrah-premium-9h-7m-demo', 'category' => 'premium', 'price' => 849000, 'days' => 9, 'nights' => 7, 'image' => 'https://images.unsplash.com/photo-1542810634-71277d95dcbb?auto=format&fit=crop&w=1200&q=80'],
            ['title' => 'Umrah Ramadan 12H 10M', 'slug' => 'umrah-ramadan-12h-10m-demo', 'category' => 'ramadan', 'price' => 1199000, 'days' => 12, 'nights' => 10, 'image' => 'https://images.unsplash.com/photo-1513072064285-240f87fa81e8?auto=format&fit=crop&w=1200&q=80'],
            ['title' => 'Umrah Keluarga 9H 7M', 'slug' => 'umrah-keluarga-9h-7m-demo', 'category' => 'keluarga', 'price' => 729000, 'days' => 9, 'nights' => 7, 'image' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=1200&q=80'],
            ['title' => 'Umrah Cuti Sekolah 9H 7M', 'slug' => 'umrah-cuti-sekolah-9h-7m-demo', 'category' => 'cuti-sekolah', 'price' => 759000, 'days' => 9, 'nights' => 7, 'image' => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=1200&q=80'],
        ];

        foreach ($packages as $index => $attributes) {
            $package = Package::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [
                    'package_category_id' => $categories[$attributes['category']]->id,
                    'title' => $attributes['title'],
                    'short_description' => 'Rekod demonstrasi untuk membantu anda meneroka struktur pakej. Sila hubungi agensi bagi butiran sebenar.',
                    'description' => 'Ini ialah kandungan demonstrasi pembangunan. Butiran sebenar perjalanan, penginapan dan penerbangan akan disahkan oleh pihak agensi melalui saluran pertanyaan.',
                    'cover_image' => $attributes['image'],
                    'starting_price_sen' => $attributes['price'],
                    'duration_days' => $attributes['days'],
                    'duration_nights' => $attributes['nights'],
                    'makkah_nights' => 4,
                    'madinah_nights' => 3,
                    'hotel_information' => 'Maklumat hotel demonstrasi. Penginapan sebenar tertakluk kepada pengesahan agensi.',
                    'flight_information' => 'Maklumat penerbangan akan disahkan oleh pihak agensi.',
                    'inclusions' => ['Penginapan mengikut pakej', 'Pengangkutan asas di destinasi', 'Bimbingan perjalanan'],
                    'exclusions' => ['Perbelanjaan peribadi', 'Perkhidmatan yang tidak dinyatakan'],
                    'is_featured' => true,
                    'status' => 'published',
                    'published_at' => now()->subDay(),
                ],
            );

            $departureDate = now()->addMonths($index + 1)->startOfMonth()->addDays(9);

            PackageDeparture::query()->updateOrCreate(
                ['package_id' => $package->id, 'departure_date' => $departureDate->toDateString()],
                [
                    'return_date' => $departureDate->copy()->addDays(8)->toDateString(),
                    'departure_airport' => 'KLIA',
                    'price_sen' => $attributes['price'],
                    'capacity' => 40,
                    'status' => 'open',
                ],
            );

            PackageItinerary::query()->updateOrCreate(
                ['package_id' => $package->id, 'day_number' => 1],
                ['title' => 'Berlepas', 'description' => 'Daftar dan berlepas mengikut arahan pasukan agensi.'],
            );
        }

        foreach ([
            ['category' => 'Pertanyaan', 'question' => 'Bagaimanakah saya bertanya tentang pakej?', 'answer' => 'Pilih pakej yang diminati dan gunakan WhatsApp atau borang pertanyaan untuk berkongsi keperluan anda dengan agensi.', 'sort_order' => 1],
            ['category' => 'Pakej', 'question' => 'Adakah harga yang dipaparkan muktamad?', 'answer' => 'Harga demonstrasi dan maklumat pakej perlu disahkan oleh agensi melalui saluran pertanyaan.', 'sort_order' => 2],
        ] as $faq) {
            Faq::query()->updateOrCreate(['question' => $faq['question']], [...$faq, 'is_published' => true]);
        }

        Article::query()->updateOrCreate(
            ['slug' => 'panduan-ringkas-persediaan-umrah-demo'],
            [
                'title' => 'Panduan Ringkas Persediaan Umrah',
                'category' => 'Persediaan',
                'excerpt' => 'Kandungan demonstrasi untuk ruang panduan jemaah.',
                'body' => 'Artikel demonstrasi. Agensi boleh menggantikan kandungan ini dengan panduan rasmi yang telah disemak.',
                'status' => 'published',
                'published_at' => now()->subDay(),
            ],
        );

        WebsiteSetting::setValue('company_name', 'Darul Shofi');
        WebsiteSetting::setValue('company_tagline', 'Perjalanan ibadah, diurus dengan tenang.');
    }
}

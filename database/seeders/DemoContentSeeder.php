<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Booking;
use App\Models\BookingStatusHistory;
use App\Models\Enquiry;
use App\Models\Faq;
use App\Models\Mutawif;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PackageDeparture;
use App\Models\PackageImage;
use App\Models\PackageItinerary;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteSetting;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoMediaSeeder::class);

        $categories = $this->seedCategories();
        $packages = $this->seedPackages($categories);

        $this->seedFaqs();
        $this->seedArticles();
        $this->seedTestimonials($packages);
        $this->seedMutawifs();
        $this->seedEnquiries($packages);
        $this->seedBookings($packages);
        $this->seedWebsiteSettings();
    }

    /**
     * @return array<string, PackageCategory>
     */
    private function seedCategories(): array
    {
        $categories = [];

        foreach ([
            ['name' => 'Pakej Umrah', 'slug' => 'pakej-umrah', 'description' => 'Pilihan perjalanan Umrah dengan susunan yang jelas.', 'sort_order' => 1],
            ['name' => 'Pakej Haji', 'slug' => 'pakej-haji', 'description' => 'Pilihan perjalanan Haji dengan bimbingan menyeluruh.', 'sort_order' => 2],
            ['name' => 'Pakej Badal Haji', 'slug' => 'pakej-badal-haji', 'description' => 'Pilihan perkhidmatan Badal Haji yang diurus dengan amanah.', 'sort_order' => 3],
            ['name' => 'Pakej Pelancongan', 'slug' => 'pakej-pelancongan', 'description' => 'Pilihan pelancongan Muslim ke destinasi terpilih.', 'sort_order' => 4],
        ] as $attributes) {
            $categories[$attributes['slug']] = PackageCategory::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [...$attributes, 'is_active' => true, 'show_on_homepage' => true],
            );
        }

        return $categories;
    }

    /**
     * @param  array<string, PackageCategory>  $categories
     * @return array<string, Package>
     */
    private function seedPackages(array $categories): array
    {
        $packages = [];

        foreach ($this->packageData() as $index => $attributes) {
            $firstDepartureDate = now('Asia/Kuala_Lumpur')
                ->startOfDay()
                ->addMonths($index + 2)
                ->next('Monday');
            $content = $this->completePackageContent($attributes, $firstDepartureDate, $index);

            $package = Package::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [
                    'package_category_id' => $categories[$attributes['category']]->id,
                    'title' => $attributes['title'],
                    'code' => $attributes['code'],
                    'short_description' => $attributes['short_description'],
                    'description' => $attributes['description'],
                    'cover_image' => $content['cover_image'],
                    'starting_price_sen' => $attributes['price_sen'],
                    'room_prices' => $content['room_prices'],
                    'duration_days' => $attributes['duration_days'],
                    'duration_nights' => $attributes['duration_nights'],
                    'season_label' => $attributes['season_label'],
                    'departure_airport' => $content['departure_airport'],
                    'arrival_airport' => $content['arrival_airport'],
                    'airline' => $attributes['airline'],
                    'proposed_departure_date' => $firstDepartureDate->toDateString(),
                    'makkah_nights' => $attributes['makkah_nights'],
                    'madinah_nights' => $attributes['madinah_nights'],
                    'makkah_hotel' => $content['hotel_options'][0]['name'],
                    'makkah_hotel_rating' => $content['hotel_options'][0]['rating'],
                    'madinah_hotel' => $content['hotel_options'][2]['name'],
                    'madinah_hotel_rating' => $content['hotel_options'][2]['rating'],
                    'walking_distance' => $attributes['walking_distance'],
                    'hotel_information' => $content['hotel_information'],
                    'flight_information' => $content['flight_information'],
                    'flight_options' => $content['flight_options'],
                    'hotel_options' => $content['hotel_options'],
                    'ziarah_places' => $content['ziarah_places'],
                    'gifts' => $content['gifts'],
                    'highlights' => $content['highlights'],
                    'inclusions' => $content['inclusions'],
                    'exclusions' => $content['exclusions'],
                    'service_options' => $content['service_options'],
                    'is_featured' => $attributes['is_featured'],
                    'show_on_homepage' => $attributes['show_on_homepage'],
                    'display_order' => $index + 1,
                    'status' => $attributes['status'],
                    'published_at' => $attributes['status'] === 'published' ? now()->subDays($index + 1) : null,
                ],
            );

            $this->seedPackageImages($package, $attributes['gallery']);
            $this->seedPackageItinerary($package, $attributes['duration_days'], $content['itinerary']);

            $departures = $this->seedDepartures($package, $firstDepartureDate, $attributes);

            $packages[$attributes['slug']] = $package->setRelation('departures', $departures);
        }

        return $packages;
    }

    /**
     * @param  array<int, string>  $galleryPaths
     */
    private function seedPackageImages(Package $package, array $galleryPaths): void
    {
        foreach ($galleryPaths as $index => $path) {
            PackageImage::query()->updateOrCreate(
                ['package_id' => $package->id, 'path' => $path],
                [
                    'alt_text' => "Galeri {$package->title}",
                    'sort_order' => $index + 1,
                ],
            );
        }
    }

    /**
     * @param  array<int, array{title: string, description: string}>  $itinerary
     */
    private function seedPackageItinerary(Package $package, int $durationDays, array $itinerary): void
    {
        foreach (array_slice($itinerary, 0, $durationDays) as $index => $itineraryDay) {
            PackageItinerary::query()->updateOrCreate(
                ['package_id' => $package->id, 'day_number' => $index + 1],
                $itineraryDay,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, PackageDeparture>
     */
    private function seedDepartures(Package $package, CarbonInterface $firstDepartureDate, array $attributes): array
    {
        $departures = [
            'primary' => $this->updateDeparture(
                $package,
                $firstDepartureDate,
                $attributes['duration_days'],
                $attributes['price_sen'],
                $attributes['departure_status'],
                40,
            ),
        ];

        if ($attributes['status'] === 'published') {
            $departures['upcoming'] = $this->updateDeparture(
                $package,
                $firstDepartureDate->copy()->addMonths(2),
                $attributes['duration_days'],
                $attributes['price_sen'] + 25_000,
                'open',
                40,
            );
        }

        if ($attributes['slug'] === 'pakej-umrah-premium-9h-7m-demo') {
            $departures['full'] = $this->updateDeparture(
                $package,
                $firstDepartureDate->copy()->addMonth(),
                $attributes['duration_days'],
                $attributes['price_sen'],
                'full',
                40,
            );
        }

        if ($attributes['slug'] === 'pakej-umrah-ekonomi-9h-7m-demo') {
            $departures['completed'] = $this->updateDeparture(
                $package,
                now('Asia/Kuala_Lumpur')->startOfDay()->subMonths(2)->addDays(10),
                $attributes['duration_days'],
                $attributes['price_sen'],
                'completed',
                40,
            );
        }

        return $departures;
    }

    private function updateDeparture(
        Package $package,
        CarbonInterface $departureDate,
        int $durationDays,
        int $priceSen,
        string $status,
        int $capacity,
    ): PackageDeparture {
        $values = [
            'return_date' => $departureDate->copy()->addDays($durationDays - 1)->toDateString(),
            'departure_airport' => 'KLIA Terminal 1',
            'price_sen' => $priceSen,
            'capacity' => $capacity,
            'status' => $status,
        ];

        $departure = $package->departures()
            ->whereDate('departure_date', $departureDate->toDateString())
            ->first();

        if ($departure === null) {
            return $package->departures()->create([
                ...$values,
                'departure_date' => $departureDate->toDateString(),
            ]);
        }

        $departure->update($values);

        return $departure;
    }

    private function seedFaqs(): void
    {
        foreach ([
            ['category' => 'Pertanyaan', 'question' => 'Bagaimanakah saya bertanya tentang pakej?', 'answer' => 'Pilih pakej yang diminati dan gunakan WhatsApp atau borang pertanyaan untuk berkongsi keperluan anda dengan agensi.', 'sort_order' => 1, 'is_published' => true],
            ['category' => 'Pakej', 'question' => 'Adakah harga yang dipaparkan muktamad?', 'answer' => 'Harga demonstrasi dan maklumat pakej perlu disahkan oleh agensi melalui saluran pertanyaan.', 'sort_order' => 1, 'is_published' => true],
            ['category' => 'Pakej', 'question' => 'Apakah yang termasuk dalam harga pakej?', 'answer' => 'Setiap pakej memaparkan senarai perkhidmatan yang termasuk dan tidak termasuk pada halaman butirannya.', 'sort_order' => 2, 'is_published' => true],
            ['category' => 'Persediaan', 'question' => 'Bilakah saya perlu membuat persediaan dokumen?', 'answer' => 'Sediakan pasport dan dokumen berkaitan sebaik sahaja memilih tarikh perjalanan untuk semakan pihak agensi.', 'sort_order' => 1, 'is_published' => true],
            ['category' => 'Persediaan', 'question' => 'Bolehkah saya membawa kanak-kanak?', 'answer' => 'Ya, pilih pakej keluarga dan maklumkan umur setiap kanak-kanak kepada pasukan agensi semasa pertanyaan.', 'sort_order' => 2, 'is_published' => true],
            ['category' => 'Tempahan', 'question' => 'Bagaimanakah status tempahan dikemas kini?', 'answer' => 'Pasukan agensi akan menyemak butiran anda dan mengemas kini status selepas tindakan susulan dibuat.', 'sort_order' => 1, 'is_published' => true],
            ['category' => 'Tempahan', 'question' => 'Bolehkah saya menukar jadual berlepas?', 'answer' => 'Perubahan tertakluk kepada kekosongan dan syarat semasa. Hubungi pasukan agensi untuk bantuan.', 'sort_order' => 2, 'is_published' => false],
        ] as $faq) {
            Faq::query()->updateOrCreate(['question' => $faq['question']], $faq);
        }
    }

    private function seedArticles(): void
    {
        foreach ([
            ['slug' => 'panduan-ringkas-persediaan-umrah-demo', 'title' => 'Panduan Ringkas Persediaan Umrah', 'category' => 'Persediaan', 'excerpt' => 'Senarai awal perkara penting sebelum anda melangkah ke Tanah Suci.', 'body' => 'Artikel demonstrasi untuk memudahkan semakan aliran pengurusan artikel. Kandungan rasmi hendaklah disemak oleh pihak agensi sebelum diterbitkan.', 'cover_image' => 'demo/articles/persiapan-umrah.jpg', 'status' => 'published', 'published_at' => now()->subDays(3)],
            ['slug' => 'menjaga-ketenangan-sepanjang-ibadah-demo', 'title' => 'Menjaga Ketenangan Sepanjang Ibadah', 'category' => 'Panduan Ibadah', 'excerpt' => 'Beberapa peringatan ringkas untuk membantu jemaah menumpukan perhatian kepada ibadah.', 'body' => 'Artikel demonstrasi. Bawa ubat peribadi, ikuti masa berkumpul dan sentiasa dapatkan panduan mutawif apabila memerlukan bantuan.', 'cover_image' => 'demo/articles/ibadah-tenang.png', 'status' => 'published', 'published_at' => now()->subDays(2)],
            ['slug' => 'senarai-barang-keperluan-demo', 'title' => 'Senarai Barang Keperluan Jemaah', 'category' => 'Persediaan', 'excerpt' => 'Draf contoh untuk menyemak paparan status artikel dalam pentadbir.', 'body' => 'Kandungan draf demonstrasi untuk semakan dalaman.', 'cover_image' => 'demo/articles/persiapan-umrah.jpg', 'status' => 'draft', 'published_at' => null],
        ] as $article) {
            Article::query()->updateOrCreate(['slug' => $article['slug']], $article);
        }
    }

    /**
     * @param  array<string, Package>  $packages
     */
    private function seedTestimonials(array $packages): void
    {
        foreach ([
            ['reviewer_name' => 'Aminah Hassan', 'package' => 'pakej-umrah-ekonomi-9h-7m-demo', 'rating' => 5, 'review' => 'Susunan perjalanan jelas dan mutawif sentiasa membantu apabila kami perlukan panduan.', 'photo' => 'demo/people/jemaah-1.jpg', 'is_published' => true, 'sort_order' => 1],
            ['reviewer_name' => 'Muhammad Firdaus', 'package' => 'pakej-umrah-premium-9h-7m-demo', 'rating' => 5, 'review' => 'Penginapan dan pergerakan jemaah diurus dengan sangat baik. Kami dapat fokus pada ibadah.', 'photo' => 'demo/people/jemaah-2.jpg', 'is_published' => true, 'sort_order' => 2],
            ['reviewer_name' => 'Norazlina Omar', 'package' => 'pakej-haji-9h-7m-demo', 'rating' => 4, 'review' => 'Pilihan yang selesa untuk kami sekeluarga. Penerangan sebelum berlepas juga mudah difahami.', 'photo' => 'demo/people/jemaah-3.jpg', 'is_published' => true, 'sort_order' => 3],
            ['reviewer_name' => 'Hafiz Rahman', 'package' => 'pakej-pelancongan-muslim-12h-10m-demo', 'rating' => 5, 'review' => 'Catatan demo untuk menyemak testimoni yang masih belum diterbitkan.', 'photo' => null, 'is_published' => false, 'sort_order' => 4],
        ] as $testimonial) {
            Testimonial::query()->updateOrCreate(
                ['reviewer_name' => $testimonial['reviewer_name']],
                [
                    'package_id' => $packages[$testimonial['package']]->id,
                    'rating' => $testimonial['rating'],
                    'review' => $testimonial['review'],
                    'photo' => $testimonial['photo'],
                    'is_published' => $testimonial['is_published'],
                    'sort_order' => $testimonial['sort_order'],
                ],
            );
        }
    }

    private function seedMutawifs(): void
    {
        foreach ([
            ['full_name' => 'Ustaz Ahmad Fikri', 'photo' => 'demo/people/mutawif-1.jpg', 'is_published' => true, 'sort_order' => 1],
            ['full_name' => 'Ustazah Nur Aisyah', 'photo' => 'demo/people/mutawif-2.jpg', 'is_published' => true, 'sort_order' => 2],
            ['full_name' => 'Ustaz Muhammad Haziq', 'photo' => 'demo/people/mutawif-3.jpg', 'is_published' => true, 'sort_order' => 3],
            ['full_name' => 'Ustazah Siti Mariam', 'photo' => 'demo/people/mutawif-4.jpg', 'is_published' => false, 'sort_order' => 4],
        ] as $mutawif) {
            Mutawif::query()->updateOrCreate(['full_name' => $mutawif['full_name']], $mutawif);
        }
    }

    /**
     * @param  array<string, Package>  $packages
     */
    private function seedEnquiries(array $packages): void
    {
        foreach ([
            ['name' => 'Siti Hajar', 'phone' => '011-2000 1001', 'email' => 'siti.hajar@example.test', 'package' => 'pakej-umrah-ekonomi-9h-7m-demo', 'message' => 'Saya ingin mengetahui tarikh yang sesuai untuk dua orang dewasa.', 'status' => 'new', 'internal_notes' => null],
            ['name' => 'Azman Ismail', 'phone' => '011-2000 1002', 'email' => 'azman.ismail@example.test', 'package' => 'pakej-umrah-premium-9h-7m-demo', 'message' => 'Boleh kongsikan perbezaan bilik double dan triple?', 'status' => 'contacted', 'internal_notes' => 'Sudah dihubungi untuk menerangkan pilihan bilik.'],
            ['name' => 'Farah Nabilah', 'phone' => '011-2000 1003', 'email' => 'farah.nabilah@example.test', 'package' => null, 'message' => 'Saya mahu mendapatkan penerangan mengenai proses Umrah pertama kali.', 'status' => 'closed', 'internal_notes' => 'Maklumat asas telah diberikan melalui WhatsApp.'],
        ] as $enquiry) {
            Enquiry::query()->updateOrCreate(
                ['email' => $enquiry['email']],
                [
                    'package_id' => $enquiry['package'] === null ? null : $packages[$enquiry['package']]->id,
                    'name' => $enquiry['name'],
                    'phone' => $enquiry['phone'],
                    'message' => $enquiry['message'],
                    'status' => $enquiry['status'],
                    'internal_notes' => $enquiry['internal_notes'],
                    'privacy_consented_at' => now()->subDays(2),
                ],
            );
        }
    }

    /**
     * @param  array<string, Package>  $packages
     */
    private function seedBookings(array $packages): void
    {
        $administratorId = User::query()->where('email', config('admin_seeder.email'))->value('id');
        $economyDepartures = $packages['pakej-umrah-ekonomi-9h-7m-demo']->getRelation('departures');
        $premiumDepartures = $packages['pakej-umrah-premium-9h-7m-demo']->getRelation('departures');

        $this->seedBooking($packages['pakej-umrah-ekonomi-9h-7m-demo'], $economyDepartures['primary'], [
            'reference' => 'JUM-DEMO-0001', 'idempotency_key' => '00000000-0000-4000-8000-000000000001', 'customer_name' => 'Aisyah Abdullah', 'phone' => '011-3000 1001', 'email' => 'aisyah.abdullah@example.test', 'adults' => 2, 'children' => 0, 'status' => 'confirmed', 'notes' => 'Memerlukan bilik double.',
            'history' => [
                ['previous_status' => null, 'status' => 'pending', 'remarks' => 'Permohonan diterima.'],
                ['previous_status' => 'pending', 'status' => 'reviewing', 'remarks' => 'Dokumen sedang disemak.'],
                ['previous_status' => 'reviewing', 'status' => 'awaiting_payment', 'remarks' => 'Menunggu bayaran deposit.'],
                ['previous_status' => 'awaiting_payment', 'status' => 'confirmed', 'remarks' => 'Bayaran deposit disahkan.'],
            ],
        ], $administratorId);

        $this->seedBooking($packages['pakej-umrah-premium-9h-7m-demo'], $premiumDepartures['primary'], [
            'reference' => 'JUM-DEMO-0002', 'idempotency_key' => '00000000-0000-4000-8000-000000000002', 'customer_name' => 'Nurul Iman', 'phone' => '011-3000 1002', 'email' => 'nurul.iman@example.test', 'adults' => 1, 'children' => 1, 'status' => 'awaiting_payment', 'notes' => 'Menunggu pengesahan bayaran.',
            'history' => [
                ['previous_status' => null, 'status' => 'pending', 'remarks' => 'Permohonan diterima.'],
                ['previous_status' => 'pending', 'status' => 'reviewing', 'remarks' => 'Pakej dan tarikh telah dipilih.'],
                ['previous_status' => 'reviewing', 'status' => 'awaiting_payment', 'remarks' => 'Invois deposit dihantar.'],
            ],
        ], $administratorId);

        $this->seedBooking($packages['pakej-umrah-ekonomi-9h-7m-demo'], $economyDepartures['upcoming'], [
            'reference' => 'JUM-DEMO-0003', 'idempotency_key' => '00000000-0000-4000-8000-000000000003', 'customer_name' => 'Haziq Rahim', 'phone' => '011-3000 1003', 'email' => 'haziq.rahim@example.test', 'adults' => 1, 'children' => 0, 'status' => 'pending', 'notes' => null,
            'history' => [
                ['previous_status' => null, 'status' => 'pending', 'remarks' => 'Permohonan baharu untuk tindakan pasukan.'],
            ],
        ], $administratorId);

        $this->seedBooking($packages['pakej-umrah-ekonomi-9h-7m-demo'], $economyDepartures['completed'], [
            'reference' => 'JUM-DEMO-0004', 'idempotency_key' => '00000000-0000-4000-8000-000000000004', 'customer_name' => 'Khadijah Musa', 'phone' => '011-3000 1004', 'email' => 'khadijah.musa@example.test', 'adults' => 2, 'children' => 1, 'status' => 'completed', 'notes' => 'Rekod perjalanan yang telah selesai.',
            'history' => [
                ['previous_status' => null, 'status' => 'pending', 'remarks' => 'Permohonan diterima.'],
                ['previous_status' => 'pending', 'status' => 'confirmed', 'remarks' => 'Tempahan disahkan.'],
                ['previous_status' => 'confirmed', 'status' => 'completed', 'remarks' => 'Perjalanan telah selesai.'],
            ],
        ], $administratorId);
    }

    /**
     * @param  array{reference: string, idempotency_key: string, customer_name: string, phone: string, email: string, adults: int, children: int, status: string, notes: ?string, history: array<int, array{previous_status: ?string, status: string, remarks: string}>}  $attributes
     */
    private function seedBooking(Package $package, PackageDeparture $departure, array $attributes, ?int $administratorId): void
    {
        $travellersCount = $attributes['adults'] + $attributes['children'];
        $booking = Booking::query()->updateOrCreate(
            ['reference' => $attributes['reference']],
            [
                'package_id' => $package->id,
                'package_departure_id' => $departure->id,
                'idempotency_key' => $attributes['idempotency_key'],
                'customer_name' => $attributes['customer_name'],
                'phone' => $attributes['phone'],
                'email' => $attributes['email'],
                'adults' => $attributes['adults'],
                'children' => $attributes['children'],
                'travellers_count' => $travellersCount,
                'departure_date' => $departure->departure_date,
                'package_title' => $package->title,
                'price_per_traveller_sen' => $departure->price_sen,
                'total_quoted_sen' => $departure->price_sen * $travellersCount,
                'status' => $attributes['status'],
                'notes' => $attributes['notes'],
                'privacy_consented_at' => now()->subDays(4),
            ],
        );

        foreach ($attributes['history'] as $history) {
            BookingStatusHistory::query()->updateOrCreate(
                ['booking_id' => $booking->id, 'previous_status' => $history['previous_status'], 'status' => $history['status']],
                ['user_id' => $administratorId, 'remarks' => $history['remarks']],
            );
        }
    }

    private function seedWebsiteSettings(): void
    {
        foreach ([
            'company_name' => ['value' => 'SalamHaji Travel & Tours', 'group' => 'general'],
            'company_legal_name' => ['value' => 'SALAM HAJI TRAVEL & TOURS SDN. BHD.', 'group' => 'general'],
            'company_tagline' => ['value' => 'Salam Haji Umrahku & Hajimu Yang Teristimewa', 'group' => 'general'],
            'company_logo_path' => ['value' => 'demo/branding/company-logo.png', 'group' => 'general'],
            'footer_logo_path' => ['value' => 'demo/branding/darul-soffi-logo.jpg', 'group' => 'general'],
            'contact_phone' => ['value' => '+60 11-2000 0000', 'group' => 'contact'],
            'contact_email' => ['value' => 'salam@example.test', 'group' => 'contact'],
            'contact_whatsapp' => ['value' => '60133212212', 'group' => 'contact'],
            'office_address' => ['value' => "1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan, Selangor.", 'group' => 'contact'],
            'office_hours' => ['value' => 'Isnin–Jumaat, 9.00 pagi–5.30 petang', 'group' => 'contact'],
            'registered_address' => ['value' => "1-79, Jalan PUJ 3/9,\nTaman Puncak Jalil,\n43300 Seri Kembangan, Selangor.", 'group' => 'contact'],
            'facebook_url' => ['value' => 'https://www.facebook.com/', 'group' => 'social'],
            'instagram_url' => ['value' => 'https://www.instagram.com/', 'group' => 'social'],
            'tiktok_url' => ['value' => 'https://www.tiktok.com/', 'group' => 'social'],
            'threads_url' => ['value' => 'https://www.threads.com/', 'group' => 'social'],
            'homepage_company_intro' => ['value' => 'Pakej Umrah tersusun dengan panduan mesra untuk membantu jemaah memberi tumpuan kepada ibadah.', 'group' => 'homepage'],
            'homepage_package_heading' => ['value' => 'Pilihan Pakej SalamHaji', 'group' => 'homepage'],
            'about_eyebrow' => ['value' => 'Bersama Menuju Baitullah', 'group' => 'homepage'],
            'about_heading' => ['value' => 'Perjalanan suci, amanah kami', 'group' => 'homepage'],
            'about_description' => ['value' => "SalamHaji Travel & Tours membantu jemaah merancang perjalanan Umrah dengan susunan yang jelas, bimbingan yang mudah diikuti dan layanan yang penuh hormat.\n\nDaripada persediaan awal hingga kepulangan ke tanah air, kami mahu setiap langkah ibadah terasa lebih tenang supaya jemaah dapat memberi tumpuan kepada perkara yang paling utama.", 'group' => 'homepage'],
            'about_cta_label' => ['value' => 'Kenali Kami Lebih Dekat', 'group' => 'homepage'],
            'featured_video_title' => ['value' => 'Persediaan Umrah Bersama SalamHaji', 'group' => 'social'],
            'terms_content' => ['value' => "Kandungan terma ini ialah contoh untuk persekitaran pembangunan.\n\nSila gantikan dengan terma dan syarat rasmi sebelum laman diterbitkan.", 'group' => 'legal'],
        ] as $key => $setting) {
            WebsiteSetting::setValue($key, $setting['value'], $setting['group']);
        }
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function completePackageContent(array $attributes, CarbonInterface $firstDepartureDate, int $index): array
    {
        $coverImage = $this->coverImagePath((string) $attributes['slug']);
        $hotelKeys = array_map(
            fn (int $offset): string => sprintf('10000000-0000-4000-8000-%012d', ($index * 10) + $offset),
            [1, 2, 3],
        );
        $hotelNames = $this->hotelNames((string) $attributes['slug'], $attributes);
        $flightRoute = $this->flightRoute((string) $attributes['slug']);
        $inclusions = $this->inclusionsFor((string) $attributes['slug']);
        $exclusions = ['Caj lebihan bagasi', 'Perbelanjaan peribadi', 'Perubahan jadual atas permintaan jemaah'];

        return [
            'cover_image' => $coverImage,
            'departure_airport' => 'KLIA Terminal 1',
            'arrival_airport' => $flightRoute['arrival_airport'],
            'hotel_information' => 'Nama hotel, gambar dan pilihan bilik ini ialah data demonstrasi untuk semakan paparan. Penginapan sebenar tertakluk kepada pengesahan agensi.',
            'flight_information' => 'Jadual penerbangan demonstrasi; masa dan nombor penerbangan sebenar perlu disahkan sebelum berlepas.',
            'flight_options' => [
                [
                    'airline' => $attributes['airline'],
                    'departure_airport' => 'KLIA Terminal 1',
                    'arrival_airport' => $flightRoute['arrival_airport'],
                    'proposed_departure_date' => $firstDepartureDate->toDateString(),
                    'notes' => $flightRoute['outbound_note'],
                    'images' => [$this->airlineImagePath((string) $attributes['airline'])],
                ],
                [
                    'airline' => $attributes['airline'],
                    'departure_airport' => $flightRoute['return_departure_airport'],
                    'arrival_airport' => 'KLIA Terminal 1',
                    'proposed_departure_date' => $firstDepartureDate->copy()->addDays((int) $attributes['duration_days'] - 1)->toDateString(),
                    'notes' => 'Pilihan penerbangan pulang untuk semakan susun atur maklumat perjalanan.',
                    'images' => [$this->airlineImagePath((string) $attributes['airline'])],
                ],
            ],
            'hotel_options' => [
                [
                    'key' => $hotelKeys[0],
                    'location' => 'Makkah',
                    'name' => $hotelNames[0],
                    'rating' => $attributes['hotel_rating'],
                    'nights' => $attributes['makkah_nights'],
                    'walking_distance' => $attributes['walking_distance'],
                    'notes' => 'Pilihan utama demonstrasi dengan gambar hotel dan bilik untuk carousel.',
                    'images' => ['demo/travel/hotels/makkah-arrival.jpg', 'demo/content/room-prices/double.jpg'],
                ],
                [
                    'key' => $hotelKeys[1],
                    'location' => 'Makkah',
                    'name' => $hotelNames[1],
                    'rating' => min(5, (int) $attributes['hotel_rating'] + 1),
                    'nights' => $attributes['makkah_nights'],
                    'walking_distance' => 'Pengangkutan ulang-alik disusun mengikut jadual kumpulan.',
                    'notes' => 'Pilihan alternatif demonstrasi untuk membandingkan hotel dan harga bilik.',
                    'images' => ['demo/travel/hotels/saudi-lobby.jpg', 'demo/content/room-prices/triple.jpg'],
                ],
                [
                    'key' => $hotelKeys[2],
                    'location' => 'Madinah',
                    'name' => $hotelNames[2],
                    'rating' => $attributes['hotel_rating'],
                    'nights' => $attributes['madinah_nights'],
                    'walking_distance' => 'Anggaran 8 minit berjalan kaki ke Masjid Nabawi.',
                    'notes' => 'Pilihan Madinah demonstrasi dengan gambar hotel dan bilik untuk semakan.',
                    'images' => ['demo/travel/hotels/madinah-arrival.jpg', 'demo/content/room-prices/quad.jpg'],
                ],
            ],
            'room_prices' => $this->roomPrices((int) $attributes['price_sen'], $hotelKeys),
            'ziarah_places' => $this->ziarahPlacesFor((string) $attributes['slug'], [...$attributes, 'cover_image' => $coverImage]),
            'gifts' => [
                ['title' => 'Beg sandang jemaah', 'description' => 'Satu unit untuk setiap jemaah.', 'image' => 'demo/gallery/menuju-kaabah.jpg'],
                ['title' => 'Set ibadah perjalanan', 'description' => 'Mengandungi tag bagasi dan buku panduan ringkas.', 'image' => 'demo/gallery/tilawah.jpg'],
                ['title' => 'Cenderamata perjalanan', 'description' => 'Diberikan semasa sesi taklimat sebelum pulang.', 'image' => 'demo/gallery/doa-di-kaabah.jpg'],
            ],
            'highlights' => [
                'Pilihan hotel dan jenis bilik untuk perbandingan',
                'Dua pilihan penerbangan pergi dan pulang',
                'Gambar penginapan, bilik, ziarah dan hadiah',
            ],
            'inclusions' => $inclusions,
            'exclusions' => $exclusions,
            'service_options' => [...$inclusions, ...$exclusions],
            'itinerary' => $this->itineraryFor((string) $attributes['slug']),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, string>
     */
    private function hotelNames(string $slug, array $attributes): array
    {
        return match ($slug) {
            'pakej-umrah-ekonomi-9h-7m-demo' => ['Al Kiswah Towers Hotel', 'Emaar Grand Hotel Makkah', 'Saja Al Madinah Hotel'],
            'pakej-haji-9h-7m-demo' => ['M Hotel Makkah by Millennium', 'Makarem Ajyad Makkah Hotel', 'Emaar Elite Al Madina Hotel'],
            'pakej-badal-haji-demo' => ['Elaf Ajyad Hotel Makkah', 'Jabal Omar Marriott Hotel Makkah', 'Al Eiman Royal Hotel Madinah'],
            'pakej-pelancongan-muslim-12h-10m-demo' => ['Voco Makkah Hotel', 'Jabal Omar Hyatt Regency Makkah', 'Anwar Al Madinah Mövenpick'],
            default => [
                (string) $attributes['makkah_hotel'],
                'Pilihan hotel setaraf di Makkah',
                (string) $attributes['madinah_hotel'],
            ],
        };
    }

    private function coverImagePath(string $slug): string
    {
        return match ($slug) {
            'pakej-haji-9h-7m-demo' => 'demo/packages/haji-poster.jpg',
            'pakej-badal-haji-demo' => 'demo/packages/badal-haji-poster.jpg',
            'pakej-pelancongan-muslim-12h-10m-demo',
            'pakej-pelancongan-turkiye-9h-7m-draf-demo',
            'pakej-pelancongan-andalusia-10h-8m-arkib-demo' => 'demo/packages/pelancongan-poster.jpg',
            default => 'demo/packages/umrah-poster.jpg',
        };
    }

    /**
     * @return array{arrival_airport: string, return_departure_airport: string, outbound_note: string}
     */
    private function flightRoute(string $slug): array
    {
        if ($slug === 'pakej-pelancongan-muslim-12h-10m-demo') {
            return [
                'arrival_airport' => 'Istanbul Airport',
                'return_departure_airport' => 'Istanbul Airport',
                'outbound_note' => 'Penerbangan demonstrasi ke Istanbul; sambungan perjalanan mengikut itinerari.',
            ];
        }

        return [
            'arrival_airport' => 'Jeddah',
            'return_departure_airport' => 'Jeddah',
            'outbound_note' => 'Masa penerbangan akan dimaklumkan selepas pengesahan akhir kumpulan.',
        ];
    }

    private function airlineImagePath(string $airline): string
    {
        return match ($airline) {
            'Saudia' => 'demo/airlines/saudia.svg',
            'Qatar Airways' => 'demo/airlines/qatar-airways.svg',
            'Malaysia Airlines' => 'demo/airlines/malaysia-airlines.svg',
            'AirAsia X' => 'demo/airlines/airasia-x.svg',
            'Turkish Airlines' => 'demo/airlines/turkish-airlines.svg',
            default => 'demo/airlines/emirates.svg',
        };
    }

    /**
     * @return array<int, string>
     */
    private function inclusionsFor(string $slug): array
    {
        $inclusions = [
            'Tiket penerbangan pergi dan balik',
            'Bagasi daftar masuk',
            'Penginapan mengikut pilihan hotel',
            'Pengangkutan kumpulan berhawa dingin',
            'Bimbingan mutawif atau tour leader',
            'Set hadiah jemaah',
            'Insurans perjalanan',
        ];

        return match ($slug) {
            'pakej-haji-9h-7m-demo' => [...$inclusions, 'Visa Haji dan taklimat manasik'],
            'pakej-badal-haji-demo' => [...$inclusions, 'Dokumentasi pelaksanaan Badal Haji'],
            'pakej-pelancongan-muslim-12h-10m-demo' => [...$inclusions, 'Lawatan bandar dan tiket tarikan terpilih'],
            default => [...$inclusions, 'Visa Umrah'],
        };
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<int, array{name: string, location: string, description: string, image: string}>
     */
    private function ziarahPlacesFor(string $slug, array $attributes): array
    {
        $coverImage = (string) $attributes['cover_image'];

        return match ($slug) {
            'pakej-haji-9h-7m-demo' => [
                ['name' => 'Mina', 'location' => 'Makkah', 'description' => 'Semakan aliran aktiviti dan penginapan jemaah Haji.', 'image' => $coverImage],
                ['name' => 'Padang Arafah', 'location' => 'Arafah', 'description' => 'Maklumat ziarah dan bimbingan hari wukuf.', 'image' => 'demo/gallery/menuju-kaabah.jpg'],
                ['name' => 'Jabal Uhud', 'location' => 'Madinah', 'description' => 'Ziarah sejarah mengikut kesesuaian atur cara.', 'image' => 'demo/gallery/doa-di-kaabah.jpg'],
            ],
            'pakej-badal-haji-demo' => [
                ['name' => 'Masjidil Haram', 'location' => 'Makkah', 'description' => 'Lokasi utama pelaksanaan ibadah oleh wakil yang dilantik.', 'image' => $coverImage],
                ['name' => 'Jabal Rahmah', 'location' => 'Arafah', 'description' => 'Maklumat lokasi disediakan untuk semakan pakej.', 'image' => 'demo/gallery/menuju-kaabah.jpg'],
                ['name' => 'Masjid Quba', 'location' => 'Madinah', 'description' => 'Ziarah tambahan mengikut pengesahan jadual.', 'image' => 'demo/gallery/tilawah.jpg'],
            ],
            'pakej-pelancongan-muslim-12h-10m-demo' => [
                ['name' => 'Masjid Sultan Ahmed', 'location' => 'Istanbul', 'description' => 'Lawatan bandar untuk menghayati warisan Islam Turkiye.', 'image' => $coverImage],
                ['name' => 'Bosphorus', 'location' => 'Istanbul', 'description' => 'Aktiviti santai yang disusun mengikut masa perjalanan.', 'image' => 'demo/gallery/menuju-kaabah.jpg'],
                ['name' => 'Masjid Quba', 'location' => 'Madinah', 'description' => 'Ziarah pilihan bagi sambungan perjalanan Umrah.', 'image' => 'demo/gallery/tilawah.jpg'],
            ],
            default => [
                ['name' => 'Masjid Quba', 'location' => 'Madinah', 'description' => 'Menziarahi masjid pertama dalam sejarah Islam.', 'image' => 'demo/gallery/tilawah.jpg'],
                ['name' => 'Jabal Uhud', 'location' => 'Madinah', 'description' => 'Menghayati sejarah perjuangan para sahabat.', 'image' => 'demo/gallery/doa-di-kaabah.jpg'],
                ['name' => 'Jabal Rahmah', 'location' => 'Arafah', 'description' => 'Lawatan mengikut kesesuaian masa dan keadaan setempat.', 'image' => $coverImage],
            ],
        };
    }

    /**
     * @param  array<int, string>  $hotelKeys
     * @return array<int, array{room_type: string, price_sen: int, hotel_key: string, image: string}>
     */
    private function roomPrices(int $priceSen, array $hotelKeys): array
    {
        return [
            ['room_type' => 'Bilik Quad', 'price_sen' => $priceSen, 'hotel_key' => $hotelKeys[0], 'image' => 'demo/content/room-prices/quad.jpg'],
            ['room_type' => 'Bilik Triple', 'price_sen' => $priceSen + 35_000, 'hotel_key' => $hotelKeys[0], 'image' => 'demo/content/room-prices/triple.jpg'],
            ['room_type' => 'Bilik Double', 'price_sen' => $priceSen + 95_000, 'hotel_key' => $hotelKeys[0], 'image' => 'demo/content/room-prices/double.jpg'],
            ['room_type' => 'Bilik Triple (Hotel Alternatif)', 'price_sen' => $priceSen + 55_000, 'hotel_key' => $hotelKeys[1], 'image' => 'demo/content/room-prices/triple.jpg'],
            ['room_type' => 'Bilik Double (Madinah)', 'price_sen' => $priceSen + 115_000, 'hotel_key' => $hotelKeys[2], 'image' => 'demo/content/room-prices/double.jpg'],
        ];
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function itineraryFor(string $slug): array
    {
        return match ($slug) {
            'pakej-haji-9h-7m-demo' => [
                ['title' => 'Berlepas dari Kuala Lumpur', 'description' => 'Pendaftaran kumpulan, taklimat akhir dan penerbangan ke Tanah Suci.'],
                ['title' => 'Tiba di Jeddah', 'description' => 'Urusan imigresen dan daftar masuk penginapan.'],
                ['title' => 'Persediaan di Mina', 'description' => 'Bimbingan mutawif dan semakan jadual manasik.'],
                ['title' => 'Wukuf di Arafah', 'description' => 'Pengisian ibadah mengikut susunan kumpulan dan pihak berkuasa.'],
                ['title' => 'Muzdalifah dan melontar Jamrah', 'description' => 'Pergerakan jemaah mengikut jadual serta keadaan semasa.'],
                ['title' => 'Tawaf Ifadah', 'description' => 'Pelaksanaan ibadah dengan bimbingan yang disusun.'],
                ['title' => 'Hari rehat dan ibadah', 'description' => 'Masa ibadah kendiri serta persediaan ke Madinah.'],
                ['title' => 'Ziarah Madinah', 'description' => 'Ziarah Masjid Quba dan Jabal Uhud mengikut masa.'],
                ['title' => 'Penerbangan pulang', 'description' => 'Daftar keluar dan penerbangan pulang ke Kuala Lumpur.'],
            ],
            'pakej-badal-haji-demo' => [
                ['title' => 'Taklimat dan semakan dokumen', 'description' => 'Penerangan proses Badal Haji serta dokumen penamaan wakil.'],
                ['title' => 'Perjalanan wakil', 'description' => 'Wakil yang dilantik memulakan perjalanan mengikut jadual.'],
                ['title' => 'Persediaan pelaksanaan', 'description' => 'Semakan niat dan susunan ibadah oleh wakil bertauliah.'],
                ['title' => 'Pelaksanaan Badal Haji', 'description' => 'Ibadah dilaksanakan mengikut ketetapan dan syarat yang berkaitan.'],
                ['title' => 'Hari manasik utama', 'description' => 'Aktiviti ibadah diteruskan mengikut masa dan keadaan setempat.'],
                ['title' => 'Semakan rekod ibadah', 'description' => 'Rekod dalaman dikemas kini untuk rujukan agensi.'],
                ['title' => 'Ziarah Madinah', 'description' => 'Ziarah pilihan berdasarkan susunan kumpulan wakil.'],
                ['title' => 'Penyediaan dokumentasi', 'description' => 'Dokumentasi pelaksanaan disusun untuk semakan pelanggan.'],
                ['title' => 'Makluman penyelesaian', 'description' => 'Agensi memaklumkan status selepas proses lengkap.'],
            ],
            'pakej-pelancongan-muslim-12h-10m-demo' => [
                ['title' => 'Berlepas ke Istanbul', 'description' => 'Berkumpul di KLIA dan penerbangan ke Istanbul.'],
                ['title' => 'Tiba dan daftar masuk', 'description' => 'Urusan ketibaan dan rehat sebelum lawatan bandar.'],
                ['title' => 'Warisan Sultanahmet', 'description' => 'Lawatan ke kawasan bersejarah mengikut atur cara.'],
                ['title' => 'Pelayaran Bosphorus', 'description' => 'Aktiviti santai untuk melihat panorama bandar.'],
                ['title' => 'Bursa', 'description' => 'Lawatan pilihan ke bandar bersejarah bersama tour leader.'],
                ['title' => 'Cappadocia', 'description' => 'Pengalaman landskap dan aktiviti pilihan mengikut cuaca.'],
                ['title' => 'Masa bebas dan membeli-belah', 'description' => 'Masa bebas dengan cadangan lokasi mesra Muslim.'],
                ['title' => 'Persediaan sambungan Umrah', 'description' => 'Taklimat ringkas dan persediaan perjalanan ke Tanah Suci.'],
                ['title' => 'Tiba di Makkah', 'description' => 'Daftar masuk dan masa ibadah di Masjidil Haram.'],
                ['title' => 'Ziarah Madinah', 'description' => 'Perjalanan ke Madinah dan ziarah mengikut jadual.'],
                ['title' => 'Ibadah di Masjid Nabawi', 'description' => 'Masa ibadah serta persiapan pulang.'],
                ['title' => 'Penerbangan pulang', 'description' => 'Perjalanan pulang ke Kuala Lumpur.'],
            ],
            default => $this->itinerary(),
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function packageData(): array
    {
        return [
            ['title' => 'Pakej Umrah Ekonomi 9H 7M', 'slug' => 'pakej-umrah-ekonomi-9h-7m-demo', 'code' => 'DS-UMR-09', 'category' => 'pakej-umrah', 'price_sen' => 599_000, 'duration_days' => 9, 'duration_nights' => 7, 'makkah_nights' => 4, 'madinah_nights' => 3, 'season_label' => 'Musim biasa', 'airline' => 'Saudia', 'makkah_hotel' => 'Hotel setaraf 3 bintang Makkah', 'madinah_hotel' => 'Hotel setaraf 3 bintang Madinah', 'hotel_rating' => 3, 'walking_distance' => 'Anggaran 700 m ke Masjidil Haram', 'short_description' => 'Pakej praktikal dengan atur cara lengkap untuk perjalanan Umrah pertama.', 'description' => '<p>Pakej demonstrasi untuk menyemak paparan maklumat asas, penginapan dan jadual perjalanan.</p><p>Butiran sebenar perlu disahkan oleh pihak agensi.</p>', 'cover_image' => 'demo/packages/umrah-ekonomi.png', 'gallery' => ['demo/gallery/jemaah-tawaf.jpg', 'demo/gallery/doa-di-kaabah.jpg', 'demo/gallery/menuju-kaabah.jpg'], 'is_featured' => true, 'show_on_homepage' => true, 'status' => 'published', 'departure_status' => 'open'],
            ['title' => 'Pakej Umrah Premium 9H 7M', 'slug' => 'pakej-umrah-premium-9h-7m-demo', 'code' => 'DS-UMP-09', 'category' => 'pakej-umrah', 'price_sen' => 849_000, 'duration_days' => 9, 'duration_nights' => 7, 'makkah_nights' => 4, 'madinah_nights' => 3, 'season_label' => 'Musim pilihan', 'airline' => 'Qatar Airways', 'makkah_hotel' => 'Hotel setaraf 5 bintang Makkah', 'madinah_hotel' => 'Hotel setaraf 5 bintang Madinah', 'hotel_rating' => 5, 'walking_distance' => 'Anggaran 300 m ke Masjidil Haram', 'short_description' => 'Pilihan lebih selesa dengan penginapan berhampiran kawasan ibadah.', 'description' => '<p>Pakej demonstrasi premium dengan data penginapan, penerbangan, galeri dan itinerary yang lengkap.</p><p>Butiran sebenar perlu disahkan oleh pihak agensi.</p>', 'cover_image' => 'demo/packages/umrah-premium.png', 'gallery' => ['demo/packages/umrah-premium.png', 'demo/gallery/tilawah.jpg', 'demo/gallery/doa-di-kaabah.jpg'], 'is_featured' => true, 'show_on_homepage' => true, 'status' => 'published', 'departure_status' => 'open'],
            ['title' => 'Pakej Pelancongan Muslim 12H 10M', 'slug' => 'pakej-pelancongan-muslim-12h-10m-demo', 'code' => 'DS-PLG-12', 'category' => 'pakej-pelancongan', 'price_sen' => 1_199_000, 'duration_days' => 12, 'duration_nights' => 10, 'makkah_nights' => 6, 'madinah_nights' => 4, 'season_label' => 'Pelancongan Muslim', 'airline' => 'Emirates', 'makkah_hotel' => 'Hotel setaraf 4 bintang', 'madinah_hotel' => 'Hotel setaraf 4 bintang', 'hotel_rating' => 4, 'walking_distance' => 'Pengangkutan tempatan disediakan mengikut itinerari', 'short_description' => 'Pelancongan Muslim dengan jadual lawatan yang tersusun dan tempoh yang lebih panjang.', 'description' => '<p>Pakej pelancongan demonstrasi untuk menyemak pakej berdurasi panjang dan susunan itinerary 12 hari.</p><p>Butiran sebenar perlu disahkan oleh pihak agensi.</p>', 'cover_image' => 'demo/packages/umrah-ramadan.png', 'gallery' => ['demo/packages/umrah-ramadan.png', 'demo/gallery/jemaah-tawaf.jpg', 'demo/gallery/tilawah.jpg'], 'is_featured' => true, 'show_on_homepage' => true, 'status' => 'published', 'departure_status' => 'open'],
            ['title' => 'Pakej Haji 9H 7M', 'slug' => 'pakej-haji-9h-7m-demo', 'code' => 'DS-HAJ-09', 'category' => 'pakej-haji', 'price_sen' => 729_000, 'duration_days' => 9, 'duration_nights' => 7, 'makkah_nights' => 4, 'madinah_nights' => 3, 'season_label' => 'Musim Haji', 'airline' => 'Malaysia Airlines', 'makkah_hotel' => 'Hotel setaraf 4 bintang Makkah', 'madinah_hotel' => 'Hotel setaraf 4 bintang Madinah', 'hotel_rating' => 4, 'walking_distance' => 'Anggaran 550 m ke Masjidil Haram', 'short_description' => 'Pakej Haji demonstrasi dengan susunan perjalanan dan bimbingan yang jelas.', 'description' => '<p>Pakej Haji demonstrasi untuk menyemak maklumat harga, ziarah dan hadiah jemaah.</p><p>Butiran sebenar perlu disahkan oleh pihak agensi.</p>', 'cover_image' => 'demo/packages/umrah-keluarga.jpg', 'gallery' => ['demo/packages/umrah-keluarga.jpg', 'demo/gallery/menuju-kaabah.jpg', 'demo/gallery/doa-di-kaabah.jpg'], 'is_featured' => true, 'show_on_homepage' => true, 'status' => 'published', 'departure_status' => 'open'],
            ['title' => 'Pakej Badal Haji', 'slug' => 'pakej-badal-haji-demo', 'code' => 'DS-BHL-09', 'category' => 'pakej-badal-haji', 'price_sen' => 759_000, 'duration_days' => 9, 'duration_nights' => 7, 'makkah_nights' => 4, 'madinah_nights' => 3, 'season_label' => 'Badal Haji', 'airline' => 'AirAsia X', 'makkah_hotel' => 'Hotel setaraf 3 bintang Makkah', 'madinah_hotel' => 'Hotel setaraf 3 bintang Madinah', 'hotel_rating' => 3, 'walking_distance' => 'Anggaran 750 m ke Masjidil Haram', 'short_description' => 'Pakej Badal Haji demonstrasi untuk semakan susunan maklumat dan proses agensi.', 'description' => '<p>Pakej Badal Haji demonstrasi untuk menyemak kategori dan butiran perkhidmatan dalam sistem.</p><p>Butiran sebenar perlu disahkan oleh pihak agensi.</p>', 'cover_image' => 'demo/packages/umrah-cuti-sekolah.jpg', 'gallery' => ['demo/packages/umrah-cuti-sekolah.jpg', 'demo/gallery/tilawah.jpg', 'demo/gallery/jemaah-tawaf.jpg'], 'is_featured' => false, 'show_on_homepage' => true, 'status' => 'published', 'departure_status' => 'open'],
            ['title' => 'Pakej Pelancongan Turkiye 9H 7M', 'slug' => 'pakej-pelancongan-turkiye-9h-7m-draf-demo', 'code' => 'DS-PLD-09', 'category' => 'pakej-pelancongan', 'price_sen' => 619_000, 'duration_days' => 9, 'duration_nights' => 7, 'makkah_nights' => 4, 'madinah_nights' => 3, 'season_label' => 'Dalam perancangan', 'airline' => 'Turkish Airlines', 'makkah_hotel' => 'Hotel setaraf 3 bintang', 'madinah_hotel' => 'Hotel setaraf 3 bintang', 'hotel_rating' => 3, 'walking_distance' => 'Pengangkutan tempatan disediakan mengikut itinerari', 'short_description' => 'Rekod draf untuk menyemak kawalan status dan proses penerbitan pakej pelancongan.', 'description' => '<p>Data draf demonstrasi untuk kegunaan semakan dalam pentadbir.</p>', 'cover_image' => 'demo/packages/umrah-ekonomi.png', 'gallery' => ['demo/gallery/jemaah-tawaf.jpg', 'demo/gallery/menuju-kaabah.jpg', 'demo/gallery/tilawah.jpg'], 'is_featured' => false, 'show_on_homepage' => false, 'status' => 'draft', 'departure_status' => 'draft'],
            ['title' => 'Pakej Pelancongan Andalusia 10H 8M', 'slug' => 'pakej-pelancongan-andalusia-10h-8m-arkib-demo', 'code' => 'DS-PLA-10', 'category' => 'pakej-pelancongan', 'price_sen' => 899_000, 'duration_days' => 10, 'duration_nights' => 8, 'makkah_nights' => 5, 'madinah_nights' => 3, 'season_label' => 'Rekod arkib', 'airline' => 'Turkish Airlines', 'makkah_hotel' => 'Hotel setaraf 4 bintang', 'madinah_hotel' => 'Hotel setaraf 4 bintang', 'hotel_rating' => 4, 'walking_distance' => 'Pengangkutan tempatan disediakan mengikut itinerari', 'short_description' => 'Rekod arkib untuk menyemak pemulihan dan kawalan paparan pakej pelancongan.', 'description' => '<p>Data arkib demonstrasi untuk kegunaan semakan dalam pentadbir.</p>', 'cover_image' => 'demo/packages/umrah-premium.png', 'gallery' => ['demo/packages/umrah-premium.png', 'demo/gallery/doa-di-kaabah.jpg', 'demo/gallery/menuju-kaabah.jpg'], 'is_featured' => false, 'show_on_homepage' => false, 'status' => 'archived', 'departure_status' => 'closed'],
        ];
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function itinerary(): array
    {
        return [
            ['title' => 'Berlepas dari Kuala Lumpur', 'description' => 'Berkumpul di KLIA untuk pendaftaran, taklimat ringkas dan penerbangan ke Tanah Suci.'],
            ['title' => 'Tiba di Jeddah dan ke Makkah', 'description' => 'Urusan imigresen, perjalanan ke hotel dan masa rehat sebelum ibadah.'],
            ['title' => 'Pelaksanaan Umrah', 'description' => 'Bimbingan mutawif untuk melaksanakan ibadah Umrah mengikut susunan yang ditetapkan.'],
            ['title' => 'Ibadah di Masjidil Haram', 'description' => 'Masa bebas untuk ibadah, tawaf sunat dan sesi perkongsian ringkas.'],
            ['title' => 'Ziarah sekitar Makkah', 'description' => 'Lawatan ziarah mengikut kesesuaian masa, cuaca dan keadaan setempat.'],
            ['title' => 'Bertolak ke Madinah', 'description' => 'Daftar keluar hotel dan perjalanan ke Madinah dengan pengangkutan yang disediakan.'],
            ['title' => 'Ibadah di Masjid Nabawi', 'description' => 'Masa untuk solat dan beribadah di sekitar Masjid Nabawi.'],
            ['title' => 'Ziarah Madinah', 'description' => 'Ziarah Masjid Quba, Jabal Uhud dan lokasi berkaitan mengikut atur cara.'],
            ['title' => 'Persiapan pulang', 'description' => 'Masa bebas, daftar keluar hotel dan persiapan untuk penerbangan pulang.'],
            ['title' => 'Penerbangan pulang', 'description' => 'Berlepas dari Jeddah ke Kuala Lumpur.'],
            ['title' => 'Tiba di Kuala Lumpur', 'description' => 'Tiba di KLIA dan tamat perjalanan bersama kumpulan.'],
            ['title' => 'Hari simpanan perjalanan', 'description' => 'Hari tambahan demonstrasi untuk pakej Ramadan yang lebih panjang.'],
        ];
    }
}

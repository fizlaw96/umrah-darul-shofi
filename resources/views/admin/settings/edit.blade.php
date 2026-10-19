<x-layouts.admin heading="Tetapan laman">
    <form class="max-w-4xl space-y-7" method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <x-errors />

        <section class="panel p-6">
            <h2 class="text-lg font-extrabold text-dark-green">Identiti agensi</h2>
            <div class="mt-5 grid gap-5">
                <label>
                    <span class="field-label">Nama syarikat / agensi</span>
                    <input class="field-control" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? 'Darul Shofi') }}" required>
                </label>
                <label>
                    <span class="field-label">Tagline</span>
                    <input class="field-control" name="company_tagline" value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}">
                </label>
                <label>
                    <span class="field-label">URL logo <small>(pilihan)</small></span>
                    <input class="field-control" type="url" name="company_logo_url" value="{{ old('company_logo_url', $settings['company_logo_url'] ?? '') }}" placeholder="https://...">
                </label>
            </div>
        </section>

        <section class="panel p-6">
            <h2 class="text-lg font-extrabold text-dark-green">Hubungan</h2>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label>
                    <span class="field-label">Telefon</span>
                    <input class="field-control" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                </label>
                <label>
                    <span class="field-label">WhatsApp</span>
                    <input class="field-control" name="contact_whatsapp" value="{{ old('contact_whatsapp', $settings['contact_whatsapp'] ?? '') }}">
                </label>
                <label class="sm:col-span-2">
                    <span class="field-label">E-mel</span>
                    <input class="field-control" type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                </label>
                <label class="sm:col-span-2">
                    <span class="field-label">Alamat pejabat</span>
                    <textarea class="field-control" name="office_address">{{ old('office_address', $settings['office_address'] ?? '') }}</textarea>
                </label>
                <label class="sm:col-span-2">
                    <span class="field-label">Waktu operasi</span>
                    <input class="field-control" name="office_hours" value="{{ old('office_hours', $settings['office_hours'] ?? '') }}">
                </label>
            </div>
        </section>

        <section class="panel p-6">
            <h2 class="text-lg font-extrabold text-dark-green">Media sosial &amp; pautan undang-undang</h2>
            <div class="mt-5 grid gap-5">
                <label>
                    <span class="field-label">Facebook URL</span>
                    <input class="field-control" type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url'] ?? '') }}">
                </label>
                <label>
                    <span class="field-label">Instagram URL</span>
                    <input class="field-control" type="url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url'] ?? '') }}">
                </label>
                <label>
                    <span class="field-label">URL dasar privasi <small>(pilihan)</small></span>
                    <input class="field-control" type="url" name="privacy_url" value="{{ old('privacy_url', $settings['privacy_url'] ?? '') }}">
                </label>
                <label>
                    <span class="field-label">URL terma &amp; syarat <small>(pilihan)</small></span>
                    <input class="field-control" type="url" name="terms_url" value="{{ old('terms_url', $settings['terms_url'] ?? '') }}">
                </label>
            </div>
        </section>

        <section class="panel p-6">
            <div class="max-w-2xl">
                <p class="section-eyebrow">Halaman utama</p>
                <h2 class="mt-1 text-lg font-extrabold text-dark-green">Kandungan premium</h2>
                <p class="mt-2 text-xs leading-5 text-[#65706b]">Tambahkan hanya media dan kempen yang telah diluluskan. Ruang tanpa kandungan akan disorok secara automatik pada halaman utama.</p>
            </div>

            <div class="mt-6 grid gap-5">
                <label>
                    <span class="field-label">Pengenalan ringkas agensi</span>
                    <textarea class="field-control min-h-24" name="homepage_company_intro" maxlength="1000" placeholder="Dua atau tiga ayat ringkas tentang cara agensi membantu jemaah.">{{ old('homepage_company_intro', $settings['homepage_company_intro'] ?? '') }}</textarea>
                </label>
                <label>
                    <span class="field-label">Tajuk bahagian pakej</span>
                    <input class="field-control" name="homepage_package_heading" value="{{ old('homepage_package_heading', $settings['homepage_package_heading'] ?? '') }}" placeholder="Pakej Umrah 1447H / 2026M">
                </label>
                <label>
                    <span class="field-label">Video hero mengikut pakej <small>(satu baris setiap video: slug pakej | URL video)</small></span>
                    <textarea class="field-control min-h-28 font-mono text-xs" name="homepage_hero_videos" placeholder="umrah-premium-9h-7m | https://cdn.example.com/umrah-premium.mp4">{{ old('homepage_hero_videos', $settings['homepage_hero_videos'] ?? '') }}</textarea>
                </label>
                <label>
                    <span class="field-label">Logo rakan / akreditasi yang diluluskan <small>(satu baris: URL logo | nama)</small></span>
                    <textarea class="field-control min-h-28 font-mono text-xs" name="homepage_partner_logos" placeholder="https://example.com/logo.svg | Nama rakan yang diluluskan">{{ old('homepage_partner_logos', $settings['homepage_partner_logos'] ?? '') }}</textarea>
                </label>
                <label>
                    <span class="field-label">Foto tentang agensi <small>(satu baris: URL foto | teks alternatif)</small></span>
                    <textarea class="field-control min-h-28 font-mono text-xs" name="homepage_about_images" placeholder="https://example.com/foto.jpg | Jemaah bersama pasukan agensi">{{ old('homepage_about_images', $settings['homepage_about_images'] ?? '') }}</textarea>
                </label>
                <label>
                    <span class="field-label">Galeri perjalanan <small>(satu baris: URL lakaran kecil | URL imej penuh | teks alternatif)</small></span>
                    <textarea class="field-control min-h-36 font-mono text-xs" name="homepage_gallery_images" placeholder="https://example.com/thumb.jpg | https://example.com/full.jpg | Aktiviti jemaah di Makkah">{{ old('homepage_gallery_images', $settings['homepage_gallery_images'] ?? '') }}</textarea>
                </label>
                <label>
                    <span class="field-label">Kategori perkhidmatan yang diluluskan <small>(satu baris: HAJI, UMRAH, PELANCONGAN, TAKAFUL PERJALANAN, UMRAH VIP atau PERTANYAAN | packages, contact, whatsapp atau URL)</small></span>
                    <textarea class="field-control min-h-28 font-mono text-xs" name="homepage_service_categories" placeholder="HAJI | whatsapp&#10;UMRAH | packages&#10;PERTANYAAN | contact">{{ old('homepage_service_categories', $settings['homepage_service_categories'] ?? '') }}</textarea>
                </label>
            </div>
        </section>

        <section class="panel p-6">
            <h2 class="text-lg font-extrabold text-dark-green">Kempen &amp; video pilihan</h2>
            <div class="mt-5 grid gap-5">
                <label>
                    <span class="field-label">Tajuk kempen <small>(kosongkan untuk sorokkan)</small></span>
                    <input class="field-control" name="homepage_campaign_title" value="{{ old('homepage_campaign_title', $settings['homepage_campaign_title'] ?? '') }}" placeholder="Pakej Haji 1447H / 2026M">
                </label>
                <label>
                    <span class="field-label">Penerangan kempen</span>
                    <textarea class="field-control min-h-24" name="homepage_campaign_description">{{ old('homepage_campaign_description', $settings['homepage_campaign_description'] ?? '') }}</textarea>
                </label>
                <div class="grid gap-5 sm:grid-cols-2">
                    <label>
                        <span class="field-label">URL kempen</span>
                        <input class="field-control" type="url" name="homepage_campaign_link" value="{{ old('homepage_campaign_link', $settings['homepage_campaign_link'] ?? '') }}" placeholder="https://...">
                    </label>
                    <label>
                        <span class="field-label">Label CTA kempen</span>
                        <input class="field-control" name="homepage_campaign_cta" value="{{ old('homepage_campaign_cta', $settings['homepage_campaign_cta'] ?? '') }}" placeholder="Ketahui lebih lanjut">
                    </label>
                </div>
                <label>
                    <span class="field-label">URL video pilihan <small>(MP4/WebM atau URL embed)</small></span>
                    <input class="field-control" type="url" name="homepage_video_url" value="{{ old('homepage_video_url', $settings['homepage_video_url'] ?? '') }}" placeholder="https://...">
                </label>
                <label>
                    <span class="field-label">URL kulit video <small>(pilihan)</small></span>
                    <input class="field-control" type="url" name="homepage_video_cover" value="{{ old('homepage_video_cover', $settings['homepage_video_cover'] ?? '') }}" placeholder="https://...">
                </label>
                <label>
                    <span class="field-label">URL kulit Instagram <small>(pilihan; pautan Instagram tetap diperlukan)</small></span>
                    <input class="field-control" type="url" name="instagram_preview_image" value="{{ old('instagram_preview_image', $settings['instagram_preview_image'] ?? '') }}" placeholder="https://...">
                </label>
            </div>
        </section>

        <button class="button-primary" type="submit">Simpan tetapan</button>
    </form>
</x-layouts.admin>

<?php

use App\Actions\VideoDurationInspector;
use App\Models\Faq;
use App\Models\HeroSlide;
use App\Models\Mutawif;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Testimonial;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(LazilyRefreshDatabase::class);

it('lets staff manage a category that controls homepage visibility', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.package-categories.store'), [
            'name' => 'Pakej Umrah',
            'slug' => 'slug-yang-diabaikan',
            'description' => 'Perjalanan umrah.',
            'is_active' => true,
            'show_on_homepage' => true,
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('package_categories', [
        'name' => 'Pakej Umrah',
        'slug' => 'pakej-umrah',
        'is_active' => true,
        'show_on_homepage' => true,
        'sort_order' => 1,
    ]);
    $this->assertDatabaseHas('hero_slides', [
        'package_category_id' => PackageCategory::query()->where('slug', 'pakej-umrah')->value('id'),
        'title' => 'Pakej Umrah',
        'subtitle' => 'Perjalanan umrah.',
        'is_published' => true,
        'sort_order' => 1,
    ]);
});

it('lets staff manage category hero media while deriving its content from the category', function () {
    Storage::fake('public');
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create([
        'name' => 'Pakej Umrah Asas',
        'description' => '1448H',
        'is_active' => true,
        'show_on_homepage' => true,
    ]);

    $this->actingAs($staff)
        ->put(route('admin.package-categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'background_image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
            'is_active' => true,
            'show_on_homepage' => true,
        ])
        ->assertRedirect(route('admin.package-categories.index'));

    $this->assertDatabaseHas('hero_slides', [
        'package_category_id' => $category->id,
        'package_id' => null,
        'title' => 'Pakej Umrah Asas',
        'subtitle' => '1448H',
        'cta_label' => 'Terokai Pakej',
        'cta_url' => null,
        'is_published' => true,
    ]);

    $heroSlide = HeroSlide::query()->where('package_category_id', $category->id)->firstOrFail();

    Storage::disk('public')->assertExists($heroSlide->background_image);

    $this->actingAs($staff)
        ->get(route('admin.package-categories.edit', $category))
        ->assertOk()
        ->assertSee('Subtajuk / tahun')
        ->assertSee('Slug dijana secara automatik daripada nama.')
        ->assertSee('Butang CTA: Terokai Pakej')
        ->assertSee('aria-label="Keluarkan poster imej"', false)
        ->assertSee($heroSlide->imageUrl(), false)
        ->assertDontSee('Buang imej')
        ->assertDontSee('Kategori CTA')
        ->assertDontSee('URL CTA luaran');

    $imagePath = $heroSlide->background_image;

    app()->bind(VideoDurationInspector::class, fn (): VideoDurationInspector => new class extends VideoDurationInspector
    {
        public function durationInSeconds(UploadedFile $video): ?float
        {
            return 9;
        }
    });

    $this->actingAs($staff)
        ->put(route('admin.package-categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => true,
            'show_on_homepage' => true,
            'background_video' => UploadedFile::fake()->create('hero.mp4', 512, 'video/mp4'),
        ])
        ->assertSessionHasErrors('background_image');

    Storage::disk('public')->assertExists($imagePath);

    $this->actingAs($staff)
        ->put(route('admin.package-categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => true,
            'show_on_homepage' => true,
            'background_video' => UploadedFile::fake()->create('hero.mp4', 512, 'video/mp4'),
            'remove_background_image' => true,
        ])
        ->assertRedirect(route('admin.package-categories.index'));

    $this->assertDatabaseHas('hero_slides', [
        'id' => $heroSlide->id,
        'background_image' => null,
    ]);
    Storage::disk('public')->assertMissing($imagePath);
    Storage::disk('public')->assertExists($heroSlide->fresh()->background_video);
});

it('rejects a hero video longer than ten seconds', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();

    app()->bind(VideoDurationInspector::class, fn (): VideoDurationInspector => new class extends VideoDurationInspector
    {
        public function durationInSeconds(UploadedFile $video): ?float
        {
            return 10.01;
        }
    });

    $this->actingAs($staff)
        ->put(route('admin.package-categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'background_video' => UploadedFile::fake()->create('hero.mp4', 512, 'video/mp4'),
            'is_active' => true,
            'show_on_homepage' => true,
        ])
        ->assertSessionHasErrors('background_video');

    $this->assertDatabaseMissing('hero_slides', ['package_category_id' => $category->id]);
});

it('returns to the category form with an upload error when a video exceeds the PHP post limit', function () {
    $staff = User::factory()->staff()->create();
    $category = PackageCategory::factory()->create();
    $editUrl = route('admin.package-categories.edit', $category);

    $this->actingAs($staff)
        ->from($editUrl)
        ->call('PUT', route('admin.package-categories.update', $category), [], [], [], [
            'CONTENT_LENGTH' => (string) PHP_INT_MAX,
            'HTTP_REFERER' => $editUrl,
        ])
        ->assertRedirect($editUrl.'?upload_error=1');

    $this->actingAs($staff)
        ->get($editUrl.'?upload_error=1')
        ->assertOk()
        ->assertSee('Fail terlalu besar untuk dimuat naik oleh pelayan. Gunakan video yang tidak melebihi 100 MB.');

    $this->assertDatabaseHas('package_categories', ['id' => $category->id]);
});

it('does not redirect to an external website when an upload exceeds the PHP post limit', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->call('PUT', route('admin.package-categories.update', 1), [], [], [], [
            'CONTENT_LENGTH' => (string) PHP_INT_MAX,
            'HTTP_REFERER' => 'https://example.com/admin/package-categories/1/edit',
        ])
        ->assertRedirect(route('home').'?upload_error=1');
});

it('automatically orders new mutawif and testimonial content while adding FAQ content', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();
    Storage::fake('public');

    $this->actingAs($staff)
        ->post(route('admin.mutawifs.store'), ['full_name' => 'Ustaz Pertama', 'is_published' => true])
        ->assertRedirect();
    $this->post(route('admin.mutawifs.store'), ['full_name' => 'Ustaz Kedua', 'is_published' => true])
        ->assertRedirect();

    expect(Mutawif::query()->where('full_name', 'Ustaz Pertama')->value('sort_order'))->toBe(1)
        ->and(Mutawif::query()->where('full_name', 'Ustaz Kedua')->value('sort_order'))->toBe(2);

    $this->post(route('admin.testimonials.store'), [
        'package_id' => $package->id,
        'reviewer_name' => 'Jemaah Pertama',
        'rating' => 5,
        'review' => 'Alhamdulillah, urusan sangat teratur.',
        'photo' => UploadedFile::fake()->image('jemaah-pertama.jpg'),
        'is_published' => true,
    ])->assertRedirect();

    expect(Testimonial::query()->where('reviewer_name', 'Jemaah Pertama')->value('sort_order'))->toBe(1);

    $this->post(route('admin.faqs.store'), [
        'category' => 'Umum',
        'question' => 'Soalan pertama?',
        'answer' => 'Jawapan pertama.',
        'is_published' => true,
    ])->assertRedirect();
    $this->post(route('admin.faqs.store'), [
        'category' => 'Umum',
        'question' => 'Soalan kedua?',
        'answer' => 'Jawapan kedua.',
        'is_published' => true,
    ])->assertRedirect();

    $firstFaq = Faq::query()->where('question', 'Soalan pertama?')->firstOrFail();
    $secondFaq = Faq::query()->where('question', 'Soalan kedua?')->firstOrFail();

    expect($firstFaq->sort_order)->toBe(0)
        ->and($secondFaq->sort_order)->toBe(0);

    $this->put(route('admin.faqs.update', $firstFaq), [
        'category' => $firstFaq->category,
        'question' => $firstFaq->question,
        'answer' => $firstFaq->answer,
        'is_published' => true,
    ])->assertRedirect();

    expect($firstFaq->fresh()->sort_order)->toBe(0)
        ->and($secondFaq->fresh()->sort_order)->toBe(0);
});

it('ignores legacy FAQ placement input', function () {
    $staff = User::factory()->staff()->create();
    $firstFaq = Faq::factory()->create(['sort_order' => 1]);
    $secondFaq = Faq::factory()->create(['sort_order' => 2]);

    $this->actingAs($staff)
        ->post(route('admin.faqs.store'), [
            'category' => 'Tempahan',
            'question' => 'Bolehkah saya menukar tarikh?',
            'answer' => 'Sila hubungi pasukan kami untuk bantuan.',
            'placement' => 'bottom',
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.faqs.index'));

    $thirdFaq = Faq::query()->where('question', 'Bolehkah saya menukar tarikh?')->firstOrFail();

    expect($thirdFaq->sort_order)->toBe(0);

    $this->put(route('admin.faqs.update', $firstFaq), [
        'category' => $firstFaq->category,
        'question' => $firstFaq->question,
        'answer' => $firstFaq->answer,
        'placement' => 'bottom',
        'is_published' => true,
    ])->assertRedirect();

    expect($firstFaq->fresh()->sort_order)->toBe(1)
        ->and($secondFaq->fresh()->sort_order)->toBe(2)
        ->and($thirdFaq->fresh()->sort_order)->toBe(0);
});

it('does not render a FAQ placement control', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->get(route('admin.faqs.create'))
        ->assertOk()
        ->assertSee('<select class="field-control" name="category" required>', false)
        ->assertSee('value="Pakej"', false)
        ->assertSee('value="Persediaan"', false)
        ->assertSee('value="Tempahan"', false)
        ->assertDontSee('name="placement"', false)
        ->assertDontSee('Susunan paparan');
});

it('renders the FAQ edit form for staff', function () {
    $staff = User::factory()->staff()->create();
    $faq = Faq::factory()->create(['category' => 'Tempahan']);
    Faq::factory()->create(['category' => 'Pembayaran']);

    $this->actingAs($staff)
        ->get(route('admin.faqs.edit', $faq))
        ->assertOk()
        ->assertSee('id="delete-faq"', false)
        ->assertSee(route('admin.faqs.destroy', $faq), false)
        ->assertSee('value="Pembayaran"', false)
        ->assertSee('Kembali')
        ->assertDontSee('name="sort_order"', false)
        ->assertDontSee('name="placement"', false)
        ->assertDontSee('Susunan paparan');
});

it('renders FAQ management with an edit icon', function () {
    $staff = User::factory()->staff()->create();
    $faq = Faq::factory()->create();

    $this->actingAs($staff)
        ->get(route('admin.faqs.index'))
        ->assertOk()
        ->assertSee('aria-label="Urus '.$faq->question.'"', false)
        ->assertSee('>edit</span>', false)
        ->assertDontSee('Edit nombor susunan jika mahu menukar kedudukan.');
});

it('requires a linked package for a testimonial', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->post(route('admin.testimonials.store'), [
            'reviewer_name' => 'Jemaah Tanpa Foto',
            'rating' => 5,
            'review' => 'Urusan sangat baik.',
        ])
        ->assertSessionHasErrors('package_id');
});

it('lets staff create a testimonial without a pilgrim photo', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();

    $this->actingAs($staff)
        ->post(route('admin.testimonials.store'), [
            'package_id' => $package->id,
            'reviewer_name' => 'Jemaah Tanpa Foto',
            'rating' => 5,
            'review' => 'Urusan sangat baik.',
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.testimonials.index'));

    $this->assertDatabaseHas('testimonials', [
        'package_id' => $package->id,
        'reviewer_name' => 'Jemaah Tanpa Foto',
        'photo' => null,
        'is_published' => true,
    ]);
});

it('unpublishes the oldest testimonial when staff publishes a fourth testimonial', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();
    $oldestTestimonial = Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinutes(3),
    ]);
    $secondTestimonial = Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinutes(2),
    ]);
    $thirdTestimonial = Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinute(),
    ]);

    $this->actingAs($staff)
        ->post(route('admin.testimonials.store'), [
            'package_id' => $package->id,
            'reviewer_name' => 'Jemaah Baharu',
            'rating' => 5,
            'review' => 'Pengalaman ibadah yang sangat memuaskan.',
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.testimonials.index'));

    $newTestimonial = Testimonial::query()->where('reviewer_name', 'Jemaah Baharu')->firstOrFail();

    expect($oldestTestimonial->fresh()->is_published)->toBeFalse()
        ->and($secondTestimonial->fresh()->is_published)->toBeTrue()
        ->and($thirdTestimonial->fresh()->is_published)->toBeTrue()
        ->and($newTestimonial->is_published)->toBeTrue()
        ->and(Testimonial::query()->published()->count())->toBe(3);
});

it('keeps the selected testimonial published when it replaces the oldest testimonial', function () {
    $staff = User::factory()->staff()->create();
    $package = Package::factory()->create();
    $oldestTestimonial = Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinutes(3),
    ]);
    Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinutes(2),
    ]);
    Testimonial::factory()->for($package)->create([
        'is_published' => true,
        'created_at' => now()->subMinute(),
    ]);
    $draftTestimonial = Testimonial::factory()->for($package)->create([
        'is_published' => false,
        'created_at' => now()->subMinutes(4),
    ]);

    $this->actingAs($staff)
        ->put(route('admin.testimonials.update', $draftTestimonial), [
            'package_id' => $package->id,
            'reviewer_name' => $draftTestimonial->reviewer_name,
            'rating' => $draftTestimonial->rating,
            'review' => $draftTestimonial->review,
            'is_published' => true,
        ])
        ->assertRedirect(route('admin.testimonials.index'));

    expect($oldestTestimonial->fresh()->is_published)->toBeFalse()
        ->and($draftTestimonial->fresh()->is_published)->toBeTrue()
        ->and(Testimonial::query()->published()->count())->toBe(3);
});

it('keeps the featured video and official social profiles in their own management module', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->put(route('admin.media-social.update'), [
            'featured_video_title' => 'Panduan persediaan Umrah',
            'featured_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'instagram_url' => '@salamhaji',
            'threads_url' => '@salamhaji',
        ])
        ->assertRedirect(route('admin.media-social.edit'));

    expect(WebsiteSetting::getValue('featured_video_title'))->toBe('Panduan persediaan Umrah')
        ->and(WebsiteSetting::getValue('featured_video_path'))->toBeNull();
    expect(WebsiteSetting::getValue('instagram_url'))->toBe('https://www.instagram.com/salamhaji');
    expect(WebsiteSetting::getValue('threads_url'))->toBe('https://www.threads.com/@salamhaji');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ?rel=0', false)
        ->assertSee('href="https://www.instagram.com/salamhaji" target="_blank"', false)
        ->assertSee('href="https://www.threads.com/@salamhaji" target="_blank"', false)
        ->assertSee('@salamhaji')
        ->assertDontSee('data-social-post', false);
});

it('requires staff to remove a featured video before replacing it with an uploaded file', function () {
    Storage::fake('public');

    $staff = User::factory()->staff()->create();
    WebsiteSetting::setValue('featured_video_url', 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'media');

    $this->actingAs($staff)
        ->put(route('admin.media-social.update'), [
            'featured_video_file' => UploadedFile::fake()->create('panduan.mp4', 1024, 'video/mp4'),
        ])
        ->assertSessionHasErrors([
            'featured_video_file' => 'Keluarkan video semasa terlebih dahulu sebelum memilih video baharu.',
        ]);

    expect(WebsiteSetting::getValue('featured_video_url'))->toBe('https://www.youtube.com/watch?v=dQw4w9WgXcQ');

    $this->actingAs($staff)
        ->put(route('admin.media-social.update'), [
            'featured_video_file' => UploadedFile::fake()->create('panduan.mp4', 1024, 'video/mp4'),
            'remove_featured_video' => true,
        ])
        ->assertRedirect(route('admin.media-social.edit'));

    $videoPath = WebsiteSetting::getValue('featured_video_path');

    expect($videoPath)->toStartWith('homepage/video/')
        ->and(WebsiteSetting::getValue('featured_video_url'))->toBeNull();
    Storage::disk('public')->assertExists($videoPath);

    $this->actingAs($staff)
        ->get(route('admin.media-social.edit'))
        ->assertOk()
        ->assertSee('aria-label="Keluarkan video semasa"', false)
        ->assertSee('data-featured-video-file', false);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('<video class="ds-featured-video__frame" controls preload="metadata">', false)
        ->assertSee(WebsiteSetting::publicUrl($videoPath), false);

    $this->actingAs($staff)
        ->put(route('admin.media-social.update'), [
            'remove_featured_video' => true,
        ])
        ->assertRedirect(route('admin.media-social.edit'));

    expect(WebsiteSetting::getValue('featured_video_path'))->toBeNull()
        ->and(WebsiteSetting::getValue('featured_video_url'))->toBeNull();
    Storage::disk('public')->assertMissing($videoPath);
});

it('accepts YouTube links but rejects other featured video links', function () {
    $staff = User::factory()->staff()->create();

    $this->actingAs($staff)
        ->put(route('admin.media-social.update'), [
            'featured_video_url' => 'https://vimeo.com/123456789',
        ])
        ->assertSessionHasErrors([
            'featured_video_url' => 'Masukkan pautan video YouTube yang sah.',
        ]);

    expect(WebsiteSetting::getValue('featured_video_url'))->toBeNull();
});

it('renders only published mutawif profiles on the public page', function () {
    $published = Mutawif::factory()->create(['full_name' => 'Ustaz Ahmad', 'is_published' => true]);
    $draft = Mutawif::factory()->create(['full_name' => 'Ustaz Rahsia', 'is_published' => false]);

    $this->get(route('mutawifs.index'))
        ->assertOk()
        ->assertSee($published->full_name)
        ->assertDontSee($draft->full_name);
});

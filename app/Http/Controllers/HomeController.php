<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\WebsiteSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $packageRelations = [
            'category',
            'departures' => fn ($query) => $query
                ->where('status', 'open')
                ->whereDate('departure_date', '>=', today())
                ->orderBy('departure_date')
                ->limit(1),
        ];

        $heroPackages = Package::query()
            ->published()
            ->with($packageRelations)
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(5)
            ->get();

        if ($heroPackages->isEmpty()) {
            $heroPackages = Package::query()
                ->published()
                ->with($packageRelations)
                ->latest('published_at')
                ->take(5)
                ->get();
        }

        $secondaryPackages = Package::query()
            ->published()
            ->with($packageRelations)
            ->whereNotIn('id', $heroPackages->modelKeys())
            ->latest('published_at')
            ->take(8)
            ->get();

        $settings = WebsiteSetting::query()
            ->whereIn('key', [
                'contact_whatsapp',
                'company_name',
                'company_tagline',
                'homepage_company_intro',
                'homepage_package_heading',
                'homepage_hero_videos',
                'homepage_partner_logos',
                'homepage_about_images',
                'homepage_gallery_images',
                'homepage_service_categories',
                'homepage_campaign_title',
                'homepage_campaign_description',
                'homepage_campaign_link',
                'homepage_campaign_cta',
                'homepage_video_url',
                'homepage_video_cover',
                'instagram_url',
                'instagram_preview_image',
            ])
            ->pluck('value', 'key');

        $heroVideos = $this->heroVideos((string) $settings->get('homepage_hero_videos'));
        $galleryItems = $this->galleryItems((string) $settings->get('homepage_gallery_images'));
        $aboutImages = $this->imageItems((string) $settings->get('homepage_about_images'));
        $partnerLogos = $this->imageItems((string) $settings->get('homepage_partner_logos'));

        if ($aboutImages->isEmpty()) {
            $aboutImages = $heroPackages
                ->map(fn (Package $package): array => [
                    'url' => $package->imageUrl(),
                    'alt' => 'Suasana perjalanan '.$package->title,
                ])
                ->filter(fn (array $image): bool => filled($image['url']))
                ->values();
        }

        return view('home', [
            'categories' => PackageCategory::query()->active()->orderBy('sort_order')->get(),
            'heroPackages' => $heroPackages,
            'secondaryPackages' => $secondaryPackages,
            'heroVideos' => $heroVideos,
            'partnerLogos' => $partnerLogos,
            'aboutImages' => $aboutImages,
            'galleryItems' => $galleryItems,
            'serviceItems' => $this->serviceItems((string) $settings->get('homepage_service_categories')),
            'homepageSettings' => $settings,
            'whatsappNumber' => $settings->get('contact_whatsapp'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function heroVideos(string $value): array
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(function (string $line): array {
                [$packageSlug, $url] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

                return ['package_slug' => $packageSlug, 'url' => $url];
            })
            ->filter(fn (array $video): bool => filled($video['package_slug']) && $this->isPublicUrl($video['url']))
            ->mapWithKeys(fn (array $video): array => [$video['package_slug'] => $video['url']])
            ->all();
    }

    /**
     * @return Collection<int, array{thumbnail: string, full: string, alt: string}>
     */
    private function galleryItems(string $value): Collection
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(function (string $line): array {
                [$thumbnail, $full, $alt] = array_pad(array_map('trim', explode('|', $line, 3)), 3, '');

                return [
                    'thumbnail' => $thumbnail,
                    'full' => filled($full) ? $full : $thumbnail,
                    'alt' => $alt,
                ];
            })
            ->filter(fn (array $image): bool => $this->isPublicUrl($image['thumbnail']) && $this->isPublicUrl($image['full']))
            ->take(24)
            ->values();
    }

    /**
     * @return Collection<int, array{url: string, alt: string}>
     */
    private function imageItems(string $value): Collection
    {
        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(function (string $line): array {
                [$url, $alt] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');

                return ['url' => $url, 'alt' => $alt];
            })
            ->filter(fn (array $image): bool => $this->isPublicUrl($image['url']))
            ->take(12)
            ->values();
    }

    /**
     * @return Collection<int, array{icon: string, label: string, url: string}>
     */
    private function serviceItems(string $value): Collection
    {
        $icons = [
            'HAJI' => 'mosque',
            'UMRAH' => 'flight_takeoff',
            'PELANCONGAN' => 'travel_explore',
            'TAKAFUL PERJALANAN' => 'health_and_safety',
            'UMRAH VIP' => 'workspace_premium',
            'PERTANYAAN' => 'support_agent',
        ];

        $items = collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(function (string $line) use ($icons): ?array {
                [$label, $destination] = array_pad(array_map('trim', explode('|', $line, 2)), 2, '');
                $label = str($label)->upper()->toString();

                if (! array_key_exists($label, $icons)) {
                    return null;
                }

                $url = match ($destination) {
                    'packages' => route('packages.index'),
                    'contact' => route('contact.create'),
                    'whatsapp' => '#whatsapp-cta',
                    default => $this->isPublicUrl($destination) ? $destination : null,
                };

                if ($url === null) {
                    return null;
                }

                return ['icon' => $icons[$label], 'label' => $label, 'url' => $url];
            })
            ->filter()
            ->take(6)
            ->values();

        return $items->isNotEmpty()
            ? $items
            : collect([
                ['icon' => $icons['UMRAH'], 'label' => 'UMRAH', 'url' => route('packages.index')],
                ['icon' => $icons['PERTANYAAN'], 'label' => 'PERTANYAAN', 'url' => route('contact.create')],
            ]);
    }

    private function isPublicUrl(string $url): bool
    {
        return Str::startsWith($url, '/storage/')
            || filter_var($url, FILTER_VALIDATE_URL) !== false
                && Str::startsWith($url, ['http://', 'https://']);
    }
}

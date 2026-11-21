<?php

namespace App\Providers;

use App\Models\ImageFocus;
use App\Models\PackageCategory;
use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());

        Gate::define('access-admin', fn (User $user): bool => $user->isStaff());

        RateLimiter::for('admin-login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower($request->input('email', '')).'|'.$request->ip());
        });

        RateLimiter::for('admin-password-reset', function (Request $request): Limit {
            return Limit::perMinute(3)->by(strtolower($request->input('email', '')).'|'.$request->ip());
        });

        RateLimiter::for('booking', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('service-enquiry', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return route('admin.password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
        });

        view()->composer(['layouts.app', 'layouts.admin', 'darul-shofi.layout', 'darul-shofi.home', 'darul-shofi.about', 'darul-shofi.gallery', 'auth.admin-login', 'faq'], function (View $view): void {
            $request = request();
            $sharedSiteViewData = $request->attributes->get('shared_site_view_data');

            if ($sharedSiteViewData === null) {
                $sharedSiteViewData = $this->sharedSiteViewData();
                $request->attributes->set('shared_site_view_data', $sharedSiteViewData);
            }

            $view->with($sharedSiteViewData);
        });

        view()->composer('*', function (View $view): void {
            $request = request();
            $imageFocusPositions = $request->attributes->get('image_focus_positions');

            if ($imageFocusPositions === null) {
                $imageFocusPositions = Schema::hasTable('image_foci')
                    ? ImageFocus::positions()
                    : [];
                $request->attributes->set('image_focus_positions', $imageFocusPositions);
            }

            $view->with('imageFocusPositions', $imageFocusPositions);
        });
    }

    /**
     * Site settings and footer categories shared by the public and admin layouts.
     *
     * Memoised for the request because several composed views on one page need the same data.
     *
     * @return array{siteSettings: array<string, string|null>, footerCategories: Collection<int, PackageCategory>}
     */
    private function sharedSiteViewData(): array
    {
        $defaults = WebsiteSetting::siteDefaults();
        $settings = WebsiteSetting::query()
            ->whereIn('key', array_keys($defaults))
            ->pluck('value', 'key')
            ->all();

        $siteSettings = array_merge($defaults, $settings);
        $siteSettings['company_logo_url'] = WebsiteSetting::publicUrl($siteSettings['company_logo_path'])
            ?? $siteSettings['company_logo_url'];
        $faviconUrl = WebsiteSetting::publicUrl($siteSettings['company_favicon_path'])
            ?? $siteSettings['company_favicon_url'];
        $siteSettings['company_favicon_url'] = filled($faviconUrl)
            ? $faviconUrl
            : $defaults['company_favicon_url'];

        $appleTouchIconUrl = WebsiteSetting::publicUrl($siteSettings['company_apple_touch_icon_path'])
            ?? $siteSettings['company_apple_touch_icon_url'];
        $siteSettings['company_apple_touch_icon_url'] = filled($appleTouchIconUrl)
            ? $appleTouchIconUrl
            : $defaults['company_apple_touch_icon_url'];
        $siteSettings['footer_logo_url'] = WebsiteSetting::publicUrl($siteSettings['footer_logo_path']);
        $siteSettings['featured_video_upload_url'] = WebsiteSetting::publicUrl($siteSettings['featured_video_path']);
        $siteSettings['featured_video_embed_url'] = WebsiteSetting::featuredVideoEmbedUrl($siteSettings['featured_video_url']);
        $siteSettings['threads_url'] = WebsiteSetting::threadsComUrl($siteSettings['threads_url']);

        return [
            'siteSettings' => $siteSettings,
            'footerCategories' => PackageCategory::query()->active()->orderBy('sort_order')->get(['name', 'slug']),
        ];
    }
}

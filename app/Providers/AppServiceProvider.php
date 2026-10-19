<?php

namespace App\Providers;

use App\Models\User;
use App\Models\WebsiteSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        RateLimiter::for('enquiry', function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });

        view()->composer(['layouts.app', 'layouts.admin', 'contact'], function (View $view): void {
            $defaults = [
                'company_name' => 'Darul Shofi',
                'company_tagline' => 'Perjalanan ibadah, diurus dengan tenang.',
                'company_logo_url' => '',
                'contact_phone' => '',
                'contact_email' => '',
                'contact_whatsapp' => '',
                'office_address' => '',
                'office_hours' => '',
                'facebook_url' => '',
                'instagram_url' => '',
                'privacy_url' => '',
                'terms_url' => '',
            ];

            $settings = WebsiteSetting::query()
                ->whereIn('key', array_keys($defaults))
                ->pluck('value', 'key')
                ->all();

            $view->with('siteSettings', array_merge($defaults, $settings));
        });
    }
}

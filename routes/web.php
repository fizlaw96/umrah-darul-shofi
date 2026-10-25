<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartureController as AdminDepartureController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaSocialController;
use App\Http\Controllers\Admin\MutawifController as AdminMutawifController;
use App\Http\Controllers\Admin\PackageCategoryController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\PackageNotificationController;
use App\Http\Controllers\Admin\PackageParticipantController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\MutawifController as PublicMutawifController;
use App\Http\Controllers\Public\PackageController;
use App\Http\Controllers\Public\PageController;
use App\Models\Package;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/pakej-umrah', [PackageController::class, 'index'])->name('packages.index');
Route::get('/pakej-umrah/{package:slug}', [PackageController::class, 'show'])->name('packages.show');

Route::get('/tempahan/{package:slug}', [BookingController::class, 'create'])->name('bookings.create');
Route::post('/tempahan', [BookingController::class, 'store'])->middleware('throttle:enquiry')->name('bookings.store');
Route::get('/tempahan/sah/{booking:reference}', [BookingController::class, 'confirmation'])->name('bookings.confirmation');

Route::get('/tentang-kami', [PageController::class, 'about'])->name('about');
Route::get('/soalan-lazim', [PageController::class, 'faq'])->name('faq');
Route::get('/mutawif', [PublicMutawifController::class, 'index'])->name('mutawifs.index');
Route::get('/terma-syarat', [PageController::class, 'terms'])->name('terms');

Route::get('/robots.txt', function (): Response {
    return response("User-agent: *\nAllow: /\nSitemap: ".route('sitemap'), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/sitemap.xml', function (): Response {
    return response()->view('sitemap', [
        'packages' => Package::query()->published()->get(['slug', 'updated_at']),
    ], 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn (): RedirectResponse => redirect()->route('admin.login'))->name('login');
    Route::post('/admin/login', [AdminLoginController::class, 'store'])->middleware('throttle:admin-login')->name('admin.login.store');

    Route::get('/admin/lupa-kata-laluan', [ForgotPasswordController::class, 'create'])->name('admin.password.request');
    Route::post('/admin/lupa-kata-laluan', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:admin-password-reset')
        ->name('admin.password.email');
    Route::get('/admin/tetap-semula-kata-laluan/{token}', [ResetPasswordController::class, 'create'])->name('admin.password.reset');
    Route::post('/admin/tetap-semula-kata-laluan', [ResetPasswordController::class, 'store'])
        ->middleware('throttle:admin-password-reset')
        ->name('admin.password.update');
});

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');

Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])->middleware('auth')->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/notifications/{notification}', [PackageNotificationController::class, 'show'])->name('package-notifications.show');
    Route::post('/packages/bulk', [AdminPackageController::class, 'bulk'])->name('packages.bulk');
    Route::post('/packages/{package}/autosave', [AdminPackageController::class, 'autosave'])->name('packages.autosave');
    Route::get('/packages/{package}/preview', [AdminPackageController::class, 'preview'])->name('packages.preview');
    Route::put('/packages/{package}/restore', [AdminPackageController::class, 'restore'])->name('packages.restore');
    Route::delete('/packages/{package}/cover-image', [AdminPackageController::class, 'destroyCoverImage'])->name('packages.cover-image.destroy');
    Route::delete('/packages/{package}/images/{image}', [AdminPackageController::class, 'destroyImage'])->name('packages.images.destroy');
    Route::get('/packages/{package}/participants/export', [PackageParticipantController::class, 'export'])->name('packages.participants.export');
    Route::post('/packages/{package}/participants', [PackageParticipantController::class, 'store'])->name('packages.participants.store');
    Route::delete('/packages/{package}/participants/{participant}', [PackageParticipantController::class, 'destroy'])->name('packages.participants.destroy');
    Route::resource('packages', AdminPackageController::class)->except(['show']);
    Route::get('/packages/{package}', [PackageParticipantController::class, 'index'])->name('packages.show');
    Route::resource('package-categories', PackageCategoryController::class)->except(['show']);
    Route::resource('hero-slides', HeroSlideController::class)->only(['index', 'edit']);
    Route::resource('media', MediaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('/media-social', [MediaSocialController::class, 'edit'])->name('media-social.edit');
    Route::put('/media-social', [MediaSocialController::class, 'update'])->name('media-social.update');
    Route::resource('testimonials', TestimonialController::class);
    Route::resource('mutawifs', AdminMutawifController::class);
    Route::resource('departures', AdminDepartureController::class);
    Route::resource('bookings', AdminBookingController::class)->except(['store']);
    Route::resource('enquiries', AdminEnquiryController::class)->except(['store']);
    Route::resource('articles', AdminArticleController::class);
    Route::resource('faqs', AdminFaqController::class);
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

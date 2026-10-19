<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartureController as AdminDepartureController;
use App\Http\Controllers\Admin\EnquiryController as AdminEnquiryController;
use App\Http\Controllers\Admin\FaqController as AdminFaqController;
use App\Http\Controllers\Admin\PackageController as AdminPackageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Public\BookingController;
use App\Http\Controllers\Public\ContactController;
use App\Http\Controllers\Public\GuideController;
use App\Http\Controllers\Public\PackageController;
use App\Http\Controllers\Public\PageController;
use App\Models\Article;
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
Route::get('/hubungi-kami', [ContactController::class, 'create'])->name('contact.create');
Route::post('/hubungi-kami', [ContactController::class, 'store'])->middleware('throttle:enquiry')->name('contact.store');
Route::get('/panduan', [GuideController::class, 'index'])->name('guides.index');
Route::get('/panduan/{article:slug}', [GuideController::class, 'show'])->name('guides.show');
Route::get('/soalan-lazim', [PageController::class, 'faq'])->name('faq');

Route::get('/robots.txt', function (): Response {
    return response("User-agent: *\nAllow: /\nSitemap: ".route('sitemap'), 200, ['Content-Type' => 'text/plain']);
})->name('robots');

Route::get('/sitemap.xml', function (): Response {
    return response()->view('sitemap', [
        'packages' => Package::query()->published()->get(['slug', 'updated_at']),
        'articles' => Article::query()->published()->get(['slug', 'updated_at']),
    ], 200, ['Content-Type' => 'application/xml']);
})->name('sitemap');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', fn (): RedirectResponse => redirect()->route('admin.login'))->name('login');
    Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
    Route::post('/admin/login', [AdminLoginController::class, 'store'])->middleware('throttle:admin-login')->name('admin.login.store');
});

Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])->middleware('auth')->name('admin.logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function (): void {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::resource('packages', AdminPackageController::class);
    Route::resource('departures', AdminDepartureController::class);
    Route::resource('bookings', AdminBookingController::class)->except(['store']);
    Route::resource('enquiries', AdminEnquiryController::class)->except(['store']);
    Route::resource('articles', AdminArticleController::class);
    Route::resource('faqs', AdminFaqController::class);
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
});

<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\AppConfigController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CMSController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ErrorLogController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\GeneralSettingsController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RegencyController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PwaController;
use App\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicController::class, 'tour'])->name('index');
Route::get('/home', function () {
    return redirect()->route('index');
})->name('home');

// Auth routes
Route::get('/login', [WebAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
// Pendaftaran publik sengaja TIDAK disediakan: situs ini tidak punya area
// pelanggan, akun hanya dibuat admin lewat panel (users.*).

// API Sync (Realtime without Supabase)
Route::get('/api/sync/version', [SyncController::class, 'getVersion'])->name('api.sync.version');

// PWA Android (.apk) packaging — public endpoints used by PWABuilder & the TWA wrapper.
// The app itself still requires login; these only expose the manifest/asset links needed to build the APK.
Route::get('/admin-app', fn () => view('admin.install'))->name('pwa.install');

Route::get('/admin-app/manifest.webmanifest', [PwaController::class, 'manifestPublic'])->name('pwa.manifest.public');

// Digital Asset Links — verifies the APK owns this domain so the TWA opens without a URL bar.
Route::get('/.well-known/assetlinks.json', [PwaController::class, 'assetLinks'])->name('pwa.assetlinks');

// Admin Group
Route::middleware(['auth', 'role:superadmin,admin_tour,admin_umum'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Packages
    Route::get('packages/export', [PackageController::class, 'export'])->name('packages.export');
    Route::post('packages/bulk-destroy', [PackageController::class, 'bulkDestroy'])->name('packages.bulk-destroy');
    Route::post('packages/{package}/toggle-status', [PackageController::class, 'toggleStatus'])->name('packages.toggle-status');
    Route::post('packages/{package}/duplicate', [PackageController::class, 'duplicate'])->name('packages.duplicate');
    Route::resource('packages', PackageController::class);

    // Bookings
    Route::get('bookings/export', [BookingController::class, 'export'])->name('bookings.export');
    Route::post('bookings/bulk-destroy', [BookingController::class, 'bulkDestroy'])->name('bookings.bulk-destroy');
    Route::get('bookings/{booking}/invoice', [PdfController::class, 'streamInvoice'])->name('bookings.invoice');
    Route::get('bookings/{booking}/invoice/download', [PdfController::class, 'downloadInvoice'])->name('bookings.invoice.download');
    Route::patch('bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::resource('bookings', BookingController::class);

    // Blogs
    Route::get('blogs/export', [BlogController::class, 'export'])->name('blogs.export');
    Route::post('blogs/bulk-destroy', [BlogController::class, 'bulkDestroy'])->name('blogs.bulk-destroy');
    Route::resource('blogs', BlogController::class);

    // Gallery (Public Photo Gallery)
    Route::get('gallery/export', [GalleryController::class, 'export'])->name('gallery.export');
    Route::post('gallery/bulk-destroy', [GalleryController::class, 'bulkDestroy'])->name('gallery.bulk-destroy');
    Route::post('gallery/store-from-media', [GalleryController::class, 'storeFromMedia'])->name('gallery.store-from-media');
    Route::post('gallery/{gallery}/toggle-status', [GalleryController::class, 'toggleStatus'])->name('gallery.toggle-status');
    Route::get('galleries', [GalleryController::class, 'index'])->name('galleries.index');
    Route::resource('gallery', GalleryController::class)->except(['create', 'show']);

    // CMS Management
    Route::get('/cms-halaman-utama', [CMSController::class, 'index'])->name('cms.index');
    Route::get('/cms-beranda-tour', [CMSController::class, 'tour'])->name('cms.tour');
    Route::get('/cms-halaman-statis', [CMSController::class, 'pages'])->name('cms.pages');
    Route::post('/cms-save/{key}', [CMSController::class, 'save'])->name('cms.save');

    // Financial Reports (Restricted)
    Route::middleware('role:superadmin,admin_umum')->group(function () {
        Route::get('/finance', [FinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/export', [FinanceController::class, 'export'])->name('finance.export');

        Route::get('/reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('/reports/financial/export', [ReportController::class, 'export'])->name('reports.financial.export');
    });

    // Media Library (Global Storage)
    Route::post('media/batch', [MediaController::class, 'batch'])->name('media.batch');
    Route::post('media/sync', [MediaController::class, 'sync'])->name('media.sync');
    Route::post('media/sync-public-assets', [MediaController::class, 'syncPublicAssets'])->name('media.sync-public-assets');
    Route::post('media/move', [MediaController::class, 'move'])->name('media.move');
    Route::post('media/rename-folder', [MediaController::class, 'renameFolder'])->name('media.rename-folder');
    Route::post('media/{media}/rename', [MediaController::class, 'rename'])->name('media.rename');
    Route::post('media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulk-delete');
    Route::post('media/bulk-download', [MediaController::class, 'bulkDownload'])->name('media.bulk-download');
    Route::post('media/convert-all', [MediaController::class, 'convertAll'])->name('media.convert-all');
    Route::post('media/upload-from-url', [MediaController::class, 'storeFromUrl'])->name('media.upload-url');
    Route::get('media/audit', [MediaController::class, 'audit'])->name('media.audit');
    Route::post('media/audit/clean', [MediaController::class, 'cleanOrphanFiles'])->name('media.clean-orphans');
    Route::post('media/{media}/crop', [MediaController::class, 'crop'])->name('media.crop');
    Route::post('media/search', [MediaController::class, 'index'])->name('media.search');
    Route::resource('media', MediaController::class)->parameters(['media' => 'media'])->except(['create', 'show', 'edit']);

    // Region Data
    Route::get('cities/regencies', [CityController::class, 'getRegencies'])->name('cities.regencies');
    Route::resource('cities', CityController::class)->except(['show']);
    Route::resource('regencies', RegencyController::class)->only(['index', 'edit', 'update']);

    // Customers & Clients
    Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
    Route::post('customers/bulk-destroy', [CustomerController::class, 'bulkDestroy'])->name('customers.bulk-destroy');
    Route::resource('customers', CustomerController::class);
    Route::resource('clients', ClientController::class)->only(['index', 'store', 'destroy']);

    // PWA - Admin Panel Progressive Web App
    Route::get('/manifest.json', [PwaController::class, 'manifestAdmin'])->name('pwa.manifest');

    // PWA Offline page
    Route::get('/offline', [PwaController::class, 'offline'])->name('pwa.offline');

    // System Settings (Superadmin Only)
    Route::middleware('role:superadmin,admin_umum')->group(function () {
        Route::get('/settings/general', [GeneralSettingsController::class, 'index'])->name('settings.general.index');
        Route::post('/settings/general', [GeneralSettingsController::class, 'update'])->name('settings.general.update');
        Route::post('/settings/refresh-rates', [SettingController::class, 'refreshExchangeRates'])->name('settings.refresh-rates');

        // Application config. Superadmin only — deliberately narrower than the
        // surrounding group, which also admits admin_umum.
        Route::middleware('role:superadmin')->group(function () {
            Route::get('/settings/app-config', [AppConfigController::class, 'index'])->name('settings.app-config.index');
            Route::post('/settings/app-config', [AppConfigController::class, 'update'])->name('settings.app-config.update');
        });
        Route::get('logs', [ActivityLogController::class, 'index'])->name('logs.index');
        Route::get('error-logs', [ErrorLogController::class, 'index'])->name('error-logs.index');
        Route::post('error-logs/clear', [ErrorLogController::class, 'clear'])->name('error-logs.clear');
        Route::get('users/export', [UserController::class, 'export'])->name('users.export');
        Route::post('users/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');
        Route::resource('users', UserController::class);
    });
});

// Locale Route
Route::get('/change-locale/{locale}', [LocaleController::class, 'changeLocale'])->name('change-locale');

// Public Tour Routes
Route::prefix('tour')->name('tour.')->group(function () {
    Route::get('/', function () {
        return redirect()->route('index');
    })->name('index');
    Route::get('/packages', [PublicController::class, 'tourPackages'])->name('packages');
    Route::get('/gallery', [PublicController::class, 'tourGallery'])->name('gallery');
    Route::get('/blog', [PublicController::class, 'tourBlog'])->name('blog');
    Route::get('/package/{slug}', [PublicController::class, 'tourPackageDetail'])->name('package.detail');
    // Halaman detail yang sama, tanpa form pemesanan. Satu controller & satu
    // view dengan flag -- menyalin view 1.000+ baris menjamin dua halaman ini
    // melenceng, dan setiap perbaikan harus dikerjakan dua kali.
    Route::get('/detail/{slug}', [PublicController::class, 'tourPackageDetailNoForm'])->name('package.detail.plain');
    Route::get('/blog/{slug}', [PublicController::class, 'tourBlogDetail'])->name('blog.detail');

    // Booking with Rate Limiting
    Route::post('/booking/submit', [PublicController::class, 'submitBooking'])
        ->middleware('throttle:5,1')
        ->name('booking.submit');
});

// Programmatic SEO Landing Pages
Route::get('/paket-wisata-danau-toba-dari-{kota}', [PublicController::class, 'landingOrigin'])->name('landing.origin');

// Other Public Pages
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/payment', [PublicController::class, 'payment'])->name('payment');
Route::post('/outbound/quote/submit', [PublicController::class, 'submitOutboundQuote'])->middleware('throttle:5,1')->name('outbound.quote.submit');

// Invoice & Itinerary
// Kode booking dibatasi lajunya: tanpa ini, kode 6 karakter bisa ditebak massal.
Route::get('/invoice/{code}', [PdfController::class, 'streamInvoice'])->middleware('throttle:30,1')->name('invoice.download');
Route::get('/download-itinerary/{slug}', [PdfController::class, 'downloadItinerary'])->name('itinerary.download');
Route::get('/track-booking', [PublicController::class, 'showTrackBookingForm'])->name('booking.track.form');
Route::post('/track-booking', [PublicController::class, 'redirectTrackBooking'])->middleware('throttle:20,1')->name('booking.track.lookup');
Route::get('/track-booking/{code}', [PublicController::class, 'trackBooking'])->middleware('throttle:30,1')->name('booking.track');

// Dynamic OpenGraph Banners
Route::get('/og-banner/{type}/{id}.webp', [PublicController::class, 'generateOgBanner'])->name('og-banner');

// Sitemap
Route::get('/sitemap.xml', [SettingController::class, 'generateSitemap']);




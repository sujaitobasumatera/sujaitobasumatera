<?php

namespace App\Providers;

use App\Models\Blog;
use App\Models\Booking;
use App\Models\Package;
use App\Models\Setting;
use App\Observers\BlogObserver;
use App\Observers\BookingObserver;
use App\Observers\PackageObserver;
use App\Observers\SettingObserver;
use App\Repositories\BookingRepository;
use App\Services\AppConfigService;
use App\Services\BookingService;
use App\Services\DashboardService;
use App\Services\TourService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * 
     * 
     * Register any application services.
     */
    public function register(): void
    {
        // Register Services
        $this->app->singleton(DashboardService::class);
        $this->app->singleton(BookingService::class);

        // Register Repositories
        $this->app->singleton(BookingRepository::class);
    }

    public function boot(): void
    {
        // NOTE: No storage symlink is created. The 'public' disk writes directly into
        // public/storage (see config/filesystems.php), which is the folder the web server
        // serves at /storage. This avoids the unreliable symlink on shared hosting.

        // Register Observers
        Package::observe(PackageObserver::class);
        Blog::observe(BlogObserver::class);
        Booking::observe(BookingObserver::class);
        Setting::observe(SettingObserver::class);

        // Dropdown navbar membaca katalog paket yang sebenarnya, jadi menu tidak
        // pernah menjanjikan halaman yang tidak ada. Cache-nya dibersihkan oleh
        // PackageObserver -> TourService::clearCache().
        View::composer('layouts.partials.navbar', function ($view) {
            $view->with('navPackages', (new TourService)->getNavPackages());
        });

        // Share settings globally
        if (!$this->app->runningInConsole()) {
            try {
                // Point 4: Caching Site Settings - Automatically cleared on saved() via Observer
                $decodedSettings = Cache::rememberForever('site_settings_global', function () {
                    $settings = Setting::query()
                        ->select(['key', 'value'])
                        ->get()
                        ->mapWithKeys(fn($setting) => [$setting->key => $setting->value])
                        ->toArray();

                    $decoded = [];
                    foreach ($settings as $key => $value) {
                        if (is_array($value)) {
                            $decoded[$key] = $value;
                        } else {
                            $decodedValue = json_decode($value, true);
                            $decoded[$key] = (json_last_error() === JSON_ERROR_NONE) ? $decodedValue : $value;
                        }
                    }

                    return $decoded;
                });

                view()->share('siteSettings', $decodedSettings);

                // Admin-editable config, layered over .env. Fed from the cache
                // above so this costs no extra query. See config/editable.php —
                // credentials are excluded there and enforced in the service.
                AppConfigService::apply($decodedSettings[AppConfigService::STORAGE_KEY] ?? null);

                // Override Mail Config from Database Settings
                if (isset($decodedSettings['mail'])) {
                    $mail = $decodedSettings['mail'];
                    config([
                        'mail.mailers.smtp.host' => $mail['host'] ?? config('mail.mailers.smtp.host'),
                        'mail.mailers.smtp.port' => $mail['port'] ?? config('mail.mailers.smtp.port'),
                        'mail.mailers.smtp.encryption' => ($mail['encryption'] ?? 'none') === 'none' ? null : ($mail['encryption'] ?? config('mail.mailers.smtp.encryption')),
                        'mail.mailers.smtp.username' => $mail['username'] ?? config('mail.mailers.smtp.username'),
                        'mail.mailers.smtp.password' => $mail['password'] ?? config('mail.mailers.smtp.password'),
                        'mail.from.address' => $mail['from_address'] ?? config('mail.from.address'),
                        'mail.from.name' => $mail['from_name'] ?? config('mail.from.name'),
                    ]);

                    if (isset($mail['driver'])) {
                        config(['mail.default' => $mail['driver']]);
                    }
                }

                // Pending-bookings count for the admin notification bell. Only the
                // admin layout renders it, so scope the query to admin routes instead
                // of running a COUNT on every public visitor's request.
                // Di-cache 60 detik — cukup cepat untuk terasa real-time tapi tidak
                // memukul DB setiap klik di panel admin. BookingObserver sudah
                // membersihkan 'pending_bookings_count' ketika status booking berubah.
                if (! $this->app->runningInConsole() && request()->is('admin*')) {
                    $pendingCount = Cache::remember('pending_bookings_count', 60, function () {
                        return Booking::where('status', 'pending')->count();
                    });
                    view()->share('pendingBookingsCount', $pendingCount);
                }
            } catch (\Exception $e) {
                // Silently fail if DB not ready
            }
        }
    }
}

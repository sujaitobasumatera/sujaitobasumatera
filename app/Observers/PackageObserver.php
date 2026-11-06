<?php

namespace App\Observers;

use App\Models\Package;
use App\Services\OgBannerService;
use App\Services\TourService;
use Illuminate\Support\Facades\Cache;

class PackageObserver
{
    public function saved(Package $package)
    {
        (new TourService)->clearCache($package->slug);
        (new OgBannerService)->forget('package', $package->id);
        Cache::forget('admin_dashboard_stats');
    }

    public function deleted(Package $package)
    {
        (new TourService)->clearCache($package->slug);
        (new OgBannerService)->forget('package', $package->id);
        Cache::forget('admin_dashboard_stats');
    }

    public function restored(Package $package)
    {
        (new TourService)->clearCache($package->slug);
        (new OgBannerService)->forget('package', $package->id);
        Cache::forget('admin_dashboard_stats');
    }
}

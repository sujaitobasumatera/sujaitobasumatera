<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OgBannerCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_changing_a_package_price_discards_its_social_banner(): void
    {
        // Banner OG membekukan harga ke dalam file .webp dan memakainya selamanya.
        // Tanpa pembatalan ini, kartu yang muncul saat paket dibagikan di
        // WhatsApp masih memajang harga lama tanpa ada gejala di halamannya.
        Storage::fake('public');

        $package = Package::create([
            'slug' => 'banner-package',
            'name' => 'Banner Package',
            'shortDescription' => 'Short desc',
            'description' => 'Full description',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'pricingDetails' => [],
            'itinerary' => [],
            'translations' => [],
            'price' => 750000,
            'duration' => '2 Hari',
            'status' => 'active',
        ]);

        $banner = "og-banners/package_{$package->id}.webp";
        Storage::disk('public')->put($banner, 'banner-harga-lama');
        Storage::disk('public')->assertExists($banner);

        $package->update(['price' => 900000]);

        Storage::disk('public')->assertMissing($banner);
    }
}

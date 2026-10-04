<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\TourService;
use Illuminate\Support\Facades\Cache;

class SettingObserver
{
    public function saved(Setting $setting)
    {
        // Clear global site settings caches
        Cache::forget('site_settings_global');
        Cache::forget('site_settings_all');
        Cache::forget('tour_homepage_data');

        // Nomor kontak dipakai di hampir setiap halaman, jadi di-cache
        // terpisah. Tanpa baris ini, admin mengganti nomor WhatsApp dan
        // situs tetap menghubungi nomor lama tanpa gejala apa pun.
        Cache::forget('contact_whatsapp_digits');
        // Nomor kedua juga wajib di-flush — sebelumnya terlewat sehingga
        // perubahan nomor CS2 di admin tidak langsung terlihat di halaman publik.
        Cache::forget('contact_whatsapp_digits_2');

        // Nilai tukar MYR/SGD di-cache oleh CurrencyHelper::manualRates().
        // Bila admin mengubah kurs di Pengaturan > Keuangan, halaman harga
        // harus langsung merefleksikan nilai baru, bukan yang lama.
        Cache::forget('currency_manual_rates');

        // Mode maintenance di-cache 30 detik di CheckMaintenanceMode middleware.
        Cache::forget('maintenance_mode_status');

        // Clear ALL structured settings cache variants — mencakup setiap kombinasi
        // key yang dipakai PublicController::getSiteSettings(). Sebelumnya hanya
        // 4 kombinasi yang di-flush; bila controller menambah kombinasi baru
        // halaman bisa menampilkan data lama sampai TTL 3600 s habis.
        $structuredPatterns = [
            // PublicController::tour / tourPackageDetail / dll
            'site_settings_structured_cms_tour_general',
            // PublicController::landingOrigin / about / payment / privacy / terms
            'site_settings_structured_cms_landing_cms_tour_general',
            'site_settings_structured_cms_landing_general',
            // Fallback tanpa prefix cms
            'site_settings_structured_general',
            // Bila dipanggil dengan key tunggal
            'site_settings_structured_cms_tour',
            'site_settings_structured_cms_landing',
            'site_settings_structured_general',
        ];
        foreach (array_unique($structuredPatterns) as $key) {
            Cache::forget($key);
        }

        // Clear tour specific settings cache if relevant
        if (str_starts_with($setting->key, 'cms_') || str_starts_with($setting->key, 'page_')) {
            (new TourService)->clearCache();
        }
    }
}

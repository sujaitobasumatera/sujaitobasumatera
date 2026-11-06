<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GeneralSettingsController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $settings = Setting::where('key', 'general')->first();
        $general = $settings ? $settings->value : [];

        $companySetting = Setting::where('key', 'company')->first();
        $company = $companySetting ? $companySetting->value : [];

        return view('admin.settings.index', compact('general', 'company'));
    }

    public function update(Request $request)
    {
        // Branding uploads must be real images (the trait also enforces this, but validate
        // here for a clean error instead of an exception).
        $request->validate([
            'logo_light_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'logo_dark_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
            'icon_file' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:15360',
        ]);

        $data = $request->except(['_token', 'logo_light_file', 'logo_dark_file', 'icon_file', 'company']);

        $setting = Setting::firstOrNew(['key' => 'general']);
        $existing = $setting->value ?? [];

        // Handle File Uploads
        if ($request->hasFile('logo_light_file')) {
            $data['logo_light_url'] = $this->uploadAndIndex($request->file('logo_light_file'), 'branding', 'branding');
        }
        if ($request->hasFile('logo_dark_file')) {
            $data['logo_dark_url'] = $this->uploadAndIndex($request->file('logo_dark_file'), 'branding', 'branding');
        }
        if ($request->hasFile('icon_file')) {
            $data['icon_url'] = $this->uploadAndIndex($request->file('icon_file'), 'branding', 'branding');
        }

        // Handle Media Library Selections
        if ($request->filled('logo_light_url')) {
            $data['logo_light_url'] = $request->logo_light_url;
        }
        if ($request->filled('logo_dark_url')) {
            $data['logo_dark_url'] = $request->logo_dark_url;
        }
        if ($request->filled('icon_url')) {
            $data['icon_url'] = $request->icon_url;
        }

        $finalData = array_merge($existing, $data);
        $setting->value = $finalData;
        $setting->save();

        // Company / invoice identity is stored as its own settings group,
        // because PdfController & InvoiceService load it under the 'company' key.
        if ($request->has('company')) {
            $companySetting = Setting::firstOrNew(['key' => 'company']);
            $companySetting->value = array_merge($companySetting->value ?? [], $request->input('company'));
            $companySetting->save();
        }

        // JANGAN gunakan Cache::flush() — itu menghapus SELURUH cache termasuk sesi
        // pengguna bila driver cache dan session adalah Redis/Memcached yang sama.
        // SettingObserver sudah menangani ini ketika setting->save() dipanggil di
        // atas, tapi kita tetap eksplisit di sini sebagai safety net.
        foreach ([
            'site_settings_global', 'site_settings_all', 'tour_homepage_data',
            'contact_whatsapp_digits', 'contact_whatsapp_digits_2',
            'site_settings_structured_cms_tour_general',
            'site_settings_structured_cms_landing_cms_tour_general',
            'site_settings_structured_cms_landing_general',
            'site_settings_structured_general',
            'site_settings_structured_cms_tour',
            'site_settings_structured_cms_landing',
        ] as $cacheKey) {
            Cache::forget($cacheKey);
        }

        return back()->with('success', 'Pengaturan umum berhasil diperbarui!');
    }
}

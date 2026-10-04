<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\GalleryImage;
use App\Models\Package;
use App\Models\Setting;
use App\Traits\HandlesImageUploads;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CMSController extends Controller
{
    use HandlesImageUploads;

    public function index()
    {
        $settings = Setting::where('key', 'cms_landing')->first()?->value ?? [];

        return view('admin.cms.index', compact('settings'));
    }

    public function tour()
    {
        $settings = Setting::where('key', 'cms_tour')->first()?->value ?? [];

        // Fetch data for selection lists
        $packages = Package::where('status', 'active')->get();
        $blogs = Blog::where('status', 'published')->orderBy('createdAt', 'desc')->get();
        $gallery = GalleryImage::orderBy('orderPriority', 'asc')->get();

        // Featured-package picker data (moved out of the Blade view).
        $allTourPackages = Package::with('packageImages')
            ->where('status', 'active')
            ->orderBy('isFeatured', 'desc')
            ->orderBy('createdAt', 'desc')
            ->get();
        $pinnedIds = $settings['featured_package_ids'] ?? [];

        return view('admin.cms.tour', compact('settings', 'packages', 'blogs', 'gallery', 'allTourPackages', 'pinnedIds'));
    }

    public function pages()
    {
        $about = Setting::where('key', 'page_about')->first()?->value ?? [];
        $terms = Setting::where('key', 'page_terms')->first()?->value ?? [];
        $privacy = Setting::where('key', 'page_privacy')->first()?->value ?? [];

        return view('admin.cms.pages', compact('about', 'terms', 'privacy'));
    }

    public function save(Request $request, $key)
    {
        // SECURITY: {key} comes from the URL, so restrict it to the CMS/page settings this
        // controller owns. Without this, a low-privilege admin could POST to any key
        // (e.g. 'general', 'company') and overwrite global/company/SMTP settings.
        $allowedKeys = ['cms_landing', 'cms_tour', 'page_about', 'page_terms', 'page_privacy'];
        if (! in_array($key, $allowedKeys, true)) {
            abort(404);
        }

        try {
            DB::beginTransaction();

            // 1. Ambil data lama
            $setting = Setting::where('key', $key)->first();
            $existing = $setting ? ($setting->value ?? []) : [];

            // 2. Ambil data input baru (kecuali token)
            $data = $request->except(['_token', '_clear_if_empty']);

            // 2b. Daftar yang dikosongkan total tidak dikirim browser sama sekali.
            // Tanpa ini, menghapus semua slide/testimoni/pin akan terlihat "tidak tersimpan".
            foreach (explode(',', (string) $request->input('_clear_if_empty', '')) as $clearable) {
                $clearable = trim($clearable);
                if ($clearable !== '' && ! $request->has($clearable)) {
                    $data[$clearable] = [];
                }
            }

            // 3. Handle recursive file uploads (including nested arrays like slides)
            $processFiles = function ($files, &$targetData) use (&$processFiles) {
                foreach ($files as $key => $file) {
                    if (is_array($file)) {
                        if (! isset($targetData[$key])) {
                            $targetData[$key] = [];
                        }
                        $processFiles($file, $targetData[$key]);
                    } elseif ($file instanceof UploadedFile) {
                        $path = $this->uploadAndIndex($file, 'cms', 'cms_upload');

                        // Handle path convention for settings JSON
                        $savedPath = $path; // We'll keep path relative to storage

                        // If the field name ends with '_file', replace it with '_url'
                        $urlField = str_replace(['_file', '_upload'], '', $key);
                        if (! str_ends_with($urlField, '_url')) {
                            $urlField .= '_url';
                        }

                        $targetData[$urlField] = $savedPath;
                        unset($targetData[$key]);
                    }
                }
            };

            $allFiles = $request->allFiles();
            $processFiles($allFiles, $data);

            Log::info('CMS Saving Data for '.$key, ['data' => $data]);

            // 4. Merge data: data baru menimpa data lama.
            // Untuk array nested seperti homepage_slides, array_merge dangkal bisa merusak strukturnya.
            // Karena itu, kita deep-merge secara aman (minimal untuk nested arrays).
            $finalData = $existing;

            $deepMerge = function (&$target, $source) use (&$deepMerge) {
                foreach ((array) $source as $key => $value) {
                    if (is_array($value) && isset($target[$key]) && is_array($target[$key])) {
                        if (array_is_list($value)) {
                            $target[$key] = $value;
                        } else {
                            $deepMerge($target[$key], $value);
                        }
                    } else {
                        $target[$key] = $value;
                    }
                }
            };

            $deepMerge($finalData, $data);

            Log::info('CMS Final Merged Data', ['final' => $finalData]);

            // 5. Simpan ke database
            if ($setting) {
                $setting->value = $finalData;
                $setting->save();
            } else {
                Setting::create([
                    'key' => $key,
                    'value' => $finalData,
                ]);
            }

            DB::commit();

            // Clear related caches so frontend updates immediately.
            // CATATAN: SettingObserver::saved() juga dipanggil secara otomatis ketika
            // $setting->save() di atas dieksekusi, tapi kita tetap eksplisit di sini
            // supaya kode ini bisa dibaca tanpa harus tahu detail observer.
            // 'cms_tour_settings' sudah tidak dipakai — key yang benar adalah di bawah.
            foreach ([
                'site_settings_global', 'site_settings_all', 'tour_homepage_data',
                'contact_whatsapp_digits', 'contact_whatsapp_digits_2',
                'site_settings_structured_cms_tour_general',
                'site_settings_structured_cms_landing_cms_tour_general',
                'site_settings_structured_cms_landing_general',
                'site_settings_structured_general',
                'featured_packages', 'tour_packages_all', 'tour_packages_nav',
            ] as $cacheKey) {
                Cache::forget($cacheKey);
            }

            Log::alert("CMS SUCCESS: Saved '{$key}' with fields: ".implode(', ', array_keys($data)));

            SyncController::triggerSync();

            return back()->with('success', 'Perubahan berhasil diterbitkan ke halaman publik!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('CMS FATAL ERROR: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Gagal menyimpan: '.$e->getMessage());
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AppConfigService;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/**
 * Panel for the handful of application settings an admin may change without
 * server access. Credentials are not among them — see config/editable.php.
 */
class AppConfigController extends Controller
{
    use LogsActivity;

    public function index()
    {
        return view('admin.settings.app-config', [
            'fields' => AppConfigService::fields(),
            'values' => AppConfigService::current(),
            'denied' => config('editable.denied', []),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate(AppConfigService::rules());

        // Checkboxes are absent from the payload when unticked, so a boolean
        // left off the request would otherwise keep its old value forever.
        foreach (AppConfigService::fields() as $key => $field) {
            if (($field['type'] ?? 'text') === 'boolean') {
                $validated[$key] = $request->boolean($key);
            }
        }

        $before = AppConfigService::stored();
        $after = AppConfigService::save($validated);

        // The settings cache is dropped by SettingObserver; the compiled config
        // cache is not, and a stale one would make this change look like it did
        // nothing at all.
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            report($e);
        }

        $this->logActivity(
            'update',
            'app_config',
            null,
            ['before' => $before, 'after' => $after]
        );

        $warning = null;
        if (! empty($after['app_debug']) && app()->environment('production')) {
            $warning = 'Mode Debug menyala di lingkungan produksi. Pesan error kini menampilkan isi konfigurasi ke pengunjung — matikan kembali setelah selesai memeriksa.';
        }

        return redirect()
            ->route('admin.settings.app-config.index')
            ->with('success', 'Konfigurasi aplikasi tersimpan.')
            ->with('warning', $warning);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for admin, auth, and setup/diagnostic routes
        if ($request->is('admin*') || $request->is('login*') || $request->is('logout*') || $request->is('*sujai*')) {
            return $next($request);
        }

        try {
            // Di-cache 30 detik — maintenance mode jarang berubah, tidak perlu
            // hit DB setiap request. SettingObserver membuang key ini ketika
            // admin menyimpan pengaturan sistem.
            $maintenance = Cache::remember('maintenance_mode_status', 30, function () {
                $setting = Setting::where('key', 'system')->first();
                return $setting ? ($setting->value['maintenance_mode'] ?? '0') : '0';
            });

            if ($maintenance === '1') {
                return response()->view('errors.maintenance', [], 503);
            }
        } catch (\Exception $e) {
            // Silently skip if database or settings table is not ready yet
        }

        return $next($request);
    }
}

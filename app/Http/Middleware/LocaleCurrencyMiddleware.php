<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LocaleCurrencyMiddleware
{
    /**
     * Maps session locale codes to Carbon/language locale codes.
     * 'my' is used internally for Malaysia (MYR currency),
     * but Carbon uses 'ms' for Malay language.
     */
    private const CARBON_LOCALE_MAP = [
        'id' => 'id',   // Indonesia → Bahasa Indonesia
        'my' => 'ms',   // Malaysia  → Bahasa Melayu (bukan Myanmar!)
        'en' => 'en',   // English   → English
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Set default locale to Malaysia (MYR) if not yet set
        if (! session()->has('locale')) {
            session(['locale' => 'my']);
        }

        $locale = session('locale', 'my');

        // Set Laravel application locale
        app()->setLocale($locale);

        // Set Carbon locale dynamically based on session locale
        // 'my' (Malaysia) must map to 'ms' — NOT 'my' which is Myanmar in Carbon!
        $carbonLocale = self::CARBON_LOCALE_MAP[$locale] ?? 'id';
        Carbon::setLocale($carbonLocale);

        return $next($request);
    }
}

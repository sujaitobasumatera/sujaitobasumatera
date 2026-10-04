<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Package;
use App\Models\Setting;
use App\Traits\LogsActivity;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    use LogsActivity;

    public function generateSitemap(Request $request)
    {
        // POST = admin menyimpan salinan statis; GET publik dilayani dari cache.
        if ($request->isMethod('post')) {
            $xml = $this->buildSitemapXml();
            file_put_contents(public_path('sitemap.xml'), $xml);
            \Illuminate\Support\Facades\Cache::forget('sitemap_xml_v2');
            $this->logActivity('system', 'Generated new sitemap.xml');

            return response()->json(['message' => 'Sitemap.xml berhasil diperbarui dan disimpan di folder public!']);
        }

        $xml = \Illuminate\Support\Facades\Cache::remember('sitemap_xml_v2', 3600, fn () => $this->buildSitemapXml());

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Hanya URL KANONIK yang masuk sitemap.
     *
     * Halaman paket versi "-dari-{kota}" (paket x kota) sengaja DIKELUARKAN:
     * canonical-nya menunjuk ke paket induk, jadi mendaftarkannya di sini
     * mengirim sinyal yang bertentangan ke Google ("indeks ini" vs "abaikan
     * ini") dan menghabiskan crawl budget. Yang mewakili tiap kota asal adalah
     * landing page /paket-wisata-danau-toba-dari-{kota} -- berdiri sendiri,
     * ber-canonical diri sendiri -- dan itu yang didaftarkan.
     */
    private function buildSitemapXml(): string
    {
        $esc = fn (string $u) => htmlspecialchars($u, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $now = now()->format('Y-m-d');

        $entries = [];
        $add = function (string $loc, ?string $lastmod, string $freq, string $prio) use (&$entries, $esc) {
            $entries[] = '<url><loc>'.$esc($loc).'</loc>'
                .($lastmod ? '<lastmod>'.$lastmod.'</lastmod>' : '')
                .'<changefreq>'.$freq.'</changefreq><priority>'.$prio.'</priority></url>';
        };

        // Halaman statis. /tour hanya mengalihkan ke beranda, jadi tidak didaftarkan.
        $add(url('/'), $now, 'daily', '1.0');
        $add(route('tour.packages'), $now, 'daily', '0.9');
        $add(route('tour.gallery'), $now, 'weekly', '0.6');
        $add(route('tour.blog'), $now, 'weekly', '0.7');
        $add(route('about'), $now, 'monthly', '0.5');
        $add(route('payment'), $now, 'monthly', '0.4');
        $add(route('terms'), $now, 'yearly', '0.3');
        $add(route('privacy'), $now, 'yearly', '0.3');

        // Paket (kanonik)
        foreach (Package::where('status', 'active')->get() as $package) {
            $add(
                route('tour.package.detail', $package->slug),
                ($package->updatedAt ?? $package->createdAt)?->format('Y-m-d'),
                'weekly',
                '0.9'
            );
        }

        // Blog
        foreach (Blog::where('status', 'published')->get() as $blog) {
            $add(
                route('tour.blog.detail', $blog->slug),
                ($blog->updatedAt ?? $blog->createdAt)?->format('Y-m-d'),
                'monthly',
                '0.7'
            );
        }

        // Landing page kota asal (pSEO) -- satu per kota
        $general = Setting::where('key', 'general')->first();
        $originsString = $general?->value['seo_pseo_origins'] ?? 'jakarta, surabaya, bandung, bali, batam, palembang, makassar, semarang, yogyakarta, kuala-lumpur, singapore, penang, pekanbaru, padang, malaysia';
        $origins = array_values(array_unique(array_filter(array_map(
            fn ($o) => str_replace(' ', '-', trim(strtolower($o))),
            explode(',', $originsString)
        ))));

        foreach ($origins as $kota) {
            $add(route('landing.origin', $kota), $now, 'weekly', '0.7');
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
            .implode('', $entries)
            .'</urlset>';
    }

    public function refreshExchangeRates(Request $request)
    {
        try {
            $setting = Setting::where('key', 'general')->first();
            $apiKey = $setting->value['finance']['exchange_rate_api_key'] ?? '';

            if (empty($apiKey)) {
                return response()->json(['error' => 'API Key ExchangeRate-API belum disetel di pengaturan.'], 400);
            }

            $response = \Illuminate\Support\Facades\Http::get("https://v6.exchangerate-api.com/v6/{$apiKey}/latest/MYR");
            if (!$response->successful()) {
                return response()->json(['error' => 'Gagal menghubungi API kurs. Periksa API key.'], 400);
            }

            $data = $response->json();
            $myrToIdr = $data['conversion_rates']['IDR'] ?? null;
            $myrToSgd = $data['conversion_rates']['SGD'] ?? null;

            if (!$myrToIdr || !$myrToSgd) {
                return response()->json(['error' => 'Data kurs tidak valid dari API.'], 400);
            }

            $sgdToIdr = $myrToIdr / $myrToSgd; // Deriving SGD from MYR data

            return response()->json([
                'MYR' => round($myrToIdr, 2),
                'SGD' => round($sgdToIdr, 2)
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error refreshing exchange rates: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem saat mengambil data kurs.'], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Blog;
use App\Models\City;
use App\Models\Client;
use App\Models\GalleryImage;
use App\Models\Package;
use App\Models\Setting;
use App\Services\BookingService;
use App\Services\OgBannerService;
use App\Services\TourService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class PublicController extends Controller
{
    public function __construct(
        protected TourService $tourService,
        protected BookingService $bookingService
    ) {}

    /**
     * Helper: Get structured site settings.
     * Note: $siteSettings is ALSO shared globally via AppServiceProvider View Composer,
     * but some methods need the structured array for logic (not just views).
     */
    private function getSiteSettings(array $keys = ['cms_tour', 'general']): array
    {
        return Cache::remember('site_settings_structured_'.implode('_', $keys), 3600, function () use ($keys) {
            $settings = [];
            foreach ($keys as $key) {
                $settings[$key] = Setting::where('key', $key)->first()?->value ?? [];
            }

            return $settings;
        });
    }

    /**
     * Tour & Travel Main Page — Cached for performance.
     */
    public function tour()
    {
        try {
            $siteSettings = $this->getSiteSettings(['cms_tour', 'general']);
            $settings = $siteSettings['cms_tour'];

            // Cache homepage data for 10 minutes
            $homeData = Cache::remember('tour_homepage_data', 600, function () {
                $gallerySlides = GalleryImage::where('isActive', true)
                    ->orderBy('orderPriority')
                    ->take(12)
                    ->get()
                    ->map(fn ($img) => [
                        'url' => $img->imageUrl,
                        'caption' => $img->caption ?? '',
                        'category' => $img->category ?? '',
                    ])
                    ->values()
                    ->toArray();

                return [
                    'packages' => $this->tourService->getFeaturedPackages(),
                    'blogs' => $this->tourService->getBlogs(3),
                    'gallerySlides' => $gallerySlides,
                ];
            });

            $packages = $homeData['packages'];
            $blogs = $homeData['blogs'];
            $gallerySlides = $homeData['gallerySlides'];

            // Translate only lightweight fields (title, excerpt, category) — NOT full content
            $blogs->each(function ($b) {
                $b->title = __($b->title);
                $b->excerpt = __($b->excerpt);
                $b->category = __($b->category);
            });

            return view('tour.index', compact('settings', 'packages', 'blogs', 'gallerySlides', 'siteSettings'));
        } catch (\Exception $e) {
            Log::error('Error loading tour index: '.$e->getMessage());

            // 503, bukan 500: gangguan koneksi database itu SEMENTARA. Google
            // membaca 503 sebagai "coba lagi nanti" dan mempertahankan peringkat,
            // sedangkan 500 dibaca sebagai halaman rusak. Retry-After memberi
            // tahu kapan boleh kembali.
            throw new ServiceUnavailableHttpException(300, 'Gagal memuat data tour. Terjadi gangguan koneksi sistem.');
        }
    }

    public function tourPackages(Request $request)
    {
        try {
            $siteSettings = $this->getSiteSettings();
            $packages = $this->tourService->getAllPackages();
            $cities = $this->tourService->getCities();

            return view('tour.packages', compact('packages', 'cities', 'siteSettings'));
        } catch (\Exception $e) {
            Log::error('Error loading tour packages: '.$e->getMessage());

            // back() memantulkan pengunjung ke halaman lain TANPA pesan apa pun:
            // layout publik tidak pernah merender session('error'). Kegagalannya
            // jadi tak terlihat. Sama seperti /tour, ini gangguan sementara.
            throw new ServiceUnavailableHttpException(300, 'Gagal memuat daftar paket.');
        }
    }

    public function tourGallery()
    {
        $siteSettings = $this->getSiteSettings();
        $images = $this->tourService->getGallery();

        return view('tour.gallery', compact('images', 'siteSettings'));
    }

    public function tourBlog()
    {
        $siteSettings = $this->getSiteSettings();
        $posts = $this->tourService->getBlogs();

        // Translate lightweight fields only
        $posts->each(function ($post) {
            $post->title = __($post->title);
            $post->excerpt = __($post->excerpt);
            $post->category = __($post->category);
        });

        return view('tour.blog', compact('posts', 'siteSettings'));
    }

    /**
     * Halaman detail paket tanpa form pemesanan (/tour/detail/{slug}).
     *
     * Sengaja menumpang jalur yang sama: isi, media, harga, dan SEO-nya harus
     * identik dengan halaman berform -- yang berbeda cuma isi sidebarnya.
     */
    public function tourPackageDetailNoForm($slug)
    {
        return $this->tourPackageDetail($slug, false);
    }

    public function tourPackageDetail($slug, bool $showBookingForm = true)
    {
        try {
            $siteSettings = $this->getSiteSettings();
            $originCity = null;

            $package = $this->tourService->getPackageBySlug($slug);

            // pSEO: If exact slug not found, try to detect "-dari-{kota}" pattern
            if (! $package && str_contains($slug, '-dari-')) {
                // Find the last occurrence of '-dari-' to extract origin city
                $lastDariPos = strrpos($slug, '-dari-');
                $baseSlug    = substr($slug, 0, $lastDariPos);
                $kotaSlug    = substr($slug, $lastDariPos + 6); // skip '-dari-'

                // Validate kota against allowed origins in settings
                $originsString  = $siteSettings['general']['seo_pseo_origins'] ?? 'jakarta, surabaya, bandung, bali, batam, palembang, makassar, semarang, yogyakarta, kuala-lumpur, singapore, penang, pekanbaru, padang, malaysia';
                $allowedOrigins = array_filter(array_map('trim', explode(',', strtolower($originsString))));
                $allowedOrigins = array_map(fn($o) => str_replace(' ', '-', $o), $allowedOrigins);

                if (in_array($kotaSlug, $allowedOrigins)) {
                    $package    = $this->tourService->getPackageBySlug($baseSlug);
                    $originCity = Str::title(str_replace('-', ' ', $kotaSlug));
                }
            }

            if (! $package) {
                abort(404);
            }

            // Session-based view counting — prevent F5 inflation.
            // Use a raw increment so it does not fire model events (which would
            // clear the tour cache) nor bump updatedAt on every page view.
            $viewKey = 'viewed_package_'.$package->id;
            if (! session()->has($viewKey)) {
                DB::table('packages')->where('id', $package->id)->increment('views_count');
                session()->put($viewKey, true);
            }

            $city = City::find($package->cityId);

            // Bawaan nol — lihat catatan di BookingService. Kalkulator depan
            // dan tagihan harus memakai angka yang sama.
            // Ambil dari $siteSettings (sudah di-cache di getSiteSettings) daripada
            // query 'general' lagi — ini halaman publik paling ramai.
            $finance = $siteSettings['general']['finance'] ?? [];
            $taxPercentage = (float) ($finance['tax_percentage'] ?? 0);

            // Surcharge akhir pekan & musim ramai ikut dikirim ke kalkulator depan.
            // Selama nilainya 0 tidak ada bedanya, tapi begitu admin mengisinya,
            // halaman ini akan mengutip angka yang lebih murah dari yang ditagih
            // BookingService — dan tamu baru tahu setelah menekan Pesan.
            $surcharge = [
                'weekend' => (float) ($finance['surcharge_weekend'] ?? 0),
                'peak' => (float) ($finance['surcharge_peak'] ?? 0),
                'peakStart' => (string) ($finance['surcharge_peak_start'] ?? ''),
                'peakEnd' => (string) ($finance['surcharge_peak_end'] ?? ''),
            ];

            return view('tour.package-detail', compact('package', 'city', 'siteSettings', 'originCity', 'taxPercentage', 'surcharge', 'showBookingForm'));
        } catch (\Exception $e) {
            Log::error("Error loading package detail ($slug): ".$e->getMessage());

            abort(404);
        }
    }

    public function tourBlogDetail($slug)
    {
        try {
            $siteSettings = $this->getSiteSettings();
            $post = $this->tourService->getBlogPost($slug);

            if (! $post) {
                abort(404);
            }

            // Session-based view counting — prevent F5 inflation.
            // Raw increment: no model events (avoids cache clear), no updatedAt bump.
            $viewKey = 'viewed_blog_'.$post->id;
            if (! session()->has($viewKey)) {
                DB::table('blogs')->where('id', $post->id)->increment('views_count');
                session()->put($viewKey, true);
            }

            $relatedPosts = $this->tourService->getRelatedBlogs($post->id);

            return view('tour.blog-detail', compact('post', 'relatedPosts', 'siteSettings'));
        } catch (\Exception $e) {
            Log::error("Error loading blog detail ($slug): ".$e->getMessage());

            abort(404);
        }
    }

    /**
     * Programmatic SEO Landing Pages (by Origin City)
     */
    public function landingOrigin($kota)
    {
        try {
            $siteSettings = $this->getSiteSettings(['cms_tour', 'general']);
            
            $originsString = $siteSettings['general']['seo_pseo_origins'] ?? 'jakarta, surabaya, bandung, bali, batam, palembang, makassar, semarang, yogyakarta, kuala-lumpur, singapore, penang, pekanbaru, padang, malaysia';
            $allowedOrigins = array_filter(array_map('trim', explode(',', strtolower($originsString))));
            
            // Allow hyphens instead of spaces for URL matching
            $allowedOrigins = array_map(function($o) { return str_replace(' ', '-', $o); }, $allowedOrigins);

            $kotaSlug = strtolower(trim($kota));

            if (!in_array($kotaSlug, $allowedOrigins)) {
                return redirect()->route('tour.packages');
            }

            $originName = Str::title(str_replace('-', ' ', $kotaSlug));

            // Gambar kota untuk hero, bila admin sudah membuat entri kota
            // dengan slug yang sama persis dengan slug URL ini.
            //
            // Daftar kota asal di atas berasal dari Pengaturan (teks bebas),
            // sedangkan tabel `cities` saat ini berisi kota TUJUAN di Sumatera
            // Utara -- jadi umumnya tidak ada yang cocok dan hero jatuh ke
            // gambar dari Pengaturan seperti sebelumnya. Begitu admin
            // menambah entri kota bernama sama dengan kota asal, gambarnya
            // otomatis terpakai tanpa perlu sentuh kode lagi.
            $originCity = City::with('imageMedia')->where('slug', $kotaSlug)->first();
            $originImage = $originCity?->imageMedia?->path ?: ($originCity?->image ?: null);

            // Re-use logic from tour() method
            $packages = $this->tourService->getFeaturedPackages();
            $blogs = $this->tourService->getBlogs(3);

            // Tautan internal ke landing page kota asal lainnya.
            $otherOrigins = collect($allowedOrigins)
                ->reject(fn ($o) => $o === $kotaSlug)
                ->unique()
                ->map(fn ($o) => ['slug' => $o, 'name' => Str::title(str_replace('-', ' ', $o))])
                ->values()
                ->all();

            return view('tour.landing-origin', compact('packages', 'blogs', 'siteSettings', 'originName', 'kotaSlug', 'originImage', 'otherOrigins'));
        } catch (\Exception $e) {
            Log::error('Error loading pSEO landing page: '.$e->getMessage());
            return redirect()->route('tour.packages');
        }
    }

    public function submitBooking(StoreBookingRequest $request)
    {
        try {
            // Honeypot Security Check (Pencegahan Spam)
            if ($request->filled('website_url')) {
                // Bot terdeteksi karena mengisi hidden input. Balas seolah berhasil
                // supaya bot tidak belajar, TAPI tanpa kode booking palsu: kode
                // 'BOT-xxxxxx' yang dulu dikirim ke sini selalu 404 di halaman
                // pelacakan, jadi manusia yang terjaring autofill justru dikirim
                // mengejar pesanan yang tidak pernah ada.
                Log::warning('Honeypot triggered during booking submission.', ['ip' => $request->ip()]);

                return back()->with([
                    'success' => __('Booking berhasil dikirim! Kami akan menghubungi Anda segera.'),
                ]);
            }

            $validated = $request->validated();
            $package = $this->tourService->getPackageBySlug($request->slug ?? '');

            if (! $package) {
                $package = Package::find($validated['packageId']);
            }

            $endDate = $validated['startDate'];
            if ($package && $package->duration) {
                if (preg_match('/(\d+)\s*(Hari|Days|D|H)/i', $package->duration, $matches)) {
                    $days = (int) $matches[1];
                    if ($days > 1) {
                        $endDate = date('Y-m-d', strtotime($validated['startDate'].' + '.($days - 1).' days'));
                    }
                }
            }

            $booking = $this->bookingService->create(array_merge($validated, [
                'type' => 'package',
                'endDate' => $endDate,
                'status' => 'pending',
                'metadata' => [
                    'pax' => $validated['pax'],
                    'paxChildren' => $validated['paxChildren'] ?? 0,
                    'selected_services' => $validated['selected_services'] ?? [],
                    'service_qty' => $validated['service_qty'] ?? [],
                    'use_van' => (bool) ($validated['use_van'] ?? false),
                ],
            ]));

            // Set Carbon locale for date formatting
            $locale = session('locale', 'my');
            $carbonLocaleMap = ['id' => 'id', 'my' => 'ms', 'en' => 'en'];
            $carbonLocale = $carbonLocaleMap[$locale] ?? 'id';
            $formattedDate = Carbon::parse($validated['startDate'])
                ->locale($carbonLocale)
                ->translatedFormat('d F Y');

            // Construct WhatsApp Message
            $invoiceUrl = route('invoice.download', $booking->bookingCode);
            $trackingUrl = route('booking.track', $booking->bookingCode);
            $bookingDate = now()
                ->locale($carbonLocale)
                ->translatedFormat('d F Y, H:i');

            $waMessage = __('Halo Sujai Toba Sumatera, saya ingin memesan paket wisata.')."\n\n".
                         '*'.__('Detail Pesanan:')."*\n".
                         '- '.__('Kode Booking').': '.$booking->bookingCode."\n".
                         '- '.__('Status').': '.__('Menunggu konfirmasi admin')."\n".
                         '- '.__('Paket').': '.$package->name."\n".
                         '- '.__('Link Paket').': '.route('tour.package.detail', $package->slug)."\n".
                         '- '.__('Nama').': '.$validated['customerName']."\n".
                         // Email tidak lagi wajib di form; barisnya hanya ikut
                         // bila memang diisi. Membacanya tanpa penjaga membuat
                         // SELURUH pengiriman pesanan gagal dengan "Undefined
                         // array key" -- tamu menerima form yang memantul tanpa
                         // pesan yang bisa ia perbaiki.
                         (! empty($validated['customerEmail'])
                             ? '- '.__('Email').': '.$validated['customerEmail']."\n"
                             : '').
                         '- '.__('WhatsApp').': '.$validated['customerPhone']."\n".
                         '- '.__('Tanggal').': '.$formattedDate."\n".
                         '- '.__('Peserta').': '.$validated['pax'].' '.__('Orang')."\n".
                         '- '.__('Estimasi Total').': '.\App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency)."\n".
                         '- '.__('Tanggal Booking').': '.$bookingDate."\n\n".
                         '*'.__('Link Penting:')."*\n".
                         '- '.__('Invoice').': '.$invoiceUrl."\n".
                         '- '.__('Tekan ini untuk lihat track').': '.$trackingUrl."\n";

            if (! empty($validated['notes'])) {
                $waMessage .= '- '.__('Catatan').': '.$validated['notes']."\n";
            }

            $waMessage .= "\n".__('Mohon konfirmasinya. Terima kasih!');

            // Satu sumber nomor (ContactHelper), sama dengan yang ditampilkan di
            // footer/navbar/pembayaran. Sebelumnya blok ini menurunkan nomornya
            // sendiri dari setting, sehingga bisa berbeda dari yang dilihat tamu.
            $waUrl = \App\Helpers\ContactHelper::whatsappLink($waMessage);

            // Redirect ke URL permanen, bukan back(). Dengan back(), kode booking
            // hidup di flash session: begitu tamu me-refresh atau menekan Back,
            // satu-satunya bukti pesanannya hilang. Halaman pelacakan menampung
            // status, rincian biaya, invoice, dan tombol konfirmasi sekaligus.
            return redirect()->route('booking.track', $booking->bookingCode)->with([
                'success' => __('Booking berhasil dikirim! Kami akan menghubungi Anda segera.'),
                'whatsappUrl' => $waUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Booking Submission Error: '.$e->getMessage(), ['request' => $request->all()]);

            return back()->with('error', __('Terjadi kesalahan saat memproses pesanan. Tim IT kami telah dinotifikasi.'));
        }
    }

    public function showTrackBookingForm()
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'cms_tour', 'general']);

        return view('booking.lookup', compact('siteSettings'));
    }

    public function redirectTrackBooking(Request $request)
    {
        $validated = $request->validate([
            'booking_code' => ['required', 'string', 'max:30'],
        ]);

        $code = strtoupper(trim($validated['booking_code']));

        return redirect()->route('booking.track', $code);
    }

    public function trackBooking(string $code)
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'cms_tour', 'general']);
        $code = strtoupper(trim($code));
        $booking = \App\Models\Booking::with(['package', 'package.city'])
            ->where('bookingCode', $code)
            ->first();

        // firstOrFail() melempar tamu ke 404 mentah. Kode yang salah ketik adalah
        // kesalahan yang wajar dan sering terjadi justru saat orang cemas menunggu
        // kabar pesanannya — kembalikan ke form dengan pesan yang bisa ditindaklanjuti.
        if (! $booking) {
            return redirect()
                ->route('booking.track.form')
                ->withInput(['booking_code' => $code])
                ->with('error', __('Kode booking :code tidak ditemukan. Periksa kembali huruf dan angkanya, atau hubungi kami jika Anda yakin kodenya benar.', ['code' => $code]));
        }

        return view('booking.track', compact('booking', 'siteSettings'));
    }



    public function about()
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'cms_tour', 'general']);
        $content = Setting::where('key', 'page_about')->first()?->value ?? [];
        $clients = Client::orderBy('orderPriority')->get();

        return view('pages.about', compact('content', 'siteSettings', 'clients'));
    }



    public function terms()
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'general']);
        $content = Setting::where('key', 'page_terms')->first()?->value ?? [];

        return view('pages.terms', compact('content', 'siteSettings'));
    }

    public function privacy()
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'general']);
        $content = Setting::where('key', 'page_privacy')->first()?->value ?? [];

        return view('pages.privacy', compact('content', 'siteSettings'));
    }

    public function payment()
    {
        $siteSettings = $this->getSiteSettings(['cms_landing', 'general']);

        // Rekening dari pengaturan perusahaan — sumber yang sama dengan invoice.
        // Halaman ini dulu menulis "Hubungi kami untuk no. rekening" secara mati
        // di dua kotak bank, jadi meski admin sudah mengisi rekening, halaman
        // yang isinya cuma satu hal itu tetap tidak memberitahukannya.
        $company = Setting::where('key', 'company')->first()?->value ?? [];
        $bankAccounts = array_values(array_filter($company['bank_accounts'] ?? [], function ($row) {
            return ! empty($row['number'] ?? null);
        }));

        if (! $bankAccounts && ! empty($company['bank_account'])) {
            $bankAccounts = [[
                'bank' => $company['bank_name'] ?? 'Bank',
                'number' => $company['bank_account'],
                'holder' => $company['bank_account_name'] ?? ($company['legal_name'] ?? null),
            ]];
        }

        return view('pages.payment', compact('siteSettings', 'bankAccounts'));
    }

    public function submitOutboundQuote(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'participants' => 'required|integer|min:1',
            'location' => 'required|string|max:255',
            'activity_type' => 'nullable|string|max:255',
            'estimated_date' => 'required|date',
            'whatsapp' => 'required|string|max:255',
        ]);

        // Satu sumber kebenaran (ContactHelper), sama dengan navbar/footer/booking.
        // Sebelumnya blok ini menurunkan nomor dari settings secara langsung,
        // sehingga bisa berbeda dari yang tertera di halaman bila cache belum bersih.
        $waNumber = \App\Helpers\ContactHelper::whatsappDigits();

        if ($waNumber === '') {
            return back()->with('error', __('Nomor WhatsApp belum dikonfigurasi.'));
        }

        // Catat permintaan di log: halaman ini hanya membuat tautan WhatsApp,
        // jadi bila tamu menutup halaman sebelum menekan tombolnya, permintaan
        // ini hilang tanpa jejak. Dengan log, admin masih bisa menindaklanjuti.
        Log::info('Outbound quote request', $validated);

        $message = __('Halo Sujai Toba Sumatera, saya ingin meminta penawaran outbound.')."\n\n"
            ."Company: {$validated['company_name']}\n"
            ."Peserta: {$validated['participants']}\n"
            ."Lokasi: {$validated['location']}\n"
            .'Aktivitas: '.($validated['activity_type'] ?? '-')."\n"
            ."Estimasi: {$validated['estimated_date']}\n"
            ."WhatsApp: {$validated['whatsapp']}";

        $waUrl = "https://wa.me/{$waNumber}?text=".urlencode($message);

        return back()->with([
            'success' => __('Permintaan outbound berhasil dikirim.'),
            'whatsappUrl' => $waUrl,
        ]);
    }

    /**
     * Generate dynamic, beautiful OpenGraph card banners for social shares.
     */
    public function generateOgBanner(string $type, int $id, OgBannerService $ogBannerService)
    {
        $path = $ogBannerService->getOrGenerateBanner($type, $id);

        return response()->file($path, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000'
        ]);
    }
}

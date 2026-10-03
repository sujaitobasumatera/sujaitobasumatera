<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\Setting;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    /**
     * Itinerary PDF sebuah paket.
     *
     * Bawaannya DITAMPILKAN di peramban, bukan dipaksa terunduh. Tamu jarang
     * mau menyimpan berkas yang belum ia lihat isinya; memaksa unduh di awal
     * itu hambatan, dan di ponsel berkasnya sering hilang begitu saja ke folder
     * unduhan tanpa pernah dibuka. Yang benar-benar ingin menyimpan menekan
     * tautan unduh di sebelahnya (?unduh=1), atau tombol simpan milik pembaca
     * PDF peramban.
     *
     * Ini juga menyamakan perilakunya dengan brosur unggahan admin, yang selalu
     * terbuka di tab baru. Sebelumnya satu tombol yang sama berperilaku berbeda
     * tergantung ada tidaknya brosur -- dan tulisannya "Unduh" di kedua keadaan.
     *
     * Nama rutenya dibiarkan (itinerary.download) supaya tautan lama tidak
     * putus; yang berubah cuma cara berkasnya disajikan.
     *
     * Memakai Eloquent supaya atribut, cast, dan resolveImageUrl() bekerja.
     */
    public function downloadItinerary($slug, \Illuminate\Http\Request $request)
    {
        $package = Package::with(['packageImages', 'city'])
            ->where('slug', $slug)
            ->first();

        if (! $package) {
            abort(404, 'Paket tidak ditemukan.');
        }

        $city = $package->city;

        $siteSettings = [
            'general' => Setting::where('key', 'general')->first()?->value ?? [],
            'company' => Setting::where('key', 'company')->first()?->value ?? [],
        ];

        // Resolve hero image for the PDF header using the centralized helper
        $heroImageUrl = $package->packageImages->first()?->image_url
            ?? $package->first_image
            ?? imageFallback();

        $data = [
            'package' => $package,
            'city' => $city,
            'siteSettings' => $siteSettings,
            'heroImageUrl' => $heroImageUrl,
        ];

        $pdf = Pdf::loadView('pdf.itinerary', $data);
        $namaBerkas = "Itinerary-{$package->slug}.pdf";

        return $request->boolean('unduh')
            ? $pdf->download($namaBerkas)
            : $pdf->stream($namaBerkas);
    }

    public function streamInvoice($identifier)
    {
        try {
            $view = $this->renderInvoice($identifier);
            return response((string) $view->render());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Invoice tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('Invoice Render Error: '.$e->getMessage(), ['identifier' => $identifier]);
            abort(500, 'Gagal membuka invoice. Silakan coba lagi atau hubungi admin.');
        }
    }

    public function downloadInvoice($identifier)
    {
        try {
            // Match bookingCode ONLY — numeric ids would allow /invoice/1,2,3
            // enumeration of other customers' data (IDOR).
            $booking = Booking::where('bookingCode', $identifier)->firstOrFail();

            return app(InvoiceService::class)->downloadInvoice($booking);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            abort(404, 'Invoice tidak ditemukan.');
        } catch (\Throwable $e) {
            Log::error('Invoice Download Error: '.$e->getMessage(), ['identifier' => $identifier]);
            abort(500, 'Gagal mengunduh invoice. Silakan coba lagi atau hubungi admin.');
        }
    }

    /**
     * Render the premium HTML invoice view for a booking (printable via browser).
     */
    private function renderInvoice($identifier)
    {
        // bookingCode only (never id) — prevents invoice enumeration/IDOR.
        $booking = Booking::with(['package', 'package.city'])
            ->where('bookingCode', $identifier)
            ->firstOrFail();

        $general = Setting::where('key', 'general')->first()?->value ?? [];
        $company = Setting::where('key', 'company')->first()?->value ?? [];
        $landing = Setting::where('key', 'cms_landing')->first()?->value ?? [];

        $logoRaw = $general['logo_light_url'] ?? ($landing['brand_logo_url'] ?? null);

        $data = [
            'booking' => $booking,
            'companyName' => $general['site_name'] ?? 'Sujai Toba Sumatera',
            'legalName' => $company['legal_name'] ?? 'PT Sujai Toba Sumatera Experience',
            'taxId' => $company['tax_id'] ?? null,
            'bankAccount' => $company['bank_account'] ?? null,
            'bankAccountName' => $company['bank_account_name'] ?? ($company['legal_name'] ?? null),
            'address' => $general['office_address'] ?? 'Sumatera Utara',
            'email' => $general['contact_email'] ?? null,
            'instagram' => $general['social_instagram'] ?? null,
            'logoUrl' => $logoRaw ? imageUrl($logoRaw) : null,
        ];

        return view('invoice.show', $data);
    }
}

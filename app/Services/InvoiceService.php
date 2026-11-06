<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate Invoice PDF for a booking
     */
    public function generateInvoice(Booking $booking)
    {
        // Satu query menggantikan tiga: whereIn + pluck lebih efisien
        // daripada tiga kali Setting::where()->first() terpisah.
        $rows = Setting::whereIn('key', ['general', 'company', 'cms_landing'])
            ->pluck('value', 'key');

        $siteSettings = [
            'general'     => $rows->get('general', []) ?? [],
            'company'     => $rows->get('company', []) ?? [],
            'cms_landing' => $rows->get('cms_landing', []) ?? [],
        ];

        $data = [
            'booking' => $booking->load(['package', 'package.city']),
            'date' => now()->format('d F Y'),
            'siteSettings' => $siteSettings,
        ];

        $pdf = Pdf::loadView('pdf.invoice', $data);

        return $pdf;
    }

    /**
     * Stream the invoice to the browser
     */
    public function streamInvoice(Booking $booking)
    {
        $pdf = $this->generateInvoice($booking);

        return $pdf->stream("Invoice-{$booking->bookingCode}.pdf");
    }

    /**
     * Download the invoice
     */
    public function downloadInvoice(Booking $booking)
    {
        $pdf = $this->generateInvoice($booking);

        return $pdf->download("Invoice-{$booking->bookingCode}.pdf");
    }
}

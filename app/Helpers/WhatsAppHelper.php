<?php

namespace App\Helpers;

use App\Models\Booking;

/** @deprecated Tidak digunakan. Logika WA ada di PublicController. */
class WhatsAppHelper
{
    /**
     * Generate a WhatsApp link for a booking confirmation
     */
    public static function getBookingLink(Booking $booking): string
    {
        $phone = config('services.whatsapp.phone', '6282277848855');
        $packageName = $booking->package ? $booking->package->name : 'Paket Wisata';

        $message = "Halo Sujai Toba Sumatera,\n\n";
        $message .= "Saya ingin konfirmasi pesanan saya:\n";
        $message .= "🆔 *Kode Booking:* {$booking->bookingCode}\n";
        $message .= "👤 *Nama:* {$booking->customerName}\n";
        $message .= "📦 *Paket:* {$packageName}\n";
        $message .= "🗓️ *Tanggal:* {$booking->startDate}\n";
        $message .= '👥 *Pax:* '.($booking->metadata['pax'] ?? 1)."\n\n";
        $message .= 'Mohon informasi selanjutnya. Terima kasih.';

        return "https://wa.me/{$phone}?text=".urlencode($message);
    }
}

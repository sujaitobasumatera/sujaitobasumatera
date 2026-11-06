<?php

namespace App\Services;

use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportService
{
    /**
     * Generate a PDF report of bookings
     */
    public function generateBookingReport($startDate = null, $endDate = null)
    {
        $query = Booking::with(['package', 'package.city'])->orderBy('startDate', 'desc');

        if ($startDate && $endDate) {
            $query->whereBetween('startDate', [$startDate, $endDate]);
        }

        $bookings = $query->get();

        $data = [
            'bookings' => $bookings,
            'startDate' => $startDate ?? 'Awal',
            'endDate' => $endDate ?? 'Sekarang',
            'totalRevenue' => $bookings->whereIn('status', ['confirmed', 'completed'])->sum('totalPrice_idr'),
            'totalBookings' => $bookings->count(),
            'generatedAt' => now()->format('d F Y H:i'),
        ];

        return Pdf::loadView('pdf.booking-report', $data)->setPaper('a4', 'landscape');
    }
}

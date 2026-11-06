<?php

namespace App\Observers;

use App\Models\Booking;
use Illuminate\Support\Facades\Cache;

class BookingObserver
{
    public function saved(Booking $booking)
    {
        Cache::forget('admin_dashboard_stats');
        // Flush jumlah pesanan pending yang di-cache di AppServiceProvider
        // (ditampilkan di lonceng notifikasi panel admin).
        Cache::forget('pending_bookings_count');
    }

    public function deleted(Booking $booking)
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('pending_bookings_count');
    }

    public function restored(Booking $booking)
    {
        Cache::forget('admin_dashboard_stats');
        Cache::forget('pending_bookings_count');
    }
}

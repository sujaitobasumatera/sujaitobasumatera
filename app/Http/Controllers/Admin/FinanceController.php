<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Traits\LogsActivity;

class FinanceController extends Controller
{
    use LogsActivity;

    public function index()
    {
        $transactions = Booking::whereIn('status', ['confirmed', 'completed'])
            ->latest('createdAt')
            ->paginate(20);

        // Agregat dihitung ulang atas SELURUH data, bukan atas $transactions:
        // koleksi itu hanya berisi 20 baris halaman aktif, jadi menjumlahkannya
        // membuat omzet menyusut setiap kali admin pindah halaman.
        $summary = Booking::whereIn('status', ['confirmed', 'completed'])
            ->selectRaw('COALESCE(SUM(totalPrice_idr), 0) as revenue, COALESCE(AVG(totalPrice_idr), 0) as average')
            ->first();

        return view('admin.finance.index', [
            'transactions' => $transactions,
            'revenue' => (float) $summary->revenue,
            'average' => (float) $summary->average,
        ]);
    }

    public function export()
    {
        $transactions = Booking::whereIn('status', ['confirmed', 'completed'])
            ->latest('createdAt')
            ->get();

        $filename = 'finance-report-'.date('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Booking Code', 'Customer', 'Email', 'Type', 'Item', 'Currency', 'Total Price', 'Total Price (IDR)', 'Booking Date']);

            foreach ($transactions as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->bookingCode,
                    $booking->customerName,
                    $booking->customerEmail,
                    ucfirst($booking->type),
                    $booking->package->name ?? 'N/A',
                    $booking->currency,
                    $booking->totalPrice,
                    $booking->totalPrice_idr,
                    $booking->createdAt->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        $this->logActivity('export', 'Exported finance report');

        return response()->stream($callback, 200, $headers);
    }
}

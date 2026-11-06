<?php

namespace App\Http\Controllers\Admin;

use App\Exports\FinancialExport;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function financial(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            // SQLite doesn't have whereYear/whereMonth functions that Laravel can always translate reliably if types differ
            // Use whereBetween for reliability
            $startDate = "$year-".($month === 'all' ? '01' : str_pad($month, 2, '0', STR_PAD_LEFT)).'-01 00:00:00';
            if ($month === 'all') {
                $endDate = "$year-12-31 23:59:59";
            } else {
                $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            }
            $query = Booking::whereBetween('createdAt', [$startDate, $endDate]);
        } else {
            $query = Booking::whereYear('createdAt', $year);
            if ($month !== 'all') {
                $query->whereMonth('createdAt', $month);
            }
        }

        // 1. Monthly/Filtered Summary
        $monthlyBookings = $query->clone()
            ->with(['package' => fn ($q) => $q->withTrashed()])
            ->whereIn('status', ['confirmed', 'completed'])
            ->get();

        $stats = [
            'total_orders' => $monthlyBookings->count(),
            'revenue' => $monthlyBookings->sum('totalPrice_idr'),
        ];

        // 2. Status Summary (Monthly/Filtered)
        $statusCounts = $query->clone()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $statusSummary = [
            'pending' => $statusCounts['pending'] ?? 0,
            'confirmed' => $statusCounts['confirmed'] ?? 0,
            'completed' => $statusCounts['completed'] ?? 0,
            'cancelled' => $statusCounts['cancelled'] ?? 0,
        ];

        // 3. Yearly Summary
        if ($isSqlite) {
            $yearlyBookings = Booking::whereBetween('createdAt', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"])
                ->with(['package' => fn ($q) => $q->withTrashed()])
                ->whereIn('status', ['confirmed', 'completed'])
                ->get();
        } else {
            $yearlyBookings = Booking::whereYear('createdAt', $year)
                ->with(['package' => fn ($q) => $q->withTrashed()])
                ->whereIn('status', ['confirmed', 'completed'])
                ->get();
        }

        $yearlySummary = [
            'orders' => $yearlyBookings->count(),
            'revenue' => $yearlyBookings->sum('totalPrice_idr'),
        ];

        // 4. Monthly Chart Data
        $monthExpr = $isSqlite ? "CAST(strftime('%m', createdAt) AS INTEGER)" : 'MONTH(createdAt)';

        $chartDataQuery = Booking::whereIn('status', ['confirmed', 'completed']);
        if ($isSqlite) {
            $chartDataQuery->whereBetween('createdAt', ["$year-01-01 00:00:00", "$year-12-31 23:59:59"]);
        } else {
            $chartDataQuery->whereYear('createdAt', $year);
        }

        $chartData = $chartDataQuery->select(
            DB::raw("$monthExpr as month_num"),
            DB::raw('count(*) as total')
        )
            ->groupBy('month_num')
            ->get()
            ->pluck('total', 'month_num')
            ->toArray();

        $monthlyChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyChart[$m] = $chartData[$m] ?? 0;
        }

        // 5. Booking List
        $bookings = $query->clone()
            ->with(['package' => fn ($q) => $q->withTrashed(), 'customer'])
            ->latest('createdAt')
            ->get();

        return view('admin.reports.financial', compact(
            'year', 'month', 'stats', 'statusSummary', 'yearlySummary', 'monthlyChart', 'bookings'
        ));
    }

    public function export(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('n'));
        $format = $request->get('format', 'csv');

        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $exportQuery = Booking::with(['package' => fn ($q) => $q->withTrashed(), 'customer']);

        if ($isSqlite) {
            $startDate = "$year-".($month === 'all' ? '01' : str_pad($month, 2, '0', STR_PAD_LEFT)).'-01 00:00:00';
            if ($month === 'all') {
                $endDate = "$year-12-31 23:59:59";
            } else {
                $endDate = date('Y-m-t 23:59:59', strtotime($startDate));
            }
            $exportQuery->whereBetween('createdAt', [$startDate, $endDate]);
        } else {
            $exportQuery->whereYear('createdAt', $year);
            if ($month !== 'all') {
                $exportQuery->whereMonth('createdAt', $month);
            }
        }

        $bookings = $exportQuery->latest('createdAt')->get();

        if ($format === 'xlsx') {
            $filename = "Laporan_Keuangan_{$year}_{$month}.xlsx";

            return Excel::download(
                new FinancialExport($bookings),
                $filename
            );
        }

        // Default CSV
        $filename = "Laporan_Keuangan_{$year}_{$month}.csv";
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fputcsv($handle, ['No', 'Tgl Pesan', 'ID Transaksi', 'Item', 'Pelanggan', 'Mata Uang', 'Total', 'Total (IDR)', 'Status']);

        foreach ($bookings as $index => $booking) {
            fputcsv($handle, [
                $index + 1,
                $booking->createdAt->format('d/m/Y'),
                $booking->bookingCode,
                $booking->package?->name ?? 'Custom',
                $booking->customer?->name ?? 'Demo User',
                $booking->currency,
                $booking->totalPrice,
                $booking->totalPrice_idr,
                ucfirst($booking->status),
            ]);
        }

        fclose($handle);
        exit;
    }
}

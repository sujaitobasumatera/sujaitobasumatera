<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Media;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getStats()
    {
        return Cache::remember('admin_dashboard_stats', 300, function () {
            return [
                'revenue' => [
                    'total' => Booking::whereIn('status', ['confirmed', 'completed'])->sum('totalPrice_idr'),
                    'monthly' => Booking::whereIn('status', ['confirmed', 'completed'])
                        ->whereYear('createdAt', now()->year)
                        ->whereMonth('createdAt', now()->month)
                        ->sum('totalPrice_idr'),
                    'growth' => $this->calculateGrowth(),
                ],
                'profit' => [
                    'total' => Booking::whereIn('status', ['confirmed', 'completed'])->sum(DB::raw('totalPrice_idr - COALESCE(total_cost, 0)')),
                    'monthly' => Booking::whereIn('status', ['confirmed', 'completed'])
                        ->whereYear('createdAt', now()->year)
                        ->whereMonth('createdAt', now()->month)
                        ->sum(DB::raw('totalPrice_idr - COALESCE(total_cost, 0)')),
                ],
                'bookings' => [
                    'active' => Booking::whereIn('status', ['pending', 'confirmed'])->count(),
                    'pending' => Booking::where('status', 'pending')->count(),
                    'confirmed' => Booking::where('status', 'confirmed')->count(),
                ],
                'users' => [
                    'total' => User::count(),
                    'new_this_month' => User::whereYear('created_at', now()->year)->whereMonth('created_at', now()->month)->count(),
                ],
                'packages' => [
                    'total' => Package::count(),
                    'active' => Package::where('status', 'active')->count(),
                ],
                'top_views' => [
                    'packages' => Package::orderByDesc('views_count')->limit(5)->get()->toArray(),
                    'blogs' => Blog::orderByDesc('views_count')->limit(5)->get()->toArray(),
                ],
                'recent_bookings' => Booking::with(['package'])
                    ->latest('createdAt')
                    ->limit(10)
                    ->get()
                    ->toArray(),
                'recent_customers' => Customer::latest()->limit(5)->get()->toArray(),
                'top_packages' => $this->getTopPackages()->toArray(),
                'monthly_revenue' => $this->getMonthlyRevenue(),
                'revenue_7d' => $this->get7DayRevenue(),
                'bookings_7d' => $this->get7DayBookings(),
            ];
        });
    }

    private function get7DayBookings()
    {
        $startDate = now()->subDays(6)->startOfDay();

        $counts = Booking::where('createdAt', '>=', $startDate)
            ->select(
                DB::raw('date(createdAt) as date'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('D'),
                'total' => (int) ($counts[$date] ?? 0),
            ];
        }

        return $days;
    }

    private function get7DayRevenue()
    {
        $startDate = now()->subDays(6)->startOfDay();

        $revenues = Booking::whereIn('status', ['confirmed', 'completed'])
            ->where('createdAt', '>=', $startDate)
            ->select(
                DB::raw('date(createdAt) as date'),
                DB::raw('SUM(totalPrice_idr) as total')
            )
            ->groupBy('date')
            ->get()
            ->pluck('total', 'date');

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = [
                'date' => $date,
                'label' => now()->subDays($i)->format('D'),
                'total' => (float) ($revenues[$date] ?? 0),
            ];
        }

        return $days;
    }

    private function calculateGrowth()
    {
        $currentMonth = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereYear('createdAt', now()->year)
            ->whereMonth('createdAt', now()->month)
            ->sum('totalPrice_idr');

        $lastMonth = Booking::whereIn('status', ['confirmed', 'completed'])
            ->whereYear('createdAt', now()->subMonth()->year)
            ->whereMonth('createdAt', now()->subMonth()->month)
            ->sum('totalPrice_idr');

        if ($lastMonth == 0) {
            return 100;
        }

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100, 2);
    }

    private function getTopPackages()
    {
        return Package::select('packages.name', DB::raw('COUNT(bookings.id) as booking_count'))
            ->leftJoin('bookings', function ($join) {
                $join->on('packages.id', '=', 'bookings.packageId')
                     ->whereIn('bookings.status', ['confirmed', 'completed'])
                     ->whereNull('bookings.deleted_at');
            })
            ->groupBy('packages.id', 'packages.name')
            ->orderByDesc('booking_count')
            ->limit(5)
            ->get()
            ->pluck('booking_count', 'name');
    }

    private function getMonthlyRevenue()
    {
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';
        $monthExpr = $isSqlite ? "CAST(strftime('%m', createdAt) AS INTEGER)" : 'MONTH(createdAt)';

        return Booking::select(
            DB::raw("$monthExpr as month"),
            DB::raw('SUM(totalPrice_idr) as total')
        )
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereYear('createdAt', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }
}

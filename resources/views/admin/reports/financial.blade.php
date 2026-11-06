@extends('admin.layout')

@section('title', 'Laporan Keuangan - Sujai Toba Sumatera')

@section('content')
<div class="space-y-6">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Laporan Keuangan</h2>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Analisis Transaksi & Pendapatan</p>
        </div>
        
        <form action="{{ route('admin.reports.financial') }}" method="GET" class="flex flex-wrap items-center gap-2 w-full md:w-auto">
            <select name="year" class="px-4 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs">
                @for($y = date('Y'); $y >= 2024; $y--)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <select name="month" class="px-4 py-2 bg-white border border-slate-200 rounded-xl font-bold text-xs">
                <option value="all" {{ $month == 'all' ? 'selected' : '' }}>Semua Bulan</option>
                @foreach(['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $mName)
                    <option value="{{ $i + 1 }}" {{ $month == ($i + 1) ? 'selected' : '' }}>{{ $mName }}</option>
                @endforeach
            </select>
            <button type="submit" class="p-2 bg-toba-green text-white rounded-xl hover:bg-toba-green/90 transition shadow-lg shadow-toba-green/20">
                <i class="fas fa-filter"></i>
            </button>
            <a href="{{ route('admin.reports.financial.export', ['year' => $year, 'month' => $month, 'format' => 'xlsx']) }}" class="flex items-center gap-2 px-4 py-2 bg-green-800 text-white rounded-xl font-bold text-xs hover:bg-green-900 transition shadow-lg shadow-green-800/20">
                <i class="fas fa-file-excel"></i>
                Export Excel
            </a>
            <a href="{{ route('admin.reports.financial.export', ['year' => $year, 'month' => $month, 'format' => 'csv']) }}" class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-xl font-bold text-xs hover:bg-green-700 transition shadow-lg shadow-green-600/20">
                <i class="fas fa-file-csv"></i>
                Export CSV
            </a>
        </form>
    </div>

    {{-- Monthly Summary Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-4">
        <div class="bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-green-100 flex items-center justify-center text-green-800 mb-4">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Total Pesanan</p>
            <p class="text-2xl font-black text-slate-900 mt-1">{{ number_format($stats['total_orders']) }}</p>
        </div>
        
        <div class="bg-white p-5 rounded-[2rem] border border-slate-100 shadow-sm">
            <div class="w-10 h-10 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 mb-4">
                <i class="fas fa-money-bill-trend-up"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pendapatan</p>
            <p class="text-xl font-black text-slate-900 mt-1">{{ \App\Helpers\CurrencyHelper::formatIn($stats['revenue'], 'IDR') }}</p>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-amber-50/50 border border-amber-100 p-4 rounded-2xl flex items-center gap-4">
            <div class="text-2xl font-black text-amber-600">{{ $statusSummary['pending'] }}</div>
            <span class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Pending</span>
        </div>
        <div class="bg-green-100/50 border border-green-200 p-4 rounded-2xl flex items-center gap-4">
            <div class="text-2xl font-black text-green-800">{{ $statusSummary['confirmed'] }}</div>
            <span class="text-[10px] font-black text-green-900 uppercase tracking-widest">Dikonfirmasi</span>
        </div>
        <div class="bg-green-50/50 border border-green-100 p-4 rounded-2xl flex items-center gap-4">
            <div class="text-2xl font-black text-green-600">{{ $statusSummary['completed'] }}</div>
            <span class="text-[10px] font-black text-green-700 uppercase tracking-widest">Selesai</span>
        </div>
        <div class="bg-rose-50/50 border border-rose-100 p-4 rounded-2xl flex items-center gap-4">
            <div class="text-2xl font-black text-rose-600">{{ $statusSummary['cancelled'] }}</div>
            <span class="text-[10px] font-black text-rose-700 uppercase tracking-widest">Dibatalkan</span>
        </div>
    </div>

    {{-- Yearly Analytics Card --}}
    <div class="bg-gradient-to-br from-toba-green to-primary p-8 rounded-[2.5rem] text-white shadow-xl shadow-toba-green/25">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <p class="text-xs font-black uppercase tracking-[0.2em] opacity-80 mb-2">📊 Ringkasan Tahunan {{ $year }}</p>
                <h3 class="text-3xl font-black tracking-tight">Performa Bisnis</h3>
            </div>
            <div class="grid grid-cols-2 gap-4 md:gap-8 w-full md:w-auto">
                <div class="bg-white/10 p-5 md:p-6 rounded-2xl backdrop-blur-sm border border-white/10 min-w-0 md:min-w-[150px]">
                    <p class="text-2xl md:text-[28px] font-black leading-none text-white">{{ number_format($yearlySummary['orders']) }}</p>
                    <p class="text-[9px] font-black uppercase tracking-widest text-green-200 mt-2">Total Pesanan</p>
                </div>
                <div class="bg-white/10 p-5 md:p-6 rounded-2xl backdrop-blur-sm border border-white/10 min-w-0 md:min-w-[150px]">
                    <p class="text-[20px] font-black leading-none text-white">{{ \App\Helpers\CurrencyHelper::formatIn($yearlySummary['revenue'], 'IDR') }}</p>
                    <p class="text-[9px] font-black uppercase tracking-widest text-green-200 mt-2">Pendapatan</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart & Table Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Chart Placeholder --}}
        <div class="lg:col-span-1 bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm">
            <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8">📈 Tren Pesanan {{ $year }}</h3>
            <div class="flex items-end justify-between h-48 gap-1.5 px-2 bg-slate-50/50 rounded-2xl py-4">
                @php $maxCount = max($monthlyChart) ?: 1; @endphp
                @foreach($monthlyChart as $m => $count)
                    <div class="flex-1 flex flex-col items-center gap-2 group h-full justify-end">
                        <div class="w-full bg-green-200/50 group-hover:bg-green-300 rounded-t-lg transition relative overflow-hidden" 
                             style="height: {{ max(6, ($count / $maxCount) * 100) }}%">
                            <div class="absolute inset-0 bg-green-700 opacity-20 group-hover:opacity-40 transition-opacity"></div>
                            @if($month !== 'all' && $m == $month)
                                <div class="absolute inset-0 bg-toba-green shadow-[0_0_15px_rgba(16,185,81,0.5)]"></div>
                            @endif
                        </div>
                        <span class="text-[7px] font-black text-slate-400 uppercase tracking-tighter">{{ ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'][$m-1] }}</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Puncak Tertinggi</p>
                    <p class="text-lg font-black text-slate-900">{{ max($monthlyChart) }} Pesanan</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $month === 'all' ? 'Total Tahun Ini' : 'Bulan Ini' }}</p>
                    <p class="text-lg font-black text-toba-green">{{ $month === 'all' ? array_sum($monthlyChart) : $monthlyChart[$month] }}</p>
                </div>
            </div>
        </div>

        {{-- Table Section --}}
        <div class="lg:col-span-2 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden flex flex-col">
            <div class="p-8 border-bottom border-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">📝 Detail Transaksi</h3>
                <span class="px-3 py-1 bg-slate-100 rounded-full text-[10px] font-black text-slate-500 uppercase">{{ count($bookings) }} Data</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50/50">
                            <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">ID</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Paket</th>
                            <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest hidden sm:table-cell">Status</th>
                            <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($bookings as $booking)
                            <tr class="hover:bg-slate-50/30 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-xs font-black text-slate-900">{{ $booking->bookingCode }}</p>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase">{{ $booking->createdAt->format('d M Y') }}</p>
                                </td>
                                @php
                                    $statusStyles = [
                                        'pending' => 'bg-amber-100 text-amber-700',
                                        'confirmed' => 'bg-green-200 text-green-900',
                                        'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-rose-100 text-rose-700'
                                    ];
                                    $statusStyle = $statusStyles[$booking->status] ?? 'bg-slate-100 text-slate-600';
                                @endphp
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-700 truncate">{{ $booking->customer?->name }}</p>
                                    <p class="text-[9px] text-slate-400 truncate">{{ $booking->customer?->email }}</p>
                                    {{-- Status & package inline on mobile (columns hidden) --}}
                                    <div class="sm:hidden flex flex-wrap items-center gap-1.5 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest {{ $statusStyle }}">{{ $booking->status }}</span>
                                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest truncate">{{ $booking->package?->name ?? 'Custom' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 hidden lg:table-cell">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-widest bg-green-200 text-green-900">
                                        {{ $booking->package?->name ?? 'Custom' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                    <span class="px-2 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $statusStyle }}">
                                        {{ $booking->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <p class="text-xs font-black text-slate-900">{{ \App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency) }}</p>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-6 text-center">
                                    <div class="text-4xl mb-4">📭</div>
                                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Tidak ada transaksi ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

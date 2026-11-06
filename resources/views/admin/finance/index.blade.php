@extends('admin.layout')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan')

@section('content')
<div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Revenue Tour -->
        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm transition hover:shadow-xl hover:shadow-green-50 group">
            <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-500 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-map-location-dot"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Omzet Tour</p>
            {{-- Agregat omzet selalu IDR: dijumlahkan dari totalPrice_idr yang dibekukan saat pemesanan. --}}
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ \App\Helpers\CurrencyHelper::formatIn($revenue, 'IDR') }}</h3>
        </div>

        <!-- Average Value -->
        <div class="bg-white p-8 rounded-[3rem] border border-slate-100 shadow-sm transition hover:shadow-xl hover:shadow-slate-100 group">
            <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-chart-line"></i>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Rata-rata Transaksi</p>
            <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ \App\Helpers\CurrencyHelper::formatIn($average, 'IDR') }}</h3>
        </div>

        <!-- Total Bookings -->
        <div class="bg-slate-900 p-8 rounded-[3rem] shadow-2xl shadow-slate-200 group text-white">
            <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                <i class="fas fa-clipboard-check"></i>
            </div>
            <p class="text-[10px] font-black text-white/50 uppercase tracking-widest mb-1">Total Pesanan</p>
            <h3 class="text-2xl font-black tracking-tight">{{ $transactions->total() }} Booking</h3>
        </div>
    </div>

    <div class="bg-white rounded-[3.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-10 border-b border-slate-50 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-900 tracking-tight">Riwayat Transaksi</h3>
            <a href="{{ route('admin.finance.export') }}" class="px-8 py-3 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-slate-200 transition hover:-translate-y-1">Ekspor Laporan CSV</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">ID Pesanan</th>
                        <th class="px-5 md:px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Nama Customer</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Waktu Transaksi</th>
                        <th class="px-5 md:px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Nominal</th>
                        <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center hidden sm:table-cell">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($transactions as $booking)
                    <tr class="hover:bg-slate-50/50 transition cursor-default">
                        <td class="px-10 py-8 text-xs font-black text-slate-900 hidden md:table-cell">#{{ $booking->id }}</td>
                        <td class="px-5 md:px-10 py-6 md:py-8">
                            <div class="flex items-center space-x-3 md:space-x-4">
                                <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 font-black text-[10px] uppercase shrink-0">
                                    {{ substr($booking->customerName, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <span class="text-xs font-bold text-slate-700 block truncate">{{ $booking->customerName }}</span>
                                    {{-- ID, time & status inline on mobile (columns hidden) --}}
                                    <span class="lg:hidden block text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">#{{ $booking->id }} · {{ $booking->createdAt ? $booking->createdAt->format('d M Y') : '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-8 text-xs font-bold text-slate-400 hidden lg:table-cell">{{ $booking->createdAt ? $booking->createdAt->format('d M Y, H:i') : '-' }}</td>
                        <td class="px-5 md:px-10 py-6 md:py-8 text-xs font-black text-slate-900 text-right whitespace-nowrap">{{ \App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency) }}</td>
                        <td class="px-10 py-8 text-center hidden sm:table-cell">
                            <span class="px-4 py-1.5 rounded-full text-[9px] font-black uppercase tracking-widest bg-green-50 text-green-600 border border-green-100">
                                Berhasil
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-10 py-10 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-folder-open text-3xl text-slate-200 mb-4"></i>
                                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">Belum ada data transaksi yang tersimpan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="p-10 border-t border-slate-50">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

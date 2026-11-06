@extends('admin.layout')

@section('title', 'Bookings')
@section('page-title', 'Booking Management')

@section('content')
<div class="space-y-8" x-data="{ 
    selected: [],
    
    toggleAll(ids) {
        let allChecked = ids.every(id => this.selected.includes(id));
        if (allChecked) {
            this.selected = this.selected.filter(id => !ids.includes(id));
        } else {
            this.selected = [...new Set([...this.selected, ...ids])];
        }
    },
    
    isAllChecked(ids) {
        return ids.length > 0 && ids.every(id => this.selected.includes(id));
    },

    async bulkDelete() {
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} pesanan yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.bookings.bulk-destroy') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selected })
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                alert('Gagal menghapus beberapa pesanan.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus.');
        }
    }
}">
    <!-- Action Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Daftar Reservasi</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Kelola pesanan paket wisata</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.bookings.export', request()->all()) }}" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center shadow-sm">
                <i class="fas fa-file-csv mr-2 text-green-500"></i> Export CSV
            </a>
            <a href="{{ route('admin.bookings.create') }}" class="flex-1 sm:flex-none bg-slate-900 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-xl shadow-slate-200 text-center">
                <i class="fas fa-plus mr-2"></i> Tambah Pesanan
            </a>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
        <form method="GET" class="space-y-6">
            <div class="flex flex-wrap items-end gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[250px] group">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                    <div class="relative">
                        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition text-xs"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Kode booking, nama pelanggan..." 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                    </div>
                </div>

                <!-- Type Filter -->
                <div class="w-full sm:w-40">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tipe</label>
                    <select name="type" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                        <option value="">Semua Tipe</option>
                        <option value="package" {{ request('type') == 'package' ? 'selected' : '' }}>📦 Paket</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-40">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                    <select name="status" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>🏁 Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                    </select>
                </div>

                <!-- Date Range (Simplified) -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full lg:w-auto">
                    <div class="group">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                            class="w-full lg:w-40 px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                    </div>
                    <div class="group">
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                            class="w-full lg:w-40 px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="flex items-center gap-2 ml-auto">
                    @if(request()->anyFilled(['search', 'type', 'status', 'date_from', 'date_to']))
                        <a href="{{ route('admin.bookings.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 hover:text-slate-600 transition shadow-sm" title="Reset Filter">
                            <i class="fas fa-rotate-left text-xs"></i>
                        </a>
                    @endif
                    <button type="submit" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-lg shadow-slate-100">
                        Filter Data
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Grouped Table Content -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 overflow-hidden shadow-sm relative">
        <div class="overflow-x-auto">
            <table class="w-full">
                <tbody class="divide-y divide-slate-50">
                    @php 
                        $groupedBookings = $bookings->groupBy(fn($b) => $b->startDate ? $b->startDate->format('Y-m-d') : 'No Date');
                    @endphp

                    @forelse($groupedBookings as $date => $group)
                        @php $groupIds = $group->pluck('id')->toArray(); @endphp
                        <tr class="bg-slate-50/50">
                            <td colspan="6" class="px-8 py-4">
                                <div class="flex items-center gap-4">
                                    <input type="checkbox" 
                                        @click="toggleAll(@js($groupIds))"
                                        :checked="isAllChecked(@js($groupIds))"
                                        class="w-5 h-5 rounded-lg border-slate-300 text-toba-green focus:ring-toba-green/20 transition cursor-pointer">
                                    
                                    <div class="flex items-center gap-2">
                                        <div class="w-1.5 h-1.5 rounded-full bg-toba-green"></div>
                                        <span class="text-[10px] font-black text-slate-900 uppercase tracking-[0.25em]">
                                            @if($date === 'No Date') Tanpa Tanggal @else {{ \Carbon\Carbon::parse($date)->format('l, d F Y') }} @endif
                                        </span>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-slate-200/50 text-[9px] font-black text-slate-400 uppercase tracking-widest ml-auto">
                                        {{ $group->count() }} Pesanan
                                    </span>
                                </div>
                            </td>
                        </tr>

                        @foreach($group as $booking)
                            <tr class="group hover:bg-slate-50/30 transition duration-300" :class="selected.includes({{ $booking->id }}) ? 'bg-toba-green/5' : ''">
                                <td class="pl-8 py-5 shrink-0 w-10">
                                    <input type="checkbox" 
                                        value="{{ $booking->id }}" 
                                        x-model="selected"
                                        class="w-5 h-5 rounded-lg border-slate-300 text-toba-green focus:ring-toba-green/20 transition cursor-pointer">
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-5">
                                        <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shadow-sm group-hover:bg-slate-900 group-hover:text-white transition shrink-0">
                                            <i class="fas fa-box-archive text-xs"></i>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-0.5 truncate">{{ $booking->bookingCode }}</p>
                                            <h4 class="text-sm font-black text-slate-900 tracking-tight truncate">{{ $booking->customerName }}</h4>
                                            <div class="md:hidden flex flex-wrap items-center gap-2 mt-1">
                                                <p class="text-[10px] font-bold text-slate-500 truncate max-w-[150px]">{{ $booking->package->name ?? 'N/A' }}</p>
                                                <span class="text-[9px] font-black text-slate-900 uppercase">{{ \App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-8 py-5 hidden md:table-cell">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Item</p>
                                    <p class="text-xs font-bold text-slate-700 truncate max-w-[200px]">{{ $booking->package->name ?? 'N/A' }}</p>
                                </td>

                                <td class="px-8 py-5 text-center hidden md:table-cell">
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Total Bayar</p>
                                    <p class="text-xs font-black text-slate-900">{{ \App\Helpers\CurrencyHelper::formatIn($booking->totalPrice, $booking->currency) }}</p>
                                </td>

                                <td class="px-6 md:px-8 py-5">
                                    <div class="flex justify-center md:justify-center">
                                        @php
                                            $colors = [
                                                'pending' => 'bg-amber-50 text-amber-600',
                                                'confirmed' => 'bg-green-50 text-green-600',
                                                'completed' => 'bg-blue-50 text-blue-600',
                                                'cancelled' => 'bg-rose-50 text-rose-600',
                                            ];
                                            $color = $colors[$booking->status] ?? 'bg-slate-50 text-slate-500';
                                        @endphp
                                        <span class="px-3 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $color }}">
                                            {{ $booking->status }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-6 md:px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-2 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <a href="https://wa.me/{{ (str_starts_with($booking->customerPhone ?? '', '0') ? '62' . substr(preg_replace('/[^0-9]/', '', $booking->customerPhone), 1) : preg_replace('/[^0-9]/', '', $booking->customerPhone ?? '')) }}" target="_blank" class="w-11 h-11 flex items-center justify-center rounded-xl bg-green-500 text-white shadow-lg transition transform hover:-translate-y-0.5" title="WhatsApp">
                                            <i class="fab fa-whatsapp text-xs"></i>
                                        </a>

                                        @if($booking->status === 'pending')
                                        <form action="{{ route('admin.bookings.status', $booking) }}" method="POST" class="inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-xl bg-blue-500 text-white shadow-lg transition transform hover:-translate-y-0.5" title="Confirm">
                                                <i class="fas fa-check text-[10px]"></i>
                                            </button>
                                        </form>
                                        @endif

                                        <a href="{{ route('admin.bookings.show', $booking) }}" class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-900 text-white shadow-lg transition transform hover:-translate-y-0.5" title="Detail">
                                            <i class="fas fa-chevron-right text-[10px]"></i>
                                        </a>

                                        <a href="{{ route('admin.bookings.invoice', $booking->bookingCode) }}" target="_blank" class="w-11 h-11 flex items-center justify-center rounded-xl bg-green-700 text-white shadow-lg transition transform hover:-translate-y-0.5" title="View PDF">
                                            <i class="fas fa-file-pdf text-[10px]"></i>
                                        </a>

                                        <a href="{{ route('admin.bookings.invoice.download', $booking->bookingCode) }}" class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-100 text-slate-600 shadow-sm transition transform hover:-translate-y-0.5 border border-slate-200" title="Download PDF">
                                            <i class="fas fa-file-arrow-down text-[10px]"></i>
                                        </a>

                                        <form action="{{ route('admin.bookings.destroy', $booking) }}" method="POST" class="inline" onsubmit="return confirm('Hapus pesanan ini?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-11 h-11 flex items-center justify-center rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition shadow-sm">
                                                <i class="fas fa-trash-can text-[10px]"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-14 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-300">
                                    <i class="fas fa-folder-open text-6xl mb-4"></i>
                                    <p class="text-[10px] font-black uppercase tracking-[0.4em]">Tidak ada data ditemukan</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bookings->hasPages())
        <div class="px-10 py-6 border-t border-slate-50 bg-slate-50/20 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Menampilkan <span class="text-slate-900">{{ $bookings->firstItem() }}</span> - <span class="text-slate-900">{{ $bookings->lastItem() }}</span> dari <span class="text-slate-900">{{ $bookings->total() }}</span> Pesanan
            </p>
            {{ $bookings->appends(request()->all())->links() }}
        </div>
        @endif
    </div>

    <!-- Floating Bulk Actions -->
    <div x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-[calc(1.5rem+env(safe-area-inset-bottom))] md:bottom-10 left-1/2 -translate-x-1/2 z-[100] w-full max-w-md px-4"
         x-cloak>
        <div class="bg-slate-900 text-white rounded-[2.5rem] p-4 md:p-5 shadow-2xl flex flex-col sm:flex-row gap-3 sm:items-center justify-between border border-white/10 backdrop-blur-xl bg-opacity-90">
            <div class="flex items-center gap-4 pl-4">
                <div class="w-10 h-10 rounded-2xl bg-toba-green flex items-center justify-center text-white text-sm font-black shadow-lg shadow-toba-green/20">
                    <span x-text="selected.length"></span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terpilih</p>
                    <p class="text-xs font-bold text-white">Item siap dikelola</p>
                </div>
            </div>
            <div class="flex items-center gap-2 pr-2">
                <button @click="selected = []" class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition">
                    Batal
                </button>
                <button @click="bulkDelete()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-xl shadow-rose-900/30">
                    <i class="fas fa-trash-can mr-2"></i> Hapus Massal
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('admin.layout')

@section('title', 'Pelanggan')
@section('page-title', 'Manajemen Pelanggan (CRM)')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Database Pelanggan</span>
@endsection

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
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} pelanggan yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.customers.bulk-destroy') }}', {
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
                alert('Gagal menghapus beberapa pelanggan.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus.');
        }
    }
}">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Database Pelanggan</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Manajemen profil dan histori transaksi wisatawan</p>
        </div>
        <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
            <a href="{{ route('admin.customers.export', request()->all()) }}" class="flex-1 lg:flex-none bg-white border border-slate-200 text-slate-600 px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center">
                <i class="fas fa-file-excel mr-2 text-green-500"></i> Export Excel
            </a>
            <a href="{{ route('admin.customers.create') }}" class="flex-1 lg:flex-none bg-slate-900 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-xl shadow-slate-200 text-center">
                <i class="fas fa-user-plus mr-2"></i> Tambah Pelanggan
            </a>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[300px] relative group">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, atau telepon..." 
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                </div>
            </div>

            <div class="w-full sm:w-40">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Min. Bookings</label>
                <input type="number" name="min_bookings" value="{{ request('min_bookings') }}" placeholder="0" 
                    class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
            </div>

            <div class="w-full sm:w-40">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Min. Spent (IDR)</label>
                <input type="number" name="min_spent" value="{{ request('min_spent') }}" placeholder="0" 
                    class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'min_bookings', 'min_spent']))
                    <a href="{{ route('admin.customers.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 hover:text-slate-600 transition shadow-sm">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </a>
                @endif
                <button type="submit" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-lg shadow-slate-100">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Customers List -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="pl-8 py-5 w-10">
                            <input type="checkbox" 
                                @click="toggleAll(@js($customers->pluck('id')->toArray()))"
                                :checked="isAllChecked(@js($customers->pluck('id')->toArray()))"
                                class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                        </th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Pelanggan</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest text-center hidden md:table-cell">Bookings</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest text-center hidden md:table-cell">Total Spent</th>
                        <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Terakhir Pesan</th>
                        <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($customers as $customer)
                        <tr class="group hover:bg-slate-50/30 transition-colors" :class="selected.includes({{ $customer->id }}) ? 'bg-green-100/50' : ''">
                            <td class="pl-8 py-5">
                                <input type="checkbox" 
                                    value="{{ $customer->id }}" 
                                    x-model="selected"
                                    class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                            </td>
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center font-black text-xs uppercase group-hover:bg-slate-900 group-hover:text-white transition shrink-0">
                                        {{ substr($customer->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-black text-slate-900 tracking-tight truncate">{{ $customer->name }}</h4>
                                        <p class="text-[10px] font-bold text-slate-400 truncate">{{ $customer->email }}</p>
                                        {{-- Condensed stats for mobile (columns hidden) --}}
                                        <div class="md:hidden flex flex-wrap items-center gap-x-2 gap-y-1 mt-1.5">
                                            <span class="px-2 py-0.5 bg-slate-100 text-slate-600 rounded-md text-[9px] font-black">{{ $customer->total_bookings }} Trip</span>
                                            <span class="text-[10px] font-black text-slate-900">{{ \App\Helpers\CurrencyHelper::formatIn($customer->total_spent, 'IDR') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5 text-center hidden md:table-cell">
                                <span class="px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black">{{ $customer->total_bookings }} Trip</span>
                            </td>
                            <td class="px-8 py-5 text-center hidden md:table-cell">
                                <span class="text-xs font-black text-slate-900">{{ \App\Helpers\CurrencyHelper::formatIn($customer->total_spent, 'IDR') }}</span>
                            </td>
                            <td class="px-8 py-5 hidden lg:table-cell">
                                @if($customer->last_booking_at)
                                    <p class="text-xs font-bold text-slate-600">{{ $customer->last_booking_at->format('d M Y') }}</p>
                                    <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ $customer->last_booking_at->diffForHumans() }}</p>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-8 py-5 text-right">
                                <div class="flex items-center justify-end gap-2 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.customers.show', $customer) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm" title="Lihat Profil">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                    <a href="{{ route('admin.customers.edit', $customer) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm" title="Edit Data">
                                        <i class="fas fa-pencil text-[10px]"></i>
                                    </a>
                                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-300 hover:bg-rose-500 hover:text-white transition shadow-sm">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-14 text-center">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">Belum ada data pelanggan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="px-10 py-6 border-t border-slate-50 bg-slate-50/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-900">{{ $customers->firstItem() }}</span> - <span class="text-slate-900">{{ $customers->lastItem() }}</span> dari <span class="text-slate-900">{{ $customers->total() }}</span> Pelanggan
                </p>
                {{ $customers->appends(request()->all())->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Bulk Actions -->
    <div x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] w-full max-w-md px-4"
         x-cloak>
        <div class="bg-slate-900 text-white rounded-[2.5rem] p-5 shadow-2xl flex items-center justify-between border border-white/10 backdrop-blur-xl bg-opacity-90">
            <div class="flex items-center gap-4 pl-4">
                <div class="w-10 h-10 rounded-2xl bg-green-700 flex items-center justify-center text-white text-sm font-black shadow-lg">
                    <span x-text="selected.length"></span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terpilih</p>
                    <p class="text-xs font-bold text-white">Siap dikelola</p>
                </div>
            </div>
            <div class="flex items-center gap-2 pr-2">
                <button @click="selected = []" class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition">
                    Batal
                </button>
                <button @click="bulkDelete()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-xl shadow-rose-900/30">
                    <i class="fas fa-trash-can mr-2 text-xs"></i> Hapus Massal
                </button>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

@extends('admin.layout')

@section('title', 'Manajemen Paket Tour')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Paket Tour</span>
@endsection

@section('content')
@php
    $bgColor = 'bg-green-500';
    $shadowColor = 'shadow-green-100';
@endphp

<div class="space-y-6 w-full max-w-none" x-data="{ 
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
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} paket yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.packages.bulk-destroy') }}', {
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
                alert('Gagal menghapus beberapa paket.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus.');
        }
    }
}">
    <!-- Action Header (Themed) -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between bg-white p-6 md:p-8 rounded-[2.5rem] md:rounded-[3rem] shadow-sm border border-slate-50 gap-6">
        <div class="flex items-center gap-4 md:gap-6">
            <div class="w-12 h-12 md:w-16 md:h-16 rounded-[1.25rem] md:rounded-[1.5rem] {{ $bgColor }} flex items-center justify-center text-white shadow-xl {{ $shadowColor }}">
                <i class="fas fa-box-archive text-xl md:text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">Katalog Paket Tour</h1>
                <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                    Dokumentasi Perjalanan Wisatawan
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 w-full md:w-auto">
            <a href="{{ route('admin.packages.export', request()->all()) }}" class="flex-1 md:flex-none bg-white border border-slate-200 text-slate-600 px-5 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center">
                <i class="fas fa-file-excel mr-2 text-green-500"></i> Export
            </a>
            <a href="{{ route('admin.packages.create') }}" class="flex-1 md:flex-none bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-200 flex items-center justify-center gap-2">
                <i class="fas fa-plus-circle text-sm"></i> Baru
            </a>
        </div>
    </div>

    <!-- Quick Stats Summary (Themed) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-50 shadow-sm relative overflow-hidden group">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Tour</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ $packages->total() }}</h3>
        </div>
        <div class="{{ $bgColor }} p-8 rounded-[2.5rem] shadow-xl {{ $shadowColor }} relative overflow-hidden group text-white">
            <p class="text-[9px] font-black text-white/70 uppercase tracking-widest mb-3">Manajemen Massal</p>
            <button @click="toggleAll(@js($packages->pluck('id')->toArray()))" class="w-full py-2.5 bg-white/20 hover:bg-white/30 rounded-xl text-[10px] font-black uppercase tracking-widest transition">
                <span x-text="isAllChecked(@js($packages->pluck('id')->toArray())) ? 'Batal Pilih' : 'Pilih Semua Halaman'"></span>
            </button>
        </div>
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-50 shadow-sm relative overflow-hidden group">
            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2">Top Unggulan</p>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter">{{ $packages->where('isFeatured', true)->count() }}</h3>
        </div>

    </div>

    <!-- Enhanced Filter Bar -->
    <div class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-50 shadow-sm">
        <form method="GET" class="flex flex-col lg:flex-row lg:items-center gap-6">
            <div class="flex-1 relative w-full">
                <i class="fas fa-search absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama paket..." 
                    class="w-full pl-14 pr-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-slate-100 font-bold text-[11px] text-slate-900 transition">
            </div>

            <div class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                <select name="status" onchange="this.form.submit()" class="flex-1 lg:flex-none px-6 py-4 bg-slate-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-2 focus:ring-slate-100">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Draft</option>
                </select>

                <select name="featured" onchange="this.form.submit()" class="flex-1 lg:flex-none px-6 py-4 bg-slate-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-2 focus:ring-slate-100">
                    <option value="">Semua Unggulan</option>
                    <option value="yes" {{ request('featured') == 'yes' ? 'selected' : '' }}>Featured</option>
                </select>

                @if(request()->anyFilled(['search', 'status', 'featured']))
                    <a href="{{ route('admin.packages.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-500 rounded-xl hover:bg-slate-200 transition">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Premium Package Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
        @forelse($packages as $package)
            <div class="group bg-white rounded-[2.5rem] md:rounded-[3rem] overflow-hidden border border-slate-50 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition duration-500 flex flex-col relative"
                 :class="selected.includes({{ $package->id }}) ? 'ring-4 ring-green-500' : ''">
                
                {{-- Bulk Checkbox --}}
                <div class="absolute top-6 left-6 z-20">
                    <input type="checkbox" 
                        value="{{ $package->id }}" 
                        x-model="selected"
                        class="w-6 h-6 rounded-lg border-white/20 bg-black/20 backdrop-blur-md text-green-500 focus:ring-green-500/20 transition cursor-pointer">
                </div>

                <div class="h-64 relative overflow-hidden bg-slate-50">
                    @if($package->images && count($package->images) > 0)
                        <img src="{{ $package->image_url }}" alt="{{ $package->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-200">
                            <i class="fas fa-box-open text-5xl mb-4"></i>
                            <span class="text-[10px] font-black uppercase tracking-widest">No Image</span>
                        </div>
                    @endif

                    <div class="absolute top-6 right-20 flex flex-col gap-2">
                        @if($package->isFeatured)
                            <span class="px-4 py-2 rounded-xl bg-amber-400 text-white text-[9px] font-black uppercase tracking-widest shadow-lg shadow-amber-200 flex items-center gap-2">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        @endif
                    </div>

                    <div class="absolute top-6 right-6">
                        <span class="px-4 py-2 rounded-xl text-[9px] font-black uppercase tracking-widest {{ $package->status === 'active' ? $bgColor . ' text-white' : 'bg-white text-rose-500' }} shadow-lg">
                            {{ $package->status }}
                        </span>
                    </div>

                    <div class="absolute bottom-6 right-6">
                        <div class="bg-slate-900 text-white px-6 py-3 rounded-2xl shadow-2xl border border-white/10 backdrop-blur-md">
                            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest leading-none mb-1 text-center">Mulai Dari</p>
                            <p class="text-lg font-black tracking-tight leading-none">{{ \App\Helpers\CurrencyHelper::formatIn($package->price, 'MYR') }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-10 flex-1 flex flex-col">
                    <div class="flex items-center gap-3 mb-4 text-slate-400">
                        <span class="text-[10px] font-black uppercase tracking-widest"><i class="fas fa-map-marker-alt mr-2 text-green-500"></i> {{ $package->locationTag ?? 'Sumatera Utara' }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest"><i class="fas fa-clock mr-2 text-green-500"></i> {{ $package->duration ?? '3D2N' }}</span>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-900 tracking-tight mb-4 leading-tight group-hover:text-green-600 transition-colors">{{ $package->name }}</h3>
                    <p class="text-sm font-medium text-slate-400 line-clamp-2 leading-relaxed mb-8 flex-1">
                        {{ $package->shortDescription ?? 'Deskripsi paket belum tersedia.' }}
                    </p>

                    <div class="flex items-center justify-between pt-8 border-t border-slate-50 mt-auto">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.packages.edit', $package) }}" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-slate-900 hover:text-white transition shadow-sm">
                                <i class="fas fa-pencil text-xs"></i>
                            </a>
                            <a href="{{ route('admin.packages.show', $package) }}" class="w-10 h-10 rounded-xl bg-slate-50 text-slate-400 flex items-center justify-center hover:bg-slate-900 hover:text-white transition shadow-sm">
                                <i class="fas fa-external-link-alt text-xs"></i>
                            </a>
                            <form action="{{ route('admin.packages.toggle-status', $package) }}" method="POST" onsubmit="return confirm('{{ $package->status === 'active' ? 'Nonaktifkan paket ini?' : 'Aktifkan paket ini?' }}')">
                                @csrf
                                <button type="submit" class="w-10 h-10 rounded-xl {{ $package->status === 'active' ? 'bg-amber-50 text-amber-600 hover:bg-amber-500' : 'bg-green-50 text-green-600 hover:bg-green-600' }} flex items-center justify-center hover:text-white transition shadow-sm" title="{{ $package->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    <i class="fas {{ $package->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }} text-xs"></i>
                                </button>
                            </form>
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.packages.duplicate', $package) }}" method="POST" onsubmit="return confirm('Duplikat paket ini?')">
                                @csrf
                                <button type="submit" class="w-10 h-10 rounded-xl bg-slate-50 text-green-700 flex items-center justify-center hover:bg-green-800 hover:text-white transition shadow-sm" title="Duplikat Paket">
                                    <i class="fas fa-copy text-xs"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Hapus paket ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-10 h-10 rounded-xl bg-rose-50 text-rose-500 flex items-center justify-center hover:bg-rose-600 hover:text-white transition shadow-sm" title="Hapus Paket">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white rounded-[4rem] border border-dashed border-slate-200">
                <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 text-slate-200">
                    <i class="fas fa-box-open text-4xl"></i>
                </div>
                <h5 class="text-2xl font-black text-slate-900 mb-2">Daftar Paket Tour Kosong</h5>
                <a href="{{ route('admin.packages.create') }}" class="mt-6 inline-block bg-slate-900 text-white px-10 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition">Buat Paket Sekarang</a>
            </div>
        @endforelse
    </div>

    @if($packages->hasPages())
        <div class="px-10 py-8 bg-white rounded-[3rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Menampilkan <span class="text-slate-900">{{ $packages->firstItem() }}</span> - <span class="text-slate-900">{{ $packages->lastItem() }}</span> dari <span class="text-slate-900">{{ $packages->total() }}</span> Paket
            </p>
            {{ $packages->appends(request()->all())->links() }}
        </div>
    @endif

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
                <div class="w-10 h-10 rounded-2xl {{ $bgColor }} flex items-center justify-center text-white text-sm font-black shadow-lg">
                    <span x-text="selected.length"></span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terpilih</p>
                    <p class="text-xs font-bold text-white">Paket siap dihapus</p>
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
@endsection

@extends('admin.layout')

@section('title', 'Manajemen Galeri Tour')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Galeri Tour</span>
@endsection

@section('content')
@php
    $bgColor = 'bg-green-800';
    $textColor = 'text-green-800';
    $shadowColor = 'shadow-green-200';
    $darkBg = 'bg-green-950';
@endphp

<div x-data="{
    dragging: false,
    preview: null,
    previewUrl: '',
    previews: [],
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
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} foto yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.gallery.bulk-destroy') }}', {
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
                alert('Gagal menghapus beberapa foto.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus.');
        }
    },

    showPreview(url) { this.previewUrl = url; this.preview = true; },
    async copyUrl(url) {
        const fullUrl = window.location.origin + url;
        await navigator.clipboard.writeText(fullUrl);
        alert('URL Galeri disalin!');
    },

    openMediaPickerForGallery() {
        window.dispatchEvent(new CustomEvent('open-media-picker', { 
            detail: { 
                callback: (item) => {
                    this.addMediaToGallery([item.id]);
                } 
            } 
        }));
    },

    async addMediaToGallery(ids) {
        try {
            const response = await fetch('{{ route('admin.gallery.store-from-media') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    media_ids: ids,
                    category: 'tour'
                })
            });
            const data = await response.json();
            if (data.success) {
                window.location.reload();
            }
        } catch (e) {
            console.error(e);
        }
    }
}" class="space-y-6 w-full max-w-none">

    <!-- Previews Floating Bar -->
    <template x-if="previews.length > 0">
        <div class="fixed top-10 left-1/2 -translate-x-1/2 z-[200] bg-white/90 backdrop-blur-xl p-4 rounded-[2rem] shadow-2xl border border-slate-100 flex items-center gap-4 animate-bounce-in">
            <div class="flex -space-x-4">
                <template x-for="url in previews.slice(0, 5)" :key="url">
                    <img :src="url" class="w-12 h-12 rounded-xl object-cover border-4 border-white shadow-sm">
                </template>
                <template x-if="previews.length > 5">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center text-[10px] font-black border-4 border-white shadow-sm">
                        +<span x-text="previews.length - 5"></span>
                    </div>
                </template>
            </div>
            <div class="pr-4">
                <p class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Mengunggah <span x-text="previews.length"></span> Foto...</p>
                <div class="w-full h-1 bg-slate-100 rounded-full mt-1 overflow-hidden">
                    <div class="h-full bg-toba-green animate-progress"></div>
                </div>
            </div>
        </div>
    </template>

    <!-- Hero Header -->
    <div class="bg-white p-6 md:p-8 rounded-[2.5rem] md:rounded-[3rem] shadow-sm border border-slate-50 flex flex-col md:flex-row md:items-center justify-between gap-6 md:gap-8">
        <div class="flex items-center gap-4 md:gap-5">
            <div class="w-12 h-12 md:w-14 md:h-14 rounded-2xl {{ $bgColor }} flex items-center justify-center text-white shadow-xl {{ $shadowColor }}">
                <i class="fas fa-camera-retro text-xl md:text-2xl"></i>
            </div>
            <div>
                <h2 class="text-xl md:text-2xl font-black text-slate-900 tracking-tight">Galeri Tour</h2>
                <p class="text-[9px] md:text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                    Dokumentasi Perjalanan Wisatawan
                </p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
            <a href="{{ route('admin.gallery.export', request()->all()) }}" class="bg-white border border-slate-200 text-slate-600 px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center">
                <i class="fas fa-file-excel mr-2 text-green-500"></i> Export
            </a>
        </div>
    </div>

    <!-- Enhanced Filter Bar -->
    <div class="bg-white p-6 md:p-8 rounded-[2rem] border border-slate-50 shadow-sm">
        <form method="GET" class="flex flex-col lg:flex-row lg:items-end gap-6">
            <div class="flex-1 w-full">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Rentang Tanggal Unggah</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-900 transition focus:ring-4 focus:ring-toba-green/10">
                    </div>
                    <div class="relative">
                        <i class="fas fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 text-[10px]"></i>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" 
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-xl font-bold text-xs text-slate-900 transition focus:ring-4 focus:ring-toba-green/10">
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                <button type="submit" class="flex-1 lg:flex-none bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-100">
                    Filter Galeri
                </button>
                @if(request()->anyFilled(['start_date', 'end_date']))
                    <a href="{{ route('admin.gallery.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 transition">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </a>
                @endif
                <button type="button" @click="toggleAll(@js($images->pluck('id')->toArray()))" class="flex-1 lg:flex-none px-6 py-3.5 bg-slate-100 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-200 transition">
                    <span x-text="isAllChecked(@js($images->pluck('id')->toArray())) ? 'Batal' : 'Pilih Semua'"></span>
                </button>
            </div>
        </form>
    </div>

    <!-- Upload Hub -->
    <div class="{{ $darkBg }} rounded-[2.5rem] md:rounded-[3.5rem] p-8 md:p-14 shadow-2xl relative overflow-hidden">
        <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row gap-10 md:gap-14 items-center">
            <div class="w-full lg:w-1/3 space-y-6 text-center lg:text-left">
                <div>
                    <span class="px-3 py-1 bg-white/10 rounded-full text-[9px] font-black text-white/60 uppercase tracking-widest">Multi Upload Hub</span>
                    <h3 class="text-3xl md:text-4xl font-black text-white mt-4 tracking-tight leading-tight">Update Visual <br class="hidden lg:block">
                        <span class="text-green-400">Tour</span>
                    </h3>
                </div>
                <p class="text-white/40 text-sm font-medium leading-relaxed max-w-md mx-auto lg:mx-0">
                    Unggah dokumentasi terbaru untuk mempercantik halaman Galeri Tour. Mendukung banyak file sekaligus.
                </p>
            </div>

            <div class="w-full lg:w-2/3 flex flex-col gap-4">
                <button type="button" @click="openMediaPickerForGallery()" class="w-full py-8 bg-white/5 border border-white/10 rounded-[2.5rem] text-white flex flex-col items-center justify-center gap-4 hover:bg-white/10 transition group">
                    <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center text-white group-hover:scale-110 transition-transform shadow-xl">
                        <i class="fas fa-images text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-xl font-black text-white mb-1 tracking-tight">Pilih dari Galeri Pusat</h4>
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-white/50">Gunakan aset yang sudah diunggah</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <!-- Gallery View -->
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 md:gap-6">
        @forelse($images as $image)
            <div class="group bg-white rounded-[2rem] md:rounded-[2.5rem] overflow-hidden border border-slate-50 shadow-sm hover:shadow-2xl hover:shadow-slate-200/50 transition duration-500 relative"
                 :class="selected.includes({{ $image->id }}) ? 'ring-4 ring-green-700' : ''">
                
                {{-- Bulk Checkbox --}}
                <div class="absolute top-4 left-4 z-20">
                    <input type="checkbox" 
                        value="{{ $image->id }}" 
                        x-model="selected"
                        class="w-5 h-5 rounded-lg border-white/20 bg-black/20 backdrop-blur-md text-green-700 focus:ring-green-700/20 transition cursor-pointer">
                </div>

                <div class="aspect-[4/5] relative overflow-hidden bg-slate-50">
                    <img src="{{ $image->image_url }}" alt="{{ $image->caption }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    
                    <!-- Smart Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 p-6 flex flex-col justify-between">
                        <div class="flex justify-end gap-2">
                            <a href="{{ route('admin.gallery.edit', $image) }}" class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md text-white flex items-center justify-center hover:bg-green-800 transition">
                                <i class="fas fa-pencil text-xs"></i>
                            </a>
                            <button @click="copyUrl('{{ $image->image_url }}')" class="w-9 h-9 rounded-xl bg-white/10 backdrop-blur-md text-white flex items-center justify-center hover:bg-green-800 transition">
                                <i class="fas fa-link text-xs"></i>
                            </button>
                            <form action="{{ route('admin.gallery.destroy', $image) }}" method="POST" onsubmit="return confirm('Hapus?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-xl bg-rose-500/80 text-white flex items-center justify-center hover:bg-rose-600 transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                        
                        <div class="space-y-3">
                            <p class="text-[10px] font-black text-white uppercase tracking-widest truncate">{{ $image->caption ?? 'No Caption' }}</p>
                             <button @click="showPreview('{{ $image->image_url }}')" class="w-full py-3 bg-white text-slate-900 rounded-xl text-[9px] font-black uppercase tracking-widest hover:bg-green-800 hover:text-white transition">
                                Lihat Detail
                            </button>
                        </div>
                    </div>

                    <!-- Date Badge -->
                    <div class="absolute top-4 right-4">
                        <span class="px-3 py-1.5 rounded-full bg-black/40 backdrop-blur-md text-[7px] font-black uppercase tracking-widest text-white/80">
                            {{ $image->createdAt ? $image->createdAt->format('d M Y') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center bg-white rounded-[3.5rem] border border-dashed border-slate-200">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                    <i class="fas fa-camera-retro text-3xl"></i>
                </div>
                <h5 class="text-xl font-black text-slate-900 mb-2">Galeri Tour Kosong</h5>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Ayo unggah momen terbaik!</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($images->hasPages())
        <div class="px-10 py-8 bg-white rounded-[3rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-6">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Menampilkan <span class="text-slate-900">{{ $images->firstItem() }}</span> - <span class="text-slate-900">{{ $images->lastItem() }}</span> dari <span class="text-slate-900">{{ $images->total() }}</span> Foto
            </p>
            {{ $images->appends(request()->all())->links() }}
        </div>
    @endif

    <!-- Floating Bulk Actions -->
    <div x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] w-full max-w-md px-4"
         x-cloak>
        <div class="bg-slate-900 text-white rounded-[2.5rem] p-5 shadow-2xl flex items-center justify-between border border-white/10 backdrop-blur-xl bg-opacity-90">
            <div class="flex items-center gap-4 pl-4">
                <div class="w-10 h-10 rounded-2xl {{ $bgColor }} flex items-center justify-center text-white text-sm font-black shadow-lg">
                    <span x-text="selected.length"></span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terpilih</p>
                    <p class="text-xs font-bold text-white">Siap dihapus</p>
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

    <!-- Full Preview Modal -->
    <template x-if="preview">
        <div class="fixed inset-0 z-[100] bg-slate-900/90 backdrop-blur-md flex items-center justify-center p-8"
             @click.self="preview = false">
            <div class="relative max-w-5xl max-h-full">
                <img :src="previewUrl" class="max-h-[85vh] w-auto rounded-[2rem] shadow-2xl border-4 border-white/10">
                <button @click="preview = false" class="absolute -top-4 -right-4 w-12 h-12 bg-white rounded-full flex items-center justify-center text-slate-900 shadow-2xl hover:bg-rose-500 hover:text-white transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </template>

</div>
@endsection

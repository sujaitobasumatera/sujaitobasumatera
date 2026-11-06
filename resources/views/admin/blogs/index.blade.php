@extends('admin.layout')

@section('title', 'Blogs')
@section('page-title', 'Articles & Publications')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Blog & Artikel</span>
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
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} artikel yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.blogs.bulk-destroy') }}', {
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
                alert('Gagal menghapus beberapa artikel.');
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
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Blog Management</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Kelola artikel dan publikasi untuk wisatawan</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.blogs.export', request()->all()) }}" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center">
                <i class="fas fa-file-excel mr-2 text-green-500"></i> Export Excel
            </a>
            <a href="{{ route('admin.blogs.create') }}" class="flex-1 sm:flex-none bg-slate-900 text-white px-6 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-200 text-center">
                <i class="fas fa-plus mr-2"></i> New Article
            </a>
        </div>
    </div>

    <!-- Enhanced Filter Bar -->
    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[300px] relative group">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Pencarian</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan judul..." 
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                </div>
            </div>

            <div class="w-full sm:w-48">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Kategori</label>
                <select name="category" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                    <option value="">Semua Kategori</option>
                    @foreach(['Tips Wisata', 'Destinasi', 'Kuliner', 'Budaya', 'Event', 'Promo'] as $cat)
                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-40">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Status</label>
                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Published</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </div>

            @if(request()->anyFilled(['search', 'category', 'status']))
                <a href="{{ route('admin.blogs.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 hover:text-slate-600 transition shadow-sm">
                    <i class="fas fa-rotate-left text-xs"></i>
                </a>
            @endif
        </form>
    </div>

    <!-- Clean Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <tbody class="divide-y divide-slate-50">
                    <tr class="bg-slate-50/50">
                        <td class="pl-8 py-4 w-10">
                            <input type="checkbox" 
                                @click="toggleAll(@js($blogs->pluck('id')->toArray()))"
                                :checked="isAllChecked(@js($blogs->pluck('id')->toArray()))"
                                class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                        </td>
                        <td colspan="3" class="px-4 py-4">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.25em]">Daftar Artikel</span>
                        </td>
                    </tr>
                    @forelse($blogs as $blog)
                        <tr class="group hover:bg-slate-50/50 transition duration-300" :class="selected.includes({{ $blog->id }}) ? 'bg-slate-900/5' : ''">
                            <td class="pl-8 py-6">
                                <input type="checkbox" 
                                    value="{{ $blog->id }}" 
                                    x-model="selected"
                                    class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-6">
                                    <div class="w-14 h-14 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shadow-sm group-hover:scale-110 transition-transform overflow-hidden shrink-0">
                                        @if($blog->image)
                                            <img src="{{ $blog->image_url }}" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-newspaper text-slate-300 text-xs"></i>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-black text-slate-900 tracking-tight leading-tight mb-1 truncate max-w-md">{{ $blog->title }}</h4>
                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">{{ $blog->author ?? 'Admin' }} &bull; {{ $blog->category }}</p>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-8 py-6 hidden md:table-cell text-center">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-1">Status</p>
                                <span class="px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest {{ $blog->status === 'published' ? 'bg-green-50 text-green-600' : 'bg-amber-50 text-amber-600' }}">
                                    {{ $blog->status }}
                                </span>
                            </td>

                            <td class="px-6 md:px-8 py-6 text-right">
                                <div class="flex items-center justify-end gap-2 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.blogs.show', $blog) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                    <a href="{{ route('admin.blogs.edit', $blog) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm">
                                        <i class="fas fa-pencil text-[10px]"></i>
                                    </a>
                                    <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin?')">
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
                            <td colspan="4" class="px-8 py-14 text-center">
                                <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">No Blog Posts Available</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($blogs->hasPages())
            <div class="px-10 py-6 border-t border-slate-50 bg-slate-50/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-900">{{ $blogs->firstItem() }}</span> - <span class="text-slate-900">{{ $blogs->lastItem() }}</span> dari <span class="text-slate-900">{{ $blogs->total() }}</span> Artikel
                </p>
                {{ $blogs->appends(request()->all())->links() }}
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

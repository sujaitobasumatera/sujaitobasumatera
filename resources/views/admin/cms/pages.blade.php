@extends('admin.layout')

@section('title', 'CMS Halaman Statis')
@section('page-title', 'Manajemen Halaman Statis')

@section('content')
@php
    $resolve = function($path, $default = '') {
        if (empty($path)) return $default;
        if (Str::startsWith($path, ['http', '//', 'data:', 'blob:'])) return $path;
        $clean = ltrim($path, '/');
        if (Str::startsWith($clean, ['storage/', 'assets/'])) return asset($clean);
        return asset('storage/' . $clean);
    };
@endphp
<div class="space-y-6 pb-14">
    <!-- Header -->
    <div class="flex flex-col gap-2">
        <h1 class="text-3xl font-black text-slate-900 tracking-tight">Halaman Statis</h1>
        <p class="text-sm font-bold text-slate-400">Kelola konten untuk halaman Tentang Kami, Syarat & Ketentuan, dan Kebijakan Privasi.</p>
    </div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cmsPagesHandler', () => ({
        imageUrl: @json($resolve($about['image_url'] ?? '', asset('assets/images/2023/10/003-1.webp'))),
        
        openMedia() {
            window.dispatchEvent(new CustomEvent('open-media-picker', { 
                detail: { 
                    callback: (item) => {
                        let path = item.path;
                        if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
                        if (path.startsWith('storage/')) path = path.replace('storage/', '');
                        this.imageUrl = '/storage/' + path;
                        if (this.$refs.imageUrlInput) {
                            this.$refs.imageUrlInput.value = path;
                        }
                    }
                } 
            }));
        }
    }));
});
</script>
@endpush

    <!-- About Us -->
    <div x-cloak x-data="cmsPagesHandler()" class="bg-white rounded-[2.5rem] p-10 border border-slate-50 shadow-sm">
        <form action="{{ route('admin.cms.save', 'page_about') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-toba-green flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-info-circle text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[12px] font-black text-slate-900 uppercase tracking-[0.2em]">Tentang Kami</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Halaman Visi, Misi & Sejarah</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition">Simpan Perubahan</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                <div class="space-y-6">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Judul Utama</label>
                        <input type="text" name="title" value="{{ $about['title'] ?? 'Mengenal Lebih Dekat Sujai Toba Sumatera' }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-sm">
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Deskripsi Singkat</label>
                        <textarea name="description" rows="4" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-sm">{{ $about['description'] ?? 'Berawal dari kecintaan terhadap keindahan alam Sumatera Utara, Sujai Toba Sumatera hadir untuk memberikan pengalaman perjalanan yang tak terlupakan bagi setiap wisatawan.' }}</textarea>
                    </div>
                    <div class="bg-slate-50 rounded-2xl p-6 space-y-4">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">📊 Statistik Pencapaian</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Angka (misal: 12+)</label>
                                <input type="text" name="stat_years" value="{{ $about['stat_years'] ?? '12+' }}" class="w-full px-4 py-3 bg-white border-none rounded-xl font-black text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Label Stat 1</label>
                                <input type="text" name="stat_years_label" value="{{ $about['stat_years_label'] ?? 'Tahun Pengalaman' }}" class="w-full px-4 py-3 bg-white border-none rounded-xl font-bold text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Angka (misal: 5k+)</label>
                                <input type="text" name="stat_tourists" value="{{ $about['stat_tourists'] ?? '5k+' }}" class="w-full px-4 py-3 bg-white border-none rounded-xl font-black text-sm">
                            </div>
                            <div class="space-y-2">
                                <label class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Label Stat 2</label>
                                <input type="text" name="stat_tourists_label" value="{{ $about['stat_tourists_label'] ?? 'Wisatawan Puas' }}" class="w-full px-4 py-3 bg-white border-none rounded-xl font-bold text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="bg-white border-2 border-dashed border-slate-200 rounded-2xl p-6 flex items-center justify-between group hover:border-toba-green transition">
                            <div class="flex items-center gap-4">
                                <div class="w-16 h-16 rounded-xl bg-slate-100 overflow-hidden shadow-inner">
                                    <img :src="imageUrl" class="w-full h-full object-cover">
                                </div>
                                <div>
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Gambar Utama</p>
                                    <p class="text-[8px] font-bold text-slate-300 uppercase">800x1000px</p>
                                </div>
                            </div>
                            <div class="relative cursor-pointer" @click="openMedia()">
                                <input type="hidden" name="image_url" x-ref="imageUrlInput" value="{{ $about['image_url'] ?? '' }}">
                                <span class="px-3 py-2 bg-slate-900 text-white text-[8px] font-black uppercase tracking-widest rounded-lg group-hover:bg-toba-green transition">Pilih dari Galeri</span>
                            </div>
                        </div>
                        <div class="bg-slate-50 rounded-2xl p-6 space-y-3">
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">💬 Testimoni / Kutipan</p>
                            <textarea name="testimonial_quote" rows="2" placeholder="Isi kutipan testimoni..." class="w-full px-4 py-2 bg-white border-none rounded-xl font-bold text-[11px] leading-tight">{{ $about['testimonial_quote'] ?? 'Layanan terbaik, armada baru, dan guide yang sangat informatif.' }}</textarea>
                            <input type="text" name="testimonial_name" value="{{ $about['testimonial_name'] ?? 'Pelanggan Setia Sujai Toba Sumatera' }}" placeholder="Nama pelanggan..." class="w-full px-4 py-2 bg-white border-none rounded-xl font-bold text-[11px]">
                        </div>
                    </div>
                </div>
                <div class="space-y-6">
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Visi</label>
                        <textarea name="vision" rows="3" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-sm">{{ $about['vision'] ?? 'Membawa Anda menikmati keaslian alam dan budaya Sumatera Utara melalui perjalanan yang tenang, aman, dan berkesan.' }}</textarea>
                    </div>
                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Misi (Pisahkan dengan baris baru)</label>
                        <textarea name="mission" rows="4" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-sm">{{ $about['mission'] ?? "Merancang itinerary yang sesuai dengan ritme liburan Anda.\nMenyediakan transportasi dan akomodasi lokal terbaik.\nMemastikan setiap pelanggan pulang dengan cerita yang indah." }}</textarea>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Terms & Conditions -->
    <div class="bg-white rounded-[2.5rem] p-10 border border-slate-50 shadow-sm">
        <form action="{{ route('admin.cms.save', 'page_terms') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500 flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-file-contract text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[12px] font-black text-slate-900 uppercase tracking-[0.2em]">Syarat & Ketentuan</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Aturan & Regulasi Layanan</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-amber-600 transition">Simpan Perubahan</button>
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Konten Halaman (HTML didukung)</label>
                <textarea name="content" rows="15" class="w-full px-8 py-6 bg-slate-50 border-none rounded-[2rem] font-medium text-sm leading-relaxed">{{ $terms['content'] ?? "<h3>1. Ketentuan Umum</h3><p>Seluruh layanan yang disediakan oleh Sujai Toba Sumatera tunduk pada syarat dan ketentuan yang berlaku...</p>" }}</textarea>
            </div>
        </form>
    </div>

    <!-- Privacy Policy -->
    <div class="bg-white rounded-[2.5rem] p-10 border border-slate-50 shadow-sm">
        <form action="{{ route('admin.cms.save', 'page_privacy') }}" method="POST">
            @csrf
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-green-700 flex items-center justify-center text-white shadow-lg">
                        <i class="fas fa-shield-halved text-sm"></i>
                    </div>
                    <div>
                        <h2 class="text-[12px] font-black text-slate-900 uppercase tracking-[0.2em]">Kebijakan Privasi</h2>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Perlindungan Data Pelanggan</p>
                    </div>
                </div>
                <button type="submit" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-800 transition">Simpan Perubahan</button>
            </div>

            <div class="space-y-3">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Konten Halaman (HTML didukung)</label>
                <textarea name="content" rows="15" class="w-full px-8 py-6 bg-slate-50 border-none rounded-[2rem] font-medium text-sm leading-relaxed">{{ $privacy['content'] ?? "<h3>Kebijakan Privasi</h3><p>Kami berkomitmen untuk menjaga kerahasiaan data pribadi Anda...</p>" }}</textarea>
            </div>
        </form>
    </div>
</div>
@endsection

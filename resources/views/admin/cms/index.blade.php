@extends('admin.layout')

@section('title', 'CMS Halaman Utama')
@section('page-title', 'CMS Halaman Utama')

@section('content')
@php
    $resolve = function($path, $default = '') {
        return imageUrl($path, $default);
    };
@endphp

<div x-cloak x-data="cmsLandingHandler()" class="flex flex-col xl:flex-row gap-8 min-h-[85vh]">

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('cmsLandingHandler', () => ({
        activeTab: 'branding',
        brandName: @json($settings['brand_name'] ?? 'Sujai Toba Sumatera'),
        brandTagline: @json($settings['brand_tagline'] ?? 'SUMATERA UTARA'),
        brandIcon: @json($resolve($settings['brand_icon_url'] ?? '', 'https://ui-avatars.com/api/?name=W&background=0f172a&color=fff')),
        brandLogo: @json($resolve($settings['brand_logo_url'] ?? '')),
        metaTitle: @json($settings['meta_title'] ?? 'Sujai Toba Sumatera | Premium Tour Travel'),
        metaDescription: @json($settings['meta_description'] ?? 'Portal utama Sujai Toba Sumatera. Pilih layanan premium Tour Travel Sumatera Utara.'),
        tourTitle: @json($settings['tour_title'] ?? "Tour &\nTravel."),
        tourSubtitle: @json($settings['tour_subtitle'] ?? 'Eksplorasi keindahan Danau Toba dengan paket liburan eksklusif.'),
        tourImage: @json($resolve($settings['tour_image_url'] ?? '', 'tour')),
        
        updatePreview(e, type) {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                if (type === 'brand') this.brandIcon = url;
                if (type === 'logo') this.brandLogo = url;
                if (type === 'tour') this.tourImage = url;
            }
        },

        openCMSMediaPicker(type) {
            window.dispatchEvent(new CustomEvent('open-media-picker', { 
                detail: { 
                    callback: (item) => {
                        let path = item.path;
                        if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
                        if (path.startsWith('storage/')) path = path.replace('storage/', '');
                        
                        const finalUrl = '/storage/' + path;
                        if (type === 'tour') this.tourImage = finalUrl;
                    } 
                } 
            }));
        }
    }));
});
</script>
@endpush
    
    <!-- LEFT: CONTROL PANEL (Form) -->
    <div class="w-full xl:w-[400px] flex-shrink-0 space-y-6">
        <div class="bg-white p-2 rounded-[2rem] shadow-sm border border-slate-100 flex items-center space-x-1">
            <button @click="activeTab = 'branding'" :class="activeTab === 'branding' ? 'bg-slate-900 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 py-3 rounded-[1.2rem] font-black text-[8px] uppercase tracking-widest transition">Brand</button>
            <button @click="activeTab = 'tour'" :class="activeTab === 'tour' ? 'bg-toba-green text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 py-3 rounded-[1.2rem] font-black text-[8px] uppercase tracking-widest transition">Tour</button>
            <button @click="activeTab = 'seo'" :class="activeTab === 'seo' ? 'bg-green-800 text-white shadow-lg' : 'text-slate-400 hover:bg-slate-50'" class="flex-1 py-3 rounded-[1.2rem] font-black text-[8px] uppercase tracking-widest transition">SEO</button>
        </div>

        <form action="{{ route('admin.cms.save', 'cms_landing') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            
            <div class="bg-white rounded-[2.5rem] p-8 border border-slate-100 shadow-sm overflow-hidden">
                <!-- Branding Tab -->
                <div x-show="activeTab === 'branding'" x-transition class="space-y-6">
                    <div class="bg-amber-50 border border-amber-100 p-8 rounded-[2rem] flex flex-col items-center text-center gap-6">
                        <div class="w-16 h-16 rounded-2xl bg-amber-500 text-white flex items-center justify-center shadow-lg shadow-amber-200">
                            <i class="fas fa-arrows-rotate text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-amber-900 uppercase tracking-widest">Pengaturan Dipindahkan</h3>
                            <p class="text-[10px] font-bold text-amber-700/70 mt-2">Logo, Icon, dan Identitas Brand kini telah dipusatkan di menu **Pengaturan Sistem**.</p>
                        </div>
                        <a href="{{ route('admin.settings.general.index') }}" class="px-6 py-3 bg-amber-600 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-amber-700 transition">
                            Buka Pengaturan Sistem
                        </a>
                    </div>
                </div>

                <!-- Tour Tab -->
                <div x-show="activeTab === 'tour'" x-transition class="space-y-6">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-sm font-black text-toba-green uppercase tracking-widest flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-toba-green"></span> Sisi Tour Travel
                        </h4>
                    </div>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Judul Utama</label>
                            <textarea name="tour_title" x-model="tourTitle" rows="3" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-black text-sm text-slate-900"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Deskripsi</label>
                            <textarea name="tour_subtitle" x-model="tourSubtitle" rows="4" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green font-bold text-slate-600 text-xs leading-relaxed"></textarea>
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Background Image</label>
                            
                            <div class="grid grid-cols-1 gap-4">
                                <div @click="openCMSMediaPicker('tour')" class="h-20 border-2 border-slate-200 rounded-2xl flex flex-col items-center justify-center bg-white hover:border-toba-green hover:bg-green-50 transition group cursor-pointer">
                                    <i class="fas fa-images text-lg text-slate-300 group-hover:text-toba-green mb-1"></i>
                                    <span class="text-[8px] font-black text-slate-400 uppercase group-hover:text-toba-green">Pilih dari Media Library</span>
                                </div>
                            </div>

                            <div class="relative w-full h-32 rounded-2xl overflow-hidden border border-slate-100 shadow-sm mt-4">
                                <img :src="tourImage" class="w-full h-full object-cover">
                                <input type="hidden" name="tour_image_url" :value="tourImage">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Tab -->
                <div x-show="activeTab === 'seo'" x-transition class="space-y-6">
                    <h4 class="text-sm font-black text-green-800 uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-800"></span> SEO & Meta Tags
                    </h4>
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Meta Title (Browser Tab)</label>
                            <input type="text" name="meta_title" x-model="metaTitle" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-800 font-bold text-slate-900 text-sm">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Meta Description</label>
                            <textarea name="meta_description" x-model="metaDescription" rows="5" class="w-full px-5 py-3.5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-800 font-medium text-slate-600 text-xs leading-relaxed"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-[2rem] font-black text-[10px] uppercase tracking-widest shadow-2xl hover:bg-slate-800 transition flex items-center justify-center gap-3">
                <i class="fas fa-cloud-arrow-up"></i> Simpan Publikasi
            </button>
        </form>
    </div>

    <!-- RIGHT: LIVE INTERACTIVE PREVIEW -->
    <div class="flex-1 bg-black rounded-[3.5rem] overflow-hidden relative shadow-[0_40px_100px_-20px_rgba(0,0,0,0.5)] border-8 border-slate-900/50 min-h-[600px] xl:h-[85vh] sticky top-8">
        
        <div class="absolute inset-0 flex flex-col md:flex-row">
            <!-- Full Preview: Tour -->
            <div class="relative w-full h-full group overflow-hidden">
                <div class="absolute inset-0 bg-cover bg-center transition-transform duration-[8s] scale-105" 
                     :style="`background-image: url('${tourImage}')`"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-black/20 to-black/80"></div>
                
                <div class="absolute inset-0 flex flex-col justify-end items-center p-8 md:p-12 text-center z-10">
                    <span class="inline-block px-3 py-1 bg-green-500/10 backdrop-blur-md border border-green-500/20 text-green-400 text-[8px] font-black uppercase tracking-widest rounded-full mb-4 w-fit">Premium Travel</span>
                    <h2 class="text-4xl md:text-6xl font-black text-white leading-[0.85] tracking-tighter whitespace-pre-line mb-6" x-text="tourTitle"></h2>
                    <p class="text-slate-200 text-xs font-medium max-w-[400px] leading-relaxed opacity-80" x-text="tourSubtitle"></p>
                </div>
            </div>
        </div>

        <!-- Center Brand -->
        <div class="absolute top-12 left-1/2 -translate-x-1/2 z-[60] pointer-events-none flex flex-col items-center">
            <template x-if="brandLogo">
                <img :src="brandLogo" class="h-12 md:h-16 w-auto object-contain drop-shadow-2xl">
            </template>
            <template x-if="!brandLogo">
                <div class="flex flex-col items-center">
                    <div class="w-12 h-12 bg-green-600 rounded-2xl flex items-center justify-center text-white font-black text-xl shadow-2xl mb-3">W</div>
                    <h1 class="text-white font-black text-xs tracking-[0.2em] uppercase" x-text="brandName"></h1>
                </div>
            </template>
        </div>

        <!-- Overlay Status -->
        <div class="absolute top-6 left-6 z-50 flex items-center gap-3">
            <div class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white text-[8px] font-black uppercase tracking-widest rounded-lg shadow-lg shadow-green-500/20">
                <span class="w-1 h-1 rounded-full bg-white animate-pulse"></span> Live Sync
            </div>
            <div class="px-3 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 text-white text-[8px] font-black uppercase tracking-widest rounded-lg">Landing Preview</div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    textarea { resize: none; }
</style>
@endsection

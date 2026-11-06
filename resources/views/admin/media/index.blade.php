@extends('admin.layout')

@section('title', 'Media Library')
@section('page-title', 'Pusat Galeri Media')

@section('content')
<!-- Load CropperJS CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>

<div x-data="mediaManager()" 
     @dragover.prevent="isDragging = true" 
     @dragleave.prevent="isDragging = false" 
     @drop.prevent="handleDrop($event)"
     class="flex flex-col lg:flex-row gap-8 min-h-[80vh] relative">
    
    <!-- Drag & Drop Blur Overlay -->
    <div x-show="isDragging" 
         class="absolute inset-0 bg-green-800/80 backdrop-blur-md z-[300] rounded-[3.5rem] flex flex-col items-center justify-center text-white p-8 transition duration-300"
         @dragover.prevent=""
         @drop.prevent="handleDrop($event); isDragging = false"
         @dragleave.prevent="isDragging = false"
         x-cloak>
        <div class="w-32 h-32 bg-white/10 rounded-[3rem] flex items-center justify-center border-4 border-dashed border-white/40 mb-8 animate-bounce">
            <i class="fas fa-cloud-arrow-up text-5xl"></i>
        </div>
        <h3 class="text-3xl font-black tracking-tight">Lepaskan File Anda Di Sini</h3>
        <p class="text-xs font-bold uppercase tracking-widest text-green-300 mt-2">Unggah otomatis ke folder <span class="text-white" x-text="filters.category || 'uploads'"></span></p>
    </div>
    <!-- Left Sidebar: Folder Navigation -->
    <aside class="w-full lg:w-80 shrink-0 space-y-6">
        <!-- Quick Stats -->
        <div class="bg-green-800 rounded-[2.5rem] p-8 text-white shadow-2xl shadow-green-200 relative overflow-hidden group">
            <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:scale-150 transition-transform duration-1000"></div>
            <div class="relative z-10">
                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-green-300">Library Health</p>
                <h4 class="text-3xl font-black mt-2" x-text="stats.orphans">0</h4>
                <p class="text-[9px] font-bold text-green-200/60 uppercase tracking-widest mt-1">Unused Assets found</p>
            </div>
        </div>

        <!-- Image Guidelines Quick Card -->
        <div class="bg-gradient-to-br from-green-800 to-green-950 rounded-[2.5rem] p-6 text-white shadow-xl relative overflow-hidden group">
            <div class="relative z-10">
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] text-green-300">
                    <i class="fas fa-ruler-combined"></i>
                    <span>Ukuran &amp; Rasio</span>
                </div>
                <h4 class="text-base font-black mt-2 leading-tight">Standar Desain Aset</h4>
                <p class="text-[10px] font-medium text-green-100/70 mt-1 leading-relaxed">
                    Galeri 4:3 (1600&times;1200), Brosur (1200&times;900), Hero 16:9 (1920&times;1080).
                </p>
                <button type="button" @click="$dispatch('open-image-guide')"
                        class="mt-4 w-full py-2.5 px-4 bg-green-600 hover:bg-green-500 text-white rounded-xl font-black text-xs uppercase tracking-wider transition shadow-md flex items-center justify-center gap-2">
                    <i class="fas fa-table-list text-xs"></i>
                    <span>Buka Panduan Lengkap</span>
                </button>
            </div>
        </div>

        <!-- Folder List -->
        <nav class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-sm space-y-2">
            <div class="flex items-center justify-between px-4 mb-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Master Folders</p>
                <button @click="createNewFolder()" class="w-6 h-6 rounded-lg bg-slate-50 text-slate-400 hover:bg-green-800 hover:text-white transition flex items-center justify-center">
                    <i class="fas fa-plus text-[10px]"></i>
                </button>
            </div>
            
            <button @click="setCategory('')" 
                    :class="filters.category === '' && activeTab === 'library' ? 'bg-green-100 text-green-800 shadow-sm' : 'text-slate-600 hover:bg-slate-50'"
                    class="w-full flex items-center justify-between px-6 py-4 rounded-2xl transition group">
                <div class="flex items-center gap-4">
                    <i class="fas fa-layer-group text-sm group-hover:scale-110 transition-transform"></i>
                    <span class="text-[11px] font-black uppercase tracking-widest">All Assets</span>
                </div>
                <span class="text-[9px] font-bold opacity-40" x-text="stats.total"></span>
            </button>

            <template x-for="cat in categories" :key="cat.name">
                <div class="group/folder relative">
                    <button @click="setCategory(cat.name)" 
                            :class="filters.category === cat.name && activeTab === 'library' ? 'bg-green-100 text-green-800 shadow-sm' : 'text-slate-600 hover:bg-slate-50'"
                            class="w-full flex items-center justify-between px-6 py-4 rounded-2xl transition group">
                        <div class="flex items-center gap-4">
                            <i class="fas text-sm group-hover:scale-110 transition-transform" :class="cat.icon"></i>
                            <span class="text-[11px] font-black uppercase tracking-widest" x-text="cat.name"></span>
                        </div>
                        <span class="text-[9px] font-bold opacity-40 group-hover:hidden" x-text="cat.count"></span>
                        
                        <!-- Rename Folder Trigger -->
                        <div @click.stop="openRenameFolder(cat.name)" class="hidden group-hover:flex w-6 h-6 bg-white rounded-lg shadow-sm items-center justify-center text-slate-400 hover:text-green-800 transition-colors">
                            <i class="fas fa-pen text-[9px]"></i>
                        </div>
                    </button>
                </div>
            </template>
        </nav>

        <!-- Status Filter -->
        <div class="bg-slate-900 rounded-[2.5rem] p-6 text-white space-y-2">
            <p class="px-4 text-[10px] font-black text-white/30 uppercase tracking-[0.3em] mb-4">Quick Filters</p>
            <button @click="setUsage('all')" 
                    :class="filters.usage === 'all' && activeTab === 'library' ? 'bg-white/10 text-white' : 'text-white/40 hover:text-white'"
                    class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition group">
                <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-circle-nodes text-[10px]"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">All Status</span>
            </button>
            <button @click="setUsage('orphan')" 
                    :class="filters.usage === 'orphan' && activeTab === 'library' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/20' : 'text-white/40 hover:text-white'"
                    class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition group">
                <div class="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-broom text-[10px]"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">Orphan Items</span>
            </button>

            <!-- Storage Health Audit Tab Trigger -->
            <button @click="openAuditPanel()" 
                    :class="activeTab === 'audit' ? 'bg-green-800 text-white shadow-xl shadow-green-950/20' : 'text-white/40 hover:text-white'"
                    class="w-full flex items-center gap-4 px-6 py-4 rounded-2xl transition group border border-white/5 mt-4">
                <div class="w-8 h-8 rounded-xl bg-white/10 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <i class="fas fa-chart-pie text-[10px]"></i>
                </div>
                <span class="text-[11px] font-black uppercase tracking-widest">Storage Health</span>
            </button>
        </div>
    </aside>

    <!-- Main Content: The Grid / Audit Panel -->
    <main class="flex-1 space-y-8">
        
        <!-- Header & Breadcrumbs -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-3 text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">
                    <a href="#" @click.prevent="activeTab = 'library'" class="hover:text-green-800">Library</a>
                    <i class="fas fa-chevron-right text-[8px] opacity-30"></i>
                    <span class="text-green-800" x-text="activeTab === 'audit' ? 'Storage Health' : (filters.category || 'Home')"></span>
                </div>
                <h2 class="text-3xl md:text-5xl font-black text-slate-900 tracking-tighter leading-none">
                    <span x-text="activeTab === 'audit' ? 'STORAGE HEALTH' : (filters.category ? filters.category.toUpperCase() : 'GALLERY HUB')"></span>
                </h2>
            </div>

            <!-- Library View Actions -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto" x-show="activeTab === 'library'">
                <input type="file" id="mediaUpload" multiple class="hidden" @change="uploadFiles($event)">
                <label for="mediaUpload" class="w-full sm:w-auto px-10 py-5 bg-green-800 text-white rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest shadow-2xl shadow-green-300 hover:bg-green-900 hover:-translate-y-1 transition cursor-pointer flex items-center justify-center gap-3 group">
                    <i class="fas fa-upload group-hover:-translate-y-1 transition-transform"></i> Upload Foto
                </label>

                <!-- Dropdown Fitur Lanjutan -->
                <div class="relative" x-data="{ advOpen: false }">
                    <button @click="advOpen = !advOpen" @click.away="advOpen = false" class="w-full sm:w-auto px-6 py-5 bg-white border border-slate-200 text-slate-900 rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-cogs"></i> Fitur Lanjutan <i class="fas fa-chevron-down ml-1"></i>
                    </button>
                    <div x-show="advOpen" x-transition class="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-50 flex flex-col origin-top-right">
                        <!-- Watermark Checkbox Toggle -->
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-slate-50">
                            <input type="checkbox" id="uploadWatermark" x-model="useWatermark" class="w-5 h-5 rounded-md text-green-800 border-slate-300 focus:ring-green-700 cursor-pointer">
                            <label for="uploadWatermark" class="text-[10px] font-black text-slate-500 uppercase tracking-widest cursor-pointer flex-1">Beri Watermark Otomatis</label>
                        </div>
                        
                        <button @click="syncMedia(); advOpen=false" class="text-left px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 border-b border-slate-50 flex items-center gap-3">
                            <i class="fas fa-rotate" :class="syncing ? 'animate-spin' : ''"></i> Sinkronisasi Disk (Sync)
                        </button>
                        <button @click="syncPublicAssets(); advOpen=false" class="text-left px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 border-b border-slate-50 flex items-center gap-3" :disabled="syncingStatic">
                            <i class="fas fa-folder-tree"></i> Sync Aset Statis
                        </button>
                        <button @click="convertAllToWebp(); advOpen=false" class="text-left px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 border-b border-slate-50 flex items-center gap-3" :disabled="converting">
                            <i class="fas fa-magic"></i> Konversi WebP Massal
                        </button>
                        <button @click="openUrlModal(); advOpen=false" class="text-left px-5 py-4 text-[10px] font-black uppercase tracking-widest text-slate-700 hover:bg-slate-50 flex items-center gap-3">
                            <i class="fas fa-globe"></i> Upload Gambar dari URL
                        </button>
                    </div>
                </div>
            </div>

            <!-- Audit View Actions -->
            <div class="flex flex-wrap items-center gap-3" x-show="activeTab === 'audit'" x-cloak>
                <button @click="fetchAuditData()" class="px-8 py-5 bg-white border border-slate-200 text-slate-900 rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest hover:bg-slate-50 transition shadow-sm flex items-center gap-3">
                    <i class="fas fa-rotate" :class="auditLoading ? 'animate-spin' : ''"></i> Rescan Storage
                </button>
                <button @click="activeTab = 'library'" class="px-8 py-5 bg-slate-900 text-white rounded-[1.5rem] font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition flex items-center gap-3 shadow-sm">
                    <i class="fas fa-folder-open"></i> Back to Library
                </button>
            </div>
        </div>

        <!-- 1. LIBRARY TAB VIEW -->
        <div x-show="activeTab === 'library'" class="space-y-8">
            <!-- Search Bar -->
            <div class="relative group">
                <i class="fas fa-search absolute left-7 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-green-800 transition-colors"></i>
                <input type="text" x-model="filters.search" @input.debounce.500ms="fetchMedia()" placeholder="Cari aset dalam folder ini..." 
                       class="w-full pl-16 pr-8 py-6 bg-white border border-slate-100 rounded-[2rem] font-bold text-sm text-slate-900 shadow-sm focus:ring-[1rem] focus:ring-green-800/5 transition outline-none">
            </div>

            <!-- Bulk Actions Floating Bar -->
            <template x-if="selectedIds.length > 0">
                <div class="fixed bottom-6 lg:bottom-12 left-4 right-4 lg:left-[calc(50%+160px)] lg:right-auto lg:-translate-x-1/2 z-[100] bg-slate-900 text-white p-5 lg:px-10 lg:py-6 rounded-3xl lg:rounded-[2.5rem] shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] flex flex-col lg:flex-row items-center gap-4 lg:gap-10 animate-in slide-in-from-bottom duration-500 backdrop-blur-xl bg-opacity-95 border border-white/5">
                    <div class="flex items-center gap-4 w-full lg:w-auto justify-between lg:justify-start">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-green-800 rounded-2xl flex items-center justify-center text-lg font-black shadow-lg" x-text="selectedIds.length"></div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white/40 leading-none">Items Selected</p>
                                <p class="text-[12px] font-bold mt-1.5 uppercase tracking-widest">Ready to Manage</p>
                            </div>
                        </div>
                        <button @click="selectedIds = []" class="lg:hidden w-10 h-10 bg-white/10 hover:bg-white/20 transition rounded-xl flex items-center justify-center text-white"><i class="fas fa-times"></i></button>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 border-t lg:border-t-0 lg:border-l border-white/10 pt-4 lg:pt-0 lg:pl-10 w-full lg:w-auto justify-center">
                        <button @click="openMoveModal()" class="flex-1 lg:flex-none px-4 lg:px-7 py-4 bg-white/5 border border-white/10 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-white/10 transition flex items-center justify-center gap-2">
                            <i class="fas fa-up-down-left-right text-green-600"></i> Move
                        </button>
                        <button @click="bulkDownload()" class="flex-1 lg:flex-none px-4 lg:px-7 py-4 bg-white/5 border border-white/10 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-white/10 transition flex items-center justify-center gap-2">
                            <i class="fas fa-download text-green-400"></i> <span class="hidden sm:inline">Download</span><span class="sm:hidden">DL</span>
                        </button>
                        <button @click="bulkDelete()" class="w-full lg:w-auto px-4 lg:px-7 py-4 bg-rose-500 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-rose-600 transition flex items-center justify-center gap-2 shadow-xl shadow-rose-950/40">
                            <i class="fas fa-trash"></i> <span class="hidden sm:inline">Delete Permanent</span><span class="sm:hidden">Delete</span>
                        </button>
                        <button @click="selectedIds = []" class="hidden lg:block text-[10px] font-black uppercase tracking-widest text-white/30 hover:text-white transition ml-4">Cancel</button>
                    </div>
                </div>
            </template>

            <!-- The Grid -->
            <div x-show="!loading" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8">
                <template x-for="item in media" :key="item.id">
                    <div class="group relative aspect-square rounded-3xl md:rounded-[3.5rem] bg-white border border-slate-50 overflow-hidden hover:shadow-[0_40px_80px_-20px_rgba(0,0,0,0.15)] transition duration-700 transform hover:-translate-y-2"
                         :class="selectedIds.includes(item.id) ? 'ring-[0.5rem] ring-green-800 ring-offset-4' : ''"
                         :style="item.dominant_color ? 'background-color: ' + item.dominant_color : 'background-color: #f8fafc'">
                        
                        <img :src="item.thumbnail_url" class="w-full h-full object-cover transition-transform duration-[2.5s] group-hover:scale-110">
                        
                        <!-- Static Asset Badge -->
                        <div x-show="item.is_static_asset" class="absolute top-4 left-4 z-20 px-2.5 py-1 bg-amber-400 rounded-lg text-[7px] font-black uppercase tracking-widest text-white shadow-lg" title="Aset statis dari public/images/ — file fisik terlindungi">
                            <i class="fas fa-lock text-[7px] mr-1"></i>Static
                        </div>

                        <!-- Selection Indicator -->
                        <div class="absolute top-6 right-6 z-20">
                            <input type="checkbox" :value="item.id" x-model="selectedIds" class="w-7 h-7 rounded-xl border-2 border-white/20 bg-black/40 text-green-800 focus:ring-0 cursor-pointer shadow-2xl transition">
                        </div>

                        <!-- Smart Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/10 to-transparent opacity-100 md:opacity-0 md:group-hover:opacity-100 transition duration-500 p-4 md:p-8 flex flex-col justify-end">
                            <div class="space-y-5">
                                <div class="flex flex-wrap gap-2 translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
                                    <div class="px-3 py-1.5 bg-white/20 backdrop-blur-md rounded-xl text-[7px] font-black uppercase tracking-widest text-white border border-white/20" x-text="item.category"></div>
                                    <div class="px-3 py-1.5 bg-green-800 rounded-xl text-[7px] font-black uppercase tracking-widest text-white" x-show="item.usage_count > 0" x-text="item.usage_count + ' Links'"></div>
                                </div>
                                
                                <div class="flex gap-2 translate-y-4 group-hover:translate-y-0 transition-transform duration-700 delay-75">
                                    <button @click="openEditModal(item)" class="flex-1 py-3.5 bg-white text-slate-900 rounded-[1.25rem] font-black text-[9px] uppercase tracking-widest hover:bg-green-800 hover:text-white transition shadow-xl">
                                        Manage
                                    </button>
                                    <button @click="copyUrl(item.url)" class="w-11 h-11 bg-white/10 backdrop-blur-md text-white rounded-[1.25rem] flex items-center justify-center hover:bg-white hover:text-slate-900 transition border border-white/20">
                                        <i class="fas fa-link text-[11px]"></i>
                                    </button>
                                    <!-- Tombol hapus: disabled + tooltip untuk static assets -->
                                    <button x-show="!item.is_static_asset" @click="deleteItem(item.id)" class="w-11 h-11 bg-rose-500 text-white rounded-[1.25rem] flex items-center justify-center hover:bg-rose-600 transition shadow-xl">
                                        <i class="fas fa-trash text-[11px]"></i>
                                    </button>
                                    <div x-show="item.is_static_asset" class="w-11 h-11 bg-amber-500/80 text-white rounded-[1.25rem] flex items-center justify-center" title="Aset statis terlindungi dari penghapusan">
                                        <i class="fas fa-shield-halved text-[11px]"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Empty State -->
            <div x-show="!loading && media.length === 0" class="py-20 text-center bg-white rounded-[4rem] border border-dashed border-slate-200">
                <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center mx-auto mb-8 text-slate-200">
                    <i class="fas fa-folder-open text-4xl"></i>
                </div>
                <h4 class="text-2xl font-black text-slate-900">Folder is Empty</h4>
                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-3">Start by dragging files here</p>
            </div>

            <!-- Skeleton -->
            <div x-show="loading" class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-8">
                @for($i=0; $i<10; $i++)
                    <div class="aspect-square bg-slate-50 rounded-[3.5rem] animate-pulse"></div>
                @endfor
            </div>

            <!-- Pagination -->
            <div x-show="last_page > 1" class="flex flex-col sm:flex-row items-center justify-between gap-8 pt-6 border-t border-slate-100">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest">
                    Showing <span class="text-slate-900" x-text="media.length"></span> of <span class="text-slate-900" x-text="stats.total"></span> Assets
                </p>
                <div class="flex items-center gap-3">
                    <button @click="changePage(current_page - 1)" :disabled="current_page === 1" class="w-14 h-14 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-green-800 disabled:opacity-20 transition flex items-center justify-center">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="px-8 py-4 bg-slate-900 text-white rounded-2xl font-black text-[11px] uppercase tracking-widest shadow-xl">
                        Page <span x-text="current_page"></span> / <span x-text="last_page"></span>
                    </div>
                    <button @click="changePage(current_page + 1)" :disabled="current_page === last_page" class="w-14 h-14 bg-white border border-slate-200 rounded-2xl text-slate-400 hover:text-green-800 disabled:opacity-20 transition flex items-center justify-center">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- 2. STORAGE HEALTH AUDIT VIEW -->
        <div x-show="activeTab === 'audit'" class="space-y-8" x-cloak>
            <!-- Audit Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Total size of orphans -->
                <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Orphan Storage Space</p>
                    <h4 class="text-3xl font-black mt-2 text-rose-500" x-text="auditData.total_size_formatted || '0.00 MB'"></h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Can be safely reclaimed</p>
                </div>
                <!-- Orphan Files count -->
                <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Orphan Files on Disk</p>
                    <h4 class="text-3xl font-black mt-2 text-slate-800" x-text="auditData.orphans ? auditData.orphans.length : 0"></h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Files with no database records</p>
                </div>
                <!-- DB Orphans count -->
                <div class="bg-white border border-slate-100 rounded-[2.5rem] p-8 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Unused DB Records</p>
                    <h4 class="text-3xl font-black mt-2 text-green-800" x-text="stats.orphans"></h4>
                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Filter in library to delete</p>
                </div>
            </div>

            <!-- Orphan files list panel -->
            <div class="bg-white border border-slate-100 rounded-[3rem] p-8 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-xl font-black text-slate-900">Physical Orphan Files</h3>
                        <p class="text-xs font-bold text-slate-400 mt-1">File-file di bawah ini tersimpan secara fisik di server tetapi tidak terdaftar di database.</p>
                    </div>
                    
                    <template x-if="selectedAuditPaths.length > 0">
                        <button @click="deleteSelectedAudits()" class="px-6 py-3.5 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition shadow-lg flex items-center gap-2">
                            <i class="fas fa-trash"></i> Delete Selected (<span x-text="selectedAuditPaths.length"></span>)
                        </button>
                    </template>
                </div>

                <!-- Table of orphan files -->
                <div class="overflow-x-auto rounded-2xl border border-slate-100">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="p-6 text-[9px] font-black uppercase tracking-widest text-slate-400 w-16">
                                    <input type="checkbox" @change="selectedAuditPaths = $event.target.checked ? auditData.orphans.map(o => o.path) : []" :checked="selectedAuditPaths.length === auditData.orphans.length && auditData.orphans.length > 0" class="w-5 h-5 rounded-md text-green-800 border-slate-300">
                                </th>
                                <th class="p-6 text-[9px] font-black uppercase tracking-widest text-slate-400 w-32">Preview</th>
                                <th class="p-6 text-[9px] font-black uppercase tracking-widest text-slate-400">File Path</th>
                                <th class="p-6 text-[9px] font-black uppercase tracking-widest text-slate-400 w-28">Size</th>
                                <th class="p-6 text-[9px] font-black uppercase tracking-widest text-slate-400 w-44">Modified Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="file in auditData.orphans" :key="file.path">
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">
                                    <td class="p-6">
                                        <input type="checkbox" :value="file.path" x-model="selectedAuditPaths" class="w-5 h-5 rounded-md text-green-800 border-slate-300">
                                    </td>
                                    <td class="p-6">
                                        <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
                                            <img :src="file.url" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="p-6">
                                        <p class="font-bold text-sm text-slate-800" x-text="file.filename"></p>
                                        <p class="font-mono text-[10px] text-slate-400 mt-1" x-text="file.path"></p>
                                    </td>
                                    <td class="p-6 font-bold text-xs text-slate-600" x-text="file.size_formatted"></td>
                                    <td class="p-6 text-xs text-slate-400" x-text="file.created_at"></td>
                                </tr>
                            </template>
                            <template x-if="!auditLoading && (!auditData.orphans || auditData.orphans.length === 0)">
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 font-bold text-sm">
                                        <i class="fas fa-circle-check text-green-400 text-3xl mb-4"></i>
                                        <p>Hebat! Tidak ditemukan file yatim (orphan) fisik di disk. Penyimpanan Anda 100% sehat.</p>
                                    </td>
                                </tr>
                            </template>
                            <template x-if="auditLoading">
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-slate-400 font-bold text-sm">
                                        <i class="fas fa-spinner animate-spin text-green-800 text-3xl mb-4"></i>
                                        <p>Memindai seluruh penyimpanan disk server...</p>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal: Upload from URL -->
    <div x-show="uploadingUrlModal" class="fixed inset-0 z-[210] flex items-center justify-center p-6" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="uploadingUrlModal = false"></div>
        <div class="relative bg-white w-full max-w-lg rounded-3xl md:rounded-[3.5rem] p-6 md:p-12 shadow-2xl max-h-[90dvh] overflow-y-auto" x-transition>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter leading-none mb-4">Upload from URL</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6">Unduh & konversi otomatis ke WebP</p>
            
            <div class="space-y-8">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-4">Image Source URL</label>
                    <input type="url" x-model="urlUploadData.url" placeholder="https://example.com/image.jpg" class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-bold text-sm focus:ring-8 focus:ring-green-800/5 transition outline-none">
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-4">SEO Alternative Text (Optional)</label>
                    <input type="text" x-model="urlUploadData.alt_text" placeholder="Deskripsi gambar..." class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-bold text-sm focus:ring-8 focus:ring-green-800/5 transition outline-none">
                </div>
                
                <!-- Watermark URL checkbox -->
                <div class="flex items-center gap-3 bg-slate-50 px-6 py-4 rounded-2xl">
                    <input type="checkbox" id="urlWatermark" x-model="urlUploadData.watermark" class="w-5 h-5 rounded-md text-green-800 border-slate-300 focus:ring-green-700 cursor-pointer">
                    <label for="urlWatermark" class="text-[10px] font-black text-slate-500 uppercase tracking-widest cursor-pointer select-none">Watermark Brand</label>
                </div>

                <button @click="submitUrlUpload()" class="w-full py-6 bg-slate-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-green-800 transition disabled:opacity-50" :disabled="!urlUploadData.url || urlUploading">
                    <span x-show="!urlUploading">Download & Convert</span>
                    <span x-show="urlUploading">Downloading...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Rename Folder -->
    <div x-show="renamingFolder" class="fixed inset-0 z-[210] flex items-center justify-center p-6" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="renamingFolder = null"></div>
        <div class="relative bg-white w-full max-w-md rounded-3xl md:rounded-[3.5rem] p-6 md:p-12 shadow-2xl max-h-[90dvh] overflow-y-auto" x-transition>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter leading-none mb-4">Rename Folder</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6">Folder: <span x-text="renamingFolderData.old_name" class="text-green-800"></span></p>
            
            <div class="space-y-8">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-4">New Folder Name</label>
                    <input type="text" x-model="renamingFolderData.new_name" class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-bold text-sm focus:ring-8 focus:ring-green-800/5 transition outline-none">
                </div>
                <button @click="saveRenameFolder()" class="w-full py-6 bg-slate-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-green-800 transition">
                    Update Folder Name
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Move Assets -->
    <div x-show="movingAssets" class="fixed inset-0 z-[210] flex items-center justify-center p-6" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="movingAssets = null"></div>
        <div class="relative bg-white w-full max-w-md rounded-3xl md:rounded-[3.5rem] p-6 md:p-12 shadow-2xl max-h-[90dvh] overflow-y-auto" x-transition>
            <h3 class="text-3xl font-black text-slate-900 tracking-tighter leading-none mb-4">Move Items</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mb-6">Relocate <span x-text="selectedIds.length" class="text-green-800"></span> selected items</p>
            
            <div class="space-y-8">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 ml-4">Select Target Folder</label>
                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="cat in categories" :key="cat.name">
                            <button @click="moveTarget = cat.name" 
                                    :class="moveTarget === cat.name ? 'bg-green-800 text-white shadow-xl scale-105' : 'bg-slate-50 text-slate-600 hover:bg-slate-100'"
                                    class="w-full flex items-center justify-between px-6 py-4 rounded-2xl transition">
                                <div class="flex items-center gap-4">
                                    <i class="fas text-xs" :class="cat.icon"></i>
                                    <span class="text-[11px] font-black uppercase tracking-widest" x-text="cat.name"></span>
                                </div>
                            </button>
                        </template>
                        <button @click="moveTarget = prompt('New Folder Name?')" class="w-full px-6 py-4 bg-slate-50 text-slate-400 border border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:border-green-600 hover:text-green-800 transition">
                            + Create & Move to New
                        </button>
                    </div>
                </div>
                <button @click="saveMove()" class="w-full py-6 bg-slate-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-green-800 transition disabled:opacity-30" :disabled="!moveTarget">
                    Confirm Move
                </button>
            </div>
        </div>
    </div>

    <!-- Modal: Edit File / Rename & Crop -->
    <div x-show="editingItem" class="fixed inset-0 z-[210] flex items-center justify-center p-6" x-cloak>
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-md" @click="if (!isCropping) editingItem = null"></div>
        <div class="relative bg-white w-full max-w-xl rounded-[4rem] p-6 md:p-12 shadow-2xl overflow-hidden max-h-[90dvh] flex flex-col" x-transition>
            <div class="flex justify-between items-start mb-6 shrink-0">
                <div>
                    <h3 class="text-3xl font-black text-slate-900 tracking-tighter leading-none">File Properties</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-4" x-text="editingItem?.mime_type + ' • ' + (editingItem?.size / 1024).toFixed(1) + ' KB'"></p>
                </div>
                <button @click="if (!isCropping) editingItem = null" class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition" :disabled="isCropping">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <!-- Scrollable Content -->
            <div class="flex-1 overflow-y-auto pr-2 space-y-8 scrollbar-thin scrollbar-thumb-slate-200">
                <!-- Preview Canvas / Image with Cropper -->
                <div class="aspect-video w-full rounded-[2.5rem] overflow-hidden bg-slate-50 border border-slate-100 shadow-inner group/preview relative flex items-center justify-center shrink-0">
                    <img :src="editingItem?.url" id="cropperSourceImage" x-ref="cropperImage" class="max-h-full max-w-full object-contain">
                    
                    <!-- Crop Trigger Overlay Button -->
                    <button @click="initCropper()" x-show="!isCropping" class="absolute bottom-4 left-4 px-5 py-2.5 bg-slate-900/80 backdrop-blur text-white hover:bg-green-800 rounded-xl text-[9px] font-black uppercase tracking-widest transition">
                        <i class="fas fa-crop-alt mr-2"></i> Crop Image
                    </button>
                    
                    <!-- Aspect Ratio Overlays -->
                    <div x-show="isCropping" class="flex gap-2 justify-center py-3 bg-slate-900/90 backdrop-blur rounded-b-2xl absolute top-0 left-0 right-0 z-30">
                        <button @click="setAspectRatio(NaN)" class="px-3 py-1.5 bg-white/20 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-white/40">Free</button>
                        <button @click="setAspectRatio(1.7777777777777777)" class="px-3 py-1.5 bg-white/20 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-white/40">16:9</button>
                        <button @click="setAspectRatio(1.3333333333333333)" class="px-3 py-1.5 bg-white/20 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-white/40">4:3</button>
                        <button @click="setAspectRatio(1)" class="px-3 py-1.5 bg-white/20 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-white/40">1:1</button>
                        <button @click="cancelCropping()" class="px-3 py-1.5 bg-rose-500 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest ml-4">Cancel</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6" x-show="!isCropping">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-4">Original Filename</label>
                        <input type="text" x-model="editingItemData.filename" class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-bold text-sm focus:ring-8 focus:ring-green-800/5 transition outline-none">
                    </div>
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-4">SEO Alternative Text</label>
                        <input type="text" x-model="editingItemData.alt_text" class="w-full px-8 py-5 bg-slate-50 border-none rounded-3xl font-bold text-sm focus:ring-8 focus:ring-green-800/5 transition outline-none" placeholder="Deskripsi untuk Google Image...">
                    </div>
                </div>

                <!-- Premium Metadata & Optimization Details -->
                <div class="border-t border-slate-100 pt-8 space-y-6" x-show="!isCropping && editingItem">
                    <h4 class="text-sm font-black text-slate-900 uppercase tracking-wider mb-4">Premium Media Optimization</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Dominant Color Card -->
                        <div class="bg-slate-50 rounded-3xl p-5 border border-slate-100 flex items-center justify-between">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Warna Dominan</p>
                                <p class="font-mono text-xs font-bold text-slate-800 mt-2" x-text="editingItem?.dominant_color || 'Tidak tersedia'"></p>
                            </div>
                            <div class="w-10 h-10 rounded-full border-2 border-white shadow-md transition-transform hover:scale-110" 
                                 :style="'background-color: ' + (editingItem?.dominant_color || '#e2e8f0')"></div>
                        </div>

                        <!-- Blur Placeholder Card -->
                        <div class="bg-slate-50 rounded-3xl p-5 border border-slate-100 flex items-center justify-between">
                            <div class="flex-1 pr-3">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none">Loading Placeholder</p>
                                <p class="text-[10px] font-bold text-green-600 mt-2 flex items-center gap-1.5">
                                    <i class="fas fa-circle-check"></i> Active WebP Base64
                                </p>
                            </div>
                            <div class="w-12 h-12 rounded-xl overflow-hidden bg-slate-200 border border-slate-100 shadow-inner relative flex items-center justify-center group/blur-preview">
                                <img :src="editingItem?.blur_hash || editingItem?.thumbnail_url" 
                                     class="w-full h-full object-cover filter blur-[2px] scale-125 transition group-hover/blur-preview:blur-none group-hover/blur-preview:scale-100">
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Variants Status -->
                    <div class="bg-green-100/50 rounded-3xl p-6 border border-green-200/50 space-y-4">
                        <div class="flex items-center justify-between">
                            <p class="text-[9px] font-black text-green-900 uppercase tracking-widest leading-none">Status Ukuran Responsif (WebP Srcset)</p>
                            <span class="px-2.5 py-1 bg-green-800 text-white text-[7px] font-black uppercase tracking-widest rounded-lg">PageSpeed Hijau</span>
                        </div>
                        <div class="grid grid-cols-3 gap-3 pt-1">
                            <div class="bg-white border border-green-200/30 rounded-2xl p-3 text-center">
                                <i class="fas fa-circle-check text-green-500 text-xs"></i>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Large</p>
                                <p class="text-[9px] font-bold text-slate-700 mt-0.5">1200px</p>
                            </div>
                            <div class="bg-white border border-green-200/30 rounded-2xl p-3 text-center">
                                <i class="fas fa-circle-check text-green-500 text-xs"></i>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Medium</p>
                                <p class="text-[9px] font-bold text-slate-700 mt-0.5">800px</p>
                            </div>
                            <div class="bg-white border border-green-200/30 rounded-2xl p-3 text-center">
                                <i class="fas fa-circle-check text-green-500 text-xs"></i>
                                <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mt-1.5">Mobile</p>
                                <p class="text-[9px] font-bold text-slate-700 mt-0.5">480px</p>
                            </div>
                        </div>
                        <p class="text-[8px] font-semibold text-green-800/70 italic text-center mt-1">
                            * Varian mobile, medium, dan large otomatis diunggah untuk mengoptimalkan bandwidth pengunjung sesuai resolusi perangkat.
                        </p>
                    </div>

                    <!-- EXIF Travel Camera & GPS Panel -->
                    <div class="bg-slate-900 text-white rounded-3xl p-6 border border-slate-800 space-y-4" x-show="editingItem?.exif_data">
                        <div class="flex items-center justify-between border-b border-white/10 pb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-camera text-green-600 text-xs"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest text-green-400">Camera Specs (EXIF)</span>
                            </div>
                            <span class="px-2 py-0.5 bg-green-800/30 border border-green-700/20 text-green-400 text-[6px] font-black uppercase tracking-widest rounded-md">Auto Extracted</span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 text-xs font-bold">
                            <div>
                                <p class="text-[8px] font-black text-white/40 uppercase tracking-widest leading-none">Kamera</p>
                                <p class="text-white mt-1.5 font-black uppercase tracking-wider text-[10px]" x-text="(editingItem?.exif_data?.camera_brand || '') + ' ' + (editingItem?.exif_data?.camera_model || 'Unknown')"></p>
                            </div>
                            <div>
                                <p class="text-[8px] font-black text-white/40 uppercase tracking-widest leading-none">Exposure Settings</p>
                                <p class="text-slate-200 mt-1.5 font-mono text-[10px]" x-text="(editingItem?.exif_data?.aperture || '-') + ' • ' + (editingItem?.exif_data?.shutter_speed || '-') + ' • ISO ' + (editingItem?.exif_data?.iso || '-')"></p>
                            </div>
                        </div>

                        <!-- GPS location if available -->
                        <div class="pt-2 border-t border-white/5 flex items-center justify-between gap-4" x-show="editingItem?.exif_data?.gps">
                            <div>
                                <p class="text-[8px] font-black text-white/40 uppercase tracking-widest leading-none">Koordinat GPS</p>
                                <p class="text-slate-300 font-mono text-[9px] mt-1.5" x-text="editingItem?.exif_data?.gps?.lat.toFixed(5) + ', ' + editingItem?.exif_data?.gps?.lng.toFixed(5)"></p>
                            </div>
                            <a :href="'https://www.google.com/maps/search/?api=1&query=' + editingItem?.exif_data?.gps?.lat + ',' + editingItem?.exif_data?.gps?.lng" 
                               target="_blank" 
                               class="px-4 py-2.5 bg-green-800 hover:bg-green-900 text-white text-[8px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-green-950/50 flex items-center gap-1.5 transition">
                                <i class="fas fa-map-location-dot"></i> Buka Google Maps
                            </a>
                        </div>
                    </div>

                    <!-- Bi-directional Integration Panel: Usage Details -->
                    <div class="bg-green-100/30 rounded-3xl p-6 border border-green-200/50 space-y-4" x-show="editingItem?.usage_details && editingItem?.usage_details.length > 0">
                        <div class="flex items-center justify-between border-b border-green-200 pb-3">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-circle-nodes text-green-800 text-xs"></i>
                                <span class="text-[9px] font-black uppercase tracking-widest text-green-900">Bi-directional Links & Integration</span>
                            </div>
                            <span class="px-2.5 py-1 bg-green-800 text-white text-[7px] font-black uppercase tracking-widest rounded-lg">Connected</span>
                        </div>
                        
                        <div class="space-y-2">
                            <template x-for="link in editingItem?.usage_details" :key="link.name">
                                <div class="bg-white border border-green-200/50 rounded-2xl p-4 flex items-center justify-between shadow-sm hover:shadow-md transition-shadow">
                                    <div class="space-y-1">
                                        <span class="px-2 py-0.5 bg-green-100 border border-green-200 text-green-900 text-[6px] font-black uppercase tracking-widest rounded-md" x-text="link.type"></span>
                                        <p class="text-xs font-black text-slate-800 tracking-tight" x-text="link.name"></p>
                                    </div>
                                    <a :href="link.edit_url" 
                                       class="px-4 py-2 bg-green-800 hover:bg-green-900 text-white text-[8px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-green-200 flex items-center gap-1.5 transition">
                                        <i class="fas fa-pen-to-square"></i> Edit
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Orphan Warning (If no usage details) -->
                    <div class="bg-amber-50/50 rounded-3xl p-6 border border-amber-200/50 flex items-center gap-4" x-show="!editingItem?.usage_details || editingItem?.usage_details.length === 0">
                        <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center shrink-0">
                            <i class="fas fa-triangle-exclamation"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-[9px] font-black text-amber-800 uppercase tracking-widest">Orphan Media Asset</p>
                            <p class="text-[10px] font-bold text-amber-600 mt-1 leading-normal font-sans">Aset media ini tidak terhubung/digunakan di konten mana pun (Paket Wisata, Artikel Blog, Galeri, atau Pengaturan). Anda dapat menghapusnya untuk menghemat penyimpanan disk.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="pt-6 border-t border-slate-100 mt-6 shrink-0">
                <!-- Update Properties (Normal View) -->
                <button @click="saveEdit()" x-show="!isCropping" class="w-full py-6 bg-slate-900 text-white rounded-[2.5rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-green-800 transition">
                    Update All Properties
                </button>

                <!-- Crop Actions (Cropping View) -->
                <button @click="saveCroppedImage()" x-show="isCropping" class="w-full py-6 bg-green-800 text-white rounded-[2.5rem] font-black text-xs uppercase tracking-widest shadow-2xl hover:bg-green-900 transition flex items-center justify-center gap-3">
                    <i class="fas fa-check"></i> <span x-text="cropSaving ? 'Saving Crop...' : 'Save Cropped Image'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

<script>
function mediaManager() {
    return {
        media: [],
        categories: [],
        current_page: 1,
        last_page: 1,
        isDragging: false,
        loading: true,
        selectedIds: [],
        editingItem: null,
        editingItemData: { filename: '', alt_text: '', category: '' },
        movingAssets: false,
        moveTarget: '',
        renamingFolder: null,
        renamingFolderData: { old_name: '', new_name: '' },
        syncing: false,
        syncingStatic: false,
        converting: false,
        uploadingUrlModal: false,
        urlUploading: false,
        urlUploadData: { url: '', alt_text: '', watermark: false },
        stats: { total: 0, orphans: 0 },
        filters: { search: '', category: '', usage: 'all' },
        
        // Premium features state
        activeTab: 'library', // 'library' or 'audit'
        useWatermark: false,
        isCropping: false,
        cropSaving: false,
        cropper: null,
        auditData: { orphans: [], total_size_formatted: '0.00 MB' },
        auditLoading: false,
        selectedAuditPaths: [],

        init() { this.fetchMedia(); },

        setCategory(cat) {
            this.activeTab = 'library';
            this.filters.category = cat;
            this.current_page = 1;
            this.fetchMedia();
        },

        setUsage(usage) {
            this.activeTab = 'library';
            this.filters.usage = usage;
            this.current_page = 1;
            this.fetchMedia();
        },

        createNewFolder() {
            const name = prompt('Nama Folder Baru?');
            if (name) { this.setCategory(name.toLowerCase()); }
        },

        openRenameFolder(name) {
            this.renamingFolder = true;
            this.renamingFolderData = { old_name: name, new_name: name };
        },

        async saveRenameFolder() {
            try {
                const response = await fetch('{{ route('admin.media.rename-folder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify(this.renamingFolderData)
                });
                const data = await response.json();
                if (data.success) {
                    this.renamingFolder = null;
                    if (this.filters.category === this.renamingFolderData.old_name) {
                        this.filters.category = this.renamingFolderData.new_name;
                    }
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                }
            } catch (e) { console.error(e); }
        },

        openMoveModal() {
            this.movingAssets = true;
            this.moveTarget = '';
        },

        async saveMove() {
            try {
                const response = await fetch('{{ route('admin.media.move') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ids: this.selectedIds, category: this.moveTarget })
                });
                const data = await response.json();
                if (data.success) {
                    this.movingAssets = false;
                    this.selectedIds = [];
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                }
            } catch (e) { console.error(e); }
        },

        openUrlModal() {
            this.uploadingUrlModal = true;
            this.urlUploadData = { url: '', alt_text: '', watermark: false };
        },

        async submitUrlUpload() {
            if (!this.urlUploadData.url) return;
            this.urlUploading = true;
            try {
                const response = await fetch('{{ route('admin.media.upload-url') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        url: this.urlUploadData.url,
                        category: this.filters.category || 'uploads',
                        alt_text: this.urlUploadData.alt_text,
                        watermark: this.urlUploadData.watermark ? '1' : '0'
                    })
                });
                const data = await response.json();
                if (data.success) {
                    this.uploadingUrlModal = false;
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                } else {
                    alert(data.message || 'Gagal mengunduh gambar');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat mengunduh gambar');
            } finally {
                this.urlUploading = false;
            }
        },

        async convertAllToWebp() {
            if (!confirm('Konversi semua gambar yang bukan format WebP ke format WebP? Proses ini mungkin membutuhkan waktu beberapa detik.')) return;
            this.converting = true;
            try {
                const response = await fetch('{{ route('admin.media.convert-all') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.converting = false;
            }
        },

        async syncMedia() {
            this.syncing = true;
            try {
                const response = await fetch('{{ route('admin.media.sync') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) { this.fetchMedia(); this.showToast('✓ ' + data.message); }
            } catch (e) { console.error(e); } finally { this.syncing = false; }
        },

        async syncPublicAssets() {
            if (!confirm('Daftarkan semua gambar dari folder public/images/ ke Media Library?\n\nFile fisik TIDAK akan dipindahkan. Hanya dicatat di database.')) return;
            this.syncingStatic = true;
            try {
                const response = await fetch('{{ route('admin.media.sync-public-assets') }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await response.json();
                if (data.success) {
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                } else {
                    alert(data.message || 'Sync gagal.');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat sync aset statis.');
            } finally {
                this.syncingStatic = false;
            }
        },

        fetchMedia() {
            this.loading = true;
            let url = `{{ route('admin.media.search') }}?_t=${new Date().getTime()}`;
            
            const formData = new FormData();
            formData.append('page', this.current_page);
            formData.append('search', this.filters.search);
            formData.append('category', this.filters.category);
            formData.append('usage', this.filters.usage);
            
            fetch(url, { 
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Cache-Control': 'no-cache, no-store, must-revalidate',
                    'Pragma': 'no-cache',
                    'Expires': '0'
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    this.media = data.media.data;
                    this.last_page = data.media.last_page;
                    this.categories = data.categories;
                    this.stats = data.stats;
                    this.loading = false;
                });
        },

        changePage(page) {
            if (page < 1 || page > this.last_page) return;
            this.current_page = page; this.fetchMedia();
        },

        handleDrop(e) {
            this.isDragging = false;
            const files = e.dataTransfer.files;
            this.uploadFiles({ target: { files } });
        },

        uploadFiles(e) {
            const files = e.target.files;
            if (!files.length) return;
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) { formData.append('files[]', files[i]); }
            formData.append('category', this.filters.category || 'uploads');
            formData.append('watermark', this.useWatermark ? '1' : '0');
            formData.append('_token', '{{ csrf_token() }}');
            this.loading = true;
            fetch('/admin/media', { method: 'POST', body: formData })
            .then(res => res.json()).then(data => { if (data.success) { this.fetchMedia(); this.showToast('✓ ' + data.message); } })
            .catch(err => { alert('Upload error'); this.loading = false; });
        },

        bulkDownload() {
            const form = document.createElement('form');
            form.method = 'POST'; form.action = '{{ route('admin.media.bulk-download') }}';
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token'; csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            this.selectedIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden'; input.name = 'ids[]'; input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form); form.submit(); form.remove();
        },

        openEditModal(item) {
            this.editingItem = item;
            this.isCropping = false;
            this.editingItemData = { 
                filename: item.original_name || item.filename,
                alt_text: item.alt_text || '', 
                category: item.category || '' 
            };
        },

        async saveEdit() {
            try {
                // First save metadata
                await fetch(`/admin/media/${this.editingItem.id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ alt_text: this.editingItemData.alt_text, category: this.editingItemData.category })
                });

                // Then rename file if changed
                if (this.editingItemData.filename !== (this.editingItem.original_name || this.editingItem.filename)) {
                    await fetch(`/admin/media/${this.editingItem.id}/rename`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ filename: this.editingItemData.filename })
                    });
                }

                this.editingItem = null;
                this.fetchMedia();
                this.showToast('✓ File Properties Updated');
            } catch (e) { console.error(e); }
        },

        async bulkDelete() {
            if (!confirm(`Hapus permanen ${this.selectedIds.length} aset?`)) return;
            try {
                const response = await fetch('{{ route('admin.media.bulk-delete') }}?_t=' + new Date().getTime(), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ ids: this.selectedIds })
                });
                const data = await response.json();
                if (data.success) {
                    this.selectedIds = [];
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                }
            } catch (e) { console.error(e); }
        },

        deleteItem(id) {
            if (!confirm('Hapus permanen?')) return;
            fetch(`/admin/media/${id}?_t=${new Date().getTime()}`, { 
                method: 'DELETE', 
                headers: { 
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                } 
            })
            .then(res => res.json())
            .then(data => {
                if (data.success === true) {
                    // Langsung hapus dari array lokal (instant feedback)
                    this.media = this.media.filter(m => m.id !== id);
                    this.showToast('✓ Media berhasil dihapus');
                    // Refresh dari server untuk pastikan data DB sinkron
                    setTimeout(() => this.fetchMedia(), 500);
                } else {
                    alert('Gagal menghapus: ' + (data.message || 'Server error'));
                    this.fetchMedia();
                }
            })
            .catch(err => {
                alert('Network error: ' + err.message);
                this.fetchMedia();
            });
        },

        copyUrl(url) {
            navigator.clipboard.writeText(url); this.showToast('✓ URL Copied');
        },

        showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-12 left-1/2 -translate-x-1/2 px-10 py-5 bg-slate-900 text-white rounded-[2rem] font-black text-[10px] uppercase tracking-widest shadow-2xl z-[1000] animate-in slide-in-from-bottom duration-500 backdrop-blur-xl bg-opacity-95';
            toast.innerText = message;
            document.body.appendChild(toast);
            setTimeout(() => { toast.classList.add('animate-out', 'fade-out', 'duration-500'); setTimeout(() => toast.remove(), 500); }, 3000);
        },

        // Storage Audit methods
        async openAuditPanel() {
            this.activeTab = 'audit';
            this.fetchAuditData();
        },

        async fetchAuditData() {
            this.auditLoading = true;
            try {
                const res = await fetch('{{ route('admin.media.audit') }}');
                const data = await res.json();
                if (data.success) {
                    this.auditData = data;
                }
            } catch (e) {
                console.error(e);
            } finally {
                this.auditLoading = false;
            }
        },

        async deleteSelectedAudits() {
            if (this.selectedAuditPaths.length === 0) return;
            if (!confirm(`Hapus permanen ${this.selectedAuditPaths.length} file yatim dari server?`)) return;
            try {
                const res = await fetch('{{ route('admin.media.clean-orphans') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ paths: this.selectedAuditPaths })
                });
                const data = await res.json();
                if (data.success) {
                    this.selectedAuditPaths = [];
                    this.fetchAuditData();
                    this.fetchMedia(); // Refresh stats
                    this.showToast('✓ ' + data.message);
                }
            } catch (e) {
                console.error(e);
            }
        },

        // CropperJS functions
        initCropper() {
            this.isCropping = true;
            this.$nextTick(() => {
                const imageEl = document.getElementById('cropperSourceImage');
                this.cropper = new Cropper(imageEl, {
                    viewMode: 1,
                    dragMode: 'move',
                    background: false,
                    responsive: true,
                    restore: false
                });
            });
        },

        setAspectRatio(ratio) {
            if (this.cropper) {
                this.cropper.setAspectRatio(ratio);
            }
        },

        cancelCropping() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }
            this.isCropping = false;
        },

        async saveCroppedImage() {
            if (!this.cropper) return;
            this.cropSaving = true;
            try {
                // Get cropped canvas as base64 webp (high compression quality)
                const canvas = this.cropper.getCroppedCanvas({
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });
                const dataUrl = canvas.toDataURL('image/webp', 0.85);
                
                const response = await fetch(`/admin/media/${this.editingItem.id}/crop`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify({ image: dataUrl })
                });
                
                const data = await response.json();
                if (data.success) {
                    this.cancelCropping();
                    this.editingItem = null;
                    this.fetchMedia();
                    this.showToast('✓ ' + data.message);
                } else {
                    alert(data.message || 'Gagal memotong gambar');
                }
            } catch (e) {
                console.error(e);
                alert('Terjadi kesalahan saat memotong gambar.');
            } finally {
                this.cropSaving = false;
            }
        }
    }
}
</script>
@endsection

<div x-data="mediaPicker()" 
     @open-media-picker.window="open($event.detail)"
     x-show="isOpen" 
     class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-md"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">
    
    <div class="bg-white w-full max-w-6xl h-[85dvh] rounded-2xl md:rounded-[4rem] shadow-2xl overflow-hidden flex flex-col border border-white/20 animate-in zoom-in duration-500"
         @click.away="isOpen = false">
        
        <!-- Header -->
        <div class="px-4 py-4 md:px-10 md:py-8 border-b border-slate-50 flex items-center justify-between gap-3 bg-slate-50/50">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 bg-green-800 rounded-[1.5rem] flex items-center justify-center text-white shadow-xl shadow-green-200">
                    <i class="fas fa-images text-xl"></i>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter leading-none">Media Library Picker</h3>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2">Pilih atau unggah aset untuk digunakan kembali</p>
                </div>
            </div>
            <button @click="isOpen = false" class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white text-slate-400 hover:text-rose-500 shadow-sm transition hover:rotate-90">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
            <!-- Sidebar Folders -->
            <aside class="w-full md:w-64 shrink-0 border-b md:border-b-0 md:border-r border-slate-50 p-3 md:p-6 flex flex-row md:flex-col gap-2 overflow-x-auto md:overflow-x-visible md:overflow-y-auto custom-scrollbar">
                <p class="px-4 text-[9px] font-black text-slate-300 uppercase tracking-[0.3em] mb-3">Folders</p>
                
                <button @click="category = ''; fetchMedia()" 
                        :class="category === '' ? 'bg-green-100 text-green-800' : 'text-slate-500 hover:bg-slate-50'"
                        class="shrink-0 md:w-full flex items-center gap-4 px-5 py-3 rounded-xl transition group whitespace-nowrap">
                    <i class="fas fa-layer-group text-xs"></i>
                    <span class="text-[10px] font-black uppercase tracking-widest">All Assets</span>
                </button>

                <template x-for="cat in categories" :key="cat.name">
                    <button @click="category = cat.name; fetchMedia()" 
                            :class="category === cat.name ? 'bg-green-100 text-green-800' : 'text-slate-500 hover:bg-slate-50'"
                            class="shrink-0 md:w-full flex items-center gap-4 px-5 py-3 rounded-xl transition group whitespace-nowrap">
                        <i class="fas text-xs" :class="cat.icon"></i>
                        <span class="text-[10px] font-black uppercase tracking-widest" x-text="cat.name"></span>
                    </button>
                </template>
            </aside>

            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Filters -->
                <div class="px-4 py-4 md:px-8 bg-white border-b border-slate-50 flex flex-wrap items-center gap-3 md:gap-4">
                    <div class="flex-1 relative">
                        <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-sm"></i>
                        <input type="text" x-model="search" @input.debounce.500ms="fetchMedia()" placeholder="Search library..." 
                               class="w-full pl-12 pr-6 py-3.5 bg-slate-50 border-none rounded-2xl font-bold text-xs text-slate-900 focus:ring-4 focus:ring-green-800/5 transition">
                    </div>
                    
                    <button @click="$refs.pickerUpload.click()" class="px-8 py-3.5 bg-green-800 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest shadow-xl shadow-green-200 hover:bg-green-900 transition flex items-center gap-3">
                        <i class="fas fa-plus"></i> Upload New
                    </button>
                    <input type="file" x-ref="pickerUpload" class="hidden" multiple @change="uploadFiles($event)">
                </div>

                <!-- Grid -->
                <div class="flex-1 overflow-y-auto p-4 md:p-10 custom-scrollbar bg-slate-50/30">
                    <div x-show="loading" class="h-full flex items-center justify-center">
                        <div class="w-12 h-12 border-4 border-green-200 border-t-green-800 rounded-full animate-spin"></div>
                    </div>

                    <div x-show="!loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                        <template x-for="item in media" :key="item.id">
                            <button type="button" @click="toggleSelection(item)" 
                                    :class="isSelected(item) ? 'border-green-800 ring-2 ring-green-700 ring-offset-2' : 'border-slate-100 hover:border-green-800'"
                                    class="group relative aspect-square rounded-[2.5rem] bg-white border overflow-hidden transition duration-500 shadow-sm hover:shadow-2xl hover:shadow-green-200">
                                <img :src="item.thumbnail_url" class="w-full h-full object-cover transition-transform duration-[1.5s] group-hover:scale-110">
                                
                                <!-- Selection indicator for multiple mode -->
                                <div x-show="isMultiple && isSelected(item)" class="absolute top-3 right-3">
                                    <div class="w-6 h-6 bg-green-800 text-white rounded-full flex items-center justify-center text-xs font-bold shadow-lg">
                                        <i class="fas fa-check"></i>
                                    </div>
                                </div>
                                
                                <!-- Hover overlay -->
                                <div class="absolute inset-0 bg-green-800/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white text-green-800 flex items-center justify-center shadow-2xl scale-50 group-hover:scale-100 transition-transform duration-500">
                                        <i :class="isSelected(item) ? 'fas fa-minus' : 'fas fa-check'"></i>
                                    </div>
                                    <p class="text-[8px] font-black text-white uppercase tracking-widest text-center truncate w-full" x-text="item.filename"></p>
                                </div>
                            </button>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="!loading && media.length === 0" class="h-full flex flex-col items-center justify-center text-slate-300">
                        <i class="fas fa-cloud-open text-5xl mb-6 opacity-20"></i>
                        <p class="text-sm font-bold uppercase tracking-widest opacity-50">No assets found</p>
                    </div>
                </div>

                <!-- Footer Pagination -->
                <div class="px-4 py-4 md:px-8 md:py-6 border-t border-slate-50 bg-white flex flex-wrap gap-3 justify-between items-center">
                    <!-- Pagination -->
                    <div x-show="lastPage > 1" class="flex gap-2">
                        <button @click="changePage(currentPage - 1)" :disabled="currentPage === 1" class="w-12 h-12 flex items-center justify-center bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-green-800 disabled:opacity-20 transition shadow-sm">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <div class="px-6 py-3 bg-slate-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest flex items-center shadow-lg" x-text="currentPage + ' / ' + lastPage"></div>
                        <button @click="changePage(currentPage + 1)" :disabled="currentPage === lastPage" class="w-12 h-12 flex items-center justify-center bg-white border border-slate-100 rounded-xl text-slate-400 hover:text-green-800 disabled:opacity-20 transition shadow-sm">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                    
                    <!-- Confirm Selection for Multiple Mode -->
                    <div x-show="isMultiple" class="flex items-center gap-4">
                        <div x-show="selectedItems.length > 0" class="text-xs text-slate-500 font-bold">
                            <span x-text="selectedItems.length"></span> item(s) selected
                        </div>
                        <button @click="confirmSelection()" 
                                :disabled="selectedItems.length === 0"
                                :class="selectedItems.length > 0 ? 'bg-green-800 hover:bg-green-900 text-white' : 'bg-slate-200 text-slate-400 cursor-not-allowed'"
                                class="px-6 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition shadow-lg">
                            <i class="fas fa-check mr-2"></i>
                            Use Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.mediaPicker = function() {
    return {
        isOpen: false,
        loading: false,
        media: [],
        categories: [],
        search: '',
        category: '',
        currentPage: 1,
        lastPage: 1,
        callback: null,
        selectedItems: [],
        isMultiple: false,

        open(detail) {
            this.isOpen = true;
            this.callback = detail.callback;
            this.isMultiple = detail.multiple || false;
            this.selectedItems = [];
            this.category = detail.category || '';
            this.fetchMedia();
        },

        fetchMedia() {
            this.loading = true;
            let url = `/admin/media?page=${this.currentPage}&search=${this.search}&category=${this.category}`;
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(data => {
                    this.media = data.media.data;
                    this.lastPage = data.media.last_page;
                    this.categories = data.categories;
                    this.loading = false;
                });
        },

        changePage(page) {
            if (page < 1 || page > this.lastPage) return;
            this.currentPage = page;
            this.fetchMedia();
        },

        toggleSelection(item) {
            if (this.isMultiple) {
                const existingIndex = this.selectedItems.findIndex(selected => selected.id === item.id);
                if (existingIndex >= 0) {
                    this.selectedItems.splice(existingIndex, 1);
                } else {
                    this.selectedItems.push(item);
                }
            } else {
                this.selectedItems = [item];
                this.selectMedia();
            }
        },

        isSelected(item) {
            return this.selectedItems.some(selected => selected.id === item.id);
        },

        confirmSelection() {
            if (this.selectedItems.length > 0) {
                this.selectMedia();
            }
        },

        selectMedia() {
            const result = this.isMultiple ? this.selectedItems : this.selectedItems[0];
            
            // Priority 1: Direct function passed in detail
            if (typeof this.callback === 'function') {
                this.callback(result);
            } 
            // Priority 2: Global window function name
            else if (typeof this.callback === 'string' && typeof window[this.callback] === 'function') {
                window[this.callback](result);
            }
            // Priority 3: Custom Event
            else {
                window.dispatchEvent(new CustomEvent('media-selected', { detail: result }));
            }
            
            this.isOpen = false;
            this.selectedItems = [];
        },

        uploadFiles(e) {
            const files = e.target.files;
            if (!files.length) return;
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);
            formData.append('category', this.category || 'uploads');
            formData.append('_token', '{{ csrf_token() }}');

            this.loading = true;
            fetch('/admin/media', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(async res => {
                    if (res.status === 401 || res.status === 419) {
                        alert('Sesi login berakhir. Anda akan diarahkan ke halaman login.');
                        window.location.href = '/login';
                        throw new Error('Sesi berakhir.');
                    }
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.success) {
                        // Surface the real reason (validation, size, server error)
                        let msg = data.message || 'Upload gagal.';
                        if (data.errors) msg = Object.values(data.errors).flat().join('\n');
                        if (res.status === 413) msg = 'File terlalu besar untuk server (PHP upload_max_filesize / post_max_size).';
                        throw new Error(msg);
                    }
                    return data;
                })
                .then(data => {
                    this.fetchMedia();
                    // Auto-select uploaded items if multiple mode
                    if (this.isMultiple && data.data) {
                        const newMedia = Array.isArray(data.data) ? data.data : [data.data];
                        newMedia.forEach(item => {
                            if (item && !this.isSelected(item)) {
                                this.selectedItems.push(item);
                            }
                        });
                    }
                })
                .catch(err => {
                    this.loading = false;
                    alert('Upload gagal: ' + (err.message || 'Kesalahan jaringan/server.'));
                })
                .finally(() => {
                    // Reset input so re-selecting the same file fires @change again
                    e.target.value = '';
                });
        }
    }
}
</script>

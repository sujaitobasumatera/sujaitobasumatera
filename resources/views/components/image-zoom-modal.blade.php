{{-- Modal Pratinjau Gambar Interaktif (Lightbox + Zoom & Pan)
     Dapat dipicu dari mana saja lewat:
     $dispatch('zoom-image', { url: '...', title: '...' })
     atau:
     $dispatch('zoom-image', { images: [...], index: 0, title: '...' }) --}}
<div x-data="{
        open: false,
        images: [],
        idx: 0,
        title: '',
        zoom: 1,
        panX: 0,
        panY: 0,
        isDragging: false,
        dragStartX: 0,
        dragStartY: 0,
        initialPanX: 0,
        initialPanY: 0,
        naturalWidth: 0,
        naturalHeight: 0,

        get current() {
            if (!this.images || this.images.length === 0) return { url: '' };
            let item = this.images[this.idx];
            if (!item) return { url: '' };
            return typeof item === 'string' ? { url: item } : item;
        },

        openModal(detail) {
            if (!detail) return;
            if (detail.images && Array.isArray(detail.images) && detail.images.length > 0) {
                this.images = detail.images;
                this.idx = Math.max(0, Math.min(detail.index || 0, this.images.length - 1));
            } else if (detail.url) {
                this.images = [{ url: detail.url, srcset: detail.srcset || '', title: detail.title || '' }];
                this.idx = 0;
            } else {
                return;
            }
            this.title = detail.title || '';
            this.resetZoom();
            this.open = true;
            document.body.style.overflow = 'hidden';
        },

        closeModal() {
            this.open = false;
            this.resetZoom();
            document.body.style.overflow = '';
        },

        resetZoom() {
            this.zoom = 1;
            this.panX = 0;
            this.panY = 0;
            this.isDragging = false;
        },

        zoomIn() {
            this.zoom = Math.min(3.5, Math.round((this.zoom + 0.5) * 10) / 10);
        },

        zoomOut() {
            this.zoom = Math.max(1, Math.round((this.zoom - 0.5) * 10) / 10);
            if (this.zoom === 1) {
                this.panX = 0;
                this.panY = 0;
            }
        },

        toggleZoom() {
            if (this.zoom > 1) {
                this.resetZoom();
            } else {
                this.zoom = 2;
            }
        },

        next() {
            if (this.images.length <= 1) return;
            this.idx = (this.idx + 1) % this.images.length;
            this.resetZoom();
        },

        prev() {
            if (this.images.length <= 1) return;
            this.idx = (this.idx - 1 + this.images.length) % this.images.length;
            this.resetZoom();
        },

        onWheel(e) {
            if (e.deltaY < 0) {
                this.zoomIn();
            } else if (e.deltaY > 0) {
                this.zoomOut();
            }
        },

        startDrag(e) {
            if (this.zoom <= 1) return;
            this.isDragging = true;
            this.dragStartX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
            this.dragStartY = e.clientY || (e.touches && e.touches[0].clientY) || 0;
            this.initialPanX = this.panX;
            this.initialPanY = this.panY;
        },

        onDrag(e) {
            if (!this.isDragging || this.zoom <= 1) return;
            let clientX = e.clientX || (e.touches && e.touches[0].clientX) || 0;
            let clientY = e.clientY || (e.touches && e.touches[0].clientY) || 0;
            let dx = clientX - this.dragStartX;
            let dy = clientY - this.dragStartY;
            this.panX = this.initialPanX + dx;
            this.panY = this.initialPanY + dy;
        },

        endDrag() {
            this.isDragging = false;
        },

        onImageLoaded(e) {
            this.naturalWidth = e.target.naturalWidth || 0;
            this.naturalHeight = e.target.naturalHeight || 0;
        }
    }"
    @zoom-image.window="openModal($event.detail)"
    @keydown.escape.window="if (open) closeModal()"
    @keydown.arrow-left.window="if (open) prev()"
    @keydown.arrow-right.window="if (open) next()"
>
    <template x-if="open">
        <div class="fixed inset-0 z-[200] bg-black/95 backdrop-blur-md flex flex-col justify-between select-none"
             @click.self="closeModal()"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">

            {{-- Bilah Atas: Judul, Info Resolusi, Counter, dan Tombol Tutup --}}
            <div class="relative z-[220] flex items-center justify-between px-4 sm:px-6 py-3.5 bg-gradient-to-b from-black/80 to-transparent text-white">
                <div class="flex items-center gap-3 min-w-0 pr-4">
                    <template x-if="images.length > 1">
                        <span class="px-2.5 py-1 rounded-md bg-white/10 text-[11px] font-bold tracking-widest uppercase shrink-0">
                            <span x-text="idx + 1"></span> / <span x-text="images.length"></span>
                        </span>
                    </template>
                    <div class="min-w-0">
                        <h4 class="text-sm font-semibold truncate leading-tight" x-text="title || current.title || '{{ __('Pratinjau Foto') }}'"></h4>
                        <template x-if="naturalWidth > 0 && naturalHeight > 0">
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5" x-text="naturalWidth + ' × ' + naturalHeight + ' px'"></p>
                        </template>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    {{-- Bilah Alat Zoom --}}
                    <div class="flex items-center bg-white/10 rounded-xl p-1 gap-1 border border-white/10">
                        <button type="button" @click="zoomOut()" :disabled="zoom <= 1"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-white hover:bg-white/20 disabled:opacity-30 disabled:hover:bg-transparent transition active:scale-95"
                                title="{{ __('Perkecil') }}">
                            <span class="material-symbols-outlined text-lg">zoom_out</span>
                        </button>
                        <button type="button" @click="resetZoom()"
                                class="px-2 h-8 rounded-lg flex items-center justify-center text-[11px] font-mono font-bold hover:bg-white/20 transition active:scale-95"
                                title="{{ __('Kembalikan 100%') }}">
                            <span x-text="Math.round(zoom * 100) + '%'"></span>
                        </button>
                        <button type="button" @click="zoomIn()" :disabled="zoom >= 3.5"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-white hover:bg-white/20 disabled:opacity-30 disabled:hover:bg-transparent transition active:scale-95"
                                title="{{ __('Perbesar') }}">
                            <span class="material-symbols-outlined text-lg">zoom_in</span>
                        </button>
                    </div>

                    {{-- Tombol Tutup --}}
                    <button type="button" @click="closeModal()"
                            class="w-10 h-10 rounded-xl bg-white/10 hover:bg-red-600 hover:text-white flex items-center justify-center text-white transition border border-white/10 active:scale-95 ml-1"
                            title="{{ __('Tutup (Esc)') }}">
                        <span class="material-symbols-outlined text-xl">close</span>
                    </button>
                </div>
            </div>

            {{-- Area Kanvas Gambar --}}
            <div class="relative flex-1 w-full h-full flex items-center justify-center overflow-hidden p-2 sm:p-6"
                 @wheel.prevent="onWheel($event)"
                 @mousedown="startDrag($event)"
                 @mousemove="onDrag($event)"
                 @mouseup="endDrag()"
                 @mouseleave="endDrag()"
                 @touchstart.passive="startDrag($event)"
                 @touchmove.prevent="onDrag($event)"
                 @touchend="endDrag()">

                <div class="relative max-w-full max-h-full flex items-center justify-center transition-transform"
                     :style="'transform: translate3d(' + panX + 'px, ' + panY + 'px, 0px) scale(' + zoom + '); cursor: ' + (zoom > 1 ? (isDragging ? 'grabbing' : 'grab') : 'zoom-in') + '; transform-origin: center center;'"
                     @dblclick="toggleZoom()">
                    <img :src="current.url"
                         :srcset="current.srcset || ''"
                         sizes="100vw"
                         @load="onImageLoaded($event)"
                         alt=""
                         draggable="false"
                         class="max-w-[95vw] max-h-[80vh] object-contain rounded-lg shadow-2xl transition-all duration-75 select-none pointer-events-none">
                </div>

                {{-- Tombol Navigasi Kiri / Kanan --}}
                <template x-if="images.length > 1">
                    <button type="button" @click.stop="prev()"
                            class="absolute left-3 sm:left-6 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-black/40 hover:bg-white/20 text-white backdrop-blur-md flex items-center justify-center border border-white/15 transition active:scale-95 z-[210]"
                            title="{{ __('Sebelumnya') }}">
                        <span class="material-symbols-outlined text-2xl">chevron_left</span>
                    </button>
                </template>
                <template x-if="images.length > 1">
                    <button type="button" @click.stop="next()"
                            class="absolute right-3 sm:right-6 top-1/2 -translate-y-1/2 w-11 h-11 sm:w-13 sm:h-13 rounded-2xl bg-black/40 hover:bg-white/20 text-white backdrop-blur-md flex items-center justify-center border border-white/15 transition active:scale-95 z-[210]"
                            title="{{ __('Berikutnya') }}">
                        <span class="material-symbols-outlined text-2xl">chevron_right</span>
                    </button>
                </template>
            </div>

            {{-- Bilah Bawah: Petunjuk Interaksi --}}
            <div class="relative z-[220] py-2.5 px-4 text-center text-[11px] text-slate-400 bg-gradient-to-t from-black/80 to-transparent">
                <span>{{ __('Ketuk dua kali untuk perbesar/perkecil • Geser untuk navigasi • Gunakan scroll roda mouse untuk zoom') }}</span>
            </div>
        </div>
    </template>
</div>

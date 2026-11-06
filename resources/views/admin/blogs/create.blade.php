@extends('admin.layout')

@section('title', 'Editor Artikel Mandiri - Sujai Toba Sumatera')

@section('content')
<div class="w-full max-w-full">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 bg-white p-5 md:p-8 rounded-[2.5rem] shadow-sm border border-slate-100">
        <div class="flex items-center gap-5">
            <div class="w-14 h-14 rounded-[1.25rem] bg-green-600 flex items-center justify-center text-white shadow-xl shadow-green-100">
                <i class="fas fa-feather-pointed text-2xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-black text-slate-900 leading-tight">Editor Mandiri v2.0</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em]">Bebas TinyMCE • Performa Maksimal • Rapi</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="document.getElementById('write_form').submit()" class="px-10 py-4 bg-slate-900 hover:bg-black text-white rounded-2xl font-black uppercase tracking-widest text-[10px] transition shadow-2xl shadow-slate-200 flex items-center gap-2">
                Tayangkan Artikel
            </button>
        </div>
    </div>

    <form action="{{ route('admin.blogs.store') }}" method="POST" enctype="multipart/form-data" id="write_form">
        @csrf
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-10 items-start">
            
            <!-- Main Content Area -->
            <div class="xl:col-span-8">
                <!-- Title Section -->
                <div class="bg-white rounded-[3rem] p-12 shadow-sm border border-slate-100 mb-8">
                    <label class="block text-[10px] font-black text-slate-300 uppercase tracking-[0.3em] mb-4 text-center">Judul Utama</label>
                    <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Tuliskan Judul Yang Menarik..."
                        class="w-full text-2xl md:text-4xl font-black text-slate-900 placeholder:text-slate-100 border-none focus:ring-0 p-0 text-center mb-4">
                    <div class="flex justify-center">
                        <div class="h-1.5 w-24 bg-green-500 rounded-full"></div>
                    </div>
                </div>

                <!-- The "Paper" Editor -->
                <div class="bg-slate-50 p-3 sm:p-8 md:p-16 rounded-2xl md:rounded-[3.5rem] border border-slate-100 min-h-[400px] md:min-h-[900px] flex justify-center relative">
                    <div class="w-full max-w-[800px] bg-white shadow-2xl rounded-sm p-5 sm:p-10 md:p-24 min-h-[400px] md:min-h-[1000px] relative">
                        <!-- CKEditor Container -->
                        <div id="editor-container" class="prose prose-slate max-w-none">
                            <textarea name="content" id="editor" rows="20" class="w-full px-6 py-4 bg-slate-50 border-none rounded-[2rem] font-medium text-sm leading-relaxed focus:ring-2 focus:ring-toba-green transition">{{ old('content') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="xl:col-span-4 space-y-8">
                
                <!-- Classification -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-slate-900 font-black text-[11px] uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fas fa-folder-open text-[12px]"></i>
                        </span>
                        Kategori & Penulis
                    </h3>
                    <div class="space-y-4">
                        <select name="category" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500/20 text-xs font-bold text-slate-600">
                            <option value="tour">Tour & Travel</option>
                            <option value="news">Berita</option>
                            <option value="event">Event</option>
                            <option value="tips">Tips</option>
                        </select>
                        <input type="text" name="author" value="{{ auth()->user()->name }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500/20 text-xs font-bold text-slate-600">
                    </div>
                </div>
                <!-- Cover Image -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-slate-900 font-black text-[11px] uppercase tracking-[0.2em] mb-6 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fas fa-camera text-[12px]"></i>
                        </span>
                        Foto Sampul
                    </h3>
                    
                    <x-image-input 
                        name="cover_image"
                        label=""
                        :value="old('cover_image_id')"
                        :required="false"
                        category="blogs"
                        help="Pilih atau upload foto sampul untuk artikel blog ini"
                        class="mb-4"
                    />

                    @error('cover_image') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                    @error('cover_image_id') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                </div>

                <!-- Teaser -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
                    <h3 class="text-slate-900 font-black text-[11px] uppercase tracking-[0.2em] mb-4 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fas fa-paragraph text-[12px]"></i>
                        </span>
                        Ringkasan Singkat
                    </h3>
                    <textarea name="excerpt" rows="4" placeholder="Tuliskan teaser artikel..."
                        class="w-full px-6 py-5 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-green-500/20 text-xs font-medium text-slate-500 leading-relaxed">{{ old('excerpt') }}</textarea>
                </div>

                <!-- Publish Hub -->
                <div class="bg-slate-900 rounded-[3rem] p-10 shadow-2xl relative overflow-hidden">
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-green-500/10 rounded-full blur-3xl"></div>
                    <div class="relative space-y-6">
                        <div>
                            <label class="block text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3">Jadwal Tayang</label>
                            <input type="datetime-local" name="published_at" value="{{ now()->format('Y-m-d\TH:i') }}"
                                class="w-full bg-slate-800 border-none rounded-2xl text-white text-[11px] font-bold px-6 py-4 focus:ring-2 focus:ring-green-500">
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <label class="block text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-3">Status</label>
                                <select name="status" class="w-full bg-slate-800 border-none rounded-2xl text-white text-[10px] font-black uppercase tracking-widest px-6 py-4">
                                    <option value="published">Tayang</option>
                                    <option value="draft">Draft</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
{{-- Menggunakan CKEditor 5 (Versi Klasik) yang sepenuhnya mandiri dan gratis tanpa API Key --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<style>
    /* Styling agar CKEditor terlihat seperti kertas Word */
    .ck-editor__editable {
        min-height: 800px;
        border: none !important;
        padding: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
        border-color: transparent !important;
    }
    .ck-toolbar {
        border: none !important;
        background: #f8fafc !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 1rem 2rem !important;
        position: sticky !important;
        top: 0;
        z-index: 100;
        border-radius: 2.5rem 2.5rem 0 0 !important;
    }
    .ck.ck-editor__top .ck-sticky-panel .ck-toolbar {
        border-radius: 0 !important;
    }
</style>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'insertTable', 'undo', 'redo'
                ]
            },
            language: 'id',
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            window.editor = editor;
        })
        .catch(error => {
            console.error(error);
        });

    function blogCoverHandler() {
        return {
            previewUrl: '',
            imagePath: '',
            previewLocalImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewUrl = URL.createObjectURL(file);
                    this.imagePath = '';
                }
            },
            openMediaPicker() {
                window.dispatchEvent(new CustomEvent('open-media-picker', { 
                    detail: { 
                        callback: (item) => {
                            // Unified Path Logic matching ImageHelper.php
                            let path = item.path;
                            if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
                            if (path.startsWith('storage/')) path = path.replace('storage/', '');
                            
                            this.previewUrl = item.url || ('/storage/' + path);
                            this.imagePath = path;
                        } 
                    } 
                }));
            }
        }
    }
</script>
@endpush





@extends('admin.layout')

@section('title', 'Edit Destinasi')
@section('page-title', 'Edit Place')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.cities.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-toba-green transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-[3rem] border border-slate-50 shadow-sm overflow-hidden">
        <div class="bg-slate-900 p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-10">
                <i class="fas fa-edit text-6xl text-white"></i>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight relative z-10">Edit Data Destinasi</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1 relative z-10">{{ $city->name }}</p>
        </div>

        <form action="{{ route('admin.cities.update', $city) }}" method="POST" enctype="multipart/form-data" x-data="cityForm" class="p-12">
            @csrf
            @method('PATCH')
            
            <div class="space-y-6">
                <!-- Step 1: Lokasi Terstruktur -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Pilih Provinsi *</label>
                        <select name="province_id" id="province_id" required
                            x-model="provinceId"
                            @change="fetchRegencies()"
                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-slate-700">
                            <option value="">Pilih Provinsi</option>
                            @foreach($provinces as $province)
                                <option value="{{ $province->id }}">{{ $province->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Pilih Kabupaten/Kota *</label>
                        <div class="space-y-4">
                            <div class="relative">
                                <select name="regency_id" id="regency_id" required
                                    x-model="regencyId"
                                    :disabled="loadingRegencies || !provinceId"
                                    class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-slate-700 disabled:opacity-50">
                                    <option value="">Pilih Kabupaten</option>
                                    <template x-for="reg in regencies" :key="reg.id">
                                        <option :value="reg.id" x-text="reg.name"></option>
                                    </template>
                                    <option value="manual" x-show="provinceId">+ Input Manual</option>
                                </select>
                                <div x-show="loadingRegencies" class="absolute right-12 top-1/2 -translate-y-1/2">
                                    <i class="fas fa-circle-notch fa-spin text-toba-green"></i>
                                </div>
                            </div>

                            <!-- Manual Input Field -->
                            <div x-show="regencyId === 'manual'" x-transition class="relative group">
                                <input type="text" name="regency_name_manual" placeholder="Ketik Nama Kabupaten Baru..."
                                    class="w-full px-6 py-4 bg-green-50 border-2 border-green-100 rounded-2xl focus:ring-4 focus:ring-green-500/10 focus:border-green-500 transition font-bold text-green-700 placeholder:text-green-200">
                                <div class="absolute right-6 top-1/2 -translate-y-1/2 text-green-400">
                                    <i class="fas fa-pen-nib"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Nama Tempat -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 text-center">Nama Tempat / Objek Wisata *</label>
                    <input type="text" name="name" value="{{ old('name', $city->name) }}" required
                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[2rem] focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-black text-2xl text-slate-900 text-center">
                    @error('name') <p class="text-red-500 text-xs mt-2 text-center font-bold">{{ $message }}</p> @enderror
                </div>

                <!-- Step 3: Image Selection -->
                <x-image-input 
                    name="city_image"
                    label="Foto Wilayah / Destinasi"
                    :value="$city->image_id"
                    :required="false"
                    category="destinations"
                    help="Pilih atau upload foto yang mewakili destinasi ini"
                />

                @error('city_image') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                @error('image_id') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest group-hover:text-green-800">Pilih dari Galeri</p>
                        </div>
                    </div>

                    <!-- Preview Container -->
                    <div x-show="previewUrl || currentImage || selectedMedia" class="mt-6 flex justify-center">
                        <div class="relative w-full max-w-md h-64 rounded-[2.5rem] overflow-hidden border-4 border-white shadow-2xl group">
                            <img :src="previewUrl || (selectedMedia ? selectedMedia.url : currentImage)" class="w-full h-full object-cover">
                            <button type="button" @click="clearImage()" class="absolute top-4 right-4 w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                <i class="fas fa-times"></i>
                            </button>
                            <input type="hidden" name="media_id" :value="selectedMedia ? selectedMedia.id : ''">
                        </div>
                    </div>
                </div>

                <!-- Step 4: Deskripsi -->
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Deskripsi Singkat</label>
                    <textarea name="description" rows="4"
                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[2rem] focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-medium text-slate-600 leading-relaxed">{{ old('description', $city->description) }}</textarea>
                </div>

                <div class="pt-6 border-t border-slate-50 flex items-center gap-6">
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-5 rounded-[2rem] font-black uppercase tracking-widest hover:bg-black transition shadow-2xl shadow-slate-200">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.cities.index') }}" class="px-12 py-5 bg-slate-100 text-slate-500 rounded-[2rem] font-black uppercase tracking-widest hover:bg-slate-200 transition">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function initCityForm() {
        if (typeof Alpine === 'undefined') return;
        
        Alpine.data('cityForm', () => ({
            previewUrl: null,
            selectedMedia: null,
            currentImage: '{{ imageUrl($city->image) }}',
            
            // Dropdown Logic
            provinceId: '{{ $city->regency->province_id ?? '' }}',
            regencyId: '{{ $city->regency_id ?? '' }}',
            regencies: @json($regencies),
            loadingRegencies: false,

            async fetchRegencies() {
                if (!this.provinceId) {
                    this.regencies = [];
                    this.regencyId = '';
                    return;
                }

                this.loadingRegencies = true;
                try {
                    const response = await fetch(`{{ route('admin.cities.regencies') }}?province_id=${this.provinceId}`);
                    this.regencies = await response.json();
                    this.regencyId = ''; // Reset regency on province change
                } catch (error) {
                    console.error('Error fetching regencies:', error);
                } finally {
                    this.loadingRegencies = false;
                }
            },

            previewImage(e) {
                const file = e.target.files[0];
                if (file) {
                    this.previewUrl = URL.createObjectURL(file);
                    this.selectedMedia = null;
                    this.currentImage = null;
                }
            },

            openMediaPicker() {
                window.dispatchEvent(new CustomEvent('open-media-picker', { 
                    detail: { 
                        callback: (item) => {
                            this.selectedMedia = item;
                            this.previewUrl = null;
                            this.currentImage = null;
                        } 
                    } 
                }));
            },

            clearImage() {
                this.previewUrl = null;
                this.selectedMedia = null;
                this.currentImage = null;
                const fileInput = document.querySelector('input[type="file"]');
                if (fileInput) fileInput.value = '';
            }
        }));
    }

    if (window.Alpine) {
        initCityForm();
    } else {
        document.addEventListener('alpine:init', initCityForm);
    }
</script>
@endpush


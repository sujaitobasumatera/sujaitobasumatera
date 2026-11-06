@extends('admin.layout')

@section('title', 'Edit Package')
@section('page-title', 'Edit Package')

@push('head')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
@endpush

@section('content')
<div class="w-full max-w-full" x-data="packageEditForm">
    <div class="mb-6">
        <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 font-semibold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Packages
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-black text-gray-900 mb-6">Edit Package: {{ $package->name }}</h2>

        <form action="{{ route('admin.packages.update', $package) }}" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Package Name *</label>
                    <input type="text" name="name" value="{{ old('name', $package->name) }}" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Status *</label>
                    <select name="status" required
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                        <option value="active" {{ old('status', $package->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $package->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Current Images & Upload -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Package Images</label>
                    
                    <!-- Design Guidelines Guide -->
                    <div class="mb-4 p-4 rounded-2xl bg-green-50 border border-green-200 text-xs text-slate-700 space-y-2 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 font-black text-slate-900 text-xs uppercase tracking-wider">
                                <i class="fas fa-ruler-combined text-toba-green"></i>
                                <span>Panduan Standar Ukuran &amp; Rasio Gambar</span>
                            </div>
                            <button type="button" @click="$dispatch('open-image-guide')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-green-700 hover:bg-green-800 text-white font-bold text-[11px] transition shadow-sm">
                                <i class="fas fa-table-list text-[10px]"></i>
                                <span>Lihat Tabel Lengkap</span>
                            </button>
                        </div>
                        <p class="text-xs leading-relaxed text-slate-600">
                            • <strong>Galeri Paket (Rekomendasi Utama):</strong> <span class="font-bold text-slate-900">1600 &times; 1200 px</span> (Rasio <strong>4:3</strong>), minimal 1200 &times; 900 px.<br>
                            • <strong>Foto Sampul Kartu:</strong> Foto urutan pertama otomatis menjadi cover kartu beranda, kartu paket wisata, dan banner WhatsApp preview.<br>
                            • <strong>Aman Tampil (Safe Zone):</strong> Posisikan subjek wisata di area tengah dengan margin 10% dari tepi agar tidak terpotong di berbagai layar perangkat.<br>
                            • <strong>Format &amp; Kompresi:</strong> JPG, PNG, atau WebP (maks 15 MB). Sistem secara otomatis mengoptimasi resolusi dan menyajikan WebP ringan dengan srcset responsif.
                        </p>
                    </div>

                    @if($package->images && count($package->images) > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mb-4">
                            @foreach($package->images as $image)
                                <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-200 shadow-sm group transition"
                                     :class="isRemoving('{{ $image }}') ? 'ring-4 ring-red-500 opacity-50 scale-95' : ''">
                                    <img src="{{ $package->resolveImageUrl($image) }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                        <button type="button" @click="$dispatch('zoom-image', { url: '{{ $package->resolveImageUrl($image) }}', title: '{{ addslashes($package->name) }}' })"
                                                class="w-8 h-8 rounded-lg bg-slate-900/90 hover:bg-slate-900 text-white flex items-center justify-center shadow-lg transition"
                                                title="Lihat & Zoom Foto">
                                            <i class="fas fa-search-plus text-xs"></i>
                                        </button>
                                        <label class="cursor-pointer bg-red-600 hover:bg-red-700 text-white w-8 h-8 rounded-lg flex items-center justify-center transition"
                                               title="Hapus / Kembalikan">
                                            <input type="checkbox" name="remove_images[]" value="{{ $image }}" class="hidden" @change="toggleRemove('{{ $image }}')">
                                            <i class="fas" :class="isRemoving('{{ $image }}') ? 'fa-undo' : 'fa-trash-alt'"></i>
                                        </label>
                                    </div>
                                    <div class="absolute top-1 right-1 bg-red-600 text-[10px] text-white px-1 rounded font-black" x-show="isRemoving('{{ $image }}')">TO DELETE</div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="flex flex-col gap-4 mb-4">
                        <button type="button" @click="openPackageMediaPicker()" class="w-full border-2 border-dashed border-gray-300 rounded-2xl p-10 text-center hover:border-green-700 hover:bg-green-100/30 transition group bg-gray-50/50 flex flex-col items-center justify-center gap-3">
                            <div class="w-16 h-16 rounded-2xl bg-green-100 text-green-800 flex items-center justify-center group-hover:scale-110 transition-transform shadow-sm">
                                <i class="fas fa-images text-3xl"></i>
                            </div>
                            <p class="text-lg font-black text-slate-800 tracking-tight mt-2">Pilih dari Galeri Pusat</p>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Pilih satu atau lebih aset tambahan untuk paket ini</p>
                        </button>
                    </div>

                    <div id="selected-media-container" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mb-4" x-show="selectedMedia.length > 0">
                        <template x-for="(item, idx) in selectedMedia" :key="'media'+item.id">
                            <div class="relative aspect-square rounded-lg overflow-hidden border-2 border-green-700 shadow-lg group">
                                <img :src="'/storage/' + (item.path.replace(/^\/?storage\//, ''))" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    <button type="button" @click="$dispatch('zoom-image', { url: '/storage/' + (item.path.replace(/^\/?storage\//, '')), title: item.title || 'Foto Galeri' })"
                                            class="w-8 h-8 rounded-lg bg-slate-900/90 hover:bg-slate-900 text-white flex items-center justify-center shadow-lg transition"
                                            title="Lihat & Zoom Foto">
                                        <i class="fas fa-search-plus text-xs"></i>
                                    </button>
                                    <button type="button" @click="selectedMedia.splice(idx, 1)" class="w-8 h-8 rounded-lg bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg transition" title="Batal Pilih">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="media_ids[]" :value="item.id">
                                <div class="absolute top-1 right-1 bg-green-800 text-[7px] text-white px-1.5 py-0.5 rounded-full font-black tracking-widest pointer-events-none">GALLERY</div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-4 flex flex-col gap-2">
                        <label class="block text-[10px] font-bold text-gray-400 uppercase tracking-widest">Atau Upload Gambar Baru dari Perangkat (Lokal)</label>
                        <input type="file" name="images[]" multiple @change="handleLocalFiles($event)" class="block w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:tracking-widest file:bg-slate-900 file:text-white hover:file:bg-slate-800 transition cursor-pointer">
                    </div>

                    <!-- Local Images Preview -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4 mt-4" x-show="localPreviews.length > 0">
                        <template x-for="(preview, idx) in localPreviews" :key="'local'+idx">
                            <div class="relative aspect-square rounded-lg overflow-hidden border-2 border-green-500 shadow-lg group">
                                <img :src="preview.url" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-slate-900/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center gap-2">
                                    <button type="button" @click="$dispatch('zoom-image', { url: preview.url, title: preview.file.name })"
                                            class="w-8 h-8 rounded-lg bg-slate-900/90 hover:bg-slate-900 text-white flex items-center justify-center shadow-lg transition"
                                            title="Lihat & Zoom Foto">
                                        <i class="fas fa-search-plus text-xs"></i>
                                    </button>
                                    <button type="button" @click="removeLocalFile(idx)" class="w-8 h-8 rounded-lg bg-rose-500 hover:bg-rose-600 text-white flex items-center justify-center shadow-lg transition" title="Hapus">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                                <div class="absolute top-1 right-1 bg-green-600 text-[7px] text-white px-1.5 py-0.5 rounded-full font-black tracking-widest pointer-events-none">LOCAL</div>
                            </div>
                        </template>
                    </div>
                    <p class="text-xs text-gray-400 mt-2 italic">* Arahkan kursor ke foto untuk memperbesar atau menandai hapus.</p>
                </div>

                @include('admin.packages._media-fields')

                <!-- Short Description -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Short Description</label>
                    <input type="text" name="shortDescription" value="{{ old('shortDescription', $package->shortDescription) }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Full Description</label>
                    <textarea name="description" id="editor" rows="15"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">{{ old('description', $package->description) }}</textarea>
                </div>

                <!-- Dynamic Itinerary Editor -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">Itinerary (Rencana Perjalanan)</h3>
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-1">Susun jadwal perjalanan per hari</p>
                        </div>
                        <button type="button" @click="addDay()" class="bg-slate-900 text-white px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-200">
                            <i class="fas fa-plus mr-2"></i> Tambah Hari
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(item, index) in itinerary" :key="index">
                            <div class="bg-white p-5 rounded-2xl border border-gray-200 shadow-sm relative group animate-in fade-in slide-in-from-top-2">
                                <div class="flex items-center gap-4 mb-4">
                                    <div class="w-8 h-8 rounded-lg bg-toba-green text-white flex items-center justify-center font-black text-xs shadow-sm">
                                        <span x-text="index + 1"></span>
                                    </div>
                                    <input type="text" :name="'itinerary['+index+'][title]'" x-model="item.title" placeholder="Judul Hari (misal: Penjemputan & City Tour)"
                                        class="flex-1 min-w-0 px-4 py-2 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm">
                                    <button type="button" @click="removeDay(index)" class="shrink-0 text-gray-300 hover:text-red-500 transition p-3">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                                <textarea :name="'itinerary['+index+'][description]'" x-model="item.description" rows="3" placeholder="Detail kegiatan hari ini..."
                                    class="w-full px-4 py-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-toba-green/20 text-sm font-medium"></textarea>
                            </div>
                        </template>

                        <div x-show="itinerary.length === 0" class="py-8 text-center border-2 border-dashed border-gray-200 rounded-2xl">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Belum ada jadwal perjalanan</p>
                            <button type="button" @click="addDay()" class="mt-3 text-toba-green text-[10px] font-black uppercase tracking-widest hover:underline">
                                Mulai susun sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Location & Duration Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Location Tag</label>
                        <input type="text" name="locationTag" value="{{ old('locationTag', $package->locationTag) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Duration</label>
                        <input type="text" name="duration" value="{{ old('duration', $package->duration) }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                    </div>
                </div>

                <!-- Price & Child Price Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Price (Adult) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">RM</span>
                            <input type="number" name="price" value="{{ old('price', $package->price) }}" required min="0" step="0.01"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition @error('price') border-red-500 @enderror">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Price (Child)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">RM</span>
                            <input type="number" name="childPrice" value="{{ old('childPrice', $package->childPrice) }}" min="0" step="0.01"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                        </div>
                    </div>
                </div>

                <!-- HARGA GROSIR / TIERED PRICING -->
                <div class="mt-8 border-t border-gray-200 pt-8">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-sm font-black text-gray-900">Harga Grosir (Tiered Pricing)</h4>
                            <p class="text-xs text-gray-500 mt-1">Atur harga berbeda berdasarkan jumlah orang (misal 1-9 pax = RM 350, 10-15 pax = RM 320)</p>
                        </div>
                        <button type="button" @click="addTier()" class="px-4 py-2 bg-green-100 text-green-800 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-green-200 transition shadow-sm border border-green-200">
                            <i class="fas fa-plus mr-1"></i> Add Tier
                        </button>
                    </div>

                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="flex flex-wrap gap-4 mb-4 p-4 bg-white rounded-xl border border-gray-200 relative group shadow-sm items-end">
                            <!-- Min Pax -->
                            <div class="flex-1 md:w-32">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Min Orang</label>
                                <input type="number" :name="'pricingDetails[tiers]['+index+'][min_pax]'" x-model.number="tier.min_pax" required min="1"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                            </div>
                            
                            <!-- Max Pax -->
                            <div class="flex-1 md:w-32">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Max Orang</label>
                                <input type="number" :name="'pricingDetails[tiers]['+index+'][max_pax]'" x-model.number="tier.max_pax" required min="1"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                            </div>

                            <!-- Harga -->
                            <div class="w-full md:flex-1 md:min-w-[140px]">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga Dewasa (RM)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">RM</span>
                                    <input type="number" :name="'pricingDetails[tiers]['+index+'][price]'" x-model.number="tier.price" required min="0" step="0.01"
                                        class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                </div>
                            </div>

                            <!-- Harga Anak -->
                            <div class="w-full md:flex-1 md:min-w-[140px]">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga Anak (RM)</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">RM</span>
                                    <input type="number" :name="'pricingDetails[tiers]['+index+'][child_price]'" x-model.number="tier.child_price" required min="0" step="0.01" placeholder="0 jika anak gratis"
                                        class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                </div>
                            </div>

                            <!-- Delete Button -->
                            <div class="flex items-end pb-1">
                                <button type="button" @click="tiers.splice(index, 1)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition">
                                    <i class="fas fa-trash-alt text-sm"></i>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div x-show="tiers.length === 0" class="text-center py-6 text-green-600 text-xs font-bold uppercase tracking-widest bg-white border-2 border-dashed border-green-200 rounded-2xl">
                        Belum ada pengaturan harga grosir
                    </div>
                </div>

                <!-- PENGATURAN LANJUTAN (ACCORDION) -->
                <div x-data="{ advancedOpen: false }" class="bg-gray-50 rounded-2xl border border-gray-200 mt-8">
                    <button type="button" @click="advancedOpen = !advancedOpen" class="w-full px-6 py-4 flex items-center justify-between focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center font-black">
                                <i class="fas fa-sliders-h"></i>
                            </div>
                            <div class="text-left">
                                <h3 class="text-sm font-black text-gray-900">Pengaturan Lanjutan (Opsional)</h3>
                                <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest">Harga Modal, Layanan Tambahan, Lokasi Spesifik</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300" :class="advancedOpen ? 'rotate-180' : ''"></i>
                    </button>
                    
                    <div x-show="advancedOpen" x-collapse x-cloak class="px-6 pb-6 space-y-6 border-t border-gray-200 pt-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Harga Modal (Internal)</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                                <input type="number" name="cost_price" value="{{ old('cost_price', $package->cost_price) }}" min="0" step="1000"
                                    class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-transparent transition" placeholder="Opsional">
                            </div>
                            <p class="mt-1 text-[10px] text-gray-400 font-bold uppercase tracking-widest">Dicatat dalam Rupiah (dibayar ke vendor lokal), berbeda dari harga jual yang dalam Ringgit</p>
                        </div>

                <!-- Layanan Tambahan (Additional Services) - DYNAMIC CRUD -->
                <div class="bg-green-100/50 rounded-2xl p-6 border border-green-200 space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-black text-green-950 flex items-center gap-2">
                                <i class="fas fa-hand-holding-usd text-green-800"></i> Layanan Tambahan (Additional Services)
                            </h3>
                            <p class="text-[10px] font-bold text-green-700 uppercase tracking-widest mt-1">Kelola opsi layanan berbayar tambahan untuk paket ini</p>
                        </div>
                        <button type="button" @click="addAdditionalService()" class="bg-green-800 text-white px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-green-900 transition shadow-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Layanan
                        </button>
                    </div>

                    {{-- Kendaraan besar. Sengaja TIDAK di dalam daftar layanan:
                         angkanya menggantikan tarif per pax, bukan menambahnya.
                         Ditaruh sebagai layanan, satu pesanan Van 6 pax ditagih
                         tarif Innova ditambah sekali biaya Van -- terlalu mahal,
                         dan tidak ada gejalanya. --}}
                    <div class="mb-4 p-4 bg-white border border-green-200 rounded-xl">
                        <label class="block text-[10px] font-bold text-gray-500 mb-2 uppercase tracking-widest">Pilihan Kendaraan Besar (tarif pengganti)</label>
                        <div class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Nama Kendaraan</label>
                                <input type="text" name="pricingDetails[vehicle][name]" x-model="vehicle.name" placeholder="Van Toyota Hiace"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                            </div>
                            <div class="md:w-32">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Mulai Dari (pax)</label>
                                <input type="number" min="1" name="pricingDetails[vehicle][min_pax]" x-model.number="vehicle.min_pax" placeholder="6"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                            </div>
                            <div class="md:w-40">
                                <label class="block text-[10px] font-bold text-gray-500 mb-1">Tarif (RM/pax)</label>
                                <input type="number" min="0" step="0.01" name="pricingDetails[vehicle][price]" x-model.number="vehicle.price" placeholder="700.00"
                                    class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                            </div>
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2 italic">* Tamu baru melihat pilihan ini setelah jumlah dewasa mencapai ambang di atas. Kalau dipilih, seluruh tarif per orang memakai angka ini — tarif grosir di atas diabaikan. Kosongkan tarifnya bila paket ini tidak menawarkan kendaraan besar.</p>
                    </div>

                    <div class="space-y-3">
                        <template x-for="(service, index) in additionalServices" :key="index">
                            <div class="flex flex-col md:flex-row gap-4 p-4 bg-white border border-green-200 rounded-xl relative group animate-in fade-in duration-200">
                                <!-- Nama Layanan -->
                                <div class="flex-1">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Nama Layanan</label>
                                    <input type="text" :name="'pricingDetails[additional_services]['+index+'][name]'" x-model="service.name" placeholder="misal: Private Jet Charter" required
                                        class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                </div>
                                
                                <!-- Icon -->
                                <div class="w-full md:w-48">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Material Icon Name</label>
                                    <div class="relative">
                                        <input type="text" :name="'pricingDetails[additional_services]['+index+'][icon]'" x-model="service.icon" placeholder="misal: flight_takeoff" required
                                            class="w-full pl-9 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                        <span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-500 text-sm" x-text="service.icon || 'help'"></span>
                                    </div>
                                </div>

                                <!-- Harga -->
                                <div class="w-full md:w-56">
                                    <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga (RM)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">RM</span>
                                        <input type="number" :name="'pricingDetails[additional_services]['+index+'][price]'" x-model.number="service.price" placeholder="350.00" required min="0" step="0.01"
                                            class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                    </div>
                                </div>

                                <!-- Delete Button -->
                                <div class="flex items-end pb-1">
                                    <button type="button" @click="additionalServices.splice(index, 1)" class="text-red-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition">
                                        <i class="fas fa-trash-alt text-sm"></i>
                                    </button>
                                </div>
                            </div>
                        </template>


                        <div x-show="tiers.length === 0" class="text-center py-6 text-green-600 text-xs font-bold uppercase tracking-widest bg-white border-2 border-dashed border-green-200 rounded-2xl">
                            Belum ada pengaturan harga grosir
                        </div>
                    </div>
                </div>

                <!-- Dynamic Includes & Excludes Editor -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Includes -->
                    <div class="bg-green-50 rounded-2xl p-5 border border-green-100">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-black text-green-900">✅ Yang Termasuk</h3>
                                <p class="text-[9px] font-bold text-green-600 uppercase tracking-widest mt-0.5">Fasilitas yang didapat</p>
                            </div>
                            <button type="button" @click="addInclude()" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-green-700 transition">
                                <i class="fas fa-plus mr-1"></i> Tambah
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(item, index) in includes" :key="'inc'+index">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'includes['+index+']'" x-model="includes[index]" placeholder="contoh: Tiket Masuk"
                                        class="flex-1 px-3 py-2 bg-white border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-green-300">
                                    <button type="button" @click="includes.splice(index, 1)" class="text-green-300 hover:text-red-500 transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </template>
                            <div x-show="includes.length === 0" class="text-center py-4 text-green-400 text-[10px] font-bold uppercase tracking-widest">Belum ada item</div>
                        </div>
                    </div>

                    <!-- Excludes -->
                    <div class="bg-red-50 rounded-2xl p-5 border border-red-100">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-sm font-black text-red-900">❌ Tidak Termasuk</h3>
                                <p class="text-[9px] font-bold text-red-500 uppercase tracking-widest mt-0.5">Fasilitas di luar paket</p>
                            </div>
                            <button type="button" @click="addExclude()" class="bg-red-500 text-white px-3 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest hover:bg-red-600 transition">
                                <i class="fas fa-plus mr-1"></i> Tambah
                            </button>
                        </div>
                        <div class="space-y-2">
                            <template x-for="(item, index) in excludes" :key="'exc'+index">
                                <div class="flex items-center gap-2">
                                    <input type="text" :name="'excludes['+index+']'" x-model="excludes[index]" placeholder="contoh: Biaya penginapan"
                                        class="flex-1 px-3 py-2 bg-white border-none rounded-xl text-xs font-bold focus:ring-2 focus:ring-red-200">
                                    <button type="button" @click="excludes.splice(index, 1)" class="text-red-300 hover:text-red-600 transition">
                                        <i class="fas fa-times text-xs"></i>
                                    </button>
                                </div>
                            </template>
                            <div x-show="excludes.length === 0" class="text-center py-4 text-red-400 text-[10px] font-bold uppercase tracking-widest">Belum ada item</div>
                        </div>
                    </div>
                </div>

                <!-- City -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kota Tujuan / City (Bisa Pilih Banyak)</label>
                    <select name="cityIds[]" multiple
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition" style="min-height: 120px;">
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ in_array($city->id, old('cityIds', $package->cities->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Tahan tombol Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu kota.</p>
                </div>

                <!-- Featured Checkbox -->
                <div class="flex items-center">
                    <input type="checkbox" name="isFeatured" value="1" id="isFeatured" {{ old('isFeatured', $package->isFeatured) ? 'checked' : '' }}
                        class="w-5 h-5 text-toba-green border-gray-300 rounded focus:ring-toba-green">
                    <label for="isFeatured" class="ml-3 text-sm font-bold text-gray-700 cursor-pointer">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>Tandai sebagai Paket Unggulan (Featured)
                    </label>
                </div>
            </div> <!-- End of Advanced Settings -->

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" :disabled="isSubmitting" class="inline-flex items-center justify-center bg-gradient-to-r from-toba-green to-green-600 text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-toba-green/30 transition shadow-md disabled:opacity-50">
                        <template x-if="!isSubmitting">
                            <div class="flex items-center">
                                <i class="fas fa-save mr-2"></i> Update Package
                            </div>
                        </template>
                        <template x-if="isSubmitting">
                            <div class="flex items-center">
                                <svg class="animate-spin h-4 w-4 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Updating...
                            </div>
                        </template>
                    </button>
                    <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center justify-center bg-gray-100 text-gray-700 px-8 py-3 rounded-xl font-bold hover:bg-gray-200 transition">
                        <i class="fas fa-times mr-2"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    tinymce.init({
        selector: '#editor',
        plugins: 'lists link table code help wordcount',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | removeformat',
        height: 500,
        branding: false,
        promotion: false
    });

    document.addEventListener('alpine:init', () => {
        Alpine.data('packageEditForm', () => ({
            previews: [],
            removeImages: [],
            itinerary: @js($package->itinerary ?? []),
            includes: @js(is_array($package->includes) ? $package->includes : ($package->includes ? array_values((array)$package->includes) : [])),
            excludes: @js(is_array($package->excludes) ? $package->excludes : ($package->excludes ? array_values((array)$package->excludes) : [])),
            additionalServices: @js($package->pricingDetails['additional_services'] ?? [
                ['name' => 'Private Jet Charter', 'icon' => 'flight_takeoff', 'price' => 0]
            ]),
            tiers: @js($package->pricingDetails['tiers'] ?? []),
            vehicle: @js($package->pricingDetails['vehicle'] ?? ['name' => '', 'min_pax' => 6, 'price' => null]),
            isSubmitting: false,
            localFiles: [],
            localPreviews: [],

            addDay() { this.itinerary.push({ title: '', description: '' }); },
            removeDay(index) { this.itinerary.splice(index, 1); },
            addInclude() { this.includes.push(''); },
            addExclude() { this.excludes.push(''); },
            addAdditionalService() { this.additionalServices.push({ name: '', icon: 'help', price: 0 }); },
            addTier() { this.tiers.push({ min_pax: 1, max_pax: 9, price: 0, child_price: null }); },

            selectedMedia: [],
            openPackageMediaPicker() {
                window.dispatchEvent(new CustomEvent('open-media-picker', { 
                    detail: { 
                        callback: (item) => {
                            let path = item.path;
                            if (path.startsWith('/storage/')) path = path.replace('/storage/', '');
                            if (path.startsWith('storage/')) path = path.replace('storage/', '');

                            if (!this.selectedMedia.some(m => m.id === item.id)) {
                                this.selectedMedia.push({ ...item, path: path });
                            }
                        } 
                    } 
                }));
            },

            toggleRemove(imageUrl) {
                if (this.removeImages.includes(imageUrl)) {
                    this.removeImages = this.removeImages.filter(i => i !== imageUrl);
                } else {
                    this.removeImages.push(imageUrl);
                }
            },

            isRemoving(imageUrl) {
                return this.removeImages.includes(imageUrl);
            },

            handleLocalFiles(e) {
                const files = Array.from(e.target.files);
                files.forEach(file => {
                    this.localFiles.push(file);
                    this.localPreviews.push({
                        url: URL.createObjectURL(file),
                        name: file.name
                    });
                });
                this.updateFileInput();
            },

            removeLocalFile(idx) {
                this.localFiles.splice(idx, 1);
                this.localPreviews.splice(idx, 1);
                this.updateFileInput();
            },

            updateFileInput() {
                const fileInput = document.querySelector('input[type="file"][name="images[]"]');
                if (!fileInput) return;
                const dataTransfer = new DataTransfer();
                this.localFiles.forEach(file => dataTransfer.items.add(file));
                fileInput.files = dataTransfer.files;
            }
        }))
    });
</script>
@endpush

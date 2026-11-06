@extends('admin.layout')

@section('title', 'Buat Paket Baru')
@section('page-title', 'Buat Paket Baru')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <a href="{{ route('admin.packages.index') }}" class="hover:text-toba-green transition">Daftar Paket</a>
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Buat Baru</span>
@endsection

@push('head')
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
@endpush

@section('content')
<div class="w-full max-w-full" x-data="packageForm">
    <div class="mb-6">
        <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center text-gray-600 hover:text-gray-900 font-semibold transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Packages
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
        <h2 class="text-2xl font-black text-gray-900 mb-6">Create New Package</h2>

        <form action="{{ route('admin.packages.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="space-y-6">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Package Name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
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
                        <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Images Upload -->
                <x-image-input 
                    name="package_images"
                    label="Package Images"
                    :value="old('media_ids', [])"
                    :multiple="true"
                    :required="false"
                    category="tours"
                    help="Pilih satu atau lebih gambar untuk paket ini. Anda bisa memilih dari media library atau upload gambar baru."
                />

                @error('package_images') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror
                @error('media_ids') <p class="text-red-500 text-xs mt-2 font-bold">{{ $message }}</p> @enderror

                @include('admin.packages._media-fields', ['package' => null])

                <!-- Short Description -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Short Description</label>
                    <input type="text" name="shortDescription" value="{{ old('shortDescription') }}"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Full Description</label>
                    <textarea name="description" id="editor" rows="15"
                        class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">{{ old('description') }}</textarea>
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
                        <input type="text" name="locationTag" value="{{ old('locationTag') }}" placeholder="e.g., Danau Toba"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Duration</label>
                        <input type="text" name="duration" value="{{ old('duration') }}" placeholder="e.g., 3 Days 2 Nights"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                    </div>
                </div>

                <!-- Price & Child Price Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Price (Adult) *</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">RM</span>
                            <input type="number" name="price" value="{{ old('price') }}" required min="0" step="0.01"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition @error('price') border-red-500 @enderror">
                        </div>
                        @error('price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Price (Child)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">RM</span>
                            <input type="number" name="childPrice" value="{{ old('childPrice') }}" min="0" step="0.01"
                                class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-toba-green focus:border-transparent transition">
                        </div>
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
                                <input type="number" name="cost_price" value="{{ old('cost_price') }}" min="0" step="1000"
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

                    {{-- Kendaraan besar: tarif PENGGANTI per pax, bukan biaya
                         tambahan. Sengaja di luar daftar layanan, sama seperti
                         di form edit. --}}
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

                        <div x-show="additionalServices.length === 0" class="text-center py-6 text-green-600 text-xs font-bold uppercase tracking-widest bg-white border-2 border-dashed border-green-200 rounded-2xl">
                            Belum ada layanan tambahan aktif
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

                                <!-- Harga Dewasa & Anak -->
                                <div class="flex-1 flex flex-col sm:flex-row gap-4">
                                    <div class="w-full md:flex-1 md:min-w-[140px]">
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga Dewasa (RM)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">RM</span>
                                            <input type="number" :name="'pricingDetails[tiers]['+index+'][price]'" x-model.number="tier.price" required min="0" step="0.01"
                                                class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                        </div>
                                    </div>
                                    <div class="w-full md:flex-1 md:min-w-[140px]">
                                        <label class="block text-[10px] font-bold text-gray-500 mb-1">Harga Anak (RM)</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-xs font-bold">RM</span>
                                            <input type="number" :name="'pricingDetails[tiers]['+index+'][child_price]'" x-model.number="tier.child_price" required min="0" step="0.01" placeholder="0 jika anak gratis"
                                                class="w-full pl-8 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-xs font-bold focus:ring-2 focus:ring-green-400">
                                        </div>
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
                            <option value="{{ $city->id }}" {{ in_array($city->id, old('cityIds', [])) ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-2">Tahan tombol Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu kota.</p>
                </div>

                <!-- Featured Checkbox -->
                <div class="flex items-center">
                    <input type="checkbox" name="isFeatured" value="1" id="isFeatured" {{ old('isFeatured') ? 'checked' : '' }}
                        class="w-5 h-5 text-toba-green border-gray-300 rounded focus:ring-toba-green">
                    <label for="isFeatured" class="ml-3 text-sm font-bold text-gray-700 cursor-pointer">
                        <i class="fas fa-star text-yellow-500 mr-1"></i>Tandai sebagai Paket Unggulan (Featured)
                    </label>
                </div>
            </div> <!-- End of Advanced Settings -->

                <!-- Submit Buttons -->
                <div class="flex flex-col sm:flex-row sm:items-center gap-4 pt-6 border-t border-gray-200">
                    <button type="submit" class="inline-flex items-center justify-center bg-gradient-to-r from-toba-green to-green-600 text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg hover:shadow-toba-green/30 transition shadow-md">
                        <i class="fas fa-save mr-2"></i> Create Package
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
        Alpine.data('packageForm', () => ({
            previews: [],
            itinerary: [],
            includes: [],
            excludes: [],
            additionalServices: [
                { name: 'Private Jet Charter', icon: 'flight_takeoff', price: 0 }
            ],
            tiers: [],
            vehicle: { name: '', min_pax: 6, price: null },
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
        }));
    });
</script>
@endpush

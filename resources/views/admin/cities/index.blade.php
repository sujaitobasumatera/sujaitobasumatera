@extends('admin.layout')

@section('title', 'Manajemen Wilayah & Destinasi')
@section('page-title', 'Location & Categories')

@section('content')
<div class="space-y-6">
    <!-- Header with Tabs -->
    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-slate-50">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-xl font-black text-slate-900 tracking-tight">Manajemen Wilayah</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Destinasi & Klasifikasi Kabupaten</p>
            </div>
            
            <!-- Tabs Switcher -->
            <div class="flex bg-slate-50 p-1.5 rounded-2xl border border-slate-100">
                <button onclick="switchTab('destinasi')" id="btn-destinasi" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition duration-300 bg-white text-slate-900 shadow-sm">
                    Daftar Destinasi
                </button>
                <button onclick="switchTab('kategori')" id="btn-kategori" class="px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest transition duration-300 text-slate-400 hover:text-slate-600">
                    Kategori Kabupaten
                </button>
            </div>

            <a href="{{ route('admin.cities.create') }}" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-200 flex items-center gap-2">
                <i class="fas fa-plus-circle text-sm"></i> Add New Place
            </a>
        </div>
    </div>

    <!-- TAB 1: DAFTAR DESTINASI -->
    <div id="tab-destinasi" class="space-y-6 tab-content active">
        <!-- Multi-Level Filters -->
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-50 shadow-sm">
            <form action="{{ route('admin.cities.index') }}" method="GET" id="filter-form" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <input type="hidden" name="active_tab" value="destinasi">
                <!-- Province Filter -->
                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Provinsi</label>
                    <select name="province_id" id="province_filter" onchange="this.form.submit()" class="w-full px-6 py-3.5 bg-slate-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-2 focus:ring-toba-green">
                        <option value="">Semua Provinsi</option>
                        @foreach($provinces as $p)
                            <option value="{{ $p->id }}" {{ request('province_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Regency Filter -->
                <div class="space-y-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Kabupaten / Kota</label>
                    <select name="regency_id" id="regency_filter" onchange="this.form.submit()" class="w-full px-6 py-3.5 bg-slate-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-2 focus:ring-toba-green {{ !request('province_id') ? 'opacity-50 cursor-not-allowed' : '' }}" {{ !request('province_id') ? 'disabled' : '' }}>
                        <option value="">Semua Kabupaten</option>
                        @foreach($regencies as $r)
                            <option value="{{ $r->id }}" {{ request('regency_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Search -->
                <div class="space-y-2 md:col-span-2">
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest ml-2">Cari Nama Tempat</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Ketik nama destinasi..."
                            class="w-full px-6 py-3.5 bg-slate-50 border-none rounded-xl text-[10px] font-bold text-slate-600 focus:ring-2 focus:ring-toba-green pl-12">
                        <div class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-300">
                            <i class="fas fa-search"></i>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cities Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
            @forelse($cities as $city)
                <div class="group bg-white rounded-[2.5rem] border border-slate-50 overflow-hidden shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition duration-500">
                    <div class="h-48 bg-slate-50 relative overflow-hidden">
                        @if($city->image)
                            <img src="{{ imageUrl($city->image) }}" alt="{{ $city->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-slate-200 bg-gradient-to-br from-slate-50 to-slate-100">
                                <i class="fas fa-map-marked-alt text-4xl mb-2"></i>
                                <span class="text-[8px] font-black uppercase tracking-widest">{{ $city->regency->name ?? 'No Location' }}</span>
                            </div>
                        @endif
                        
                        <div class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <a href="{{ route('admin.cities.edit', $city) }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white text-slate-900 hover:bg-toba-green hover:text-white transition shadow-lg">
                                <i class="fas fa-pencil text-xs"></i>
                            </a>
                            <form action="{{ route('admin.cities.destroy', $city) }}" method="POST" onsubmit="return confirm('Hapus destinasi ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-rose-500 text-white hover:bg-rose-600 transition shadow-lg">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>

                        <div class="absolute top-4 left-4 flex gap-2">
                            <span class="px-3 py-1.5 rounded-full bg-white/90 backdrop-blur-md text-[8px] font-black uppercase tracking-widest text-slate-900 shadow-sm">
                                {{ $city->regency?->province?->name ?? 'Indonesia' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-8">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-6 h-6 rounded-lg bg-green-50 flex items-center justify-center text-green-500 text-[10px]">
                                <i class="fas fa-location-dot"></i>
                            </div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $city->regency->name ?? 'Umum' }}</span>
                        </div>
                        <h3 class="text-lg font-black text-slate-900 tracking-tight mb-2 leading-tight">{{ $city->name }}</h3>
                        <p class="text-xs font-bold text-slate-400 line-clamp-2 leading-relaxed mb-6">{{ $city->description ?? 'Deskripsi destinasi belum ditambahkan.' }}</p>
                        <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest"><i class="fas fa-calendar-check mr-2 text-toba-green"></i> {{ $city->packages_count ?? 0 }} Pkt</span>
                            @if($city->regency?->category)
                                <span class="px-2 py-1 rounded bg-slate-50 text-[7px] font-black text-slate-400 uppercase tracking-widest">
                                    {{ $city->regency->category }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-14 text-center bg-white rounded-[2.5rem] border border-dashed border-slate-200">
                    <i class="fas fa-map-location-dot text-4xl text-slate-100 mb-4"></i>
                    <p class="text-[10px] font-black text-slate-300 uppercase tracking-[0.4em]">Tidak ada destinasi yang cocok</p>
                </div>
            @endforelse
        </div>

        @if($cities->hasPages())
            <div class="pt-8">
                {{ $cities->links() }}
            </div>
        @endif
    </div>

    <!-- TAB 2: KATEGORI KABUPATEN -->
    <div id="tab-kategori" class="space-y-8 tab-content hidden">
        <!-- Filter Kategori -->
        <div class="bg-white p-6 rounded-[2rem] border border-slate-50 shadow-sm">
            <form action="{{ route('admin.cities.index') }}" method="GET" class="flex items-center gap-4">
                <input type="hidden" name="active_tab" value="kategori">
                <select name="cat_province_id" onchange="this.form.submit()" class="px-6 py-3.5 bg-slate-50 border-none rounded-xl text-[10px] font-black uppercase tracking-widest text-slate-600 focus:ring-2 focus:ring-toba-green">
                    <option value="">Pilih Provinsi (Filter Kategori)</option>
                    @foreach($provinces as $p)
                        <option value="{{ $p->id }}" {{ request('cat_province_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        <!-- Regency Table -->
        <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50">
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kabupaten / Kota</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Provinsi</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($all_regencies as $reg)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-8 py-6">
                            <span class="text-sm font-black text-slate-900 tracking-tight">{{ $reg->name }}</span>
                            {{-- Province inline on mobile (column hidden) --}}
                            <span class="md:hidden block mt-1 text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $reg->province->name }}</span>
                        </td>
                        <td class="px-8 py-6 hidden md:table-cell">
                            <span class="px-3 py-1 rounded-full bg-slate-100 text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                {{ $reg->province->name }}
                            </span>
                        </td>
                        <td class="px-8 py-6">
                            @if($reg->category)
                                <span class="px-3 py-1.5 rounded-xl bg-green-50 text-[9px] font-black text-green-600 uppercase tracking-widest border border-green-100">
                                    {{ $reg->category }}
                                </span>
                            @else
                                <span class="text-[9px] font-bold text-slate-300 italic uppercase">Belum Dikategorikan</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right">
                            <a href="{{ route('admin.regencies.edit', $reg) }}" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition shadow-sm">
                                <i class="fas fa-pencil text-[10px]"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<style>
    .tab-content.active { display: block; }
    .tab-content.hidden { display: none; }
</style>

<script>
    function switchTab(tab) {
        // Elements
        const tabDestinasi = document.getElementById('tab-destinasi');
        const tabKategori = document.getElementById('tab-kategori');
        const btnDestinasi = document.getElementById('btn-destinasi');
        const btnKategori = document.getElementById('btn-kategori');

        if (tab === 'destinasi') {
            tabDestinasi.classList.remove('hidden');
            tabDestinasi.classList.add('active');
            tabKategori.classList.add('hidden');
            tabKategori.classList.remove('active');

            btnDestinasi.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
            btnDestinasi.classList.remove('text-slate-400');
            btnKategori.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
            btnKategori.classList.add('text-slate-400');
        } else {
            tabKategori.classList.remove('hidden');
            tabKategori.classList.add('active');
            tabDestinasi.classList.add('hidden');
            tabDestinasi.classList.remove('active');

            btnKategori.classList.add('bg-white', 'text-slate-900', 'shadow-sm');
            btnKategori.classList.remove('text-slate-400');
            btnDestinasi.classList.remove('bg-white', 'text-slate-900', 'shadow-sm');
            btnDestinasi.classList.add('text-slate-400');
        }
    }

    // Keep tab active on reload
    window.onload = function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('active_tab');
        if (activeTab === 'kategori') {
            switchTab('kategori');
        }
    }
</script>
@endsection

@extends('admin.layout')

@section('title', 'Daftar Kabupaten/Kota')
@section('page-title', 'Manajemen Kabupaten/Kota')

@section('content')
<div class="space-y-8">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Kabupaten & Kota</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">
                Kategori & metadata wilayah destinasi wisata
            </p>
        </div>
        <a href="{{ route('admin.cities.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-slate-100 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green hover:text-white transition">
            <i class="fas fa-map-marker-alt"></i> Kelola Kota
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-5 bg-green-50 border border-green-100 rounded-2xl text-green-700 text-sm font-bold">
            <i class="fas fa-check-circle text-green-500 text-lg"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('admin.regencies.index') }}" class="bg-white rounded-[2.5rem] p-6 border border-slate-100 shadow-sm flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[220px] space-y-1">
            <label class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Filter Provinsi</label>
            <select name="province_id" class="w-full px-5 py-3 bg-slate-50 border-none rounded-2xl text-sm font-bold text-slate-700 focus:ring-2 focus:ring-toba-green">
                <option value="">Semua Provinsi</option>
                @foreach($provinces as $prov)
                    <option value="{{ $prov->id }}" {{ request('province_id') == $prov->id ? 'selected' : '' }}>
                        {{ $prov->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-8 py-3 bg-toba-green text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-green-600 transition shadow-lg shadow-toba-green/20">
            <i class="fas fa-filter mr-2"></i>Filter
        </button>
        @if(request('province_id'))
            <a href="{{ route('admin.regencies.index') }}" class="px-8 py-3 bg-slate-100 text-slate-500 rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition">
                Reset
            </a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80">
                        <th class="px-8 py-5 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Kabupaten/Kota</th>
                        <th class="px-8 py-5 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Provinsi</th>
                        <th class="px-8 py-5 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Kategori</th>
                        <th class="px-8 py-5 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($regencies as $regency)
                    <tr class="hover:bg-slate-50/50 transition-colors group">
                        <td class="px-8 py-5">
                            <p class="font-black text-slate-900 text-sm">{{ $regency->name }}</p>
                            {{-- Province inline on mobile (column hidden) --}}
                            <span class="md:hidden block mt-1 text-[9px] font-black text-slate-400 uppercase tracking-wider">{{ $regency->province->name ?? '-' }}</span>
                        </td>
                        <td class="px-8 py-5 hidden md:table-cell">
                            <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                {{ $regency->province->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            @if($regency->category)
                                <span class="inline-block px-3 py-1 bg-toba-green/10 text-toba-green rounded-lg text-[10px] font-black uppercase tracking-wider">
                                    {{ $regency->category }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold text-slate-300 uppercase tracking-widest">— Belum dikategori</span>
                            @endif
                        </td>
                        <td class="px-8 py-5 text-right">
                            <a href="{{ route('admin.regencies.edit', $regency) }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition md:opacity-0 group-hover:opacity-100">
                                <i class="fas fa-pen text-[9px]"></i> Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-10 text-center">
                            <div class="flex flex-col items-center gap-4 text-slate-300">
                                <i class="fas fa-map text-4xl"></i>
                                <p class="font-black text-sm uppercase tracking-widest">Tidak ada data kabupaten</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($regencies->hasPages())
        <div class="px-8 py-6 border-t border-slate-100 flex items-center justify-between">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                Menampilkan {{ $regencies->firstItem() }}–{{ $regencies->lastItem() }} dari {{ $regencies->total() }} kabupaten
            </p>
            <div class="flex items-center gap-2">
                @if($regencies->onFirstPage())
                    <span class="px-4 py-2 bg-slate-100 text-slate-300 rounded-xl text-xs font-black cursor-not-allowed">← Prev</span>
                @else
                    <a href="{{ $regencies->previousPageUrl() }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-black hover:bg-toba-green transition">← Prev</a>
                @endif

                @if($regencies->hasMorePages())
                    <a href="{{ $regencies->nextPageUrl() }}" class="px-4 py-2 bg-slate-900 text-white rounded-xl text-xs font-black hover:bg-toba-green transition">Next →</a>
                @else
                    <span class="px-4 py-2 bg-slate-100 text-slate-300 rounded-xl text-xs font-black cursor-not-allowed">Next →</span>
                @endif
            </div>
        </div>
        @endif
    </div>

</div>
@endsection

@extends('admin.layout')

@section('title', 'Package Detail: ' . $package->name)
@section('page-title', 'Package Detail')

@section('content')
<div class="w-full max-w-full">
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.packages.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.packages.edit', $package) }}" class="bg-green-600 text-white px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-green-700 transition shadow-lg shadow-green-200 flex items-center gap-2">
                <i class="fas fa-edit"></i> Edit Paket
            </a>
            <a href="{{ route('tour.package.detail', $package->slug) }}" target="_blank" class="bg-white text-slate-900 px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2">
                <i class="fas fa-external-link-alt"></i> Lihat di Web
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Basic Info Card -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-50 overflow-hidden">
                <div class="relative h-80 bg-slate-100">
                    @if($package->images && count($package->images) > 0)
                        <img src="{{ $package->images[0] }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex flex-col items-center justify-center text-slate-300">
                            <i class="fas fa-box-open text-6xl mb-4"></i>
                            <span class="text-xs font-black uppercase tracking-widest">No Image</span>
                        </div>
                    @endif
                    <div class="absolute top-6 left-6 flex flex-col gap-2">
                        <span class="px-4 py-2 rounded-xl bg-slate-900/80 backdrop-blur-md text-white text-[9px] font-black uppercase tracking-widest border border-white/10">
                            ID: #{{ $package->id }}
                        </span>
                        @if($package->isFeatured)
                            <span class="px-4 py-2 rounded-xl bg-amber-400 text-white text-[9px] font-black uppercase tracking-widest shadow-lg shadow-amber-200 flex items-center gap-2">
                                <i class="fas fa-star"></i> Featured
                            </span>
                        @endif
                    </div>
                    <div class="absolute bottom-6 left-6 right-6">
                        <h1 class="text-3xl font-black text-white drop-shadow-lg tracking-tight">{{ $package->name }}</h1>
                    </div>
                </div>

                <div class="p-10 space-y-8">
                    <div>
                        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Deskripsi Lengkap</h3>
                        <div class="prose prose-sm prose-slate max-w-none text-slate-600 font-medium leading-relaxed">
                            {!! $package->description ?? '<p class="italic text-slate-400">Tidak ada deskripsi tersedia.</p>' !!}
                        </div>
                    </div>

                    @if($package->itinerary && count($package->itinerary) > 0)
                        <div class="pt-8 border-t border-slate-50">
                            <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Itinerary (Rencana Perjalanan)</h3>
                            <div class="space-y-6">
                                @foreach($package->itinerary as $index => $day)
                                    <div class="flex gap-6">
                                        <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-toba-green text-white flex flex-col items-center justify-center shadow-lg shadow-toba-green/20">
                                            <span class="text-[8px] font-black uppercase opacity-60 leading-none mb-1">Day</span>
                                            <span class="text-lg font-black leading-none">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="pt-1 flex-1">
                                            <h4 class="text-lg font-black text-slate-900 mb-2 tracking-tight">{{ $day['title'] ?? 'Hari ' . ($index+1) }}</h4>
                                            <p class="text-sm text-slate-600 font-medium leading-relaxed">{{ $day['description'] ?? '-' }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Photos Grid -->
            @if($package->images && count($package->images) > 1)
                <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Galeri Foto</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        @foreach($package->images as $index => $img)
                            @if($index > 0)
                                <div class="aspect-square rounded-3xl overflow-hidden border border-slate-100 shadow-sm">
                                    <img src="{{ $img }}" class="w-full h-full object-cover hover:scale-110 transition duration-700">
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-8">
            <!-- Status & Price Card -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50 space-y-8">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Status Layanan</p>
                    <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest {{ $package->status === 'active' ? 'bg-green-500 text-white shadow-lg shadow-green-200' : 'bg-slate-200 text-slate-500' }}">
                        {{ $package->status }}
                    </span>
                </div>

                <div class="pt-8 border-t border-slate-50">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Harga Dewasa</p>
                    <p class="text-4xl font-black text-slate-900 tracking-tighter">
                        <span class="text-lg text-toba-green mr-1">RM</span>{{ \App\Helpers\CurrencyHelper::formatIn($package->price, 'MYR', false) }}
                    </p>
                </div>

                @if($package->childPrice)
                    <div class="pt-8 border-t border-slate-50">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-3">Harga Anak</p>
                        <p class="text-3xl font-black text-slate-900 tracking-tighter">
                            <span class="text-lg text-toba-green mr-1">RM</span>{{ \App\Helpers\CurrencyHelper::formatIn($package->childPrice, 'MYR', false) }}
                        </p>
                    </div>
                @endif

                @if(auth()->user()->isSuperAdmin() && $package->cost_price)
                    <div class="pt-8 border-t border-slate-50 bg-amber-50 -mx-10 p-10 rounded-b-[2.5rem]">
                        <p class="text-[9px] font-black text-amber-600 uppercase tracking-widest mb-3">Harga Modal (Internal)</p>
                        <p class="text-2xl font-black text-slate-900 tracking-tighter">
                            <span class="text-lg text-amber-500 mr-1">Rp</span>{{ \App\Helpers\CurrencyHelper::formatIn($package->cost_price, 'IDR', false) }}
                        </p>
                        {{-- Margin tidak dihitung di sini: harga jual dicatat dalam MYR, modal dalam IDR.
                             Mengurangkan keduanya menghasilkan angka tanpa arti, jadi kedua nominal
                             ditampilkan terpisah apa adanya. --}}
                        <div class="mt-4 p-4 bg-white/50 rounded-2xl space-y-3">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Jual</p>
                                <p class="text-lg font-black text-green-600">{{ \App\Helpers\CurrencyHelper::formatIn($package->price, 'MYR') }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Harga Modal</p>
                                <p class="text-lg font-black text-slate-900">{{ \App\Helpers\CurrencyHelper::formatIn($package->cost_price, 'IDR') }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Details Card -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50 space-y-6">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">Durasi</span>
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ $package->duration ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">Lokasi</span>
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ $package->locationTag ?? '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-400">Kota</span>
                    <span class="text-xs font-black text-slate-900 uppercase tracking-widest">{{ $package->city?->name ?? '-' }}</span>
                </div>
            </div>

            <!-- Includes/Excludes -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50 space-y-8">
                <div>
                    <h3 class="text-xs font-black text-green-600 uppercase tracking-widest mb-4">✅ Inklusi</h3>
                    <ul class="space-y-3">
                        @forelse($package->includes ?? [] as $inc)
                            <li class="flex items-start gap-3 text-sm font-medium text-slate-600">
                                <i class="fas fa-check-circle text-green-500 mt-1"></i>
                                {{ $inc }}
                            </li>
                        @empty
                            <li class="text-xs italic text-slate-400">Tidak ada inklusi dicatat.</li>
                        @endforelse
                    </ul>
                </div>
                <div class="pt-8 border-t border-slate-50">
                    <h3 class="text-xs font-black text-rose-500 uppercase tracking-widest mb-4">❌ Eksklusi</h3>
                    <ul class="space-y-3">
                        @forelse($package->excludes ?? [] as $exc)
                            <li class="flex items-start gap-3 text-sm font-medium text-slate-600">
                                <i class="fas fa-times-circle text-rose-400 mt-1"></i>
                                {{ $exc }}
                            </li>
                        @empty
                            <li class="text-xs italic text-slate-400">Tidak ada eksklusi dicatat.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

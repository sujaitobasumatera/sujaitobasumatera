@extends('admin.layout')

@section('title', 'Error Logs')
@section('page-title', 'Log Error Sistem')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Log Error Sistem</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Error teknis aplikasi (booking gagal, exception, dll) dari laravel.log</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.error-logs.index') }}" class="px-5 py-3 bg-white border border-slate-100 text-slate-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition shadow-sm">
                <i class="fas fa-rotate-right mr-1"></i> Muat Ulang
            </a>
            @if($exists)
                <form method="POST" action="{{ route('admin.error-logs.clear') }}" onsubmit="return confirm('Bersihkan semua isi log? Tindakan ini tidak bisa dibatalkan.');">
                    @csrf
                    <button type="submit" class="px-5 py-3 bg-rose-50 text-rose-600 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-100 transition shadow-sm">
                        <i class="fas fa-trash-can mr-1"></i> Bersihkan Log
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[250px] group">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Cari Teks Error</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Contoh: Booking, SQLSTATE, exception..."
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                </div>
            </div>

            <div class="w-full sm:w-48">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Level</label>
                <select name="level" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                    <option value="">Semua Level</option>
                    @foreach($levels as $lvl)
                        <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>{{ $lvl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'level']))
                    <a href="{{ route('admin.error-logs.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 transition">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </a>
                @endif
                <button type="submit" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-lg shadow-slate-100">
                    Filter
                </button>
            </div>
        </form>

        @if($exists)
            <p class="mt-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                Ukuran file: <span class="text-slate-900">{{ number_format($fileSize / 1024, 1) }} KB</span>
                &middot; Menampilkan {{ count($entries) }} entri terbaru (maks 300)
            </p>
        @endif
    </div>

    <!-- Log Entries -->
    <div class="space-y-3">
        @php
            $badgeColors = [
                'ERROR' => 'bg-rose-50 text-rose-600',
                'CRITICAL' => 'bg-rose-100 text-rose-700',
                'ALERT' => 'bg-rose-100 text-rose-700',
                'EMERGENCY' => 'bg-rose-100 text-rose-700',
                'WARNING' => 'bg-amber-50 text-amber-600',
                'INFO' => 'bg-blue-50 text-blue-600',
                'DEBUG' => 'bg-slate-100 text-slate-500',
            ];
        @endphp

        @forelse($entries as $entry)
            @php $color = $badgeColors[strtoupper($entry['level'])] ?? 'bg-slate-100 text-slate-500'; @endphp
            <div class="bg-white rounded-3xl border border-slate-50 shadow-sm overflow-hidden">
                <details class="group">
                    <summary class="flex items-start gap-4 p-5 cursor-pointer list-none hover:bg-slate-50/40 transition">
                        <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest shrink-0 {{ $color }}">{{ $entry['level'] }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-slate-700 leading-relaxed break-words line-clamp-2">{{ $entry['message'] }}</p>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1">{{ $entry['env'] }} &middot; {{ $entry['time'] }}</p>
                        </div>
                        <i class="fas fa-chevron-down text-slate-300 text-xs mt-1 transition-transform group-open:rotate-180 shrink-0"></i>
                    </summary>
                    <div class="px-5 pb-5">
                        <pre class="bg-slate-900 text-slate-100 text-[11px] leading-relaxed p-4 rounded-2xl overflow-x-auto whitespace-pre-wrap break-words">{{ $entry['raw'] }}</pre>
                    </div>
                </details>
            </div>
        @empty
            <div class="bg-white rounded-[2.5rem] border border-slate-50 shadow-sm px-8 py-14 text-center">
                <div class="flex flex-col items-center justify-center text-slate-300">
                    <i class="fas fa-shield-heart text-6xl mb-4"></i>
                    <p class="text-[10px] font-black uppercase tracking-[0.4em]">
                        @if(!$exists)
                            File log belum ada
                        @elseif(request()->anyFilled(['search', 'level']))
                            Tidak ada error yang cocok
                        @else
                            Tidak ada error tercatat
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection

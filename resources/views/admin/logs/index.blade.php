@extends('admin.layout')

@section('title', 'Activity Logs')
@section('page-title', 'Audit Trail & System Logs')

@section('content')
<div class="space-y-8">
    <!-- Header Actions -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">System Audit Trail</h1>
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Pantau semua aktivitas perubahan data oleh admin</p>
        </div>
        <div class="w-full sm:w-auto">
            <div class="px-6 py-3 bg-white border border-slate-100 rounded-2xl flex items-center gap-4 shadow-sm">
                <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest">Real-time Monitoring Active</span>
            </div>
        </div>
    </div>

    <!-- Advanced Filter Bar -->
    <div class="bg-white rounded-[2rem] p-6 border border-slate-100 shadow-sm">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <!-- Search -->
            <div class="flex-1 min-w-[250px] group">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Cari Deskripsi</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari aktivitas atau model..." 
                        class="w-full pl-10 pr-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
                </div>
            </div>

            <!-- Admin Filter -->
            <div class="w-full sm:w-48">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Admin</label>
                <select name="user_id" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                    <option value="">Semua Admin</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Action Filter -->
            <div class="w-full sm:w-40">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Aksi</label>
                <select name="action" class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                    <option value="">Semua Aksi</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ strtoupper($action) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date Filter -->
            <div class="w-full sm:w-40">
                <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" 
                    class="w-full px-4 py-3 bg-slate-50 border-none rounded-2xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition">
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'user_id', 'action', 'date']))
                    <a href="{{ route('admin.logs.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-400 rounded-2xl hover:bg-slate-200 transition">
                        <i class="fas fa-rotate-left text-xs"></i>
                    </a>
                @endif
                <button type="submit" class="bg-slate-900 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-toba-green transition shadow-lg shadow-slate-100">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-[2.5rem] border border-slate-50 overflow-hidden shadow-sm relative">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-slate-50/50">
                        <th class="pl-5 md:pl-10 pr-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Admin / Operator</th>
                        <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Aksi & Modul</th>
                        <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest hidden sm:table-cell">Detail Aktivitas</th>
                        <th class="px-6 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">IP Address</th>
                        <th class="pr-5 md:pr-10 pl-6 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($logs as $log)
                        <tr class="group hover:bg-slate-50/30 transition duration-300">
                            @php
                                $colors = [
                                    'created' => 'bg-green-50 text-green-600',
                                    'updated' => 'bg-blue-50 text-blue-600',
                                    'deleted' => 'bg-rose-50 text-rose-600',
                                    'bulk_deleted' => 'bg-rose-100 text-rose-700',
                                    'status_updated' => 'bg-amber-50 text-amber-600',
                                ];
                                $color = $colors[$log->action] ?? 'bg-slate-100 text-slate-500';
                            @endphp
                            <td class="pl-5 md:pl-10 pr-6 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-2xl bg-white border border-slate-100 flex items-center justify-center shadow-sm group-hover:bg-slate-900 group-hover:text-white transition shrink-0">
                                        <span class="text-xs font-black">{{ substr($log->user->name ?? 'S', 0, 1) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-black text-slate-900 tracking-tight truncate">{{ $log->user->name ?? 'System Bot' }}</h4>
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest truncate">{{ $log->user->role ?? 'automated' }}</p>
                                        {{-- Condensed action + detail for mobile (columns hidden) --}}
                                        <div class="md:hidden flex flex-wrap items-center gap-2 mt-1.5">
                                            <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-widest {{ $color }}">{{ str_replace('_', ' ', $log->action) }}</span>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $log->model ?? '-' }}</span>
                                        </div>
                                        <p class="sm:hidden text-[10px] font-bold text-slate-500 leading-snug mt-1 line-clamp-2">{{ $log->description }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden md:table-cell">
                                <div class="space-y-1.5">
                                    <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest {{ $color }}">
                                        {{ str_replace('_', ' ', $log->action) }}
                                    </span>
                                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ $log->model ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden sm:table-cell">
                                <div class="max-w-xs">
                                    <p class="text-xs font-bold text-slate-700 leading-relaxed">{{ $log->description }}</p>
                                    @if($log->model_id)
                                        <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest mt-1">ID: #{{ $log->model_id }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-5 hidden lg:table-cell">
                                <code class="px-2 py-1 bg-slate-100 rounded text-[10px] font-bold text-slate-500">{{ $log->ip_address }}</code>
                            </td>
                            <td class="pr-5 md:pr-10 pl-6 py-5 text-right">
                                <p class="text-xs font-black text-slate-900">{{ $log->created_at->format('H:i:s') }}</p>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">{{ $log->created_at->format('d M Y') }}</p>
                                <p class="text-[8px] font-bold text-toba-green mt-0.5">{{ $log->created_at->diffForHumans() }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-14 text-center">
                                <div class="flex flex-col items-center justify-center text-slate-300">
                                    <i class="fas fa-fingerprint text-6xl mb-4"></i>
                                    <p class="text-[10px] font-black uppercase tracking-[0.4em]">Tidak ada catatan aktivitas</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-10 py-6 border-t border-slate-50 bg-slate-50/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-900">{{ $logs->firstItem() }}</span> - <span class="text-slate-900">{{ $logs->lastItem() }}</span> dari <span class="text-slate-900">{{ $logs->total() }}</span> Log
                </p>
                {{ $logs->appends(request()->all())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

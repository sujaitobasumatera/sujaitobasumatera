@extends('admin.layout')

@section('title', 'Konfigurasi Aplikasi')
@section('page-title', 'Konfigurasi Aplikasi')

@section('content')
@php
    $grouped = collect($fields)->groupBy(fn ($f) => $f['group'] ?? 'Umum');
@endphp

<div class="space-y-8">

    <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-100">
        <h2 class="text-lg font-black text-slate-900">Pengaturan yang bisa diubah tanpa akses server</h2>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            Nilai di sini disimpan di database dan menimpa isi <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">.env</code> saat aplikasi berjalan.
            Berkas <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-700">.env</code> sendiri tidak pernah ditulis.
        </p>
    </div>

    @if(session('warning'))
    <div class="rounded-[2rem] border border-amber-200 bg-amber-50 p-6 md:p-8">
        <p class="text-sm font-bold text-amber-900"><i class="fas fa-triangle-exclamation mr-2"></i>{{ session('warning') }}</p>
    </div>
    @endif

    <form action="{{ route('admin.settings.app-config.update') }}" method="POST" class="space-y-8 pb-14">
        @csrf

        @foreach($grouped as $groupName => $groupFields)
        <div class="bg-white p-6 md:p-8 rounded-[2rem] shadow-sm border border-slate-100 space-y-6">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ $groupName }}</h3>

            @foreach($groupFields as $key => $field)
            <div>
                <label for="cfg_{{ $key }}" class="block text-sm font-bold text-slate-800 mb-2">{{ $field['label'] }}</label>

                @if($field['type'] === 'boolean')
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" id="cfg_{{ $key }}" name="{{ $key }}" value="1"
                               @checked(old($key, $values[$key] ?? false))
                               class="h-5 w-5 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                        <span class="text-sm text-slate-600">Aktifkan</span>
                    </label>
                @elseif($field['type'] === 'select')
                    <select id="cfg_{{ $key }}" name="{{ $key }}"
                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900">
                        @foreach($field['options'] as $option)
                            <option value="{{ $option }}" @selected(old($key, $values[$key] ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="{{ $field['type'] === 'number' ? 'number' : ($field['type'] === 'email' ? 'email' : ($field['type'] === 'url' ? 'url' : 'text')) }}"
                           id="cfg_{{ $key }}" name="{{ $key }}"
                           value="{{ old($key, $values[$key] ?? '') }}"
                           class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl font-bold text-slate-900">
                @endif

                <p class="mt-2 text-xs leading-relaxed text-slate-400">{{ $field['help'] }}</p>

                @if(!empty($field['warn']))
                    <p class="mt-2 flex items-start gap-2 text-xs leading-relaxed text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">
                        <i class="fas fa-triangle-exclamation mt-0.5"></i>
                        <span>{{ $field['warn'] }}</span>
                    </p>
                @endif

                @error($key)
                    <p class="mt-2 text-xs font-bold text-rose-600">{{ $message }}</p>
                @enderror
            </div>
            @endforeach
        </div>
        @endforeach

        <div class="bg-slate-50 border border-slate-200 p-6 md:p-8 rounded-[2rem]">
            <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400">Tidak bisa diubah dari sini</h3>
            <p class="mt-3 text-sm leading-relaxed text-slate-500">
                Kredensial berikut sengaja tidak ditampilkan maupun bisa ditulis lewat halaman ini. Kalau bocor,
                yang dirugikan bukan hanya situs — melainkan data pribadi setiap pelanggan yang pernah memesan.
                Ubah langsung di berkas <code class="px-1.5 py-0.5 rounded bg-white text-slate-700">.env</code> pada server.
            </p>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach($denied as $key)
                    <span class="px-3 py-1.5 rounded-full bg-white border border-slate-200 text-[10px] font-black tracking-wider text-slate-400">{{ $key }}</span>
                @endforeach
            </div>
        </div>

        <button type="submit" class="w-full md:w-auto px-10 py-4 bg-slate-900 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-slate-800 transition">
            <i class="fas fa-save mr-2"></i> Simpan Konfigurasi
        </button>
    </form>
</div>
@endsection

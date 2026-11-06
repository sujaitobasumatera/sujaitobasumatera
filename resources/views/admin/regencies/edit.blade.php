@extends('admin.layout')

@section('title', 'Kategorikan Kabupaten')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.regencies.index') }}" class="inline-flex items-center text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-toba-green transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="bg-white rounded-[3rem] border border-slate-50 shadow-sm overflow-hidden">
        <div class="bg-slate-900 p-10 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-10">
                <i class="fas fa-tags text-6xl text-white"></i>
            </div>
            <h2 class="text-2xl font-black text-white tracking-tight relative z-10">{{ $regency->name }}</h2>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1 relative z-10">{{ $regency->province->name }}</p>
        </div>

        <form action="{{ route('admin.regencies.update', $regency) }}" method="POST" class="p-12">
            @csrf
            @method('PATCH')
            
            <div class="space-y-6">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Kategori Wilayah</label>
                    <input type="text" name="category" value="{{ old('category', $regency->category) }}" placeholder="Contoh: Wisata Alam, Wisata Budaya, Pusat Bisnis"
                        class="w-full px-8 py-5 bg-slate-50 border-none rounded-[2rem] focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-black text-xl text-slate-900">
                    <p class="text-[9px] font-bold text-slate-300 uppercase tracking-widest mt-4 leading-relaxed">
                        Kategori ini akan digunakan untuk mengelompokkan destinasi dalam satu wilayah yang serupa.
                    </p>
                </div>

                <div class="pt-6 border-t border-slate-50 flex items-center gap-6">
                    <button type="submit" class="flex-1 bg-slate-900 text-white py-5 rounded-[2rem] font-black uppercase tracking-widest hover:bg-black transition shadow-2xl shadow-slate-200">
                        Perbarui Kategori
                    </button>
                    <a href="{{ route('admin.regencies.index') }}" class="px-12 py-5 bg-slate-100 text-slate-500 rounded-[2rem] font-black uppercase tracking-widest hover:bg-slate-200 transition">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

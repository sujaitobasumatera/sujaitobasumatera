@extends('admin.layout')

@section('title', 'Edit Galeri')
@section('page-title', 'Detail Foto Galeri')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.gallery.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">Edit Media</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Perbarui detail aset visual</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Preview Column -->
        <div class="lg:col-span-5 space-y-6">
            <div class="bg-white p-4 rounded-[2.5rem] shadow-sm border border-slate-50">
                <div class="aspect-[4/5] rounded-[2rem] overflow-hidden bg-slate-50 border border-slate-100">
                    <img src="{{ $gallery->imageUrl }}" alt="{{ $gallery->caption }}" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="bg-slate-900 rounded-3xl p-6 text-white space-y-4 shadow-xl">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center">
                        <i class="fas fa-info-circle text-[10px]"></i>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest">Metadata</p>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-[9px] font-bold text-white/40 uppercase tracking-widest">Diupload Pada</span>
                        <span class="text-[10px] font-black">{{ $gallery->createdAt->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-[9px] font-bold text-white/40 uppercase tracking-widest">Kategori Asli</span>
                        <span class="text-[10px] font-black uppercase tracking-widest">{{ $gallery->category }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Column -->
        <div class="lg:col-span-7">
            <div class="bg-white p-10 rounded-[2.5rem] shadow-sm border border-slate-50">
                <form action="{{ route('admin.gallery.update', $gallery) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Caption / Judul Foto</label>
                        <input type="text" name="caption" value="{{ old('caption', $gallery->caption) }}" 
                            class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition @error('caption') ring-2 ring-rose-500 @enderror">
                        @error('caption') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                    </div>

                    <div class="space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Kategori Galeri</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="category" value="tour" {{ $gallery->category == 'tour' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-4 rounded-2xl border-2 border-slate-50 bg-slate-50 group-hover:bg-slate-100 peer-checked:border-green-700 peer-checked:bg-green-100 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm text-green-800">
                                            <i class="fas fa-camera-retro text-xs"></i>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-green-950">Tour</span>
                                    </div>
                                </div>
                            </label>
                            <label class="relative group cursor-pointer">
                                <input type="radio" name="category" value="outbound" {{ $gallery->category == 'outbound' ? 'checked' : '' }} class="peer sr-only">
                                <div class="p-4 rounded-2xl border-2 border-slate-50 bg-slate-50 group-hover:bg-slate-100 peer-checked:border-orange-500 peer-checked:bg-orange-50 transition">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm text-orange-600">
                                            <i class="fas fa-mountain-sun text-xs"></i>
                                        </div>
                                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-400 peer-checked:text-orange-900">Outbound</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full bg-slate-900 text-white py-5 rounded-2xl text-[12px] font-black uppercase tracking-[0.3em] hover:bg-slate-800 transition shadow-2xl shadow-slate-100">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>

                <div class="mt-6 pt-6 border-t border-slate-50">
                    <form action="{{ route('admin.gallery.destroy', $gallery) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto ini secara permanen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-4 rounded-2xl text-[10px] font-black text-rose-300 uppercase tracking-widest hover:bg-rose-50 hover:text-rose-500 transition">
                            Hapus Foto Secara Permanen
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

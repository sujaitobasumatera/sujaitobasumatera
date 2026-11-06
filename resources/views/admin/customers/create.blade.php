@extends('admin.layout')

@section('title', 'Tambah Pelanggan Baru')
@section('page-title', 'Pendaftaran Pelanggan Manual')

@section('content')
<div class="w-full max-w-full space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.customers.index') }}" class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-100 text-slate-400 hover:bg-slate-900 hover:text-white transition">
                <i class="fas fa-arrow-left text-xs"></i>
            </a>
            <div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">New Customer</h1>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Tambah database pelanggan secara manual</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-50 overflow-hidden">
        <div class="p-10 lg:p-16">
            <form action="{{ route('admin.customers.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Section 1: Identity -->
                <div class="space-y-8">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-700 flex items-center justify-center">
                            <i class="fas fa-user-tag text-xs"></i>
                        </div>
                        <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-900">Identitas Pelanggan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nama Lengkap</label>
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: John Doe"
                                class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition @error('name') ring-2 ring-rose-500 @enderror">
                            @error('name') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Alamat Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="john@example.com"
                                class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition @error('email') ring-2 ring-rose-500 @enderror">
                            @error('email') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" placeholder="628123456789"
                                class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition @error('phone') ring-2 ring-rose-500 @enderror">
                            @error('phone') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Section 2: Additional Info -->
                <div class="space-y-8 pt-6 border-t border-slate-50">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center">
                            <i class="fas fa-location-dot text-xs"></i>
                        </div>
                        <h3 class="text-[11px] font-black uppercase tracking-widest text-slate-900">Informasi Tambahan</h3>
                    </div>

                    <div class="space-y-8">
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Alamat Domisili</label>
                            <textarea name="address" rows="3" placeholder="Alamat lengkap pelanggan..."
                                class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition @error('address') ring-2 ring-rose-500 @enderror">{{ old('address') }}</textarea>
                            @error('address') <p class="text-[10px] font-bold text-rose-500 mt-1 ml-1 uppercase tracking-widest">{{ $message }}</p> @enderror
                        </div>
                        <div class="space-y-3">
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Catatan Internal (Admin Only)</label>
                            <textarea name="notes" rows="3" placeholder="Informasi khusus mengenai preferensi pelanggan ini..."
                                class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-toba-green/20 font-bold text-sm text-slate-900 transition">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="pt-6">
                    <button type="submit" class="w-full bg-slate-900 text-white py-6 rounded-3xl text-[12px] font-black uppercase tracking-[0.4em] hover:bg-slate-800 transition shadow-2xl shadow-slate-100 transform active:scale-[0.98]">
                        Daftarkan Pelanggan Baru
                    </button>
                    <p class="text-center text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-6">Data ini akan tersedia secara global untuk pemesanan selanjutnya</p>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

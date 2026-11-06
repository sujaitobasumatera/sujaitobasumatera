@extends('admin.layout')

@section('title', 'Edit Pelanggan')
@section('page-title', 'Modifikasi Profil Pelanggan')

@section('content')
<div class="w-full max-w-full space-y-8 pb-10">
    <a href="{{ route('admin.customers.show', $customer) }}" class="flex items-center gap-2 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-slate-900 transition">
        <i class="fas fa-arrow-left"></i> Batal & Kembali
    </a>

    <div class="bg-white rounded-[3rem] p-10 lg:p-16 border border-slate-100 shadow-sm relative overflow-hidden">
        <div class="flex items-center space-x-6 mb-6">
            <div class="w-16 h-16 rounded-3xl bg-slate-900 text-white flex items-center justify-center text-xl shadow-xl shadow-slate-200">
                <i class="fas fa-user-pen"></i>
            </div>
            <div>
                <h3 class="text-2xl font-black text-slate-900 tracking-tight">{{ $customer->name }}</h3>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1">{{ $customer->email }}</p>
            </div>
        </div>

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST" class="space-y-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $customer->name) }}" required class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-slate-900 font-bold text-slate-900 transition">
                </div>
                <div class="space-y-3">
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Nomor Telepon / WA</label>
                    <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-slate-900 font-bold text-slate-900 transition">
                </div>
                <div class="md:col-span-2 space-y-3">
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Alamat Domisili</label>
                    <textarea name="address" rows="2" class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-slate-900 font-bold text-slate-900 transition">{{ old('address', $customer->address) }}</textarea>
                </div>
                <div class="md:col-span-2 space-y-3">
                    <label class="text-[10px] font-black text-slate-900 uppercase tracking-widest ml-1">Catatan Khusus Admin</label>
                    <textarea name="notes" rows="4" placeholder="Misal: Tamu VIP, sering request tour guide tertentu, dll..." class="w-full px-6 py-4 bg-slate-50 border-none rounded-2xl focus:ring-2 focus:ring-slate-900 font-bold text-slate-900 transition">{{ old('notes', $customer->notes) }}</textarea>
                </div>
            </div>

            <div class="pt-6">
                <button type="submit" class="w-full py-5 bg-slate-900 text-white rounded-3xl text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-slate-200 transition hover:-translate-y-1 hover:bg-slate-800">
                    Update Profil Pelanggan
                </button>
            </div>
        </form>
    </div>

    <!-- Danger Zone -->
    <div class="p-10 rounded-[2.5rem] bg-rose-50 border border-rose-100 flex items-center justify-between">
        <div>
            <h4 class="text-sm font-black text-rose-900">Hapus Pelanggan</h4>
            <p class="text-[10px] font-bold text-rose-400 mt-1 uppercase tracking-widest">Aksi ini tidak dapat dibatalkan</p>
        </div>
        <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelanggan ini dari database?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-8 py-3 bg-white text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-widest shadow-sm hover:bg-rose-600 hover:text-white transition">Hapus Permanen</button>
        </form>
    </div>
</div>
@endsection

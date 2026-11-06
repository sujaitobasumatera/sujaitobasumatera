@extends('admin.layout')

@section('title', 'User Profile: ' . $user->name)
@section('page-title', 'User Detail')

@section('content')
<div class="w-full max-w-full">
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar User
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- User Avatar Card -->
        <div class="md:col-span-1">
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50 text-center">
                {{-- Kelas ditulis utuh; lihat catatan di users/index.blade.php --}}
                <div class="w-32 h-32 mx-auto bg-gradient-to-br {{ $user->role === 'superadmin' ? 'from-toba-green to-primary' : ($user->role === 'admin' ? 'from-toba-orange to-toba-orange-dark' : 'from-slate-500 to-slate-700') }} rounded-[2.5rem] flex items-center justify-center text-white font-black text-5xl shadow-xl mb-6">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight mb-2">{{ $user->name }}</h2>
                <span class="inline-flex items-center px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest 
                    {{ $user->role === 'superadmin' ? 'bg-green-200 text-green-900' : ($user->role === 'admin' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700') }}">
                    <i class="fas fa-{{ $user->role === 'superadmin' ? 'crown' : ($user->role === 'admin' ? 'user-shield' : 'user') }} mr-2"></i>
                    {{ $user->role === 'superadmin' ? 'Superadmin' : ($user->role === 'admin' ? 'Tour Admin' : 'User') }}
                </span>
                
                <div class="mt-6 pt-6 border-t border-slate-50 space-y-4">
                    <a href="{{ route('admin.users.edit', $user) }}" class="w-full flex items-center justify-center gap-3 py-4 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition">
                        <i class="fas fa-user-pen"></i> Edit Profil
                    </a>
                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus user ini selamanya?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-full flex items-center justify-center gap-3 py-4 bg-rose-50 text-rose-500 rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-600 hover:text-white transition">
                                <i class="fas fa-trash-alt"></i> Hapus User
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- User Information -->
        <div class="md:col-span-2 space-y-8">
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-8">Informasi Kontak & Akun</h3>
                
                <div class="space-y-8">
                    <div class="flex items-start gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Alamat Email</p>
                            <p class="text-lg font-black text-slate-900 tracking-tight">{{ $user->email }}</p>
                            @if($user->email_verified_at)
                                <span class="inline-flex items-center mt-1 text-[10px] font-bold text-green-600">
                                    <i class="fas fa-circle-check mr-1.5"></i> Terverifikasi
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Nomor Telepon / WA</p>
                            <p class="text-lg font-black text-slate-900 tracking-tight">{{ $user->phone ?? 'Belum ditambahkan' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Bergabung Sejak</p>
                            <p class="text-lg font-black text-slate-900 tracking-tight">{{ $user->created_at ? $user->created_at->format('d F Y, H:i') : '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-50 flex items-center justify-center text-slate-400">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Terakhir Diperbarui</p>
                            <p class="text-lg font-black text-slate-900 tracking-tight">{{ $user->updated_at ? $user->updated_at->diffForHumans() : '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Summary (Mock/Example) -->
            <div class="bg-white rounded-[2.5rem] p-10 shadow-sm border border-slate-50">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Ringkasan Aktivitas</h3>
                <div class="grid grid-cols-2 gap-6">
                    <div class="p-6 bg-slate-50 rounded-3xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Total Artikel</p>
                        <p class="text-3xl font-black text-slate-900 tracking-tighter">0</p>
                    </div>
                    <div class="p-6 bg-slate-50 rounded-3xl">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Log Akses</p>
                        <p class="text-3xl font-black text-slate-900 tracking-tighter">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

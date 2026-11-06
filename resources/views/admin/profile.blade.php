@extends('admin.layout')

@section('title', 'Admin Profile')
@section('page-title', 'Pengaturan Akun')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-50 overflow-hidden">
        <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Profil Saya</h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Kelola identitas dan keamanan akun Anda</p>
            </div>
            <div class="w-16 h-16 rounded-3xl bg-slate-900 text-white flex items-center justify-center text-xl font-black">
                {{ substr($user->name, 0, 1) }}
            </div>
        </div>

        <form action="{{ route('admin.profile.update') }}" method="POST" class="p-10 space-y-6">
            @csrf @method('PATCH')

            <!-- Identity -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-toba-green"></span>
                    Informasi Identitas
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Lengkap</label>
                        <div class="relative">
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-user absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Alamat Email</label>
                        <div class="relative">
                            <input type="email" name="email" value="{{ old('email', $user->user_id ?? $user->email) }}" required
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-envelope absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password -->
            <div class="space-y-6 pt-6 border-t border-slate-50">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    Keamanan Password
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Password Saat Ini (Hanya Jika Ingin Mengganti Password)</label>
                        <div class="relative">
                            <input type="password" name="current_password"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-lock-open absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-rose-500 transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Password Baru</label>
                        <div class="relative">
                            <input type="password" name="new_password"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-lock absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <input type="password" name="new_password_confirmation"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-check-circle absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-6 border-t border-slate-50 flex items-center justify-end">
                <button type="submit" class="px-12 py-5 bg-slate-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest hover:bg-toba-green transition shadow-xl shadow-slate-200 group">
                    <i class="fas fa-save mr-2 group-hover:scale-125 transition"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

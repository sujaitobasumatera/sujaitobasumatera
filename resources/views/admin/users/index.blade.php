@extends('admin.layout')

@section('title', 'Users Management')
@section('page-title', 'Users')

@section('breadcrumbs')
    <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
    <span class="text-slate-400">Tim & Pengguna</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{ 
    selected: [],
    
    toggleAll(ids) {
        let allChecked = ids.every(id => this.selected.includes(id));
        if (allChecked) {
            this.selected = this.selected.filter(id => !ids.includes(id));
        } else {
            this.selected = [...new Set([...this.selected, ...ids])];
        }
    },
    
    isAllChecked(ids) {
        return ids.length > 0 && ids.every(id => this.selected.includes(id));
    },

    async bulkDelete() {
        if (!confirm(`Apakah Anda yakin ingin menghapus ${this.selected.length} pengguna yang dipilih?`)) return;
        
        try {
            const response = await fetch('{{ route('admin.users.bulk-destroy') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selected })
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Gagal menghapus beberapa pengguna.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus.');
        }
    }
}">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div>
            <h1 class="text-3xl font-black text-gray-900 tracking-tight">Manage Users</h1>
            <p class="text-gray-500 mt-1 text-sm font-bold uppercase tracking-widest text-[10px]">Administrasi Hak Akses & Akun Staff</p>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <a href="{{ route('admin.users.export', request()->all()) }}" class="flex-1 sm:flex-none bg-white border border-slate-200 text-slate-600 px-6 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition text-center">
                <i class="fas fa-file-excel mr-2 text-green-500"></i> Export Excel
            </a>
            <a href="{{ route('admin.users.create') }}" class="flex-1 sm:flex-none inline-flex items-center justify-center bg-slate-900 text-white px-6 py-3.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-xl shadow-slate-100">
                <i class="fas fa-plus mr-2 text-xs"></i> New User
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Search</label>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}" 
                        placeholder="Name, email, phone..." 
                        class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition"
                    >
                </div>

                <!-- Role Filter -->
                <div>
                    <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Role</label>
                    <select name="role" class="w-full px-4 py-2.5 bg-slate-50 border-none rounded-xl focus:ring-4 focus:ring-toba-green/10 font-bold text-xs text-slate-900 transition appearance-none cursor-pointer">
                        <option value="">Semua Role</option>
                        <option value="superadmin" {{ request('role') == 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                        <option value="admin_tour" {{ request('role') == 'admin_tour' ? 'selected' : '' }}>Admin Tour</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Standard User</option>
                    </select>
                </div>

                <!-- Filter Button -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-slate-900 text-white px-6 py-2.5 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-800 transition shadow-lg shadow-slate-100">
                        Filter Data
                    </button>
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}" class="w-11 h-11 flex items-center justify-center bg-slate-100 text-slate-400 rounded-xl hover:bg-slate-200 transition">
                            <i class="fas fa-rotate-left text-xs"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="pl-8 py-4 w-10">
                            <input type="checkbox" 
                                @click="toggleAll(@js($users->pluck('id')->toArray()))"
                                :checked="isAllChecked(@js($users->pluck('id')->toArray()))"
                                class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                        </th>
                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">User</th>
                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest hidden md:table-cell">Contact</th>
                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest">Role</th>
                        <th class="px-6 py-4 text-left text-[9px] font-black text-slate-400 uppercase tracking-widest hidden lg:table-cell">Joined</th>
                        <th class="px-6 py-4 text-right text-[9px] font-black text-slate-400 uppercase tracking-widest">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $user)
                        <tr class="group hover:bg-gray-50/50 transition" :class="selected.includes({{ $user->id }}) ? 'bg-green-100/50' : ''">
                            <td class="pl-8 py-4">
                                <input type="checkbox" 
                                    value="{{ $user->id }}" 
                                    x-model="selected"
                                    class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900/20 transition cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    {{-- Kelas ditulis utuh, bukan dirakit lewat interpolasi: Tailwind memindai
                                         teks sumber, jadi 'from-' + variabel tidak pernah terdeteksi. --}}
                                    <div class="w-10 h-10 bg-gradient-to-br {{ $user->role === 'superadmin' ? 'from-toba-green to-primary' : ($user->role === 'admin' ? 'from-toba-orange to-toba-orange-dark' : 'from-slate-500 to-slate-700') }} rounded-2xl flex items-center justify-center text-white font-black text-xs mr-3 shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-black text-gray-900 text-sm tracking-tight truncate">{{ $user->name }}</p>
                                        @if($user->id === auth()->id())
                                            <span class="inline-flex items-center mt-1 px-2 py-0.5 rounded-full text-[8px] font-black bg-blue-100 text-blue-700 uppercase tracking-widest">
                                                <i class="fas fa-user-circle mr-1"></i>You
                                            </span>
                                        @endif
                                        {{-- Email shown inline on mobile (Contact column hidden) --}}
                                        <p class="md:hidden text-gray-400 text-[11px] font-medium truncate mt-0.5">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <div class="text-xs space-y-1 font-medium">
                                    <p class="text-gray-900">{{ $user->email }}</p>
                                    @if($user->phone)
                                        <p class="text-gray-400 text-[10px]">{{ $user->phone }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-3 py-1 rounded-xl text-[8px] font-black uppercase tracking-widest
                                    {{ $user->role === 'superadmin' ? 'bg-green-100 text-green-900' : ($user->role === 'admin_tour' ? 'bg-orange-50 text-orange-700' : 'bg-blue-50 text-blue-700') }}">
                                    <i class="fas fa-{{ $user->role === 'superadmin' ? 'crown' : ($user->role === 'admin_tour' ? 'user-shield' : 'user') }} mr-1.5"></i>
                                    {{ str_replace('_', ' ', $user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-bold text-gray-400 uppercase tracking-widest text-[9px] hidden lg:table-cell">
                                {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.users.show', $user) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm" title="View">
                                        <i class="fas fa-eye text-[10px]"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="w-9 h-9 flex items-center justify-center rounded-xl bg-slate-100 text-slate-500 hover:bg-slate-900 hover:text-white transition shadow-sm" title="Edit">
                                        <i class="fas fa-pencil text-[10px]"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-50 text-rose-300 hover:bg-rose-500 hover:text-white transition shadow-sm" title="Delete">
                                                <i class="fas fa-trash text-[10px]"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fas fa-users text-5xl mb-4"></i>
                                    <p class="text-sm font-black uppercase tracking-widest">No users found</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($users->hasPages())
            <div class="px-10 py-6 border-t border-gray-100 bg-gray-50 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    Menampilkan <span class="text-slate-900">{{ $users->firstItem() }}</span> - <span class="text-slate-900">{{ $users->lastItem() }}</span> dari <span class="text-slate-900">{{ $users->total() }}</span> Pengguna
                </p>
                {{ $users->appends(request()->all())->links() }}
            </div>
        @endif
    </div>

    <!-- Floating Bulk Actions -->
    <div x-show="selected.length > 0" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] w-full max-w-md px-4"
         x-cloak>
        <div class="bg-slate-900 text-white rounded-[2.5rem] p-5 shadow-2xl flex items-center justify-between border border-white/10 backdrop-blur-xl bg-opacity-90">
            <div class="flex items-center gap-4 pl-4">
                <div class="w-10 h-10 rounded-2xl bg-green-700 flex items-center justify-center text-white text-sm font-black shadow-lg">
                    <span x-text="selected.length"></span>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">Terpilih</p>
                    <p class="text-xs font-bold text-white">Siap dikelola</p>
                </div>
            </div>
            <div class="flex items-center gap-2 pr-2">
                <button @click="selected = []" class="px-5 py-3 text-[10px] font-black uppercase tracking-widest text-slate-400 hover:text-white transition">
                    Batal
                </button>
                <button @click="bulkDelete()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition shadow-xl shadow-rose-900/30">
                    <i class="fas fa-trash-can mr-2 text-xs"></i> Hapus Massal
                </button>
            </div>
        </div>
    </div>
</div>
    </div>
</div>
@endsection

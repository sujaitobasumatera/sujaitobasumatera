@extends('admin.layout')

@section('title', 'Create New Booking')
@section('page-title', 'Add Reservation')

@section('content')
<div class="max-w-4xl" x-data="bookingCreateForm">
    <div class="mb-6">
        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center text-slate-600 hover:text-slate-900 font-bold transition">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Pesanan
        </a>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-slate-50 overflow-hidden">
        <div class="p-10 border-b border-slate-50 flex items-center justify-between bg-slate-50/30">
            <div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">Buat Pesanan Manual</h2>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mt-1">Input reservasi baru dari admin panel</p>
            </div>
            <div class="w-16 h-16 rounded-3xl bg-toba-green/10 text-toba-green flex items-center justify-center">
                <i class="fas fa-calendar-plus text-2xl"></i>
            </div>
        </div>

        <form action="{{ route('admin.bookings.store') }}" method="POST" class="p-10 space-y-6">
            @csrf

            <!-- Booking Type Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-8 bg-slate-50 rounded-[2rem] border border-slate-100">
                <div class="relative group">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Tipe Pesanan</label>
                    <select name="type" x-model="type" required
                        class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-sm appearance-none cursor-pointer">
                        <option value="package">📦 Paket Wisata (Tour)</option>
                    </select>
                    <i class="fas fa-chevron-down absolute right-6 bottom-5 text-slate-300 pointer-events-none"></i>
                </div>

                <div class="relative group" x-show="type === 'package'">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Pilih Paket Wisata</label>
                    <select name="packageId" x-model="packageId" :required="type === 'package'"
                        class="w-full px-6 py-4 bg-white border border-slate-200 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:border-toba-green transition font-bold text-sm appearance-none cursor-pointer">
                        <option value="">-- Pilih Paket --</option>
                        @foreach($packages as $pkg)
                            <option value="{{ $pkg->id }}">{{ $pkg->name }} ({{ \App\Helpers\CurrencyHelper::formatIn($pkg->price, 'MYR') }})</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down absolute right-6 bottom-5 text-slate-300 pointer-events-none"></i>
                </div>


            </div>

            <!-- Schedule -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-toba-green"></span>
                    Jadwal Perjalanan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Tanggal Mulai</label>
                        <div class="relative">
                            <input type="date" name="startDate" x-model="startDate" required
                                class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="far fa-calendar-alt absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Tanggal Selesai</label>
                        <div class="relative">
                            <input type="date" name="endDate" x-model="endDate" required
                                class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="far fa-calendar-check absolute right-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div class="space-y-6">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    Informasi Pelanggan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="md:col-span-2 group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nama Lengkap</label>
                        <div class="relative">
                            <input type="text" name="customerName" placeholder="Contoh: John Doe" required
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-user absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Alamat Email <span class="text-slate-300 normal-case font-bold">(opsional)</span></label>
                        <div class="relative">
                            <input type="email" name="customerEmail" placeholder="john@example.com"
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fas fa-envelope absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                    <div class="group">
                        <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Nomor WA / HP</label>
                        <div class="relative">
                            <input type="text" name="customerPhone" placeholder="0812..." required
                                class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                            <i class="fab fa-whatsapp absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Details -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="group">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Jumlah Peserta (Pax)</label>
                    <div class="relative">
                        <input type="number" name="pax" value="1" min="1" required
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                        <i class="fas fa-users absolute left-6 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-toba-green transition"></i>
                    </div>
                </div>
                <div class="md:col-span-2 group">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-1">Catatan Tambahan</label>
                    <input type="text" name="notes" placeholder="Opsional..."
                        class="w-full px-6 py-4 bg-slate-50 border border-slate-100 rounded-2xl focus:ring-4 focus:ring-toba-green/10 focus:bg-white transition font-bold text-sm">
                </div>
            </div>

            <!-- Form Actions -->
            <div class="pt-6 border-t border-slate-50 flex flex-col sm:flex-row items-center gap-4">
                <button type="submit" class="w-full sm:w-auto px-12 py-5 bg-slate-900 text-white rounded-[2rem] font-black text-xs uppercase tracking-widest hover:bg-toba-green transition shadow-xl shadow-slate-200 group">
                    <i class="fas fa-save mr-2 group-hover:scale-125 transition"></i> Simpan Reservasi
                </button>
                <a href="{{ route('admin.bookings.index') }}" class="w-full sm:w-auto px-10 py-5 bg-slate-100 text-slate-500 rounded-[2rem] font-black text-xs uppercase tracking-widest hover:bg-slate-200 transition text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('bookingCreateForm', () => ({
            type: 'package',
            packageId: '',
            startDate: '{{ now()->format('Y-m-d') }}',
            endDate: '{{ now()->format('Y-m-d') }}',
            
            init() {
                this.$watch('startDate', value => {
                    if (this.endDate < value) {
                        this.endDate = value;
                    }
                });
            }
        }));
    });
</script>
@endpush
@endsection

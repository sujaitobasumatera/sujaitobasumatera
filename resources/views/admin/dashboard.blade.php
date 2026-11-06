@extends('admin.layout')

@section('title', 'Dashboard')
@section('page-title', 'Pusat Kontrol & Analitik')

@section('content')
<div x-data="dashboardHandler()" x-init="initDashboard()" class="space-y-6">
    
    <!-- 1. TOP CARDS & ANALYTICS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Revenue Card -->
        <div class="bg-slate-900 p-8 rounded-[3.5rem] shadow-2xl shadow-slate-200 text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition duration-700">
                <i class="fas fa-wallet text-8xl -rotate-12"></i>
            </div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-6">
                    <p class="text-[10px] font-black text-white/50 uppercase tracking-[0.2em]">Total Revenue</p>
                    <div class="w-2 h-2 rounded-full bg-toba-green animate-pulse"></div>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-xl font-black text-white/70">Rp</span>
                    <h3 class="text-4xl font-black tracking-tight" x-text="Number(data.revenue.total).toLocaleString('id-ID')"></h3>
                </div>
                <div class="mt-8 flex items-center gap-4">
                    <div class="px-4 py-2 bg-white/10 rounded-2xl backdrop-blur-md">
                        <p class="text-[8px] font-black uppercase text-white/50 mb-0.5">Bulan Ini</p>
                        <p class="text-xs font-black" x-text="'Rp ' + Number(data.revenue.monthly).toLocaleString('id-ID')"></p>
                    </div>
                    <div class="px-4 py-2 bg-green-500/20 rounded-2xl border border-green-500/20 backdrop-blur-md">
                        <p class="text-[8px] font-black uppercase text-green-400 mb-0.5">Growth</p>
                        <p class="text-xs font-black text-green-400" x-text="'+' + (data.revenue.growth || 0) + '%'"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 7-Day Chart -->
        <div class="lg:col-span-2 bg-white p-8 rounded-[3.5rem] border border-slate-100 shadow-sm flex flex-col relative overflow-hidden">
            <div class="flex items-center justify-between mb-6 relative z-10">
                <div>
                    <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">Analitik 7 Hari Terakhir</h3>
                    <p class="text-[10px] font-bold text-slate-400 mt-1">Tren pendapatan dan jumlah reservasi</p>
                </div>
                <div class="flex bg-slate-50 p-1 rounded-xl">
                    <button @click="activeChart = 'revenue'" :class="activeChart === 'revenue' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-400'" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition">Revenue</button>
                    <button @click="activeChart = 'bookings'" :class="activeChart === 'bookings' ? 'bg-white shadow-sm text-slate-900' : 'text-slate-400'" class="px-4 py-1.5 rounded-lg text-[9px] font-black uppercase tracking-widest transition">Bookings</button>
                </div>
            </div>
            <div class="flex-1 min-h-[180px] relative">
                <canvas id="mainChart"></canvas>
            </div>
        </div>
    </div>

    <!-- 2. QUICK STATS ROW -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
        <div class="bg-white p-4 md:p-6 rounded-3xl md:rounded-[2.5rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row sm:items-center gap-3 md:gap-5">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center text-lg">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active</p>
                <h4 class="text-xl font-black text-slate-900" x-text="data.bookings.active"></h4>
            </div>
        </div>
        <div class="bg-white p-4 md:p-6 rounded-3xl md:rounded-[2.5rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row sm:items-center gap-3 md:gap-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center text-lg">
                <i class="fas fa-clock"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Pending</p>
                <h4 class="text-xl font-black text-slate-900" x-text="data.bookings.pending"></h4>
            </div>
        </div>
        <div class="bg-white p-4 md:p-6 rounded-3xl md:rounded-[2.5rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row sm:items-center gap-3 md:gap-5">
            <div class="w-12 h-12 rounded-2xl bg-green-50 text-green-500 flex items-center justify-center text-lg">
                <i class="fas fa-check-double"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Confirmed</p>
                <h4 class="text-xl font-black text-slate-900" x-text="data.bookings.confirmed"></h4>
            </div>
        </div>
        <div class="bg-white p-4 md:p-6 rounded-3xl md:rounded-[2.5rem] border border-slate-50 shadow-sm flex flex-col sm:flex-row sm:items-center gap-3 md:gap-5">
            <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-lg">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">New Users</p>
                <h4 class="text-xl font-black text-slate-900" x-text="data.users.new_this_month"></h4>
            </div>
        </div>
    </div>

    <!-- (Bagian Media & System Health disembunyikan agar dashboard lebih ramah bisnis) -->

    <!-- 3. MAIN TRANSACTIONS -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Bookings -->
        <div class="lg:col-span-2 bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[3.5rem] border border-slate-100 shadow-sm flex flex-col">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-lg font-black text-slate-900 tracking-tight flex items-center gap-3">
                    <div class="w-2 h-8 bg-toba-green rounded-full"></div>
                    Pesanan Terbaru
                </h3>
                <a href="{{ route('admin.bookings.index') }}" class="px-5 py-2.5 bg-slate-50 hover:bg-slate-900 hover:text-white rounded-xl text-[9px] font-black uppercase tracking-widest transition">Lihat Semua</a>
            </div>
            
            <div class="flex-1 space-y-4 overflow-y-auto custom-scrollbar max-h-[500px] pr-2">
                <template x-for="booking in data.recent_bookings" :key="booking.id">
                    <div class="flex items-center justify-between group p-4 rounded-3xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 rounded-2xl bg-white border border-slate-100 text-slate-400 flex items-center justify-center font-black group-hover:bg-slate-900 group-hover:text-white transition">
                                <i class="fas" :class="booking.type === 'package' ? 'fa-box-archive' : 'fa-car-side'"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-slate-900 tracking-tight" x-text="booking.customerName"></h5>
                                <div class="flex items-center gap-2">
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest" x-text="booking.bookingCode"></p>
                                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                    <p class="text-[10px] font-black text-slate-500 uppercase" x-text="booking.type"></p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                {{-- Nominal pesanan dirender dalam mata uang yang dibekukan saat dipesan, bukan dikonversi ulang. --}}
                                <p class="text-sm font-black text-slate-900" x-text="new Intl.NumberFormat(booking.currency === 'IDR' ? 'id-ID' : 'en-MY', { style: 'currency', currency: booking.currency || 'IDR' }).format(booking.totalPrice)"></p>
                                <span class="px-2.5 py-1 rounded-lg text-[8px] font-black uppercase tracking-widest mt-1 inline-block" 
                                      :class="{
                                          'bg-amber-50 text-amber-600': booking.status === 'pending',
                                          'bg-green-50 text-green-600': booking.status === 'confirmed',
                                          'bg-blue-50 text-blue-600': booking.status === 'completed',
                                          'bg-rose-50 text-rose-600': booking.status === 'cancelled'
                                      }"
                                      x-text="booking.status"></span>
                            </div>
                            <a :href="`{{ url('/admin/bookings') }}/${booking.id}`" class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-900 hover:text-white transition shadow-sm">
                                <i class="fas fa-chevron-right text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Sidebar Activity -->
        <div class="space-y-8">
            <!-- Top Packages -->
            <div class="bg-slate-900 p-6 md:p-10 rounded-[2rem] md:rounded-[3.5rem] shadow-2xl text-white">
                <h3 class="text-sm font-black uppercase tracking-widest mb-8 text-white/50">Top Booked Packages</h3>
                <div class="space-y-6">
                    <template x-for="(count, name) in data.top_packages" :key="name">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <p class="text-[11px] font-bold truncate pr-4" x-text="name"></p>
                                <span class="text-[10px] font-black text-toba-green" x-text="count + ' Booked'"></span>
                            </div>
                            <div class="w-full h-1.5 bg-white/5 rounded-full overflow-hidden">
                                <div class="h-full bg-toba-green rounded-full transition duration-1000" :style="`width: ${Math.min(100, (count / 10) * 100)}%`"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Top Viewed Content -->
            <div class="bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[3.5rem] border border-slate-100 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8">Populer (Banyak Dilihat)</h3>
                <div class="space-y-6">
                    <template x-for="pkg in data.top_views.packages" :key="pkg.id">
                        <div class="flex items-center justify-between group">
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-900 truncate" x-text="pkg.name"></p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Tour Package</p>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl">
                                <i class="fas fa-eye text-green-700 text-[10px]"></i>
                                <span class="text-[10px] font-black text-slate-900" x-text="pkg.views_count"></span>
                            </div>
                        </div>
                    </template>
                    <div class="h-px bg-slate-50 my-4"></div>
                    <template x-for="blog in data.top_views.blogs" :key="blog.id">
                        <div class="flex items-center justify-between group">
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-900 truncate" x-text="blog.title"></p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Blog Post</p>
                            </div>
                            <div class="flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl">
                                <i class="fas fa-eye text-green-500 text-[10px]"></i>
                                <span class="text-[10px] font-black text-slate-900" x-text="blog.views_count"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Customers -->
            <div class="bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[3.5rem] border border-slate-100 shadow-sm">
                <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest mb-8">Pelanggan Baru</h3>
                <div class="space-y-6">
                    <template x-for="customer in data.recent_customers" :key="customer.id">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-2xl bg-slate-50 flex items-center justify-center font-black text-xs text-slate-400 uppercase" x-text="customer.name.charAt(0)"></div>
                            <div class="min-w-0">
                                <p class="text-xs font-black text-slate-900 truncate" x-text="customer.name"></p>
                                <p class="text-[10px] font-bold text-slate-400 truncate" x-text="customer.phone"></p>
                            </div>
                            <a :href="`https://wa.me/${customer.phone.replace(/[^0-9]/g, '')}`" target="_blank" class="w-8 h-8 rounded-xl bg-green-500 text-white flex items-center justify-center ml-auto shadow-lg shadow-green-500/20">
                                <i class="fab fa-whatsapp text-xs"></i>
                            </a>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function dashboardHandler() {
        return {
            data: @json($stats),
            activeChart: 'revenue',
            chart: null,

            initDashboard() {
                this.renderChart();
                this.$watch('activeChart', () => this.updateChart());
                
                // Real-time polling
                setInterval(async () => {
                    try {
                        const response = await fetch('{{ route('admin.dashboard.stats') }}');
                        this.data = await response.json();
                        this.updateChart();
                    } catch (e) { console.error('Refresh error', e); }
                }, 15000);
            },

            renderChart() {
                const ctx = document.getElementById('mainChart').getContext('2d');
                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: this.getChartData(),
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, border: { display: false }, grid: { color: '#f1f5f9' }, ticks: { font: { size: 9, weight: 'bold' }, color: '#94a3b8' } },
                            x: { border: { display: false }, grid: { display: false }, ticks: { font: { size: 9, weight: 'bold' }, color: '#94a3b8' } }
                        },
                        elements: {
                            line: { tension: 0.4 },
                            point: { radius: 4, hoverRadius: 6, backgroundColor: '#10b951', borderColor: '#fff', borderWidth: 2 }
                        }
                    }
                });
            },

            getChartData() {
                const isRevenue = this.activeChart === 'revenue';
                const source = isRevenue ? this.data.revenue_7d : this.data.bookings_7d;
                
                return {
                    labels: source.map(d => d.label),
                    datasets: [{
                        data: source.map(d => d.total),
                        borderColor: isRevenue ? '#10b951' : '#6366f1',
                        borderWidth: 3,
                        fill: true,
                        backgroundColor: (context) => {
                            const ctx = context.chart.ctx;
                            const gradient = ctx.createLinearGradient(0, 0, 0, 300);
                            gradient.addColorStop(0, isRevenue ? 'rgba(16, 185, 81, 0.15)' : 'rgba(99, 102, 241, 0.15)');
                            gradient.addColorStop(1, 'rgba(255, 255, 255, 0)');
                            return gradient;
                        }
                    }]
                };
            },

            updateChart() {
                if (this.chart) {
                    this.chart.data = this.getChartData();
                    this.chart.update('none');
                }
            }
        };
    }
</script>
@endpush

<style>
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f8fafc; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>
@endsection

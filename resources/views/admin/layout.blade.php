<!DOCTYPE html>
<html lang="id" x-data="{ 
    sidebarOpen: window.innerWidth >= 1024,
    isMobile: window.innerWidth < 1024,
    loading: false
}" @resize.window="let prevMobile = isMobile; isMobile = window.innerWidth < 1024; if (isMobile && !prevMobile) sidebarOpen = false; if (!isMobile && prevMobile) sidebarOpen = true"
   @submit.window="loading = true">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>@yield('title', 'Dashboard') - Sujai Toba Sumatera Admin</title>
    @php
        $iconUrl = $siteSettings['general']['icon_url'] ?? ($siteSettings['cms_landing']['brand_icon_url'] ?? asset('favicon.ico'));
        if ($iconUrl && !Str::startsWith($iconUrl, ['http', '//', 'data:', 'blob:'])) {
            $cleanIcon = ltrim($iconUrl, '/');
            if (Str::startsWith($cleanIcon, 'assets/')) {
                $iconUrl = asset($cleanIcon);
            } else {
                $iconUrl = asset('storage/' . ltrim(str_replace('storage/', '', $cleanIcon), '/'));
            }
        }
    @endphp
    <link rel="icon" type="image/x-icon" href="{{ $iconUrl }}">

    {{-- PWA: hanya Superadmin yang melihat & bisa meng-install panel admin --}}
    @if(auth()->check() && auth()->user()->isSuperAdmin())
        <link rel="manifest" href="{{ route('admin.pwa.manifest') }}" crossorigin="use-credentials">
        <meta name="theme-color" content="#1e40af">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="Sujai Admin">
        <link rel="apple-touch-icon" href="/icon-192.png">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">
    <!-- FontAwesome deferred (non-render-blocking) -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    
    <style>
        [x-cloak] { display: none !important; }
        * { box-sizing: border-box; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .glass { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); }
        .custom-scrollbar::-webkit-scrollbar { width: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }

        /* --- Core Layout --- */
        #admin-wrapper {
            display: block;
            min-height: 100vh;
            min-height: 100dvh;
            position: relative;
        }

        /* Sidebar is fixed overlay - does not affect document flow */
        #sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            height: 100dvh;
            padding-top: env(safe-area-inset-top);
            padding-bottom: env(safe-area-inset-bottom);
            width: 272px;
            background: white;
            border-right: 1px solid #F1F5F9;
            z-index: 100;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        #sidebar > div {
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
        }

        #sidebar nav {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
        }

        /* Content area is always 100% wide; shifts via padding-left on desktop when sidebar open */
        #content-area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-height: 100dvh;
            width: 100%;
            transition: padding-left 0.3s ease;
        }

        @media (min-width: 1024px) {
            #content-area.sidebar-visible {
                padding-left: 272px;
            }
        }
    </style>
</head>
<body class="antialiased text-slate-900">

    <div id="admin-wrapper">


        {{-- ====== SIDEBAR ====== --}}
        <aside id="sidebar"
            :style="sidebarOpen ? 'transform: translateX(0)' : 'transform: translateX(-100%)'"
            class="z-[100]"
        >
            <div class="flex flex-col h-full">

                {{-- Branding --}}
                <div class="px-6 pt-8 pb-6 flex flex-col items-center text-center flex-shrink-0 border-b border-slate-50 relative">
                    {{-- Mobile Close Button --}}
                    <button @click="sidebarOpen = false" class="lg:hidden absolute top-4 right-4 w-11 h-11 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:bg-slate-100 transition">
                        <i class="fas fa-times text-xs"></i>
                    </button>

                    @php
                        $logoUrl = $siteSettings['general']['logo_light_url'] ?? ($siteSettings['cms_landing']['brand_logo_url'] ?? null);
                        if ($logoUrl && !Str::startsWith($logoUrl, ['http', '//', 'data:', 'blob:'])) {
                            $cleanLogo = ltrim($logoUrl, '/');
                            if (Str::startsWith($cleanLogo, 'assets/')) {
                                $logoUrl = asset($cleanLogo);
                            } else {
                                $logoUrl = asset('storage/' . ltrim(str_replace('storage/', '', $cleanLogo), '/'));
                            }
                        }
                    @endphp

                    @if(!empty($logoUrl))
                        <div class="mb-4">
                            <img src="{{ $logoUrl }}" class="h-10 w-auto object-contain">
                        </div>
                    @else
                        <div class="w-12 h-12 bg-toba-green rounded-2xl flex items-center justify-center shadow-lg shadow-toba-green/30 mb-3">
                            <span class="text-white font-black text-xl">W</span>
                        </div>
                        <h1 class="text-base font-black text-slate-900 tracking-tight leading-tight mb-3">
                            {{ $siteSettings['general']['site_name'] ?? ($siteSettings['cms_landing']['brand_name'] ?? 'Sujai Toba Sumatera') }}
                        </h1>
                    @endif
                    <a href="{{ route('index') }}" target="_blank"
                       class="flex items-center gap-2 px-4 py-2 bg-slate-50 text-slate-400 hover:text-toba-green rounded-xl text-[10px] font-black uppercase tracking-widest transition">
                        <i class="fas fa-external-link text-[8px]"></i>
                        <span>Lihat Website</span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 min-h-0 px-3 py-4 overflow-y-auto custom-scrollbar">

                    {{-- UTAMA --}}
                    <div class="mb-5">
                        <p class="px-4 mb-1.5 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">Utama</p>
                        <a href="{{ route('admin.dashboard') }}"
                           class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                  {{ request()->routeIs('admin.dashboard') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                            <i class="fas fa-chart-line w-5 text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-toba-green' }}"></i>
                            Beranda
                        </a>
                    </div>

                    {{-- TRANSAKSI (MOVED TO TOP) --}}
                    <div class="mb-5">
                        <p class="px-4 mb-1.5 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">Transaksi</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.bookings.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.bookings.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-clipboard-list w-5 text-sm {{ request()->routeIs('admin.bookings.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Daftar Pesanan
                            </a>

                            <a href="{{ route('admin.customers.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.customers.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-user-group w-5 text-sm {{ request()->routeIs('admin.customers.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Daftar Pelanggan
                            </a>

                            @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.reports.financial') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.reports.financial') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-file-invoice-dollar w-5 text-sm {{ request()->routeIs('admin.reports.financial') ? 'text-white' : 'text-green-500' }}"></i>
                                Laporan Keuangan
                            </a>
                            <a href="{{ route('admin.finance.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.finance.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-cash-register w-5 text-sm {{ request()->routeIs('admin.finance.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Buku Kas / Transaksi
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- MANAJEMEN KONTEN (CMS) --}}
                    <div class="mb-5">
                        <p class="px-4 mb-1.5 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">Artikel & Berita</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.cms.tour') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.cms.tour') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-display w-5 text-sm {{ request()->routeIs('admin.cms.tour') ? 'text-white' : 'text-green-500' }}"></i>
                                Halaman Beranda Web
                            </a>
                            <a href="{{ route('admin.blogs.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.blogs.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-pen-nib w-5 text-sm {{ request()->routeIs('admin.blogs.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Blog / Artikel
                            </a>
                            <a href="{{ route('admin.media.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.media.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-photo-film w-5 text-sm {{ request()->routeIs('admin.media.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Media Pusat
                            </a>
                            <button type="button" @click="$dispatch('open-image-guide')"
                                    class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px] text-slate-500 hover:bg-slate-50 hover:text-slate-900 text-left">
                                <i class="fas fa-ruler-combined w-5 text-sm text-green-500"></i>
                                Panduan Gambar
                            </button>
                             @if(auth()->user()->isSuperAdmin())
                             <a href="{{ route('admin.cities.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                       {{ request()->routeIs('admin.cities.*') || request()->routeIs('admin.regencies.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                 <i class="fas fa-location-dot w-5 text-sm {{ request()->routeIs('admin.cities.*') || request()->routeIs('admin.regencies.*') ? 'text-white' : 'text-green-500' }}"></i>
                                 Kota Tujuan
                             </a>
                             @endif
                             <a href="{{ route('admin.gallery.index', ['category' => 'tour']) }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                       {{ request()->routeIs('admin.gallery.*') && request('category', 'tour') == 'tour' ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                 <i class="fas fa-images w-5 text-sm {{ request()->routeIs('admin.gallery.*') && request('category', 'tour') == 'tour' ? 'text-white' : 'text-green-500' }}"></i>
                                 Galeri Tour
                             </a>
                             <a href="{{ route('admin.clients.index') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                       {{ request()->routeIs('admin.clients.*') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                 <i class="fas fa-handshake w-5 text-sm {{ request()->routeIs('admin.clients.*') ? 'text-white' : 'text-green-500' }}"></i>
                                 Partner / Klien
                             </a>
                             @if(auth()->user()->isSuperAdmin())
                             <a href="{{ route('admin.cms.pages') }}"
                                class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                       {{ request()->routeIs('admin.cms.pages') ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                 <i class="fas fa-file-invoice w-5 text-sm {{ request()->routeIs('admin.cms.pages') ? 'text-white' : 'text-green-500' }}"></i>
                                 Halaman Statis
                             </a>
                             @endif
                        </div>
                    </div>

                    {{-- PRODUK & LAYANAN --}}
                    <div class="mb-5">
                        <p class="px-4 mb-1.5 text-[9px] font-black text-slate-300 uppercase tracking-[0.25em]">Produk & Layanan (Tour)</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.packages.index', ['type' => 'tour']) }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.packages.*') && request('type', 'tour') == 'tour' ? 'bg-toba-green text-white shadow-lg shadow-toba-green/20' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                 <i class="fas fa-map w-5 text-sm {{ request()->routeIs('admin.packages.*') && request('type', 'tour') == 'tour' ? 'text-white' : 'text-green-500' }}"></i>
                                 Paket Tour Wisata
                            </a>
                        </div>
                    </div>

                    {{-- SYSTEM MANAGEMENT - Restricted to Superadmin --}}
                    @if(auth()->user()->isSuperAdmin())
                    <div class="mb-5 pt-4 border-t border-slate-50">
                        <p class="px-4 mb-3 text-[9px] font-black text-slate-400 uppercase tracking-[0.25em]">Pengaturan Sistem</p>
                        <div class="space-y-0.5">
                            <a href="{{ route('admin.settings.general.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.settings.general.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-sliders w-5 text-sm {{ request()->routeIs('admin.settings.general.*') ? 'text-white' : 'text-slate-400' }}"></i>
                                Pengaturan Dasar
                            </a>
                            @if(auth()->user()?->role === 'superadmin')
                            <a href="{{ route('admin.settings.app-config.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.settings.app-config.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-gears w-5 text-sm {{ request()->routeIs('admin.settings.app-config.*') ? 'text-white' : 'text-slate-400' }}"></i>
                                Konfigurasi Aplikasi
                            </a>
                            @endif
                            <a href="{{ route('admin.users.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.users.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-user-shield w-5 text-sm {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-green-500' }}"></i>
                                Manajemen Pengguna
                            </a>
                            <a href="{{ route('admin.logs.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.logs.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-clock-rotate-left w-5 text-sm {{ request()->routeIs('admin.logs.*') ? 'text-white' : 'text-slate-400' }}"></i>
                                Log Aktivitas
                            </a>
                            <a href="{{ route('admin.error-logs.index') }}"
                               class="flex items-center gap-3 px-4 py-2.5 rounded-xl transition font-bold text-[13px]
                                      {{ request()->routeIs('admin.error-logs.*') ? 'bg-slate-900 text-white shadow-lg shadow-slate-200' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900' }}">
                                <i class="fas fa-triangle-exclamation w-5 text-sm {{ request()->routeIs('admin.error-logs.*') ? 'text-white' : 'text-rose-400' }}"></i>
                                Log Error Sistem
                            </a>
                        </div>
                    </div>
                    @endif

                </nav>

                {{-- Footer / Logout --}}
                <div class="flex-shrink-0 p-4 border-t border-slate-100">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition font-bold text-[13px]">
                            <i class="fas fa-right-from-bracket w-5 text-sm"></i>
                            Keluar
                        </button>
                    </form>
                </div>

            </div>
        </aside>

        {{-- ====== MAIN CONTENT ====== --}}
        <div id="content-area"
             :class="sidebarOpen && !isMobile ? 'sidebar-visible' : ''">

            {{-- Top Header --}}
            <header class="glass sticky top-0 z-50 pt-[env(safe-area-inset-top)] border-b border-slate-100 px-5 lg:px-8 py-4 flex items-center justify-between flex-shrink-0">
                <div class="flex items-center gap-4">
                    {{-- Toggle Button --}}
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="w-11 h-11 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white transition focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2">
                        <i class="fas fa-bars text-xs"></i>
                    </button>
                    <div>
                        <div class="hidden sm:flex items-center gap-1.5 text-[8px] font-black uppercase tracking-widest text-slate-300 mb-1">
                            <a href="{{ route('admin.dashboard') }}" class="hover:text-toba-green transition">Admin</a>
                            @hasSection('breadcrumbs')
                                <i class="fas fa-chevron-right text-[6px] opacity-40"></i>
                                @yield('breadcrumbs')
                            @endif
                        </div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-900 tracking-tight">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    {{-- Global Search / Command Bar --}}
                    <div class="hidden lg:flex items-center ml-8 relative group" x-data="{ commandOpen: false }">
                        <div class="relative">
                            <i class="fas fa-magnifying-glass absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 text-[10px] group-focus-within:text-toba-green transition-colors"></i>
                            <input type="text" 
                                   @focus="commandOpen = true"
                                   @click.away="commandOpen = false"
                                   placeholder="Cari Pesanan, Paket, atau Berita... (CTRL+K)" 
                                   class="w-80 lg:w-[400px] pl-12 pr-12 py-3 bg-slate-50 border-none rounded-[1.2rem] text-[11px] font-bold text-slate-600 placeholder:text-slate-300 focus:ring-2 focus:ring-toba-green/20 focus:bg-white transition shadow-sm group-hover:shadow-md">
                            <div class="absolute right-4 top-1/2 -translate-y-1/2 px-2 py-0.5 bg-white border border-slate-100 rounded-md text-[8px] font-black text-slate-300 tracking-tighter">
                                CTRL K
                            </div>
                        </div>

                        {{-- Search Results Dropdown (Static Preview for now) --}}
                        <div x-show="commandOpen" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute top-full left-0 mt-3 w-[min(500px,calc(100vw-2rem))] bg-white rounded-[2.5rem] shadow-2xl border border-slate-50 p-6 z-[100]" x-cloak>
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-3 px-2">Shortcut Cepat</h4>
                                    <div class="grid grid-cols-2 gap-2">
                                        <a href="{{ route('admin.packages.create') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl hover:bg-green-50 transition border border-transparent hover:border-green-100">
                                            <div class="w-8 h-8 rounded-xl bg-green-100 text-green-600 flex items-center justify-center text-xs"><i class="fas fa-plus"></i></div>
                                            <span class="text-[10px] font-black uppercase text-slate-700">Buat Paket Baru</span>
                                        </a>
                                        <a href="{{ route('admin.blogs.create') }}" class="flex items-center gap-3 p-3 bg-slate-50 rounded-2xl hover:bg-green-100 transition border border-transparent hover:border-green-200">
                                            <div class="w-8 h-8 rounded-xl bg-green-200 text-green-800 flex items-center justify-center text-xs"><i class="fas fa-feather"></i></div>
                                            <span class="text-[10px] font-black uppercase text-slate-700">Tulis Artikel</span>
                                        </a>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="text-[9px] font-black text-slate-300 uppercase tracking-widest mb-3 px-2">Pencarian Terakhir</h4>
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between p-3 rounded-2xl hover:bg-slate-50 cursor-pointer transition">
                                            <div class="flex items-center gap-3">
                                                <i class="fas fa-history text-slate-300 text-xs"></i>
                                                <span class="text-xs font-bold text-slate-600 italic">Belum ada riwayat...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Bell -->
                    <div class="relative group">
                        <a href="{{ route('admin.bookings.index', ['status' => 'pending']) }}" 
                           class="w-10 h-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-400 hover:bg-slate-900 hover:text-white transition">
                            <i class="fas fa-bell text-xs"></i>
                            @if($pendingBookingsCount > 0)
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[9px] font-black flex items-center justify-center rounded-full border-2 border-white shadow-lg animate-bounce">
                                    {{ $pendingBookingsCount }}
                                </span>
                            @endif
                        </a>
                        <!-- Tooltip -->
                        <div class="absolute top-full mt-2 left-1/2 -translate-x-1/2 px-3 py-1.5 bg-slate-900 text-white text-[9px] font-black uppercase tracking-widest rounded-lg opacity-0 group-hover:opacity-100 transition whitespace-nowrap pointer-events-none">
                            {{ $pendingBookingsCount }} Pesanan Baru
                        </div>
                    </div>

                    <!-- Tombol Panduan Ukuran Desain Gambar -->
                    <button type="button" @click="$dispatch('open-image-guide')"
                            class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-green-50 text-green-800 hover:bg-green-600 hover:text-white transition font-bold text-xs shadow-sm border border-green-200/70"
                            title="Panduan Ukuran &amp; Rasio Gambar">
                        <i class="fas fa-ruler-combined text-xs"></i>
                        <span class="hidden md:inline">Panduan Gambar</span>
                    </button>

                    <a href="{{ route('index') }}" target="_blank"
                       class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-100 text-[10px] font-black text-slate-400 hover:text-slate-900 hover:border-slate-300 uppercase tracking-widest transition">
                        View Site
                        <i class="fas fa-arrow-up-right-from-square text-[8px]"></i>
                    </a>

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" 
                                class="flex items-center gap-2 md:gap-3 p-1 rounded-2xl md:bg-slate-50 hover:bg-slate-100 transition group">
                            <div class="w-8 h-8 rounded-xl bg-slate-900 text-white flex items-center justify-center text-[10px] font-black group-hover:bg-toba-green transition">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <div class="hidden lg:block text-left pr-2">
                                <p class="text-[10px] font-black text-slate-900 leading-none mb-1">{{ auth()->user()->name }}</p>
                                <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ auth()->user()->role }}</p>
                            </div>
                            <i class="hidden sm:block fas fa-chevron-down text-[8px] text-slate-300 transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>

                        <div x-show="open" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
                             class="absolute right-0 mt-3 w-56 bg-white rounded-[2rem] shadow-2xl border border-slate-50 py-3 z-[100]"
                             x-cloak>
                            <div class="px-6 py-4 border-b border-slate-50 mb-2">
                                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.2em] mb-1">Signed in as</p>
                                <p class="text-xs font-black text-slate-900 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('admin.profile.edit') }}" class="flex items-center gap-3 px-6 py-3 text-[11px] font-bold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition">
                                <i class="fas fa-user-gear text-slate-300"></i>
                                Pengaturan Profil
                            </a>
                            <div class="h-px bg-slate-50 my-2 mx-6"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-6 py-3 text-[11px] font-bold text-rose-500 hover:bg-rose-50 transition">
                                    <i class="fas fa-power-off text-rose-300"></i>
                                    Keluar (Logout)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-5 lg:p-10 w-full max-w-full">

                {{-- Floating Toast Notifications --}}
                <div class="fixed top-24 left-4 right-4 sm:left-auto sm:right-8 z-[9999] flex flex-col gap-4 w-auto sm:w-80 pointer-events-none">
                    @if(session('success'))
                        <div x-data="{ show: true }"
                             x-init="setTimeout(() => show = false, 5000)"
                             x-show="show"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-x-20 scale-95"
                             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-x-20 scale-95"
                             class="bg-white/90 backdrop-blur-2xl border border-green-100 p-6 rounded-[2rem] flex items-center justify-between shadow-[0_20px_50px_rgba(0,0,0,0.1)] pointer-events-auto overflow-hidden relative group">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-toba-green"></div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-toba-green text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-toba-green/20">
                                    <i class="fas fa-check-double text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-toba-green mb-0.5">Success</p>
                                    <p class="font-bold text-xs text-slate-900 leading-tight">{{ session('success') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-slate-300 hover:text-slate-900 transition ml-4">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }"
                             x-init="setTimeout(() => show = false, 7000)"
                             x-show="show"
                             x-transition:enter="transition ease-out duration-500"
                             x-transition:enter-start="opacity-0 translate-x-20 scale-95"
                             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
                             x-transition:leave="transition ease-in duration-300"
                             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-x-20 scale-95"
                             class="bg-white/90 backdrop-blur-2xl border border-rose-100 p-6 rounded-[2rem] flex items-center justify-between shadow-[0_20px_50px_rgba(225,29,72,0.1)] pointer-events-auto overflow-hidden relative group">
                            <div class="absolute top-0 left-0 w-1.5 h-full bg-rose-500"></div>
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-rose-500 text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-rose-200">
                                    <i class="fas fa-triangle-exclamation text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-[9px] font-black uppercase tracking-[0.2em] text-rose-500 mb-0.5">Error</p>
                                    <p class="font-bold text-xs text-slate-900 leading-tight">{{ session('error') }}</p>
                                </div>
                            </div>
                            <button @click="show = false" class="text-slate-300 hover:text-slate-900 transition ml-4">
                                <i class="fas fa-times text-[10px]"></i>
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Page Content Yield --}}
                <div class="animate-in fade-in slide-in-from-bottom-3 duration-500">
                    @yield('content')
                </div>

            </main>

            {{-- Footer --}}
            <footer class="px-8 py-5 border-t border-slate-50 flex flex-col sm:flex-row items-center justify-between gap-2 flex-shrink-0">
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">
                    Sujai Toba Sumatera Engine &bull; Management v3.0
                </p>
                <p class="text-[9px] font-black text-slate-300 uppercase tracking-widest">
                    Crafted with <i class="fas fa-heart text-rose-400"></i> for Wonderful Indonesia
                </p>
            </footer>

        </div>

        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen && isMobile"
             @click="sidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-[99] lg:hidden"
             x-cloak>
        </div>

    </div>

    <!-- Global Loading Spinner -->
    <div x-show="loading" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-[9999] bg-slate-900/60 backdrop-blur-sm flex flex-col items-center justify-center"
         x-cloak>
        <div class="relative">
            <div class="w-20 h-20 border-4 border-toba-green/20 border-t-toba-green rounded-full animate-spin"></div>
            <div class="absolute inset-0 flex items-center justify-center">
                <span class="text-white font-black text-xs">W</span>
            </div>
        </div>
        <p class="mt-6 text-[10px] font-black text-white uppercase tracking-[0.5em] animate-pulse">Sedang Memproses...</p>
    </div>

    <x-media-modal />
    <x-image-zoom-modal />
    <x-admin-image-guide-modal />
    @stack('scripts')

    {{-- PWA Service Worker: didaftarkan hanya untuk Superadmin, scope dibatasi ke /admin/ --}}
    @if(auth()->check() && auth()->user()->isSuperAdmin())
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register('/admin-sw.js', { scope: '/admin/' })
                    .catch(function (err) { console.warn('Admin SW registration failed:', err); });
            });
        }
    </script>
    @endif
</body>
</html>

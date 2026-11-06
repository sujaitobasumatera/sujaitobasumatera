<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'my' ? 'ms' : str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 — {{ __('Halaman Tidak Ditemukan') }} | Sujai Toba Sumatera</title>
    <meta name="robots" content="noindex, nofollow">
    
    {{-- Plus Jakarta Sans sudah di-host sendiri di app.css, jadi Google Fonts
         tidak diperlukan. FontAwesome penuh (~70 KB) dulu dimuat di sini hanya
         untuk empat ikon; keempatnya kini SVG inline. Outfit sudah dibuang dari
         layout utama di Fase 0 tapi masih tertinggal di halaman ini. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .font-outfit {
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, sans-serif;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-effect {
            box-shadow: 0 0 80px -10px rgba(16, 185, 81, 0.25);
        }
    </style>
</head>
<body class="bg-[#0b0f19] min-h-screen flex items-center justify-center overflow-hidden relative">

    <!-- Premium Animated Background Particles -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-[60vw] h-[60vw] rounded-full bg-toba-green/10 blur-[150px] animate-pulse" style="animation-duration: 8s;"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] rounded-full bg-toba-green/5 blur-[120px] animate-pulse" style="animation-duration: 12s;"></div>
        <div class="absolute inset-0" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,255,255,0.015) 1px, transparent 0); background-size: 32px 32px;"></div>
    </div>

    <div class="relative z-10 text-center px-6 max-w-2xl mx-auto py-6">
        
        <!-- Logo -->
        <div class="mb-8 flex justify-center scale-95 md:scale-100">
            @php
                $logoUrl = imageUrl($siteSettings['general']['logo_dark_url'] ?? null, asset('images/logo_compressed.webp'));
            @endphp
            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" class="h-14 w-auto object-contain brightness-0 invert opacity-90 transition hover:opacity-100" alt="Sujai Toba Sumatera">
            @else
                <div class="flex items-center gap-3">
                    <span class="text-white font-outfit font-black text-2xl uppercase tracking-widest">Sujai Toba Sumatera</span>
                </div>
            @endif
        </div>

        <!-- 404 Glass Card -->
        <div class="glass-panel glow-effect rounded-[3.5rem] p-8 md:p-14 mb-8 text-center relative overflow-hidden max-w-lg mx-auto">
            <!-- 404 Large Text -->
            <div class="relative mb-6 select-none">
                <span class="text-[8rem] md:text-[10rem] font-outfit font-extrabold text-transparent bg-clip-text bg-gradient-to-b from-white/20 to-white/0 leading-none tracking-tighter">404</span>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-20 h-20 bg-toba-green/20 border border-toba-green/30 rounded-[1.8rem] flex items-center justify-center backdrop-blur-md shadow-inner">
                        <svg class="w-8 h-8 text-toba-green" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 01-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 1116 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Message -->
            <h1 class="text-2xl md:text-3xl font-outfit font-extrabold text-white tracking-tight leading-snug mb-3">
                {{ __('Halaman Tidak Ditemukan') }}
            </h1>
            <p class="text-slate-400 font-normal text-xs md:text-sm max-w-sm mx-auto mb-8 leading-relaxed">
                {{ __('Destinasi yang Anda cari belum ditemukan atau sudah dialihkan. Temukan petualangan menarik lainnya bersama Sujai Toba Sumatera.') }}
            </p>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5">
                <a href="/" 
                   class="w-full sm:w-auto px-8 py-4 bg-toba-green text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-primary-container hover:scale-[1.02] transition duration-300 shadow-lg shadow-toba-green/10 flex items-center justify-center gap-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 11l9-8 9 8M5 10v10h14V10"/></svg>
                    {{ __('Kembali ke Beranda') }}
                </a>
                <a href="/tour/packages" 
                   class="w-full sm:w-auto px-8 py-4 bg-white/5 border border-white/10 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-white/10 hover:scale-[1.02] transition duration-300 flex items-center justify-center gap-2.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5l-2 5-5 2 2-5 5-2z"/></svg>
                    {{ __('Jelajahi Paket') }}
                </a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="flex flex-wrap items-center justify-center gap-5 text-[10px] font-bold uppercase tracking-widest text-slate-500">
            <a href="/tour" class="hover:text-toba-green transition-colors">{{ __('Tour & Wisata') }}</a>
            <span class="text-slate-700/50">•</span>
            <a href="/tour/blog" class="hover:text-toba-green transition-colors">{{ __('Blog & Info') }}</a>
            <span class="text-slate-700/50">•</span>
            <a href="/about" class="hover:text-toba-green transition-colors">{{ __('Tentang Kami') }}</a>
            <span class="text-slate-700/50">•</span>
            <a href="/track-booking" class="hover:text-toba-green transition-colors">{{ __('Lacak Pesanan') }}</a>
        </div>

        {{-- Halaman ini berdiri sendiri tanpa navbar/footer, jadi tanpa baris ini
             SEMUA jalur kontak lenyap tepat ketika pengunjung sedang tersesat. --}}
        <div class="mt-8">
            <a href="{{ \App\Helpers\ContactHelper::whatsappLink(__('Halo Sujai Toba Sumatera, saya tidak menemukan halaman yang saya cari.')) }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 text-[11px] font-bold text-slate-400 hover:text-toba-green transition-colors">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2 22l5.25-1.38a9.9 9.9 0 004.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91C21.95 6.45 17.5 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 01-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 01-1.26-4.38c0-4.54 3.7-8.23 8.25-8.23a8.2 8.2 0 018.23 8.24c0 4.54-3.69 8.23-8.23 8.23z"/></svg>
                {{ __('Butuh bantuan? Chat kami') }} — {{ \App\Helpers\ContactHelper::whatsappDisplay() }}
            </a>
        </div>
    </div>

</body>
</html>

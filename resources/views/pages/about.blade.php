@extends('layouts.app')

@section('title', __('Tentang Kami') . ' - ' . ($siteSettings['general']['seo_meta_title'] ?? 'Sujai Laketoba'))
{{-- Cadangan wajib ada. meta_description di CMS kosong, dan tanpa cadangan
     halaman ini terbit dengan <meta name="description" content=""> -- Google
     mengarang sendiri cuplikannya dari potongan teks pertama yang ia temukan,
     dan pratinjau tautan WhatsApp tampil tanpa satu baris penjelasan pun.
     Satu-satunya halaman dari empat belas yang kehilangan ini. --}}
@section('description', $content['meta_description']
    ?? __('Sujai Laketoba adalah biro perjalanan Danau Toba yang menyusun perjalanan pribadi, rombongan, dan korporat di Samosir, Parapat, Berastagi, dan seluruh Sumatera Utara.'))

@section('content')
<div class="bg-surface min-h-screen pb-14 font-body-md text-on-background selection:bg-primary-container selection:text-on-primary-container">
    <!-- Cinematic Premium Hero Section -->
    <div class="relative h-[60dvh] flex items-center overflow-hidden bg-slate-900">
        <img src="{{ imageUrl($content['hero_image'] ?? '2026/04/lake-toba-premium.webp') }}" alt="About Hero" @if($__ss = imageSrcset(imageUrl($content['hero_image'] ?? '2026/04/lake-toba-premium.webp'))) srcset="{{ $__ss }}" sizes="100vw" @endif class="absolute inset-0 w-full h-full object-cover opacity-45 animate-subtle-zoom">
        <div class="absolute inset-0 bg-gradient-to-r from-primary via-primary/50 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-surface via-transparent to-transparent"></div>

        <div class="relative z-10 w-full max-w-7xl mx-auto px-5 md:px-8 pt-10">
            <div class="max-w-4xl">
                <div class="flex items-center space-x-2 mb-4 animate-fade-in-down">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur-md border border-white/10 text-white text-[10px] font-semibold uppercase tracking-[0.25em] rounded-full">
                        {{ __('OUR LEGACY') }}
                    </span>
                </div>
                <h1 class="text-4xl md:text-7xl font-bold text-white tracking-tight leading-[1.05] mb-6 animate-fade-in-up">
                    Dedikasi Untuk <br />
                    <span class="text-green-300">{{ __('Pariwisata Sumut') }}</span>
                </h1>
                <p class="text-slate-200 text-sm md:text-lg font-normal max-w-2xl leading-relaxed animate-fade-in-up delay-100">
                    {{ __('Kami membantu orang liburan dengan rapi, nyaman, dan mudah dipahami dari awal sampai selesai.') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Luxury Story Section -->
    <section class="py-8 md:py-16 relative overflow-hidden bg-surface">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 lg:gap-24 items-center">
                
                <!-- Image Side -->
                <div class="lg:col-span-6 relative">
                    <div class="relative z-10 aspect-[4/5] rounded-3xl overflow-hidden shadow-lg border border-slate-200">
                        <img src="{{ imageUrl($content['image_url'] ?? '2026/04/sumatra-panorama.webp') }}" alt="Sujai Laketoba Story" @if($__ss = imageSrcset(imageUrl($content['image_url'] ?? '2026/04/sumatra-panorama.webp'))) srcset="{{ $__ss }}" sizes="(max-width: 1023px) 100vw, 600px" @endif class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-primary/30 to-transparent"></div>
                    </div>
                    
                    <!-- Decorative Radial Gradients -->
                    <div class="absolute -bottom-16 -right-16 w-80 h-80 bg-secondary/5 rounded-full blur-3xl -z-10"></div>
                    <div class="absolute -top-16 -left-16 w-64 h-64 bg-primary/10 rounded-full blur-3xl -z-10"></div>
                    
                    <!-- Floating Testimonial -->
                    <div class="absolute -bottom-10 -left-6 md:-left-10 bg-white/95 backdrop-blur-md p-8 rounded-3xl shadow-lg border border-slate-200 max-w-sm z-20 transition-transform duration-500 hover:scale-[1.02]">
                        <div class="flex gap-1.5 mb-4 text-amber-500">
                            @for($i=0; $i<5; $i++)
                                <span class="material-symbols-outlined fill-1 text-base">star</span>
                            @endfor
                        </div>
                        <p class="text-slate-700 font-medium italic text-sm md:text-base mb-4 leading-relaxed">
                            "{{ $content['testimonial_quote'] ?? 'Layanan terbaik, armada baru, dan guide yang sangat informatif.' }}"
                        </p>
                        <p class="text-secondary font-semibold text-[10px] uppercase tracking-widest flex items-center gap-2">
                            <span class="w-4 h-0.5 bg-secondary inline-block"></span>
                            {{ $content['testimonial_name'] ?? 'Pelanggan Setia' }}
                        </p>
                    </div>
                </div>

                <!-- Text Side -->
                <div class="lg:col-span-6 space-y-8">
                    <div class="space-y-4">
                        <span class="text-secondary font-semibold text-xs uppercase tracking-[0.2em] block">
                            {{ __('MENGENAL KAMI') }}
                        </span>
                        <h2 class="text-3xl md:text-5xl font-headline-md font-semibold text-primary leading-tight tracking-tight">
                            {{ $content['title'] ?? 'Melayani Dengan Sepenuh Hati Sejak 2012' }}
                        </h2>
                        <div class="text-sm md:text-base text-slate-600 font-normal leading-relaxed space-y-6">
                            {!! nl2br(e($content['description'] ?? 'Berawal dari kecintaan terhadap keindahan alam Sumatera Utara, Sujai Laketoba hadir untuk memberikan pengalaman perjalanan yang tak terlupakan bagi setiap wisatawan. Kami percaya bahwa setiap perjalanan memiliki cerita unik yang layak untuk dikenang selamanya.')) !!}
                        </div>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-2 gap-4 md:gap-8 pt-8 border-t border-outline-variant/40">
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 hover:border-slate-300 transition duration-300 shadow-sm">
                            <p class="text-4xl font-headline-md font-semibold text-primary mb-1 tracking-tight">
                                {{ $content['stat_years'] ?? '12+' }}
                            </p>
                            <p class="text-[9px] font-semibold text-slate-500 uppercase tracking-widest">
                                {{ $content['stat_years_label'] ?? __('Tahun Pengalaman') }}
                            </p>
                        </div>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 hover:border-slate-300 transition duration-300 shadow-sm">
                            @php
                                // Nilai bawaannya dulu '5k+' di sini tapi '1.500+' di
                                // homepage dan halaman pSEO — situs yang sama mengklaim
                                // dua angka pelanggan yang berbeda. Kalau admin belum
                                // mengisi kolom ini, ikuti angka yang dipakai halaman
                                // lain daripada mengarang angka ketiga.
                                $touristsCount = $content['stat_tourists']
                                    ?? ($siteSettings['cms_tour']['stat_customers'] ?? '1.500+');
                            @endphp
                            <p class="text-4xl font-headline-md font-semibold text-primary mb-1 tracking-tight">
                                {{ $touristsCount }}
                            </p>
                            <p class="text-[9px] font-semibold text-slate-500 uppercase tracking-widest">
                                {{ $content['stat_tourists_label'] ?? __('Wisatawan Puas') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Vision & Mission Section -->
    <section class="py-8 md:py-16 bg-surface-container-low relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(#ffe088_0.5px,transparent_0.5px)] [background-size:16px_16px] opacity-15"></div>
        <div class="absolute top-0 right-0 w-1/3 h-full bg-surface-container skew-x-12 translate-x-20"></div>
        
        <div class="max-w-7xl mx-auto px-5 md:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-6 md:mb-10">
                <span class="text-secondary font-semibold text-xs uppercase tracking-[0.2em] mb-4 block">
                    {{ __('OUR MISSION & VISION') }}
                </span>
                <h2 class="text-3xl md:text-5xl font-headline-lg font-semibold text-primary tracking-tight leading-tight">
                    Visi & Misi <span class="text-secondary">{{ __('Masa Depan') }}</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-10 md:gap-16">
                <!-- Vision Card (Premium Dark Theme) -->
                <div class="group bg-white p-10 md:p-14 rounded-3xl shadow-sm border border-slate-200 flex flex-col justify-between transition duration-300 hover:-translate-y-1">
                    <div>
                        <div class="w-16 h-16 bg-green-50 rounded-2xl flex items-center justify-center text-green-700 mb-6 shadow-sm group-hover:scale-105 transition-transform duration-500">
                            <span class="material-symbols-outlined text-3xl font-black">visibility</span>
                        </div>
                        <h3 class="text-2xl font-headline-md text-slate-900 mb-6 tracking-tight">
                            {{ __('Visi Kami') }}
                        </h3>
                        <p class="text-slate-600 text-sm md:text-base font-normal leading-relaxed">
                            {{ $content['vision'] ?? 'Membawa Anda menikmati keaslian alam dan budaya Sumatera Utara melalui perjalanan yang tenang, aman, dan berkesan.' }}
                        </p>
                    </div>
                    <div class="pt-6 flex justify-end">
                        <span class="text-[9px] font-semibold tracking-widest text-secondary uppercase">{{ __('EXCELLENCE SERVICE') }}</span>
                    </div>
                </div>

                <!-- Mission Card (Elegant White Theme) -->
                <div class="group bg-white p-10 md:p-14 rounded-3xl shadow-sm border border-slate-200 flex flex-col justify-between transition duration-300 hover:-translate-y-1">
                    <div>
                        <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-white mb-6 shadow-sm group-hover:scale-105 transition-transform duration-500">
                            <span class="material-symbols-outlined text-3xl font-black">task_alt</span>
                        </div>
                        <h3 class="text-2xl font-headline-md text-slate-900 mb-6 tracking-tight">
                            {{ __('Misi Kami') }}
                        </h3>
                        <ul class="space-y-5">
                            @php
                                $missions = explode("\n", $content['mission'] ?? "Merancang itinerary yang sesuai dengan ritme liburan Anda.\nMenyediakan transportasi dan akomodasi lokal terbaik.\nMemastikan setiap pelanggan pulang dengan cerita yang indah.");
                            @endphp
                            @foreach($missions as $mission)
                                @if(trim($mission))
                                <li class="flex items-start gap-4">
                                    <div class="w-6 h-6 rounded-full bg-green-50 text-green-700 flex items-center justify-center shrink-0 mt-0.5">
                                        <span class="material-symbols-outlined text-sm font-black">done</span>
                                    </div>
                                    <span class="text-sm md:text-base text-slate-600 font-normal leading-relaxed">
                                        {{ trim($mission) }}
                                    </span>
                                </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="py-8 md:py-16 bg-surface">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 items-center">
                
                <div class="lg:col-span-5 space-y-6">
                    <span class="text-secondary font-semibold text-xs uppercase tracking-[0.2em] block">
                        {{ __('WHY CHOOSE US') }}
                    </span>
                    <h2 class="text-3xl md:text-5xl font-headline-md font-semibold text-primary tracking-tight leading-tight">
                        Keunggulan <br /> <span class="text-secondary">Sujai Laketoba</span>
                    </h2>
                    <p class="text-sm md:text-base text-slate-600 font-normal leading-relaxed">
                        Kami tidak sekadar menjual tiket perjalanan; kami merancang memori indah. Setiap detail kecil dari petualangan Anda dikuratori secara hati-hati oleh tim profesional kami.
                    </p>
                    <div class="pt-4">
                        <a href="/tour/packages" class="inline-flex items-center gap-3 py-4 px-8 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-semibold text-[10px] uppercase tracking-widest transition duration-300 shadow-sm group">
                            <span>{{ __('Lihat Layanan Kami') }}</span>
                            <span class="material-symbols-outlined text-sm transition-transform duration-300 group-hover:translate-x-1">arrow_forward</span>
                        </a>
                    </div>
                </div>
                
                <div class="lg:col-span-7">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        
                        <div class="card-flat-soft p-8 transition duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-slate-900 mb-6 group-hover:bg-slate-900 group-hover:text-white transition duration-300 shadow-sm">
                                <span class="material-symbols-outlined text-2xl">diamond</span>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-900 mb-2">Layanan Rapi</h4>
                            <p class="text-xs text-slate-600 font-normal leading-relaxed">Armada bersih, akomodasi jelas, dan proses yang mudah dipahami.</p>
                        </div>
                        
                        <div class="card-flat-soft p-8 transition duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-slate-900 mb-6 group-hover:bg-slate-900 group-hover:text-white transition duration-300 shadow-sm">
                                <span class="material-symbols-outlined text-2xl">badge</span>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-900 mb-2">Guide Berpengalaman</h4>
                            <p class="text-xs text-slate-600 font-normal leading-relaxed">Didampingi pemandu lokal yang ramah dan paham rute.</p>
                        </div>
                        
                        <div class="card-flat-soft p-8 transition duration-300 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-slate-900 mb-6 group-hover:bg-slate-900 group-hover:text-white transition duration-300 shadow-sm">
                                <span class="material-symbols-outlined text-2xl">verified_user</span>
                            </div>
                            <h4 class="text-lg font-semibold text-slate-900 mb-2">Aman & Terpercaya</h4>
                            <p class="text-xs text-slate-600 font-normal leading-relaxed">Proses pemesanan jelas dan dukungan yang responsif.</p>
                        </div>
                        
                        <div class="p-8 bg-white rounded-3xl border border-outline-variant/20 shadow-lg transition duration-300 hover:border-secondary/40 hover:-translate-y-1 group">
                            <div class="w-14 h-14 bg-surface-container rounded-2xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-white transition duration-500 shadow-inner">
                                <span class="material-symbols-outlined text-2xl">support_agent</span>
                            </div>
                            <h4 class="text-lg font-bold text-primary mb-2">Support 24/7</h4>
                            <p class="text-xs text-on-surface-variant font-light leading-relaxed">Tim asisten spesialis kami siap melayani seluruh pertanyaan dan kebutuhan Anda.</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Partners Section -->
    <section class="py-8 md:py-12 bg-primary relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] [background-size:24px_24px]"></div>
        <div class="max-w-7xl mx-auto px-5 md:px-8 relative z-10">
            <div class="text-center mb-8">
                <span class="text-secondary-fixed font-black text-[9px] uppercase tracking-[0.3em] mb-2 block">MITRA &amp; KLIEN</span>
                <h3 class="text-2xl md:text-4xl font-bold text-white tracking-tight">Mitra &amp; Klien Kami</h3>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 md:gap-12 items-center">
                @php
                $svgPartners = [
                    ['name' => 'Bank Mandiri Taspen', 'logo' => '/images/partners/mandiri.svg'],
                    ['name' => 'Universitas Sumatera Utara', 'logo' => '/images/partners/usu.svg'],
                    ['name' => 'Pelindo 1', 'logo' => '/images/partners/pelindo.svg'],
                    ['name' => 'Hyundai', 'logo' => '/images/partners/hyundai.svg'],
                ];
                @endphp
                @foreach($svgPartners as $partner)
                <div class="flex flex-col items-center gap-4 group">
                    <div class="w-full h-16 flex items-center justify-center opacity-40 group-hover:opacity-100 transition duration-500">
                        <img src="{{ asset($partner['logo']) }}" alt="{{ $partner['name'] }}" class="max-h-12 w-auto object-contain filter brightness-0 invert group-hover:scale-105 transition duration-500">
                    </div>
                    <p class="text-on-primary-container text-[8px] font-black uppercase tracking-widest text-center group-hover:text-secondary-fixed transition-colors">
                        {{ $partner['name'] }}
                    </p>
                </div>
                @endforeach
            </div>

            <!-- Wonderful Indonesia Badge -->
            <div class="mt-10 pt-6 border-t border-white/10 flex flex-col md:flex-row items-center justify-center gap-6 text-center md:text-left">
                {{-- Logo lokal diutamakan; Wikipedia hotlink bisa hilang kapan saja. --}}
                @if($siteSettings['cms_landing']['brand_partner_logo_url'] ?? false)
                    <img src="{{ $siteSettings['cms_landing']['brand_partner_logo_url'] }}" alt="Wonderful Indonesia" class="h-10 opacity-70 hover:opacity-100 transition-opacity duration-300">
                @else
                    <span class="text-white/60 font-bold text-sm tracking-widest uppercase">🇮🇩</span>
                @endif
                <div>
                    <p class="text-white font-bold text-sm tracking-tight">Program Wonderful Indonesia</p>
                    <p class="text-on-primary-container text-xs font-light">Kementerian Pariwisata dan Ekonomi Kreatif Republik Indonesia</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Luxury Call to Action Section -->
    <section class="py-8 md:py-16 px-5 md:px-8 bg-surface">
        <div class="max-w-7xl mx-auto bg-surface-container rounded-3xl md:rounded-[2.5rem] p-8 md:p-24 relative overflow-hidden text-center border border-outline-variant/20 shadow-2xl">
            <div class="absolute -top-32 -right-32 w-[30rem] h-[30rem] bg-secondary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -left-32 w-[30rem] h-[30rem] bg-primary/10 rounded-full blur-3xl"></div>
            
            <div class="relative z-10 max-w-3xl mx-auto space-y-8">
                <h2 class="text-3xl md:text-6xl font-bold text-primary tracking-tight leading-[1.05]">
                    Mulai Cerita Indah <br /> <span class="text-secondary">{{ __('Anda Bersama Kami') }}</span>
                </h2>
                <p class="text-on-surface-variant text-sm md:text-base font-light leading-relaxed max-w-xl mx-auto">
                    Siap untuk menjelajahi keindahan tersembunyi Sumatera Utara? Hubungi spesialis perjalanan kami hari ini untuk merancang liburan impian Anda.
                </p>
                
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                    <a href="/tour/packages" class="w-full sm:w-auto px-8 py-4 bg-primary hover:bg-primary-container text-white rounded-2xl font-black text-[10px] uppercase tracking-widest transition duration-300 shadow-lg hover:-translate-y-0.5">
                        {{ __('Pilih Paket Wisata') }}
                    </a>
                    
                    <a href="{{ \App\Helpers\ContactHelper::whatsappLink() }}" target="_blank" class="w-full sm:w-auto px-8 py-4 bg-white border border-outline-variant/30 text-on-surface hover:bg-surface-container-low rounded-2xl font-black text-[10px] uppercase tracking-widest transition duration-300 shadow-md hover:-translate-y-0.5 flex items-center justify-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-toba-green animate-pulse"></span>
                        {{ __('Chat WhatsApp') }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    @keyframes subtle-zoom {
        from { transform: scale(1); }
        to { transform: scale(1.05); }
    }
    .animate-subtle-zoom {
        animation: subtle-zoom 20s infinite alternate ease-in-out;
    }
</style>
@endsection

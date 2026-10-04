@extends('layouts.app')

@section('title', 'Paket Wisata Danau Toba dari ' . $originName . ' – Harga Terbaik ' . date('Y') . ' | Sujai Toba Sumatera')
@section('description', 'Paket wisata Danau Toba dari ' . $originName . ': rute penerbangan ke Kualanamu, penjemputan private, hotel & itinerary Samosir–Berastagi. Cek harga & pesan lewat WhatsApp.')
@section('keywords', __('paket wisata danau toba dari ' . strtolower($originName) . ', travel danau toba dari ' . strtolower($originName) . ', tour samosir ' . strtolower($originName)))


@push('schema')
@php
    $sameAsLinks = [];
    if (!empty($siteSettings['general']['social_instagram'])) {
        $sameAsLinks[] = 'https://www.instagram.com/' . ltrim($siteSettings['general']['social_instagram'], '@');
    }
    if (!empty($siteSettings['general']['social_facebook'])) {
        $sameAsLinks[] = 'https://www.facebook.com/' . $siteSettings['general']['social_facebook'];
    }
    if (!empty($siteSettings['general']['social_tiktok'])) {
        $sameAsLinks[] = 'https://www.tiktok.com/@' . ltrim($siteSettings['general']['social_tiktok'], '@');
    }
    if (!empty($siteSettings['general']['social_youtube'])) {
        $sameAsLinks[] = 'https://www.youtube.com/' . $siteSettings['general']['social_youtube'];
    }
    $schemaLogoUrl = imageUrl($siteSettings['general']['logo_light_url'] ?? null, asset('images/logo_compressed.webp'));
    $schemaPhone   = '+' . \App\Helpers\ContactHelper::whatsappDigits();
    $schemaEmail   = \App\Helpers\ContactHelper::email();
    $schemaDesc    = $settings['meta_description'] ?? 'Agen perjalanan wisata Danau Toba terpercaya';

    $homepageSchema = [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'       => 'TravelAgency',
                '@id'         => url('/') . '/#organization',
                'name'        => 'Sujai Toba Sumatera',
                'url'         => url('/'),
                'logo'        => [
                    '@type' => 'ImageObject',
                    'url'   => $schemaLogoUrl,
                ],
                'image'       => $schemaLogoUrl,
                'description' => 'Agen perjalanan wisata premium untuk Danau Toba, Samosir, Berastagi, Tangkahan, dan seluruh destinasi Sumatera Utara.',
                'telephone'   => $schemaPhone,
                'email'       => $schemaEmail,
                'address'     => [
                    '@type'           => 'PostalAddress',
                    'streetAddress'   => $siteSettings['general']['office_address'] ?? 'Jl. Trimurti 109 Berastagi Kabupaten Karo',
                    'addressLocality' => 'Berastagi',
                    'addressRegion'   => 'Sumatera Utara',
                    'addressCountry'  => 'ID',
                ],
                'areaServed'    => ['@type' => 'State', 'name' => 'Sumatera Utara'],
                'sameAs'        => $sameAsLinks,
                'priceRange'    => '$$',
                'openingHours'  => 'Mo-Su 08:00-20:00',
            ],
            [
                '@type'       => 'WebSite',
                '@id'         => url('/') . '/#website',
                'url'         => url('/'),
                'name'        => 'Sujai Toba Sumatera',
                'description' => $schemaDesc,
                'publisher'   => ['@id' => url('/') . '/#organization'],
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => [
                        '@type'       => 'EntryPoint',
                        'urlTemplate' => url('/tour/packages') . '?search={search_term_string}',
                    ],
                    'query-input' => 'required name=search_term_string',
                ],
                'inLanguage' => ['id', 'en', 'ms'],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => url('/')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => 'Paket Wisata', 'item' => route('tour.packages')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => 'Dari ' . $originName, 'item' => route('landing.origin', $kotaSlug)],
                ],
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($homepageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('content')
<div x-data="{ waNumber: @js(\App\Helpers\ContactHelper::whatsappDigits()) }">
    
    <!-- Programmatic SEO Hero Banner -->
    <section class="relative pt-14 pb-8 md:pt-20 md:pb-12 overflow-hidden bg-primary px-5 md:px-8">
        <div class="absolute inset-0 opacity-40">
            {{-- Halaman pSEO adalah jalur masuk utama dari Google, tapi selama ini
                 gambarnya melewatkan semua optimasi yang sudah dipakai homepage.
                 responsiveImage() memasang srcset 480/800/1200w. Hero tetap eager
                 + fetchpriority high karena dialah elemen LCP-nya. --}}
            {{-- Gambar kota asal dipakai lebih dulu bila ada (lihat
                 PublicController::landingOrigin), baru jatuh ke hero umum
                 dari Pengaturan. Alt-nya ikut berubah supaya tidak
                 menjanjikan "Panorama Danau Toba" pada foto kota asal. --}}
            @php $heroPath = ($originImage ?? null) ?: ($settings['hero_image_1_url'] ?? null); @endphp
            {!! responsiveImage($heroPath, 'w-full h-full object-cover', ($originImage ?? null) ? __('Panorama :city', ['city' => $originName]) : __('Panorama Danau Toba'), 'fetchpriority="high" decoding="async"') !!}
        </div>
        <div class="absolute inset-0 bg-gradient-to-t from-primary via-primary/80 to-transparent"></div>
        <div class="max-w-5xl mx-auto relative z-10 text-center">
            <x-breadcrumb :dark="true" class="mb-5 flex justify-center" :items="[
                ['label' => __('Paket Wisata'), 'url' => route('tour.packages')],
                ['label' => __('Dari :city', ['city' => $originName])],
            ]" />
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-secondary/20 border border-secondary text-secondary font-bold text-xs uppercase tracking-widest mb-6 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                Keberangkatan dari {{ $originName }}
            </span>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white tracking-tight leading-[1.1] mb-6">
                Paket Wisata Danau Toba dari <span class="text-secondary">{{ $originName }}</span>
            </h1>
            <p class="text-slate-300 text-lg md:text-xl max-w-2xl mx-auto mb-6 leading-relaxed">
                Penerbangan dan perjalanan Anda dari {{ $originName }} kini lebih mudah. Nikmati penjemputan VIP dari bandara Kualanamu / Silangit, rute terkurasi, dan pengalaman premium di Danau Toba tanpa ribet.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="https://wa.me/{{ \App\Helpers\ContactHelper::whatsappDigits() }}?text={{ urlencode('Halo Sujai Toba Sumatera, saya tertarik paket wisata Danau Toba dari ' . $originName) }}" 
                   class="w-full sm:w-auto bg-toba-green hover:bg-primary-container text-white px-8 py-4 rounded-full font-bold text-sm uppercase tracking-widest transition shadow-xl flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                    Konsultasi Gratis
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Packages -->
    @if($settings['show_featured'] ?? true)
    <section class="py-8 md:py-12 bg-surface overflow-hidden"
             x-data="{
                 isDragging: false, startX: 0, scrollLeft: 0,
                 scrollPercent: 0,
                 get el() { return this.$refs.pkgStrip },
                 onDown(e) { this.isDragging = true; this.startX = e.pageX - this.el.offsetLeft; this.scrollLeft = this.el.scrollLeft; },
                 onMove(e) { if (!this.isDragging) return; e.preventDefault(); this.el.scrollLeft = this.scrollLeft - ((e.pageX - this.el.offsetLeft) - this.startX); },
                 onUp() { this.isDragging = false; },
                 scrollPrev() { this.el.scrollBy({ left: -380, behavior: 'smooth' }); },
                 scrollNext() { this.el.scrollBy({ left: 380, behavior: 'smooth' }); },
             }">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="flex flex-col md:flex-row items-start md:items-end justify-between mb-6 md:mb-8 gap-6">
                <div class="max-w-xl">
                    <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.25em] text-secondary mb-3">
                        <span class="w-6 h-px bg-secondary"></span>{{ __('Paket Pilihan') }}
                    </span>
                    <h2 class="text-3xl md:text-5xl font-bold text-primary tracking-tight leading-[1.1]">{{ __('Pilihan Liburan Terbaik') }}</h2>
                    <p class="text-on-surface-variant text-sm md:text-base mt-3 leading-relaxed">{{ __('Geser untuk menjelajahi destinasi terkurasi di seluruh Sumatera Utara.') }}</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <button @click="scrollPrev()"
                            class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </button>
                    <button @click="scrollNext()"
                            class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>

        <div x-ref="pkgStrip"
             @mousedown="onDown($event)" @mousemove="onMove($event)" @mouseup="onUp()" @mouseleave="onUp()"
             @scroll="const max = el.scrollWidth - el.clientWidth; scrollPercent = max > 0 ? (el.scrollLeft / max) * 100 : 0"
             {{-- items-start: lihat catatan di tour/packages.blade.php. --}}
             class="flex items-start gap-6 overflow-x-auto scroll-smooth px-6 md:px-[max(1.5rem,calc((100vw-80rem)/2+1.5rem))] pb-4 no-scrollbar select-none snap-x snap-mandatory overscroll-x-contain"
             :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'">

            @foreach($packages as $index => $pkg)
            <div class="flex-shrink-0 snap-start w-[260px] sm:w-[280px] md:w-[310px]">
                <x-package-card :package="$pkg" />
            </div>
            @endforeach

        </div>

        <!-- Modern Scroll Progress Bar for Packages -->
        <div class="max-w-7xl mx-auto px-6 md:px-8 mt-6">
            <div class="h-[3px] w-full bg-slate-100 rounded-full overflow-hidden relative">
                <div class="h-full bg-secondary rounded-full absolute left-0 top-0 transition duration-150"
                     :style="'width: ' + scrollPercent + '%'"></div>
            </div>
        </div>
    </section>
    @endif

    @php
        $ctaImg = imageUrl($settings['cta_image_url'] ?? null, 'sumatra-panorama');
    @endphp

    <!-- Gallery Showcase -->
    @if($settings['show_about'] ?? true)
    @php
        // Use pre-fetched gallery slides from controller (cached)
        $slides = $gallerySlides ?? [];

        if (empty($slides)) {
            $fallbackImg = asset('images/home/tour.webp');
            $slides = [
                ['url' => imageUrl($settings['why_image_1_url'] ?? null, $fallbackImg), 'caption' => '', 'category' => ''],
                ['url' => imageUrl($settings['why_image_2_url'] ?? null, $fallbackImg), 'caption' => '', 'category' => ''],
                ['url' => imageUrl($settings['why_image_3_url'] ?? null, $fallbackImg), 'caption' => '', 'category' => ''],
                ['url' => $fallbackImg, 'caption' => '', 'category' => ''],
            ];
        }
    @endphp

    <section class="bg-primary py-8 md:py-12 overflow-hidden"
             x-data="{
                 slides: @js($slides),
                 scrollContainer: null,
                 isDragging: false,
                 startX: 0,
                 scrollLeft: 0,
                 autoTimer: null,
                 scrollPercent: 0,

                 init() {
                     this.scrollContainer = this.$refs.strip;
                     this.startAutoScroll();
                 },

                 startAutoScroll() {
                     this.stopAutoScroll();
                     this.autoTimer = setInterval(() => {
                         if (!this.scrollContainer) return;
                         const maxScroll = this.scrollContainer.scrollWidth - this.scrollContainer.clientWidth;
                         if (this.scrollContainer.scrollLeft >= maxScroll - 10) {
                             this.scrollContainer.scrollTo({ left: 0, behavior: 'smooth' });
                         } else {
                             this.scrollContainer.scrollBy({ left: 340, behavior: 'smooth' });
                         }
                     }, 4000);
                 },

                 stopAutoScroll() {
                     if (this.autoTimer) clearInterval(this.autoTimer);
                 },

                 scrollPrev() {
                     this.scrollContainer.scrollBy({ left: -340, behavior: 'smooth' });
                     this.stopAutoScroll(); this.startAutoScroll();
                 },

                 scrollNext() {
                     this.scrollContainer.scrollBy({ left: 340, behavior: 'smooth' });
                     this.stopAutoScroll(); this.startAutoScroll();
                 },

                 onMouseDown(e) {
                     this.isDragging = true;
                     this.startX = e.pageX - this.scrollContainer.offsetLeft;
                     this.scrollLeft = this.scrollContainer.scrollLeft;
                     this.stopAutoScroll();
                 },

                 onMouseMove(e) {
                     if (!this.isDragging) return;
                     e.preventDefault();
                     const x = e.pageX - this.scrollContainer.offsetLeft;
                     this.scrollContainer.scrollLeft = this.scrollLeft - (x - this.startX);
                 },

                 onMouseUp() {
                     this.isDragging = false;
                     this.startAutoScroll();
                 }
             }">

        {{-- Header --}}
        <div class="max-w-7xl mx-auto px-5 md:px-8 mb-6 md:mb-8 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.25em] text-secondary-fixed mb-3">
                    <span class="w-6 h-px bg-secondary-fixed"></span>{{ __('Galeri Destinasi') }}
                </span>
                <h2 class="text-3xl md:text-5xl font-bold text-white leading-[1.1] tracking-tight">
                    {{ __('Kenangan Nyata dari Toba') }}
                </h2>
            </div>
            <a href="{{ route('tour.gallery') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 backdrop-blur-md border border-white/20 rounded-full text-white hover:bg-secondary hover:border-secondary transition duration-300 group">
                <span class="material-symbols-outlined text-[16px]">photo_library</span>
                <span class="font-label-caps text-[10px] uppercase tracking-wider">{{ __('Lihat Semua') }}</span>
                <span class="material-symbols-outlined text-[14px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
            </a>
        </div>

        {{-- Scrollable strip --}}
        <div class="relative group/strip">

            {{-- Prev / Next arrows --}}
            <button @click="scrollPrev()"
                    class="hidden md:flex absolute left-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/10 backdrop-blur-md border border-white/20 rounded-full items-center justify-center text-white hover:bg-secondary hover:border-secondary transition duration-300 opacity-60 hover:opacity-100">
                <span class="material-symbols-outlined text-[20px]">chevron_left</span>
            </button>
            <button @click="scrollNext()"
                    class="hidden md:flex absolute right-4 top-1/2 -translate-y-1/2 z-20 w-12 h-12 bg-white/10 backdrop-blur-md border border-white/20 rounded-full items-center justify-center text-white hover:bg-secondary hover:border-secondary transition duration-300 opacity-60 hover:opacity-100">
                <span class="material-symbols-outlined text-[20px]">chevron_right</span>
            </button>

            {{-- Photo strip --}}
            <div x-ref="strip"
                 @mousedown="onMouseDown($event)"
                 @mousemove="onMouseMove($event)"
                 @mouseup="onMouseUp()"
                 @mouseleave="onMouseUp()"
                 @scroll="const max = scrollContainer.scrollWidth - scrollContainer.clientWidth; scrollPercent = max > 0 ? (scrollContainer.scrollLeft / max) * 100 : 0"
                 class="flex gap-5 overflow-x-auto scroll-smooth px-6 md:px-8 pb-4 no-scrollbar select-none snap-x snap-mandatory overscroll-x-contain"
                 :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'">

                {{-- Dicetak server. Isi <template x-for> tidak pernah ada di HTML,
                     jadi empat foto terberat halaman ini -- termasuk batak_house
                     165 KB -- tidak bisa ditemukan pemindai pramuat peramban
                     maupun perayap. Alpine tetap mengurus geser-tarik dan
                     gulir otomatisnya; yang berubah cuma siapa yang membuat
                     elemennya. --}}
                @foreach($slides as $i => $slide)
                    @php
                        $slideUrl = $slide['url'] ?? '';
                        $slideCap = trim((string) ($slide['caption'] ?? ''));
                        $slideCat = trim((string) ($slide['category'] ?? ''));
                    @endphp
                    <div class="flex-shrink-0 snap-start w-[220px] sm:w-[250px] md:w-[280px] group/card py-2">
                        <div class="relative h-64 md:h-72 rounded-2xl overflow-hidden shadow-lg border border-white/10 transition-all duration-500 ease-out group-hover/card:shadow-2xl group-hover/card:shadow-black/50 group-hover/card:-translate-y-2">
                            <img src="{{ $slideUrl }}"
                                 @if($__ss = imageSrcset($slideUrl)) srcset="{{ $__ss }}" sizes="280px" @endif
                                 alt="{{ $slideCap !== '' ? $slideCap : 'Sujai Toba Sumatera' }}"
                                 class="w-full h-full object-cover transition-transform duration-[1.5s] ease-out group-hover/card:scale-105"
                                 loading="lazy" decoding="async"
                                 onerror="this.src='{{ asset('images/home/tour.webp') }}'">

                            {{-- Gradient overlay permanent --}}
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/20 to-transparent flex flex-col justify-end p-4">
                                @if($slideCat !== '')
                                <span class="inline-block px-2.5 py-0.5 bg-toba-orange text-white text-[9px] font-bold uppercase tracking-wider rounded-full mb-1.5 w-fit shadow-sm">{{ $slideCat }}</span>
                                @endif
                                @if($slideCap !== '')
                                <p class="text-white text-[13px] font-semibold leading-snug line-clamp-2">{{ $slideCap }}</p>
                                @endif
                            </div>

                            {{-- Index badge --}}
                            <div class="absolute top-3 left-3 z-10 w-7 h-7 bg-white/20 backdrop-blur-md rounded-full border border-white/20 flex items-center justify-center shadow-sm">
                                <span class="text-white text-[10px] font-bold">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

        <!-- Modern Scroll Progress Bar for Gallery -->
        <div class="max-w-7xl mx-auto px-6 md:px-8 mt-6">
            <div class="h-[3px] w-full bg-white/10 rounded-full overflow-hidden relative">
                <div class="h-full bg-secondary rounded-full absolute left-0 top-0 transition duration-150"
                     :style="'width: ' + scrollPercent + '%'"></div>
            </div>
        </div>

    </section>
    @endif


    <!-- Testimonials — minimal -->
    {{-- Hanya testimoni ASLI dari admin. Dulu di sini ada "Wisatawan dari {kota}"
         yang di-generate per halaman pSEO (15 kota) + nama fiktif Julian/Isabella
         — testimoni karangan melanggar UU Perlindungan Konsumen. Section kosong
         lebih baik daripada kesaksian palsu. --}}
    <!-- Testimonials — Modern & Elegant -->
    @php $testimonials = $settings['testimonials'] ?? []; @endphp
    @if(($settings['show_testimonials'] ?? true) && count($testimonials))
    <section class="py-8 md:py-12 bg-slate-50/50 border-t border-b border-slate-100">
        <div class="max-w-5xl mx-auto px-5 md:px-8">
            <div class="flex items-center gap-3 mb-6 md:mb-6">
                <span class="w-6 h-px bg-toba-green"></span>
                <span class="text-[11px] font-bold text-toba-green uppercase tracking-[0.25em]">{{ __('Testimoni Wisatawan') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($testimonials as $t)
                <div class="bg-white p-6 md:p-8 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-1 text-amber-400 mb-4">
                            @for($i=0; $i<5; $i++)
                            <svg class="w-4 h-4 fill-amber-400 text-amber-400" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endfor
                        </div>
                        <p class="text-slate-700 text-sm md:text-[15px] leading-relaxed font-medium italic mb-6">
                            "{{ __($t['text']) }}"
                        </p>
                    </div>
                    <div class="flex items-center gap-3.5 pt-4 border-t border-slate-100">
                        <img alt="{{ $t['name'] }}"
                             src="{{ imageUrl($t['image'] ?? null, 'user' . ($loop->iteration ?? 1)) }}"
                             loading="lazy" decoding="async"
                             class="w-11 h-11 rounded-full object-cover shrink-0 ring-2 ring-slate-100">
                        <div>
                            <p class="text-sm font-bold text-slate-900 leading-tight">{{ $t['name'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5 font-medium">{{ __($t['location'] ?? 'Wisatawan Terverifikasi') }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Specialist — High Contrast Banner -->
    @if($settings['show_specialist'] ?? true)
    <section class="py-6 md:py-8 px-4 md:px-8">
        <div class="max-w-5xl mx-auto">
            <div class="bg-slate-900 rounded-2xl p-6 md:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl border border-slate-800">
                <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-5 text-center sm:text-left">
                    <img alt="{{ $settings['specialist_name'] ?? 'Sarah Anggraini' }}"
                         class="w-14 h-14 rounded-full object-cover ring-2 ring-toba-green/50 shrink-0"
                         loading="lazy" decoding="async"
                         src="{{ imageUrl($settings['specialist_image_url'] ?? '', 'staff1') }}"/>
                    <div>
                        <p class="text-white font-bold text-base md:text-lg leading-tight">{{ $settings['specialist_name'] ?? 'Sarah Anggraini' }}</p>
                        <p class="text-slate-300 text-xs md:text-sm font-medium mt-1">{{ __('Ada pertanyaan? Saya bersedia membantu merancang percutian impian anda.') }}</p>
                    </div>
                </div>
                <a target="_blank" rel="noopener"
                   href="https://wa.me/{{ \App\Helpers\ContactHelper::specialistDigits() }}?text={{ urlencode('Halo ' . ($settings['specialist_name'] ?? 'Sarah') . ', saya ingin tanya paket tour...') }}"
                   class="inline-flex items-center gap-2.5 px-6 py-3 bg-toba-green hover:bg-green-700 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition-all duration-300 shadow-md shrink-0 transform hover:scale-105">
                    <x-icon name="whatsapp" class="w-4 h-4" />
                    <span>{{ __('WHATSAPP') }}</span>
                </a>
            </div>
        </div>
    </section>
    @endif

    <!-- Journal/Blog -->
    @if($settings['show_blogs'] ?? true)
    <section class="py-8 md:py-12 max-w-7xl mx-auto px-5 md:px-8 bg-surface">
        <div class="flex flex-col sm:flex-row items-start sm:items-end justify-between mb-6 md:mb-8 gap-4">
            <div class="max-w-xl">
                <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.25em] text-secondary mb-3">
                    <span class="w-6 h-px bg-secondary"></span>{{ __('Cerita') }}
                </span>
                <h2 class="text-3xl md:text-5xl font-bold text-primary tracking-tight leading-[1.1]">{{ __('Jurnal Perjalanan') }}</h2>
            </div>
            <a class="text-[11px] font-bold uppercase tracking-widest text-secondary underline underline-offset-8 shrink-0" href="/tour/blog">{{ __('Lihat Semua Cerita') }}</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 md:gap-8">
            @foreach($blogs as $blog)
            <a href="{{ route('tour.blog.detail', $blog->slug) }}" class="group block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary focus-visible:ring-offset-2 rounded-lg">
                <div class="aspect-[16/10] overflow-hidden rounded-lg mb-4 md:mb-6 shadow-md border border-slate-100 bg-slate-100">
                    <img alt="{{ $blog->translated_title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" src="{{ $blog->image_url }}" loading="lazy" decoding="async"/>
                </div>
                <span class="font-label-caps text-[10px] text-secondary border border-secondary px-2 py-0.5 rounded-full uppercase tracking-wider mb-3 md:mb-4 inline-block">{{ strtoupper($blog->category ?? 'EKSPEDISI') }}</span>
                <h3 class="font-headline-md text-[20px] md:text-[22px] group-hover:text-secondary transition-colors duration-300 font-bold leading-tight">{{ $blog->translated_title }}</h3>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- FAQ -->
    <section class="py-8 md:py-12 bg-surface-container-low">
        <div class="max-w-3xl mx-auto px-5">
            <div class="text-center mb-6 md:mb-8">
                <h2 class="text-3xl md:text-5xl font-bold text-primary tracking-tight mb-4">{{ __('Pertanyaan Umum') }}</h2>
                <div class="w-12 h-0.5 bg-secondary mx-auto"></div>
            </div>
            @php
                $faqs = $settings['faqs'] ?? [
                    [
                        'q' => 'Bagaimana cara terbaik menuju Danau Toba dari Bandara Kualanamu (KNO)?',
                        'a' => 'Cara terbaik dan paling nyaman adalah menggunakan layanan transfer private (armada premium dengan supir pribadi) yang disediakan oleh Sujai Toba Sumatera. Perjalanan darat memakan waktu sekitar 3.5 hingga 4 jam melalui jalan tol Medan-Tebing Tinggi, lalu dilanjutkan ke Parapat, pintu gerbang utama menuju Pulau Samosir.'
                    ],
                    [
                        'q' => 'Apakah makanan halal mudah ditemukan di sekitar Danau Toba?',
                        'a' => 'Ya, sangat mudah. Di Parapat dan Pulau Samosir (terutama daerah wisata Tuk-tuk dan Tomok), terdapat banyak restoran Muslim lokal yang bersertifikat halal atau menyajikan menu ramah Muslim seperti ikan mas bakar, ayam penyet, dan masakan khas Minang/Padang. Supir dan pemandu Sujai Toba Sumatera akan selalu mengarahkan Anda ke tempat makan halal pilihan.'
                    ],
                    [
                        'q' => 'Mata uang apa yang digunakan, dan apakah kartu kredit diterima?',
                        'a' => 'Mata uang resmi yang digunakan adalah Rupiah Indonesia (IDR). Di kota besar seperti Medan, kartu kredit/debit internasional diterima secara luas. Namun, di sekitar Danau Toba, disarankan membawa uang tunai Rupiah untuk transaksi kecil di warung makan atau toko suvenir. Anda juga dapat melakukan pembayaran transfer bank internasional via Wise.'
                    ],
                    [
                        'q' => 'Kapan waktu terbaik untuk berkunjung ke Danau Toba?',
                        'a' => 'Danau Toba indah sepanjang tahun karena iklimnya yang sejuk di dataran tinggi. Waktu terbaik adalah antara bulan Mei hingga September saat curah hujan cenderung lebih rendah, memberikan pemandangan langit yang cerah dan danau yang biru. Hindari musim liburan nasional jika Anda menyukai suasana yang tenang.'
                    ],
                    [
                        'q' => 'Apakah tersedia paket kustom (private tour) untuk rombongan keluarga?',
                        'a' => 'Tentu saja! Semua paket wisata kami bersifat private dan dapat disesuaikan (customized) sepenuhnya sesuai keinginan Anda. Mulai dari pemilihan hotel premium, penyesuaian rute perjalanan, hingga akomodasi kebutuhan khusus untuk lansia atau anak-anak.'
                    ]
                ];
            @endphp
            @php
                // Pertanyaan khusus kota ini -- satu-satunya FAQ yang berbeda antar
                // halaman, dan yang paling dicari orang dari kota tersebut.
                $faqs = array_merge([[
                    'q' => 'Bagaimana cara ke Danau Toba dari ' . $originName . '?',
                    'a' => 'Dari ' . $originName . ', terbang atau menuju Bandara Kualanamu (KNO) Medan, atau Bandara Silangit (DTB) bila jadwalnya tersedia. Setelah mendarat, tim Sujai Toba Sumatera menjemput Anda dengan kendaraan private menuju Parapat dan Pulau Samosir; perjalanan darat dari Kualanamu sekitar 3,5 hingga 4 jam. Kami bantu menyusun rute dan jadwal penjemputan sesuai jam kedatangan Anda.'
                ]], $faqs);
            @endphp
            <script type="application/ld+json">{!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => collect($faqs)->map(fn ($f) => [
                    '@type' => 'Question',
                    'name' => $f['q'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
                ])->values()->all(),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
            <div class="space-y-2 md:space-y-4" x-data="{ selected: 1 }">
                @foreach($faqs as $index => $faq)
                <div class="bg-white px-5 md:px-6 rounded-2xl border border-slate-100 shadow-xs transition-shadow hover:shadow-sm">
                    <button @click="selected !== {{ $index + 1 }} ? selected = {{ $index + 1 }} : selected = null"
                            :aria-expanded="selected === {{ $index + 1 }} ? 'true' : 'false'"
                            aria-controls="faq-panel-{{ $index + 1 }}"
                            id="faq-tombol-{{ $index + 1 }}"
                            class="w-full py-5 md:py-6 flex justify-between items-center gap-4 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2">
                        <span class="text-[15px] md:text-[18px] text-primary font-bold leading-snug">{{ __($faq['q']) }}</span>
                        <span :class="selected === {{ $index + 1 }} ? 'rotate-180 text-secondary' : ''" class="material-symbols-outlined transition-transform duration-300" aria-hidden="true">expand_more</span>
                    </button>
                    <div x-show="selected === {{ $index + 1 }}" id="faq-panel-{{ $index + 1 }}" role="region" aria-labelledby="faq-tombol-{{ $index + 1 }}" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="overflow-hidden">
                        <p class="pb-5 md:pb-6 font-body-md text-[14px] md:text-[16px] text-on-surface-variant leading-relaxed">
                            {{ __($faq['a']) }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Tautan internal ke landing page kota lain: menyebarkan otoritas halaman
         dan memberi perayap jalur ke seluruh halaman pSEO. --}}
    @if(!empty($otherOrigins))
    <section class="py-8 md:py-10 bg-white border-t border-slate-100">
        <div class="max-w-5xl mx-auto px-5 md:px-8 text-center">
            <h2 class="text-lg md:text-xl font-bold text-primary mb-4">{{ __('Berangkat dari kota lain?') }}</h2>
            <div class="flex flex-wrap justify-center gap-2">
                @foreach($otherOrigins as $o)
                <a href="{{ route('landing.origin', $o['slug']) }}"
                   class="px-4 py-2 rounded-full border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-primary hover:text-white hover:border-primary transition">
                    {{ __('Paket Wisata Danau Toba dari :city', ['city' => $o['name']]) }}
                </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Cinema CTA -->
    <section class="py-8 md:py-14 px-5 md:px-8 bg-surface">
        <div class="max-w-7xl mx-auto bg-primary rounded-[2rem] md:rounded-[4rem] p-8 md:p-24 relative overflow-hidden shadow-[0_50px_100px_-20px] shadow-primary/30">
            <div class="absolute inset-0 opacity-40">
                <img src="{{ $ctaImg }}" alt="{{ $ctaAlt ?? __('Suasana perjalanan bersama Sujai Toba Sumatera') }}" @if($__ss = imageSrcset($ctaImg)) srcset="{{ $__ss }}" sizes="(max-width: 1023px) 100vw, 800px" @endif class="w-full h-full object-cover" loading="lazy" decoding="async">
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary/60 to-transparent"></div>
            
            <!-- Animated Circles Overlay -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-secondary/10 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-secondary/10 rounded-full blur-[120px]"></div>

            <div class="relative z-10 text-center lg:text-left max-w-4xl">
                <h2 class="text-3xl sm:text-4xl md:text-7xl font-bold text-white mb-6 md:mb-8 tracking-tight leading-[1.05] md:leading-[0.95]">
                    {{ __('Siap Untuk') }} <br/> <span class="text-white">{{ __('Petualangan Nyata?') }}</span>
                </h2>
                <p class="text-base md:text-xl text-slate-300 mb-6 md:mb-8 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                    @php
                        $touristsCount = $settings['stat_customers'] ?? '1.500+';
                    @endphp
                    {{ __('Bergabunglah dengan') }} <span class="text-white font-bold">{{ $touristsCount }}</span> {{ __('wisatawan lainnya yang telah menemukan keindahan Sumatera Utara bersama kami.') }}
                </p>
                <div class="flex flex-col sm:flex-row items-center gap-6 justify-center lg:justify-start">
                    <a href="/tour/packages" class="bg-white text-primary px-8 py-4 md:px-12 md:py-6 rounded-2xl md:rounded-[2rem] font-bold text-sm uppercase tracking-[0.2em] hover:bg-secondary hover:text-white transition duration-500 shadow-2xl flex items-center gap-3 group">
                        <span>{{ __('Pesan Paket Sekarang') }}</span>
                        <svg class="w-5 h-5 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <div class="flex -space-x-4">
                        @php
                        $avatarPhotos = [
                            imageUrl('avatar_user_1'),
                            imageUrl('avatar_user_2'),
                            imageUrl('avatar_user_3'),
                            imageUrl('avatar_user_4'),
                        ];
                        @endphp
                        @foreach($avatarPhotos as $avatarUrl)
                            <img src="{{ $avatarUrl }}" class="w-14 h-14 rounded-full border-4 border-primary shadow-xl object-cover" alt="" loading="lazy" decoding="async">
                        @endforeach
                        <div class="w-14 h-14 rounded-full border-4 border-primary bg-secondary flex items-center justify-center text-white text-[10px] font-bold">
                            {{ $touristsCount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

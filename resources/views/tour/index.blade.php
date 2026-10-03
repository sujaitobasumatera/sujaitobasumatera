@extends('layouts.app')

@section('title', $settings['meta_title'] ?? $settings['hero_title'] ?? 'Sujai Laketoba – Wisata Sumatera Utara')
@section('description', $settings['meta_description'] ?? $settings['hero_subtitle'] ?? 'Temukan keindahan Danau Toba, Samosir, Berastagi, Tangkahan, dan Bukit Lawang bersama Sujai Laketoba.')
@section('keywords', __('paket wisata danau toba, layanan premium danau toba, private tour samosir, travel vip medan, wisata sumatera utara, sujai laketoba'))

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
                'name'        => 'Sujai Laketoba',
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
                    'streetAddress'   => $siteSettings['general']['office_address'] ?? 'Jl. Trimurti 109',
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
                'name'        => 'Sujai Laketoba',
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
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($homepageSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('content')
<div x-data="{ waNumber: @js(\App\Helpers\ContactHelper::whatsappDigits()) }">

    {{-- H1 semantik halaman. sr-only karena slider kini hanya gambar + tombol,
         tak ada tempat wajar untuk heading terlihat tanpa merusak desainnya.
         Diletakkan di level halaman (di luar guard show_slider) supaya satu h1
         ini tetap ada meski slider dimatikan. --}}
    <h1 class="sr-only">{{ $settings['hero_title'] ?? __('Sujai Laketoba — Paket Wisata Danau Toba & Sumatera Utara') }}</h1>

    <!-- Premium Hero Slider -->
    @if($settings['show_slider'] ?? true)
    <x-home-slider :settings="$settings" :packages="$packages" />
    @endif

    <!-- Kenapa Memilih Sujai Laketoba (4 Keunggulan Utama) -->
    <section class="py-6 md:py-10 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                <!-- Poin 1 -->
                <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 border border-slate-100 hover:border-toba-green/30 hover:bg-green-50/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-toba-green/10 text-toba-green flex items-center justify-center shrink-0 group-hover:bg-toba-green group-hover:text-white transition duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-2xl">badge</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight mb-1">{{ __('Pemandu Asli Danau Toba') }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ __('Lahir &amp; tumbuh di Danau Toba, menguasai sejarah, kearifan Batak, dan spot tersembunyi.') }}</p>
                    </div>
                </div>

                <!-- Poin 2 -->
                <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 border border-slate-100 hover:border-toba-green/30 hover:bg-green-50/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-toba-green/10 text-toba-green flex items-center justify-center shrink-0 group-hover:bg-toba-green group-hover:text-white transition duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-2xl">directions_car</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight mb-1">{{ __('Armada Bersih &amp; Prima') }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ __('Kendaraan ber-AC sejuk, terawat rutin, dengan driver andal rute perbukitan Sumatera.') }}</p>
                    </div>
                </div>

                <!-- Poin 3 -->
                <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 border border-slate-100 hover:border-toba-green/30 hover:bg-green-50/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-toba-green/10 text-toba-green flex items-center justify-center shrink-0 group-hover:bg-toba-green group-hover:text-white transition duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-2xl">receipt_long</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight mb-1">{{ __('Harga Jujur &amp; Transparan') }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ __('Tanpa pungutan tersembunyi. Tiket wisata, akomodasi, dan rincian fasilitas tertulis jelas.') }}</p>
                    </div>
                </div>

                <!-- Poin 4 -->
                <div class="flex items-start gap-4 p-4 md:p-5 rounded-2xl bg-slate-50/80 border border-slate-100 hover:border-toba-green/30 hover:bg-green-50/40 transition duration-300 group">
                    <div class="w-12 h-12 rounded-xl bg-toba-green/10 text-toba-green flex items-center justify-center shrink-0 group-hover:bg-toba-green group-hover:text-white transition duration-300 shadow-sm">
                        <span class="material-symbols-outlined text-2xl">support_agent</span>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 leading-tight mb-1">{{ __('Fleksibel &amp; Konsultasi Ramah') }}</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">{{ __('Bebas custom jadwal untuk keluarga, rombongan, atau gathering. Siap bantu 24/7 via WhatsApp.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Packages -->
    @if($settings['show_featured'] ?? true)
    <section class="py-6 md:py-8 bg-surface overflow-hidden"
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
                            class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition min-w-[40px]">
                        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
                    </button>
                    <button @click="scrollNext()"
                            class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition min-w-[40px]">
                        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
                    </button>
                </div>
            </div>
        </div>

        <div x-ref="pkgStrip"
             @mousedown="onDown($event)" @mousemove="onMove($event)" @mouseup="onUp()" @mouseleave="onUp()"
             @scroll="const max = el.scrollWidth - el.clientWidth; scrollPercent = max > 0 ? (el.scrollLeft / max) * 100 : 0"
             {{-- items-start: lihat catatan yang sama di tour/packages.blade.php.
                  Tanpa ini, membuka satu akordeon menarik seluruh kartu di
                  strip ini ikut setinggi kartu yang terbuka. --}}
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

    <section class="bg-primary py-6 md:py-8 overflow-hidden"
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
                     // Berhenti saat kursor atau fokus keyboard berada di dalam
                     // galeri: gerakan yang jalan sendiri membuat orang kehilangan
                     // tempat bacaannya, dan bagi pengguna keyboard kartunya
                     // berpindah tepat saat hendak ditekan.
                     this.$el.addEventListener('mouseenter', () => this.stopAutoScroll());
                     this.$el.addEventListener('mouseleave', () => this.startAutoScroll());
                     this.$el.addEventListener('focusin', () => this.stopAutoScroll());
                 },

                 startAutoScroll() {
                     this.stopAutoScroll();
                     // prefers-reduced-motion: sebagian orang memilih setelan ini
                     // karena gerakan otomatis memicu pusing atau mual. Hormati.
                     if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                         return;
                     }
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
        <div class="max-w-7xl mx-auto px-5 md:px-8 mb-5 md:mb-7 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-4">
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
                                 alt="{{ $slideCap !== '' ? $slideCap : 'Sujai Laketoba' }}"
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


    {{-- Testimonials — slider

         Hanya render bila admin sudah mengisi testimoni ASLI. Tidak ada
         placeholder fiktif: testimoni karangan melanggar UU Perlindungan
         Konsumen, dan section kosong lebih baik daripada nama palsu.

         Baris kosong ikut dibuang. Form admin menyimpan baris begitu tombol
         "Tambah Ulasan" ditekan, jadi baris yang belum sempat diisi tetap
         tersimpan -- tanpa saringan ini ia terbit sebagai kartu hampa
         lengkap dengan bintang lima dan foto profil kosong. --}}
    @php
        $testimonials = array_values(array_filter(
            $settings['testimonials'] ?? [],
            fn ($t) => is_array($t)
                && trim((string) ($t['name'] ?? '')) !== ''
                && trim((string) ($t['text'] ?? '')) !== ''
        ));
    @endphp
    @if(($settings['show_testimonials'] ?? true) && count($testimonials))
    <section class="py-6 md:py-8 bg-slate-50/50 border-t border-b border-slate-100 overflow-hidden"
             x-data="{
                 isDragging: false, startX: 0, scrollLeft: 0,
                 get el() { return this.$refs.tstStrip },
                 onDown(e) { this.isDragging = true; this.startX = e.pageX - this.el.offsetLeft; this.scrollLeft = this.el.scrollLeft; },
                 onMove(e) { if (!this.isDragging) return; e.preventDefault(); this.el.scrollLeft = this.scrollLeft - ((e.pageX - this.el.offsetLeft) - this.startX); },
                 onUp() { this.isDragging = false; },
                 scrollPrev() { this.el.scrollBy({ left: -420, behavior: 'smooth' }); },
                 scrollNext() { this.el.scrollBy({ left: 420, behavior: 'smooth' }); },
             }">
        <div class="max-w-3xl mx-auto px-5 md:px-8 text-center">
            <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.25em] text-toba-green mb-3">
                <span class="w-6 h-px bg-toba-green"></span>
                {{ $settings['testimonials_eyebrow'] ?? __('Testimoni Wisatawan') }}
                <span class="w-6 h-px bg-toba-green"></span>
            </span>
            <h2 class="text-3xl md:text-5xl font-bold text-primary tracking-tight leading-[1.1]">
                {{ $settings['testimonials_title'] ?? __('Apa Kata Mereka Tentang Sujai Laketoba?') }}
            </h2>
            @if(! empty($settings['testimonials_subtitle']))
            <p class="text-on-surface-variant text-sm md:text-base mt-4 leading-relaxed">
                {{ $settings['testimonials_subtitle'] }}
            </p>
            @endif
        </div>

        {{-- items-start supaya kartu tidak saling menarik tingginya, sama
             seperti strip paket di atas. --}}
        <div x-ref="tstStrip"
             @mousedown="onDown($event)" @mousemove="onMove($event)" @mouseup="onUp()" @mouseleave="onUp()"
             class="flex items-start gap-6 overflow-x-auto scroll-smooth px-5 md:px-[max(1.5rem,calc((100vw-64rem)/2+1.5rem))] pt-6 md:pt-8 pb-4 no-scrollbar select-none snap-x snap-mandatory overscroll-x-contain"
             :class="isDragging ? 'cursor-grabbing' : 'cursor-grab'">
            @foreach($testimonials as $t)
            <figure class="shrink-0 snap-start w-[300px] sm:w-[360px] md:w-[420px] bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition-shadow duration-300 p-6 md:p-8 flex flex-col">
                <svg class="w-8 h-8 text-toba-green/15 shrink-0 mb-3" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9.5 5C6.46 5 4 7.46 4 10.5c0 2.76 2.24 5 5 5 .17 0 .34-.01.5-.03V16c0 1.66-1.34 3-3 3v3c3.31 0 6-2.69 6-6v-5.5C12.5 7.46 10.04 5 9.5 5zm10 0C16.46 5 14 7.46 14 10.5c0 2.76 2.24 5 5 5 .17 0 .34-.01.5-.03V16c0 1.66-1.34 3-3 3v3c3.31 0 6-2.69 6-6v-5.5C22.5 7.46 20.04 5 19.5 5z"/>
                </svg>

                {{-- Tinggi tetap + gulir sendiri: ulasan panjang tidak membuat
                     satu kartu jauh lebih tinggi daripada tetangganya. --}}
                <blockquote class="max-h-32 overflow-y-auto pr-2 text-slate-700 text-sm md:text-[15px] leading-relaxed font-medium">
                    {{ __($t['text']) }}
                </blockquote>

                <div class="flex items-center gap-1 text-amber-400 mt-5" role="img" aria-label="{{ __('Bintang 5 dari 5') }}">
                    @for($i = 0; $i < 5; $i++)
                    <svg class="w-4 h-4 fill-amber-400" viewBox="0 0 24 24" aria-hidden="true"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>

                <figcaption class="flex items-center gap-3.5 mt-5 pt-5 border-t border-slate-100">
                    <img alt="{{ $t['name'] }}"
                         src="{{ imageUrl($t['image'] ?? null, 'user' . $loop->iteration) }}"
                         loading="lazy" decoding="async"
                         class="w-11 h-11 rounded-full object-cover shrink-0 ring-2 ring-slate-100">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-slate-900 leading-tight truncate">{{ $t['name'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 font-medium truncate">{{ __($t['location'] ?? 'Wisatawan') }}</p>
                    </div>
                </figcaption>
            </figure>
            @endforeach
        </div>

        {{-- Tombol geser disembunyikan bila isinya belum cukup untuk digeser;
             tombol yang tidak melakukan apa-apa lebih membingungkan daripada
             tidak ada tombol. --}}
        @if(count($testimonials) > 1)
        <div class="flex items-center justify-center gap-3 mt-6">
            <button type="button" @click="scrollPrev()" aria-label="{{ __('Testimoni sebelumnya') }}"
                    class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_left</span>
            </button>
            <button type="button" @click="scrollNext()" aria-label="{{ __('Testimoni berikutnya') }}"
                    class="w-10 h-10 rounded-full border border-outline-variant flex items-center justify-center text-on-surface-variant hover:bg-primary hover:text-on-primary hover:border-primary transition">
                <span class="material-symbols-outlined text-[18px]" aria-hidden="true">chevron_right</span>
            </button>
        </div>
        @endif
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
    <section class="py-6 md:py-8 max-w-7xl mx-auto px-5 md:px-8 bg-surface">
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
            <a href="{{ route('tour.blog.detail', $blog->slug) }}" class="group block focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary focus-visible:ring-offset-2 rounded-[1.5rem] p-4 bg-white shadow-sm border border-slate-100 hover:shadow-xl transition-all duration-500 hover:-translate-y-2">
                <div class="aspect-[16/10] overflow-hidden rounded-xl mb-5 shadow-sm bg-slate-100">
                    <img alt="{{ $blog->translated_title }}" loading="lazy" decoding="async" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out" src="{{ $blog->image_url }}" @if($__ss = imageSrcset($blog->image_url)) srcset="{{ $__ss }}" sizes="(max-width: 639px) 100vw, (max-width: 767px) 50vw, 380px" @endif/>
                </div>
                <span class="font-label-caps text-[10px] text-secondary bg-secondary/10 px-3 py-1 rounded-full font-bold uppercase tracking-widest mb-4 inline-block">{{ strtoupper($blog->category ?? 'EKSPEDISI') }}</span>
                <h3 class="font-headline-md text-[20px] md:text-[22px] group-hover:text-secondary transition-colors duration-300 font-bold leading-tight tracking-tight">{{ $blog->translated_title }}</h3>
            </a>
            @endforeach
        </div>
    </section>
    @endif

    <!-- FAQ -->
    <section class="py-6 md:py-8 bg-surface-container-low">
        <div class="max-w-3xl mx-auto px-5">
            <div class="text-center mb-6 md:mb-8">
                <h2 class="text-3xl md:text-5xl font-bold text-primary tracking-tight mb-4">{{ __('Pertanyaan Umum') }}</h2>
                <div class="w-12 h-0.5 bg-secondary mx-auto"></div>
            </div>
            @php
                $faqs = $settings['faqs'] ?? [
                    [
                        'q' => 'Bagaimana cara terbaik menuju Danau Toba dari Bandara Kualanamu (KNO)?',
                        'a' => 'Cara terbaik dan paling nyaman adalah menggunakan layanan transfer private (armada premium dengan supir pribadi) yang disediakan oleh Sujai Laketoba. Perjalanan darat memakan waktu sekitar 3.5 hingga 4 jam melalui jalan tol Medan-Tebing Tinggi, lalu dilanjutkan ke Parapat, pintu gerbang utama menuju Pulau Samosir.'
                    ],
                    [
                        'q' => 'Apakah makanan halal mudah ditemukan di sekitar Danau Toba?',
                        'a' => 'Ya, sangat mudah. Di Parapat dan Pulau Samosir (terutama daerah wisata Tuk-tuk dan Tomok), terdapat banyak restoran Muslim lokal yang bersertifikat halal atau menyajikan menu ramah Muslim seperti ikan mas bakar, ayam penyet, dan masakan khas Minang/Padang. Supir dan pemandu Sujai Laketoba akan selalu mengarahkan Anda ke tempat makan halal pilihan.'
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
            <div class="space-y-2 md:space-y-4" x-data="{ selected: 1 }">
                @foreach($faqs as $index => $faq)
                <div class="bg-white px-5 md:px-6 rounded-2xl border border-slate-100 shadow-xs transition-shadow hover:shadow-sm">
                    {{-- aria-expanded + aria-controls: tanpa ini pembaca layar
                         mengumumkan tombol tanpa memberi tahu apakah jawabannya
                         sedang terbuka atau tertutup. --}}
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

    <!-- Cinema CTA -->
    <section class="py-6 md:py-8 px-5 md:px-8 bg-surface">
        <div class="max-w-7xl mx-auto bg-primary rounded-[2rem] md:rounded-[4rem] p-8 md:p-24 relative overflow-hidden shadow-[0_50px_100px_-20px] shadow-primary/30">
            <div class="absolute inset-0 opacity-40">
                <img src="{{ $ctaImg }}" alt="{{ $ctaAlt ?? 'Call to action image' }}" @if($__ss = imageSrcset($ctaImg)) srcset="{{ $__ss }}" sizes="(max-width: 1023px) 100vw, 800px" @endif loading="lazy" decoding="async" class="w-full h-full object-cover">
            </div>
            <div class="absolute inset-0 bg-gradient-to-br from-primary via-primary/60 to-transparent"></div>
            
            <!-- Animated Circles Overlay -->
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-secondary/10 rounded-full blur-[120px]"></div>
            <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-secondary/10 rounded-full blur-[120px]"></div>

            <div class="relative z-10 text-center lg:text-left max-w-4xl">
                <h2 class="text-2xl sm:text-4xl md:text-7xl font-bold text-white mb-5 md:mb-8 tracking-tight leading-[1.1] md:leading-[0.95]">
                    {{ __('Siap Untuk') }} <br/> <span class="text-white">{{ __('Petualangan Nyata?') }}</span>
                </h2>
                <p class="text-base md:text-xl text-slate-300 mb-5 md:mb-7 leading-relaxed max-w-2xl mx-auto lg:mx-0">
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
                            <img src="{{ $avatarUrl }}" loading="lazy" decoding="async" class="w-14 h-14 rounded-full border-4 border-primary shadow-xl object-cover" alt="Pelanggan Sujai Laketoba">
                        @endforeach
                        <div class="w-14 h-14 rounded-full border-4 border-primary bg-secondary flex items-center justify-center text-white text-[10px] font-bold">
                            {{ $touristsCount }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SEO Internal Links (Cities) -->
    <section class="py-8 bg-surface border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-5 md:px-8">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Populer: Paket Wisata dari Berbagai Kota</h3>
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                @php
                    $originsString = $siteSettings['general']['seo_pseo_origins'] ?? 'Jakarta, Surabaya, Bandung, Bali, Batam, Palembang, Makassar, Semarang, Yogyakarta, Kuala Lumpur, Singapore, Penang, Pekanbaru, Padang, Malaysia';
                    $pSEOCities = array_filter(array_map('trim', explode(',', $originsString)));
                @endphp
                @foreach($pSEOCities as $city)
                    <a href="{{ route('landing.origin', Str::slug($city)) }}" class="text-[11px] text-slate-500 hover:text-secondary transition-colors">
                        Paket Wisata Danau Toba dari {{ ucwords($city) }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>

</div>
@endsection

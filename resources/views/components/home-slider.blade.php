@props(['settings' => [], 'packages' => []])

@php
    // ========================================================
    // RESOLVE SLIDES FROM ADMIN CMS
    // Struktur slide dari admin: title, subtitle, image_url,
    // cta_text, cta_link, location, price, duration, type
    // ========================================================
    $slides = $settings['homepage_slides'] ?? [];

    // Fallback: jika pakai format hero lama (single hero)
    if (empty($slides) && !empty($settings['hero_title'])) {
        $slides[] = [
            'type'      => 'manual',
            'title'     => $settings['hero_title'],
            'subtitle'  => $settings['hero_subtitle'] ?? '',
            'image_url' => $settings['hero_image_url'] ?? null,
            'cta_text'  => $settings['hero_cta_text'] ?? 'Book Now!',
            'cta_link'  => $settings['hero_cta_link'] ?? '/tour/packages',
        ];
    }

    // Default slides jika belum ada setting sama sekali
    if (empty($slides)) {
        $slides = [
            [
                'type'      => 'manual',
                'image_url' => asset('images/sumut/toba_hero.webp'),
                'alt'       => 'Sujai Tour - Danau Toba',
                'cta_text'  => 'Book Now!',
                'cta_link'  => '/tour/packages',
            ],
            [
                'type'      => 'manual',
                'image_url' => asset('images/sumut/sumatra_panorama.webp'),
                'alt'       => 'Sujai Tour - Wisata Toba',
                'cta_text'  => 'Book Now!',
                'cta_link'  => '/tour/packages',
            ],
        ];
    } else {
        // Normalize: resolusi path gambar dari storage
        $slides = array_map(function ($slide) {
            $slide = (array) $slide;
            $url = $slide['image_url'] ?? null;
            if (is_string($url) && !str_starts_with($url, 'http') && !str_starts_with($url, 'blob')) {
                $url = preg_replace('/^(\/?storage\/)+/', '', $url);
                $url = imageUrl($url);
            }
            $slide['image_url']  = $url;
            $slide['image_webp'] = is_string($url) ? str_replace(['.png', '.jpg', '.jpeg'], '.webp', $url) : $url;
            $slide['alt']        = $slide['title'] ?? ($slide['alt'] ?? 'Sujai Tour');
            $slide['cta_link']   = $slide['cta_link'] ?? '/tour/packages';
            $slide['cta_text']   = $slide['cta_text'] ?? 'Book Now!';
            return $slide;
        }, $slides);
    }

    // Pass slides as JSON for Alpine
    $slidesJson = json_encode(array_values($slides), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<style>
/* ==========================================
   HERO SLIDER — SUJAI TOUR
   ========================================== */
.sujai-hero-section {
    position: relative;
    width: 100%;
    overflow: hidden;
    /* Height = full viewport minus total header (~115px) */
    height: calc(100vh - 115px);
    min-height: 480px;
    max-height: 880px;
    background: #000;
}
@media (max-width: 768px) {
    .sujai-hero-section {
        /* svh (small viewport height) memperhitungkan address bar mobile, sehingga
           dasar hero (features bar + tombol Book Now) tidak melorot menabrak
           fixed CTA bar di bawah. vh dipertahankan sebagai fallback browser lama. */
        height: 65vh;
        height: 65svh;
        min-height: 360px;
        max-height: 650px;
    }
}

/* Tiap slide: absolute full */
.sujai-slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    transition: opacity 0.9s ease-in-out;
}
.sujai-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}
.sujai-slide-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(to bottom,
        rgba(0,0,0,0.1) 0%,
        rgba(0,0,0,0.2) 40%,
        rgba(0,0,0,0.7) 100%
    );
    z-index: 2;
}

/* BOOK NOW button wrapper — posisi absolut di dalam hero */
.sujai-book-wrap {
    position: absolute;
    bottom: 78px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
    z-index: 35;
}

/* BOOK NOW button */
.sujai-book-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #E67E22 0%, #D35400 100%);
    color: #fff;
    font-weight: 800;
    padding: 15px 56px;
    border-radius: 9999px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    font-size: 14px;
    box-shadow: 0 10px 30px rgba(230,126,34,0.5);
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    white-space: nowrap;
    text-decoration: none;
    animation: pulse-glow 3s infinite;
}
.sujai-book-btn:hover {
    box-shadow: 0 15px 40px rgba(230,126,34,0.7);
    transform: translateY(-2px) scale(1.05);
    text-decoration: none;
    color: #fff;
}
@keyframes pulse-glow {
    0%   { box-shadow: 0 10px 30px rgba(230,126,34,0.5), 0 0 0 0 rgba(230,126,34,0.4); }
    50%  { box-shadow: 0 10px 30px rgba(230,126,34,0.5), 0 0 0 15px rgba(230,126,34,0); }
    100% { box-shadow: 0 10px 30px rgba(230,126,34,0.5), 0 0 0 0 rgba(230,126,34,0); }
}
@media (max-width: 640px) {
    .sujai-book-btn { padding: 12px 36px; font-size: 12px; }
}

/* Arrow buttons */
.sujai-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 40;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(0,0,0,0.40);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid rgba(255,255,255,0.25);
    cursor: pointer;
    backdrop-filter: blur(6px);
    transition: background 0.2s, transform 0.2s;
    margin-top: -30px; /* shift up from center to avoid features bar */
}
.sujai-arrow:hover { background: rgba(230,126,34,0.8); transform: translateY(-50%) scale(1.1); }
.sujai-arrow-left  { left: 16px; }
.sujai-arrow-right { right: 16px; }

/* Dot indicators */
.sujai-dots {
    position: absolute;
    bottom: 70px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 40;
    display: flex;
    gap: 8px;
    align-items: center;
}
.sujai-dot {
    height: 6px;
    border-radius: 9999px;
    border: none;
    cursor: pointer;
    transition: all 0.35s;
    background: rgba(255,255,255,0.45);
    width: 6px;
    padding: 0;
}
.sujai-dot.active { width: 24px; background: white; }

/* Features bar — overlay bottom */
.sujai-features-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 30;
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: stretch;
    justify-content: center;
}
.sujai-feat-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px 24px;
    color: white;
    flex: 1;
    justify-content: center;
    border-right: 1px solid rgba(255,255,255,0.10);
    min-width: 0;
}
.sujai-feat-item:last-child { border-right: none; }
.sujai-feat-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #E67E22 0%, #D35400 100%);
    box-shadow: 0 4px 10px rgba(230,126,34,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.sujai-feat-title { font-weight: 800; font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; line-height: 1.3; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
.sujai-feat-sub   { font-size: 10px; color: rgba(255,255,255,0.7); line-height: 1.3; }

@media (max-width: 640px) {
    .sujai-features-bar { flex-wrap: wrap; }
    .sujai-feat-item {
        /* tepat 50%, tidak melebar; teks boleh menyusut & wrap -> tak terpotong */
        flex: 0 0 50%;
        max-width: 50%;
        min-width: 0;
        justify-content: flex-start;
        border-right: none;
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding: 10px 10px;
        gap: 8px;
    }
    /* wadah teks harus boleh menyusut agar judul/subjudul wrap, bukan meluber */
    .sujai-feat-item > div:last-child { min-width: 0; }
    .sujai-feat-item:nth-child(odd) { border-right: 1px solid rgba(255,255,255,0.10); }
    .sujai-feat-item:nth-last-child(-n+2) { border-bottom: none; }
    .sujai-feat-icon { width: 30px; height: 30px; }
    .sujai-feat-title { font-size: 10px; letter-spacing: 0.04em; }
    .sujai-feat-sub { font-size: 9px; }
    .sujai-dots { bottom: 82px; }
    /* features bar 2 baris (~105px) menutupi tombol Book Now di bottom:78px.
       Angkat tombol ke atas features bar agar tidak ketimpa. */
    .sujai-book-wrap { bottom: 132px; }
}
</style>

{{-- ============================================================
     HERO SLIDER — dirender dari data admin CMS
     Setiap slide memakai image_url + cta_link + cta_text
     yang diatur melalui /admin/cms-beranda-tour
     ============================================================ --}}
<section
    class="sujai-hero-section"
    x-data="{
        slides: {{ $slidesJson }},
        active: 0,
        total: {{ count($slides) }},
        touchStartX: 0,
        timer: null,
        init() {
            if (this.total > 1) {
                this.timer = setInterval(() => this.next(), 5500);
            }
        },
        goTo(i) {
            this.active = i;
            /* Reset auto-play timer on manual nav */
            if (this.timer) { clearInterval(this.timer); this.timer = setInterval(() => this.next(), 5500); }
        },
        prev() { this.goTo((this.active - 1 + this.total) % this.total); },
        next() { this.goTo((this.active + 1) % this.total); },
        onTouchStart(e) { this.touchStartX = e.changedTouches[0].clientX; },
        onTouchEnd(e) {
            const dx = this.touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(dx) > 50) { dx > 0 ? this.next() : this.prev(); }
        }
    }"
    x-init="init()"
    @touchstart.passive="onTouchStart($event)"
    @touchend.passive="onTouchEnd($event)"
>

    {{-- ===== SLIDES ===== --}}
    @foreach($slides as $idx => $slide)
    <div
        class="sujai-slide"
        x-bind:style="active === {{ $idx }} ? 'opacity:1;z-index:12;' : 'opacity:0;z-index:5;'"
    >
        {{-- Gambar slide (poster dari admin atau foto biasa) --}}
        <picture>
            @if(!empty($slide['image_webp']))
            <source srcset="{{ $slide['image_webp'] }}" type="image/webp">
            @endif
            <img
                src="{{ $slide['image_url'] }}"
                alt="{{ $slide['alt'] ?? 'Sujai Tour' }}"
                loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                fetchpriority="{{ $idx === 0 ? 'high' : 'auto' }}"
            >
        </picture>

        {{-- Gradient overlay bawah --}}
        <div class="sujai-slide-overlay"></div>

        {{-- BOOK NOW button — tiap slide pakai link & teksnya sendiri --}}
        <div class="sujai-book-wrap">
            <a href="{{ $slide['cta_link'] }}" class="sujai-book-btn">
                {{ !empty($slide['cta_text']) ? $slide['cta_text'] : 'Book Now!' }}
            </a>
        </div>
    </div>
    @endforeach

    {{-- ===== ARROW PREV / NEXT ===== --}}
    @if(count($slides) > 1)
    <button type="button" class="sujai-arrow sujai-arrow-left"
            @click="prev()" aria-label="Slide sebelumnya">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
    </button>
    <button type="button" class="sujai-arrow sujai-arrow-right"
            @click="next()" aria-label="Slide berikutnya">
        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- ===== DOT INDICATORS ===== --}}
    <div class="sujai-dots">
        @foreach($slides as $idx => $slide)
        <button
            class="sujai-dot"
            :class="active === {{ $idx }} ? 'active' : ''"
            @click="goTo({{ $idx }})"
            aria-label="Slide {{ $idx + 1 }}"
        ></button>
        @endforeach
    </div>
    @endif

    {{-- ===== FEATURES BAR (tetap di bawah, di dalam hero) ===== --}}
    <div class="sujai-features-bar">
        <div class="sujai-feat-item">
            <div class="sujai-feat-icon">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <div>
                <p class="sujai-feat-title">Booking Mudah</p>
                <p class="sujai-feat-sub">Tanpa Ribet</p>
            </div>
        </div>
        <div class="sujai-feat-item">
            <div class="sujai-feat-icon">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <p class="sujai-feat-title">Proses Cepat</p>
                <p class="sujai-feat-sub">CS 24/7 Fast Respon</p>
            </div>
        </div>
        <div class="sujai-feat-item">
            <div class="sujai-feat-icon">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div>
                <p class="sujai-feat-title">Banyak Pilihan</p>
                <p class="sujai-feat-sub">Paket Fleksibel</p>
            </div>
        </div>
        <div class="sujai-feat-item">
            <div class="sujai-feat-icon">
                <svg width="18" height="18" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="sujai-feat-title">Harga Terbaik</p>
                <p class="sujai-feat-sub">Terjangkau &amp; Premium</p>
            </div>
        </div>
    </div>

</section>

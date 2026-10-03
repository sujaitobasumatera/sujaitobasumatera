<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Fonts fully self-hosted & bundled in Vite (Plus Jakarta Sans + subset Material Symbols). No external font requests. -->

    <!-- Preload Vite CSS to avoid render blocking delay -->
    {{-- Vite automatically handles standard preloads in modern Laravel versions, but we can explicitly hint it --}}
    @php
        $viteManifest = public_path('build/manifest.json');
        if (file_exists($viteManifest)) {
            $manifest = json_decode(file_get_contents($viteManifest), true);
            $cssFile = $manifest['resources/css/app.css']['file'] ?? null;
            if ($cssFile) {
                echo '<link rel="preload" href="/build/' . $cssFile . '" as="style">';
            }
        }
    @endphp

    <title>@yield('title', $siteSettings['general']['seo_meta_title'] ?? 'Sujai Toba Sumatera | Premium Tour Travel')</title>
    <meta name="description" content="{{ strip_tags($__env->yieldContent('description', $siteSettings['general']['seo_meta_desc'] ?? 'Portal utama Sujai Toba Sumatera. Pilih layanan premium Tour Travel Sumatera Utara.')) }}">
    <meta name="keywords" content="{{ strip_tags($__env->yieldContent('keywords', $siteSettings['general']['seo_meta_keywords'] ?? 'tour danau toba, travel sumatera utara')) }}">
    {{-- Bawaannya URL halaman itu sendiri. Halaman yang punya kembaran dengan
         isi sama (mis. detail paket versi tanpa form) menimpanya lewat
         @section('canonical') supaya keduanya tidak saling menggerus di Google. --}}
    <link rel="canonical" href="@yield('canonical', url()->current())">
    @if(!empty($siteSettings['general']['seo_google_verification']))
    <meta name="google-site-verification" content="{{ $siteSettings['general']['seo_google_verification'] }}">
    @endif
    <link rel="icon" type="image/x-icon" href="{{ imageUrl($siteSettings['general']['icon_url'] ?? null, asset('favicon.ico')) }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#166534">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $siteSettings['general']['site_name'] ?? 'Sujai Toba Sumatera' }}">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <!-- Open Graph / Facebook -->
    @php
        $ogModel = $package ?? $post ?? null;
        $ogDefault = $ogModel
            ? ogBannerUrl($ogModel)
            : (!empty($siteSettings['general']['og_image_url']) ? imageUrl($siteSettings['general']['og_image_url']) : ogBannerUrl(null));
    @endphp
    <meta property="og:type" content="{{ isset($post) ? 'article' : 'website' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ strip_tags($__env->yieldContent('title', $siteSettings['general']['seo_meta_title'] ?? 'Sujai Toba Sumatera | Premium Tour Travel')) }}">
    <meta property="og:description" content="{{ strip_tags($__env->yieldContent('description', $siteSettings['general']['seo_meta_desc'] ?? 'Portal utama Sujai Toba Sumatera. Pilih layanan premium Tour Travel Sumatera Utara.')) }}">
    <meta property="og:image" content="{{ $__env->yieldContent('og_image', $ogDefault) }}">

    <!-- Twitter (X reads the `name` attribute, not `property`) -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ url()->current() }}">
    <meta name="twitter:title" content="{{ strip_tags($__env->yieldContent('title', $siteSettings['general']['seo_meta_title'] ?? 'Sujai Toba Sumatera | Premium Tour Travel')) }}">
    <meta name="twitter:description" content="{{ strip_tags($__env->yieldContent('description', $siteSettings['general']['seo_meta_desc'] ?? 'Portal utama Sujai Toba Sumatera. Pilih layanan premium Tour Travel Sumatera Utara.')) }}">
    <meta name="twitter:image" content="{{ $__env->yieldContent('og_image', $ogDefault) }}">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Analytics (Dynamic from Settings) -->
    @if(!empty($siteSettings['general']['seo_ga_id']))
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $siteSettings['general']['seo_ga_id'] }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $siteSettings['general']['seo_ga_id'] }}');
    </script>
    @endif

    @if(!empty($siteSettings['general']['seo_pixel_id']))
    <!-- Meta Pixel Code -->
    <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', '{{ $siteSettings['general']['seo_pixel_id'] }}');
        fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
        src="https://www.facebook.com/tr?id={{ $siteSettings['general']['seo_pixel_id'] }}&ev=PageView&noscript=1"
    /></noscript>
    <!-- End Meta Pixel Code -->
    @endif

    @php
        // Seeded from CurrencyHelper so the presentation rules live in one
        // place. Restating them here is how the two drift apart.
        $activeLocale = session('locale', 'my');
        $activeCurrency = \App\Helpers\CurrencyHelper::currencyFor($activeLocale);
        $currencyConfig = \App\Helpers\CurrencyHelper::config($activeCurrency);
        $rate = \App\Helpers\CurrencyHelper::getRate($activeCurrency);
    @endphp
    <script>
        window.AppCurrency = {
            locale: @json($activeLocale),
            currency: @json($activeCurrency),
            rate: {{ $rate }},
            symbol: @json($currencyConfig['symbol']),
            decimals: {{ $currencyConfig['decimals'] }},
            thousandsSep: @json($currencyConfig['thousandsSep']),
            decPoint: @json($currencyConfig['decPoint']),
            all: {
                'MYR': { symbol: 'RM ', rate: 1, decimals: 0, thousandsSep: ',', decPoint: '.' },
                'IDR': { symbol: 'Rp ', rate: {{ \App\Helpers\CurrencyHelper::getRate('IDR') }}, decimals: 0, thousandsSep: '.', decPoint: ',' },
                'SGD': { symbol: 'S$ ', rate: {{ \App\Helpers\CurrencyHelper::getRate('SGD') }}, decimals: 0, thousandsSep: ',', decPoint: '.' }
            },
            setCurrency: function(code) {
                if (this.all[code]) {
                    this.currency = code;
                    this.rate = this.all[code].rate;
                    this.symbol = this.all[code].symbol;
                    this.decimals = this.all[code].decimals;
                    this.thousandsSep = this.all[code].thousandsSep;
                    this.decPoint = this.all[code].decPoint;
                    if (window.SUJAI_CUR) {
                        window.SUJAI_CUR.rate = this.rate;
                        window.SUJAI_CUR.symbol = this.symbol;
                        window.SUJAI_CUR.decimals = this.decimals;
                        window.SUJAI_CUR.thousandsSep = this.thousandsSep;
                        window.SUJAI_CUR.decPoint = this.decPoint;
                    }
                    window.dispatchEvent(new CustomEvent('currency-changed', { detail: code }));
                }
            },
            // Takes a SELLING price in MYR (the currency the catalogue is
            // stored in) and renders it for the active locale.
            format: function(priceInMyr) {
                if (priceInMyr === null || priceInMyr === undefined || priceInMyr === '') return '-';
                let converted = priceInMyr * this.rate;
                let formatted = parseFloat(converted).toFixed(this.decimals);
                
                // Format thousands separator
                let parts = formatted.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, this.thousandsSep);
                
                return this.symbol + parts.join(this.decPoint);
            }
        };

        // Wishlist Store Alpine (Tersimpan di browser tamu)
        document.addEventListener('alpine:init', () => {
            Alpine.store('wishlist', {
                items: JSON.parse(localStorage.getItem('sujai_wishlist') || '[]'),
                has(slug) {
                    return this.items.includes(slug);
                },
                toggle(slug) {
                    if (this.has(slug)) {
                        this.items = this.items.filter(s => s !== slug);
                    } else {
                        this.items.push(slug);
                    }
                    localStorage.setItem('sujai_wishlist', JSON.stringify(this.items));
                    window.dispatchEvent(new CustomEvent('wishlist-updated', { detail: this.items }));
                },
                count() {
                    return this.items.length;
                }
            });
        });
    </script>

    @stack('styles')
    @stack('head')
    @stack('schema')
</head>
{{-- situs-publik: penanda supaya aturan kerapatan & ukuran huruf di ponsel
     (app.css) hanya mengenai halaman publik. Panel admin memuat berkas CSS
     yang SAMA lewat layout-nya sendiri; tanpa penanda ini, tabel admin yang
     padat ikut membesar dan merapat diam-diam. --}}
<body class="situs-publik font-sans text-slate-900 bg-white selection:bg-green-100 selection:text-green-900 overflow-x-hidden pb-[calc(6rem+env(safe-area-inset-bottom))] md:pb-0" x-data="{ isDark: false }">
    {{-- Kalkulator pax per-kartu (paxCalc) + config mata uang untuk total live.
         Harga paket disimpan MYR; dikali rate -> mata uang tampilan sesuai locale. --}}
    @php
        $__curCode = \App\Helpers\CurrencyHelper::currencyFor();
        $__curCfg  = \App\Helpers\CurrencyHelper::CURRENCIES[$__curCode] ?? \App\Helpers\CurrencyHelper::CURRENCIES['MYR'];
        $__paxCur  = [
            'rate'         => (float) \App\Helpers\CurrencyHelper::getRate($__curCode),
            'symbol'       => $__curCfg['symbol'],
            'decimals'     => (int) $__curCfg['decimals'],
            'decPoint'     => $__curCfg['decPoint'],
            'thousandsSep' => $__curCfg['thousandsSep'],
        ];
    @endphp
    <script>
        window.SUJAI_CUR = @json($__paxCur);
        // Nomor & templat pesan untuk tombol WhatsApp di kalkulator pax.
        // Templatnya ditaruh di file bahasa (bukan dirangkai di JS) supaya
        // ketiga locale menerjemahkannya lewat jalur yang sama seperti teks lain.
        window.SUJAI_WA = @json(\App\Helpers\ContactHelper::whatsappDigits());
        window.SUJAI_WA_TPL = @json(__('Halo, saya berminat dengan paket *:name*.') . "\n"
            . __('Dewasa') . ': :adults' . "\n"
            . __('Anak-anak') . ': :children' . "\n"
            . __('Estimasi Total') . ': :total' . "\n"
            . ':url' . "\n\n"
            . __('Boleh minta info lebih lanjut?'));
        window.sujaiMoney = function (n) {
            var c = window.SUJAI_CUR;
            var fixed = (Number(n) || 0).toFixed(c.decimals);
            var parts = fixed.split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, c.thousandsSep);
            return c.symbol + (parts.length > 1 ? parts.join(c.decPoint) : parts[0]);
        };
        // Surcharge akhir pekan + musim ramai, cerminan langkah yang sama di
        // BookingService::calculateTotalPriceAndCost. Ditaruh di sini, bukan di
        // dalam atribut x-data, supaya logika tanggal ini punya tempat menulis
        // tanpa melanggar aturan tanpa-kutip-ganda di blok x-data.
        window.sujaiSurcharge = function (dateStr, cfg, subtotal) {
            var out = { amount: 0, items: [] };
            if (!dateStr || !cfg || !subtotal) return out;
            var d = new Date(dateStr + 'T00:00:00');
            if (isNaN(d.getTime())) return out;

            var add = function (label, percent) {
                var amt = subtotal * (percent / 100);
                out.amount += amt;
                out.items.push({ label: label + ' (' + percent + '%)', amount: amt });
            };

            var weekend = Number(cfg.weekend) || 0;
            if (weekend > 0 && (d.getDay() === 0 || d.getDay() === 6)) {
                add('Akhir Pekan', weekend);
            }

            var peak = Number(cfg.peak) || 0;
            var ps = String(cfg.peakStart || '').split('/');
            var pe = String(cfg.peakEnd || '').split('/');
            if (peak > 0 && ps.length === 2 && pe.length === 2) {
                var y = d.getFullYear();
                var start = new Date(y, Number(ps[1]) - 1, Number(ps[0]), 0, 0, 0);
                var end = new Date(y, Number(pe[1]) - 1, Number(pe[0]), 23, 59, 59);
                // Rentang yang melompati tahun baru, mis. 20/12 - 05/01.
                if (end < start) {
                    if (d.getMonth() <= end.getMonth()) {
                        start.setFullYear(y - 1);
                    } else {
                        end.setFullYear(y + 1);
                    }
                }
                if (d >= start && d <= end) {
                    add('Musim Ramai', peak);
                }
            }

            return out;
        };
        document.addEventListener('alpine:init', function () {
            // Ringkasan isi paket di kartu: termasuk / tidak termasuk / rute.
            // Sengaja RINGKASAN, bukan salinan halaman detail -- itinerary
            // lengkap paket 3 hari saja 15 baris aktivitas, lebih tinggi
            // daripada kartunya sendiri, dan karena kartu duduk di dalam grid
            // satu kartu yang memanjang ikut menarik seluruh barisnya.
            // Jadi: butir termasuk/tidak termasuk apa adanya (pendek), tapi
            // itinerary hanya judul harinya.
            window.Alpine.data('pkgDetails', function (includes, excludes, itinerary) {
                var bersih = function (arr) {
                    return (Array.isArray(arr) ? arr : []).filter(function (v) {
                        return typeof v === 'string' && v.trim() !== '';
                    });
                };
                return {
                    open: false,
                    includes: bersih(includes),
                    excludes: bersih(excludes),
                    days: (Array.isArray(itinerary) ? itinerary : [])
                        .map(function (d, i) {
                            if (!d || typeof d !== 'object') return null;
                            var judul = typeof d.title === 'string' ? d.title.trim() : '';
                            if (judul === '') return null;
                            return { day: (d.day != null ? d.day : i + 1), title: judul };
                        })
                        .filter(Boolean),
                    get isEmpty() {
                        return this.includes.length === 0 && this.excludes.length === 0 && this.days.length === 0;
                    },
                };
            });

            window.Alpine.data('paxCalc', function (adultMyr, childMyr, slug, tiers, name) {
                var baseAdult = Number(adultMyr) || 0;
                // null dibedakan dari 0: server memakai ?? (harga anak 0 berarti
                // gratis, bukan "kosong"), jadi jangan ratakan keduanya jadi 0.
                var baseChild = (childMyr === null || childMyr === undefined || childMyr === '') ? null : (Number(childMyr) || 0);
                // Buang baris tier yang tidak lengkap supaya perbandingan
                // jumlah pax tidak pernah dibandingkan dengan undefined.
                var tierList = (Array.isArray(tiers) ? tiers : []).filter(function (t) {
                    return t && t.min_pax != null && t.max_pax != null;
                });
                return {
                    adults: 1,
                    children: 0,
                    slug: slug || '',
                    name: name || '',
                    tiers: tierList,
                    curTicker: 0,
                    init: function () {
                        var self = this;
                        window.addEventListener('currency-changed', function () {
                            self.curTicker++;
                        });
                    },
                    _clamp: function (v, lo, hi) { v = parseInt(v, 10); if (isNaN(v)) v = lo; return Math.min(hi, Math.max(lo, v)); },
                    incA: function () { this.adults = this._clamp(this.adults + 1, 1, 30); },
                    decA: function () { this.adults = this._clamp(this.adults - 1, 1, 30); },
                    incC: function () { this.children = this._clamp(this.children + 1, 0, 30); },
                    decC: function () { this.children = this._clamp(this.children - 1, 0, 30); },
                    normA: function () { this.adults = this._clamp(this.adults, 1, 30); },
                    normC: function () { this.children = this._clamp(this.children, 0, 30); },
                    // Harga grosir. Cerminan persis Package::pricingTierFor():
                    // kalau paket punya tier, harga SELALU datang dari salah
                    // satu tier -- tidak pernah diam-diam jatuh ke harga dasar.
                    tierFor: function (n) {
                        var list = this.tiers;
                        if (!list.length) return null;

                        var match = list.find(function (t) { return n >= t.min_pax && n <= t.max_pax; });
                        if (match) return match;

                        var highest = list[0], lowest = list[0];
                        list.forEach(function (t) {
                            if (t.max_pax > highest.max_pax) highest = t;
                            if (t.min_pax < lowest.min_pax) lowest = t;
                        });
                        if (n > highest.max_pax) return highest;
                        if (n < lowest.min_pax) return lowest;

                        // Celah antar-tier: pakai tier terdekat DI BAWAHNYA.
                        // Diskon grosir baru berlaku setelah ambangnya tercapai.
                        var below = null;
                        list.forEach(function (t) {
                            if (t.max_pax < n && (below === null || t.max_pax > below.max_pax)) below = t;
                        });
                        return below || lowest;
                    },
                    // Dewasa dan anak dihitung TERPISAH, sama dengan
                    // BookingService. Satu ambang gabungan membuat menambah
                    // anak justru bisa MENURUNKAN total tagihan.
                    get adultTier() { return this.tierFor(this.adults); },
                    get childTier() { return this.tierFor(this.children); },
                    get adultUnit() {
                        var t = this.adultTier;
                        return (t && t.price != null) ? (Number(t.price) || 0) : baseAdult;
                    },
                    get childUnit() {
                        var t = this.childTier;
                        // Paket dengan harga grosir: harga anak ikut tier juga.
                        // childPrice paket sengaja dilewati -- mencampurnya
                        // dengan harga dewasa tier membuat anak bisa lebih mahal
                        // daripada setengah harga dewasa yang dibayar. Setengah
                        // harga dewasa tier-nya sendiri cuma jaring pengaman
                        // untuk baris tier lama yang terlanjur kosong.
                        if (t) {
                            if (t.child_price != null) return Number(t.child_price) || 0;
                            return ((t.price != null ? Number(t.price) : baseAdult) || 0) * 0.5;
                        }
                        // Tanpa tier: harga anak paket, lalu setengah harga
                        // dewasa. Dulu di sini jatuh ke harga dewasa PENUH,
                        // sehingga kartu memasang angka lebih mahal dari tagihan.
                        return baseChild != null ? baseChild : this.adultUnit * 0.5;
                    },
                    get rate() {
                        var _ = this.curTicker;
                        return window.SUJAI_CUR.rate;
                    },
                    get adultDisplay() { return this.adultUnit * this.rate; },
                    get childDisplay() { return this.childUnit * this.rate; },
                    get total() { return (this.adults * this.adultUnit + this.children * this.childUnit) * this.rate; },
                    fmt: function (n) {
                        var _ = this.curTicker;
                        return window.sujaiMoney(n);
                    },
                    get bookingUrl() { return '/tour/package/' + this.slug + '?pax=' + this.adults + '&anak=' + this.children; },
                    // Pesan WhatsApp dibangun dari state kalkulator yang sedang
                    // dilihat tamu -- jumlah pax dan totalnya persis yang tertera
                    // di kartu, jadi admin tidak perlu menanyakan ulang.
                    get waUrl() {
                        var vals = {
                            ':name': this.name || this.slug,
                            ':adults': String(this.adults),
                            ':children': String(this.children),
                            ':total': this.fmt(this.total),
                            ':url': window.location.origin + this.bookingUrl
                        };
                        // Penggantinya fungsi, bukan string: total dalam SGD
                        // berbunyi "S$800", dan pada replace() bentuk string
                        // "$" di sisi pengganti ditafsir sebagai pola ($&, $').
                        var msg = String(window.SUJAI_WA_TPL || '').replace(/:name|:adults|:children|:total|:url/g, function (k) {
                            return vals[k];
                        });
                        return 'https://wa.me/' + (window.SUJAI_WA || '') + '?text=' + encodeURIComponent(msg);
                    }
                };
            });
        });
    </script>
    
    <!-- Navbar -->
    @include('layouts.partials.navbar')

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    @include('layouts.partials.footer')

    @php
        // Resolve the floating WhatsApp number from saved settings.
        // env() returns null once config is cached, so never read COMPANY_PHONE here.
        $waFloat = preg_replace('/[^0-9]/', '', (string) (
            $siteSettings['general']['contact_whatsapp']
            ?? config('services.whatsapp.number')
            ?? ''
        ));
    @endphp
    <!-- Floating WhatsApp & Top (Desktop Only) -->
    <div class="fixed bottom-8 right-8 z-[90] hidden md:flex flex-col gap-4" x-data="{ showTop: false }" @scroll.window="showTop = window.scrollY > 500">
        <!-- Back to Top -->
        <button @click="window.scrollTo({top: 0, behavior: 'smooth'})" 
                x-show="showTop"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-8"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-8"
                class="w-12 h-12 bg-white/90 backdrop-blur-md text-slate-900 rounded-full flex items-center justify-center shadow-[0_8px_30px_rgb(0,0,0,0.12)] hover:bg-slate-50 transition duration-300 border border-slate-200">
            <span class="material-symbols-outlined">arrow_upward</span>
        </button>
        <!-- WhatsApp -->
        <a href="https://wa.me/{{ $waFloat }}" target="_blank" class="w-14 h-14 bg-toba-green text-white rounded-full flex items-center justify-center hover:bg-primary-container transition duration-300 shadow-[0_8px_30px_rgb(0,0,0,0.2)] hover:-translate-y-1 group relative">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            <span class="absolute right-full mr-4 px-3 py-1.5 bg-slate-900/90 backdrop-blur-sm text-white text-[10px] font-bold uppercase tracking-widest rounded-xl opacity-0 group-hover:opacity-100 transition duration-300 whitespace-nowrap pointer-events-none translate-x-2 group-hover:translate-x-0">
                {{ __('Tanya Spesialis') }}
            </span>
        </a>
    </div>

    <!-- Mobile Sticky Bottom CTA Bar (Floating Pill) -->
    {{-- Dilewati di halaman yang sudah membawa batang ajakannya SENDIRI di kaki
         layar. Tanpa penjagaan ini keduanya menempati sudut yang sama: pil ini
         (z-90) mengambang tepat di atas batang halaman (z-50), jadi tamu
         melihat dua baris tombol bertumpuk dengan dua ajakan berbeda -- dan
         yang tertutup justru yang paling berguna, karena batang halaman
         membawa harga dan tombol untuk paket yang sedang dibaca, sedangkan pil
         ini hanya menuju daftar paket.

         Halaman menyatakan dirinya lewat @section('bar-bawah-sendiri'). Dipilih
         section, bukan pemeriksaan nama rute di sini: layout tidak perlu
         menyimpan daftar rute yang harus diingat memperbaruinya setiap kali ada
         halaman baru yang memasang batangnya sendiri. --}}
    @sectionMissing('bar-bawah-sendiri')
    <div class="fixed bottom-4 left-4 right-4 z-[90] md:hidden bg-white/95 backdrop-blur-xl border border-slate-200/50 shadow-[0_12px_40px_rgb(0,0,0,0.15)] p-2 rounded-[1.25rem] flex items-center justify-between gap-2 safe-area-bottom">
        <a href="https://wa.me/{{ $waFloat }}" 
           class="flex-[0.8] bg-green-50 text-green-600 rounded-xl py-3 flex items-center justify-center gap-1.5 font-black text-[10px] uppercase tracking-[0.1em] transition-transform active:scale-95">
           <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 0 0-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
           <span class="mt-0.5">WhatsApp</span>
        </a>
        <a href="{{ route('tour.packages') }}" 
           class="flex-[1.2] bg-secondary-fixed text-on-secondary-fixed rounded-xl py-3 flex items-center justify-center gap-1.5 font-black text-[10px] uppercase tracking-[0.1em] shadow-md shadow-secondary/20 transition-transform active:scale-95">
           <span class="material-symbols-outlined text-[15px] shrink-0">travel_explore</span>
           <span class="mt-0.5">{{ __('Pesan') }}</span>
        </a>
    </div>
    @endif

    <!-- CMS Realtime Sync (No-Supabase Version) -->
    <script>
        (function() {
            let currentVersion = null;
            const checkInterval = 30000; // 30 seconds — light on battery/data for mobile
            let timer = null;
            
            async function checkCmsVersion() {
                if (document.visibilityState !== 'visible') return;

                try {
                    const response = await fetch('{{ route('api.sync.version') }}');
                    const data = await response.json();
                    
                    if (currentVersion === null) {
                        currentVersion = data.version;
                    } else if (data.version !== currentVersion) {
                        // CMS content updated. Never yank a page out from under a
                        // user who is filling a form — offer a refresh instead.
                        if (isUserEditing()) {
                            showRefreshBanner();
                            stopPolling();
                        } else {
                            window.location.reload();
                        }
                    }
                } catch (e) {}
            }

            function isUserEditing() {
                const active = document.activeElement;
                if (active && ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)) return true;
                return Array.from(document.querySelectorAll('input, textarea'))
                    .some((el) => el.type !== 'hidden' && el.type !== 'submit' && el.value.trim() !== '');
            }

            function showRefreshBanner() {
                if (document.getElementById('cms-refresh-banner')) return;
                const bar = document.createElement('div');
                bar.id = 'cms-refresh-banner';
                bar.style.cssText = 'position:fixed;left:50%;bottom:20px;transform:translateX(-50%);z-index:9999;background:#166534;color:#fff;padding:10px 16px;border-radius:9999px;box-shadow:0 8px 30px rgba(0,0,0,.25);font-size:14px;display:flex;gap:12px;align-items:center';
                bar.innerHTML = '<span>Konten telah diperbarui.</span>';
                const btn = document.createElement('button');
                btn.textContent = 'Muat ulang';
                btn.style.cssText = 'background:#fff;color:#166534;border:none;padding:4px 12px;border-radius:9999px;font-weight:600;cursor:pointer';
                btn.onclick = () => window.location.reload();
                bar.appendChild(btn);
                document.body.appendChild(bar);
            }

            function startPolling() {
                if (!timer) timer = setInterval(checkCmsVersion, checkInterval);
            }

            function stopPolling() {
                if (timer) {
                    clearInterval(timer);
                    timer = null;
                }
            }

            // Start polling only on non-admin pages
            if (!window.location.pathname.startsWith('/admin')) {
                startPolling();
                document.addEventListener('visibilitychange', () => {
                    if (document.visibilityState === 'visible') startPolling();
                    else stopPolling();
                });
            }
        })();
    </script>

    <!-- PWA: Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator && !window.location.pathname.startsWith('/admin')) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(() => {});
            });
        }
    </script>

    <!-- Universal Zoomable Lightbox Modal -->
    <x-image-zoom-modal />

    <!-- Floating Back to Top Button -->
    <div x-data="{ showTop: false }"
         @scroll.window="showTop = (window.scrollY > 400)"
         x-show="showTop"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 scale-90"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 scale-90"
         class="fixed bottom-24 right-4 md:bottom-8 md:right-8 z-40"
         style="display: none;">
        <button type="button"
                @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
                class="w-11 h-11 rounded-full bg-white/95 hover:bg-green-700 text-slate-700 hover:text-white border border-slate-200 hover:border-green-700 shadow-xl flex items-center justify-center transition-all duration-300 backdrop-blur-md active:scale-95 group cursor-pointer"
                title="{{ __('Kembali ke atas') }}"
                aria-label="{{ __('Kembali ke atas') }}">
            <span class="material-symbols-outlined text-[20px] group-hover:-translate-y-0.5 transition-transform">arrow_upward</span>
        </button>
    </div>

    <!-- Scripts -->
    @stack('scripts')
</body>
</html>

@extends('layouts.app')


@php
    $heroImages = collect();
    if ($package->packageImages && $package->packageImages->count() > 0) {
        $heroImages = $package->packageImages;
    } elseif (is_array($package->images) && count($package->images) > 0) {
        $heroImages = collect(array_map(function($path) {
            return (object)['image_path' => $path];
        }, $package->images));
    } else {
        $heroImages = collect([(object)['image_path' => null]]);
    }
    
    // Normalize for AlpineJS thumbnails and ensure json_encode includes it BEFORE x-data evaluates
    $packageImagesArray = $heroImages->map(function($img) {
        $path = is_array($img) ? ($img['image_path'] ?? null) : ($img->image_path ?? null);
        $srcset = '';
        $blurHash = '';
        
        if (!empty($path)) {
            $clean = ltrim($path, '/');
            if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, 8);
            }
            $media = \App\Models\Media::where('path', $clean)->orWhere('path', $path)->first();
            if ($media) {
                $dir = dirname($media->path);
                $base = basename($media->path);
                $mobilePath = ($dir === '.' || $dir === '/') ? 'mobile/' . $base : $dir . '/mobile/' . $base;
                $mediumPath = ($dir === '.' || $dir === '/') ? 'medium/' . $base : $dir . '/medium/' . $base;
                $largePath = ($dir === '.' || $dir === '/') ? 'large/' . $base : $dir . '/large/' . $base;

                $srcsetParts = [];
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($mobilePath)) {
                    $srcsetParts[] = \Illuminate\Support\Facades\Storage::disk('public')->url($mobilePath) . ' 480w';
                }
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($mediumPath)) {
                    $srcsetParts[] = \Illuminate\Support\Facades\Storage::disk('public')->url($mediumPath) . ' 800w';
                }
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($largePath)) {
                    $srcsetParts[] = \Illuminate\Support\Facades\Storage::disk('public')->url($largePath) . ' 1200w';
                }
                if (!empty($srcsetParts)) {
                    $srcset = implode(', ', $srcsetParts);
                }
                $blurHash = $media->blur_hash;
            }
        }
        
        $url = imageUrl($path);

        // Cadangan: kalau fotonya bukan unggahan yang terindeks di pustaka Media
        // -- misalnya paket contoh yang menunjuk aset bawaan di public/images --
        // pencarian di atas tidak menemukan apa pun dan srcset-nya kosong.
        // imageSrcset() tahu konvensi "-400/-800" milik aset bawaan, jadi foto
        // hero paket tidak lagi terkirim ukuran penuh ke layar 390px.
        if ($srcset === '') {
            $srcset = imageSrcset($url);
        }

        return [
            'url' => $url,
            'srcset' => $srcset,
            'blur_hash' => $blurHash
        ];
    })->toArray();
    $package->setAttribute('package_images', $packageImagesArray);

    $coverExif = null;
    $mainImgPath = null;
    if (is_array($package->images) && count($package->images) > 0) {
        $mainImgPath = $package->images[0];
    } elseif ($package->packageImages && $package->packageImages->count() > 0) {
        $mainImgPath = $package->packageImages->first()->image_path;
    }
    
    if ($mainImgPath) {
        $clean = ltrim($mainImgPath, '/');
        if (str_starts_with($clean, 'storage/')) {
            $clean = substr($clean, 8);
        }
        $media = \App\Models\Media::where('path', $clean)->orWhere('path', $mainImgPath)->first();
        if ($media && $media->exif_data) {
            $coverExif = $media->exif_data;
        }
    }
@endphp

@php
    $originSuffix = isset($originCity) && $originCity ? ' dari ' . $originCity : '';
@endphp
@section('title', ($package->translated_name ?? 'Paket Wisata') . $originSuffix . ' – Sujai Toba Sumatera')

{{-- Deskripsi paket datang dari editor teks kaya, jadi isinya HTML. Ditaruh
     mentah ke meta description, hasilnya terbaca sebagai kode di pratinjau
     tautan WhatsApp -- terlihat langsung di produksi sebagai
     "&amp;lt;p&amp;gt;Nikmati pengalaman..." karena tagnya ter-escape dua kali:
     sekali sudah tersimpan begitu di basis data, sekali lagi oleh Blade.

     html_entity_decode DULU baru strip_tags. Urutannya tidak boleh dibalik:
     strip_tags tidak mengenali "&lt;p&gt;" sebagai tag, jadi ia lolos utuh dan
     baru berubah jadi <p> yang kelihatan setelah didekode.

     Dipangkas 160 huruf: WhatsApp dan Google sama-sama memotong di sekitar
     angka itu, dan potongan yang kita atur sendiri lebih rapi daripada
     potongan mereka. --}}
@php
    $metaDeskripsi = trim(preg_replace('/\s+/', ' ',
        preg_replace('/<[^>]*>/', ' ', html_entity_decode((string) ($package->translated_description ?? ''), ENT_QUOTES | ENT_HTML5))
    ));
    $metaAwalan = isset($originCity) && $originCity
        ? 'Paket '.($package->translated_name ?? 'Wisata').' keberangkatan dari '.$originCity.'. '
        : '';
@endphp
@section('description', \Illuminate\Support\Str::limit($metaAwalan.$metaDeskripsi, 160))

{{-- Versi tanpa form isinya sama persis dengan versi berform. Canonical-nya
     ditunjuk ke versi berform supaya Google memilih satu pemenang, bukan
     membagi peringkat antara dua URL kembar. --}}
@section('canonical', route('tour.package.detail', $package->slug))

{{-- Halaman ini memasang batang ajakannya sendiri di kaki layar (harga paket +
     WhatsApp + Pesan), jadi pil melayang bawaan layout dilewati. Dua-duanya
     menempati sudut yang sama; yang bertahan harus yang tahu paket mana yang
     sedang dibaca. Hanya versi tanpa form: halaman berform tidak punya batang
     sendiri, jadi ia tetap butuh pil itu. --}}
@if(! $showBookingForm)
    @section('bar-bawah-sendiri', 'ya')
@endif

@section('og_image')
    @php
        $mainImg = count($packageImagesArray) > 0 ? $packageImagesArray[0]['url'] : null;
        echo $mainImg ?: asset('images/sumut/sumatra_panorama.webp');
    @endphp
@endsection

@push('schema')
<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@graph": [
    {
      "@@type": "Product",
      "name": "{{ $package->translated_name }}",
      "image": [
        "{{ count($packageImagesArray) > 0 ? $packageImagesArray[0]['url'] : asset('images/sumut/sumatra_panorama.webp') }}"
      ],
      "description": "{{ Str::limit(strip_tags($package->translated_description), 160) }}",
      "sku": "PKG-{{ $package->id }}",
      "offers": {
        "@@type": "Offer",
        "url": "{{ url()->current() }}",
        "priceCurrency": "{{ \App\Helpers\CurrencyHelper::PRICE_BASE }}",
        "price": "{{ $package->price }}",
        "availability": "https://schema.org/InStock"
      }
    },
    {
      "@@type": "TouristTrip",
      "name": "{{ $package->translated_name }}",
      "description": "{{ Str::limit(strip_tags($package->translated_description), 160) }}",
      "provider": {
        "@@type": "TravelAgency",
        "name": "Sujai Toba Sumatera",
        "url": "{{ url('/') }}"
      },
      "itinerary": {
        "@@type": "ItemList",
        "itemListElement": [
          @if(isset($package->itinerary) && is_array($package->itinerary))
            @foreach($package->itinerary as $index => $item)
            {
              "@@type": "ListItem",
              "position": {{ $index + 1 }},
              "item": {
                "@@type": "TouristAttraction",
                "name": "{{ $item['title'] ?? 'Day ' . ($index + 1) }}",
                "description": "{{ Str::limit(strip_tags($item['description'] ?? ''), 100) }}"
              }
            }{{ !$loop->last ? ',' : '' }}
            @endforeach
          @endif
        ]
      }
    }
  ]
}
</script>
@endpush

@section('content')
@php
    // Dikumpulkan sekali, dengan tipe yang sudah pasti, lalu diserahkan ke
    // Alpine lewat @json. Nilai kosong tidak boleh menghasilkan angka kosong.
    $formOld = [
        'pax' => max(1, (int) old('pax', (int) request()->query('pax', 1))),
        'paxChildren' => max(0, (int) old('paxChildren', (int) request()->query('anak', 0))),
        'notesUser' => (string) old('notesUser', ''),
        'customerName' => (string) old('customerName', ''),
        'customerPhone' => (string) old('customerPhone', ''),
        'startDate' => (string) old('startDate', ''),
    ];

    // Halaman /tour/detail dibaca di ponsel. Kartu putih bersudut lengkung di
    // dalam halaman yang latarnya sudah terang hanya menyisakan bingkai dan
    // padding: di layar 390px itu ~32px lebar bacaan yang hilang tanpa
    // menambah kejelasan apa pun. Di sana blok dibiarkan rata; kartunya baru
    // kembali mulai md. Halaman berform tidak diubah.
    $sales = ! $showBookingForm;
    $kartu = $sales
        ? 'md:bg-white md:p-8 md:rounded-2xl md:border md:border-slate-200 md:shadow-sm'
        : 'bg-white p-6 md:p-8 rounded-2xl border border-slate-200 shadow-sm';

    // HANYA kolom yang benar-benar dibaca Alpine di halaman ini. Sebelumnya
    // seluruh objek paket dikirim apa adanya, dan itu MEMBOCORKAN cost_price --
    // harga modal ke pemasok -- ke dalam HTML publik, terbaca siapa pun yang
    // membuka lihat-sumber. Bagi agen perjalanan itu marginnya sendiri yang
    // dipajang ke pesaing dan ke tamu yang sedang menawar.
    //
    // Daftar putih, bukan daftar hitam: kolom baru yang ditambahkan nanti tidak
    // ikut terkirim sampai ada yang sengaja menambahkannya ke sini. Membuang
    // satu kolom sensitif satu per satu berarti kebocoran berikutnya cuma
    // menunggu migrasi berikutnya.
    //
    // Efek sampingnya ikut menyingkirkan package_images yang duplikat -- ia
    // sudah dikirim terpisah lewat package_images di bawah, lengkap dengan
    // srcset yang panjang, dan selama ini terkirim dua kali.
    $packageAlpine = [
        'id' => $package->id,
        'slug' => $package->slug,
        'name' => $package->name,
        'translated_name' => $package->translated_name,
        'translated_description' => $package->translated_description,
        'translated_itinerary_text' => $package->translated_itinerary_text,
        'locationTag' => $package->locationTag,
        'price' => $package->price,
        'childPrice' => $package->childPrice,
        'includes' => array_values((array) ($package->includes ?? [])),
        'excludes' => array_values((array) ($package->excludes ?? [])),
        'itinerary' => array_values((array) ($package->itinerary ?? [])),
        'pricingDetails' => $package->pricingDetails,
    ];
@endphp

<div
    x-data="{
        activeImg: 0,
        activeTab: 'itinerary',
        package: @js($packageAlpine),
        package_images: @js($packageImagesArray),
        city: @js($city),
        contact: {
            whatsapp: @js(\App\Helpers\ContactHelper::whatsappDigits()),
            email: '{{ $siteSettings['cms_tour']['contact_email'] ?? $siteSettings['general']['contact_email'] ?? 'hello@sujaitobasumatera.com' }}'
        },
        get waNumber() {
            return (this.contact.whatsapp || @js(\App\Helpers\ContactHelper::whatsappDigits())).replace(/[^0-9]/g, '');
        },
        get locationDisplay() {
            return this.city ? (this.city.type === 'international' ? (this.city.place || this.city.region || '') + ', ' + this.city.country : this.city.name) : (this.package.locationTag || 'Danau Toba');
        },
        showConcierge: false,
        totalChanged: false,

        // Booking form variables.
        //
        // Semua nilai diserahkan lewat direktif js Blade, BUKAN direktif
        // json, dan tidak pernah diinterpolasi ke dalam string JS.
        // Direktif json mengeluarkan kutip ganda mentah; di dalam atribut
        // x-data kutip itu menutup atributnya dan memutus tag div ini.
        // ATURAN: jangan pernah menulis kutip ganda di dalam blok ini,
        // termasuk di dalam komentar.
        // Bentuk lamanya membungkus old() dengan tanda kutip
        // tunggal: Blade mengubah apostrof jadi entity, browser
        // mengembalikannya jadi apostrof, string JS terputus, dan SELURUH
        // Alpine di halaman ini mati. Satu pembeli bernama O(apostrof)Brien
        // yang gagal validasi sudah cukup. Nilai pax yang kosong dulu juga
        // menghasilkan properti tanpa nilai, yang sama fatalnya.
        pax: @js($formOld['pax']),
        paxChildren: @js($formOld['paxChildren']),
        pkgTiers: @js($package->pricingDetails['tiers'] ?? []),
        // Kendaraan besar: tarif per pax PENGGANTI, bukan biaya tambahan.
        // Pilihannya baru muncul setelah rombongan mencapai ambangnya.
        pkgVehicle: @js($package->pricingDetails['vehicle'] ?? null),
        useVan: false,
        services: (@js($package->pricingDetails['additional_services'] ?? [])).map(s => ({
            ...s,
            // Jumlah peserta, bukan ya/tidak. Hiking Sibayak RM 75/pax tidak
            // otomatis diikuti seluruh rombongan -- centangan membuat 8 orang
            // ditagih padahal yang naik cuma 4.
            qty: 0
        })),
        isSubmitting: false,
        notesUser: @js($formOld['notesUser']),
        customerName: @js($formOld['customerName']),
        customerPhone: @js($formOld['customerPhone']),
        startDate: @js($formOld['startDate']),

        // Pemilihan tier meniru Package::pricingTierFor() di server PERSIS:
        // cocok-persis → di atas tier tertinggi pakai tertinggi → di bawah terendah
        // pakai terendah → jatuh di celah pakai tier terdekat DI BAWAHNYA. Dewasa dan
        // anak memilih tier dari jumlah MASING-MASING, bukan tier dewasa untuk anak —
        // supaya harga yang ditampilkan sama persis dengan yang ditagih server.
        tierFor(count) {
            const tiers = this.pkgTiers || [];
            if (!tiers.length) return null;
            const match = tiers.find(t => count >= t.min_pax && count <= t.max_pax);
            if (match) return match;
            let highest = tiers[0], lowest = tiers[0];
            for (const t of tiers) {
                if (t.max_pax > highest.max_pax) highest = t;
                if (t.min_pax < lowest.min_pax) lowest = t;
            }
            if (count > highest.max_pax) return highest;
            if (count < lowest.min_pax) return lowest;
            let below = null;
            for (const t of tiers) {
                if (t.max_pax < count && (below === null || t.max_pax > below.max_pax)) below = t;
            }
            return below || lowest;
        },
        get activeTier() {
            return this.tierFor(this.pax);
        },
        get vanTersedia() {
            const v = this.pkgVehicle;
            return !!(v && v.price != null && this.pax >= Number(v.min_pax || 6));
        },
        get vanWajib() {
            const v = this.pkgVehicle;
            if (!this.vanTersedia) return false;
            const dari = Number(v.wajib_dari || (Number(v.min_pax || 6) + 1));
            return this.pax >= dari;
        },
        get vanAktif() {
            // Wajib menang atas centangan tamu, sama seperti di BookingService.
            return this.vanWajib || (this.useVan && this.vanTersedia);
        },
        get currentUnitPrice() {
            // Cerminkan BookingService persis. Van WAJIB tidak menimpa tier:
            // band 7+ memang sudah diisi tarif Van, jadi menimpanya lagi
            // menagih kelebihan. Yang menimpa hanya Van SUKARELA di bawah
            // ambang wajib.
            // (Jangan pernah menulis kutip ganda di blok x-data -- ia menutup
            // atributnya, dan sisa tag tercetak sebagai teks di halaman.)
            if (this.useVan && this.vanTersedia && !this.vanWajib) {
                return Number(this.pkgVehicle.price) || 0;
            }
            const t = this.activeTier;
            return (t && t.price != null) ? (Number(t.price) || 0) : this.package.price;
        },
        get priceDewasa() {
            return this.pax * this.currentUnitPrice;
        },
        get currentChildUnitPrice() {
            // Sama seperti server: tier anak dipilih dari jumlah ANAK. Ada tier →
            // child_price tier, kosong → separuh harga tier anak. Paket tanpa tier →
            // childPrice paket, kosong → separuh harga dewasa berlaku. != null:
            // harga anak 0 berarti gratis, bukan kosong.
            const ct = this.tierFor(this.paxChildren);
            if (ct) {
                if (ct.child_price != null) return Number(ct.child_price) || 0;
                return (ct.price != null ? (Number(ct.price) || 0) : this.currentUnitPrice) * 0.5;
            }
            if (this.package.childPrice != null) return Number(this.package.childPrice) || 0;
            return this.currentUnitPrice * 0.5;
        },
        get priceAnak() {
            return this.paxChildren * this.currentChildUnitPrice;
        },
        get additionalServicesPrice() {
            // Per pax, sama seperti BookingService. Angka di form admin adalah
            // tarif satu orang.
            return (this.services || [])
                .reduce((total, s) => total + parseFloat(s.price || 0) * (Number(s.qty) || 0), 0);
        },
        get jiwaTotal() {
            return (Number(this.pax) || 0) + (Number(this.paxChildren) || 0);
        },
        naikLayanan(s) {
            if ((Number(s.qty) || 0) < this.jiwaTotal) s.qty = (Number(s.qty) || 0) + 1;
        },
        turunLayanan(s) {
            if ((Number(s.qty) || 0) > 0) s.qty = (Number(s.qty) || 0) - 1;
        },
        get totalSebelumPajak() {
            return this.priceDewasa + this.priceAnak + this.additionalServicesPrice;
        },
        taxPercentage: {{ isset($taxPercentage) ? $taxPercentage : 0 }},
        surchargeCfg: @js($surcharge ?? []),
        get surcharge() {
            return window.sujaiSurcharge(this.startDate, this.surchargeCfg, this.totalSebelumPajak);
        },
        get surchargeAmount() { return this.surcharge.amount; },
        get surchargeItems() { return this.surcharge.items; },
        get totalDenganSurcharge() {
            // Server menambahkan surcharge SEBELUM pajak. Urutan ini tidak boleh
            // dibalik: pajak atas subtotal yang belum kena surcharge menghasilkan
            // total yang lebih kecil dari tagihan.
            return this.totalSebelumPajak + this.surchargeAmount;
        },
        get pajakLayanan() {
            // Must match BookingService::calculateTotalPriceAndCost exactly —
            // 2 decimals, because prices are in ringgit and rounding to whole
            // units here would quote a different total than the server charges.
            return Math.round(this.totalDenganSurcharge * (this.taxPercentage / 100) * 100) / 100;
        },
        get totalAkhir() {
            return this.totalDenganSurcharge + this.pajakLayanan;
        },
        get serializedNotes() {
            let lines = [];
            if (this.paxChildren > 0) {
                lines.push('Anak-anak: ' + this.paxChildren + ' Orang');
            }
            if (this.services) {
                this.services.forEach(s => {
                    if (Number(s.qty) > 0) {
                        lines.push(s.name + ': ' + s.qty + ' orang');
                    }
                });
            }
            if (this.notesUser && this.notesUser.trim()) {
                lines.push('Catatan Tambahan: ' + this.notesUser.trim());
            }
            return lines.join(' | ');
        }
    }"
    x-init="$watch('totalAkhir', value => { totalChanged = true; setTimeout(() => totalChanged = false, 500); })"
    @scroll.window="showConcierge = window.scrollY > 300"
    {{-- pt-14 bawaan itu mengimbangi navbar seolah-olah ia `fixed`. Navbar-nya
         `sticky`, jadi sudah memakan ruangnya sendiri di alur normal --
         128px itu ruang kosong murni di bawah menu. Di halaman tanpa form
         (yang dibaca sambil menggulir cepat di ponsel) dipangkas habis.
         Halaman berform sengaja dibiarkan apa adanya: bukan bagian dari
         permintaan ini. --}}
    class="bg-background text-on-background font-body-md min-h-screen {{ $showBookingForm ? 'pb-14 pt-14 md:pt-14' : 'pb-6 pt-4 md:pt-8' }}"
>
    {{-- Ringkasan teks untuk pembaca layar & crawler. sr-only saja (TANPA
         aria-hidden) supaya teknologi bantu ikut membacanya — ini pola sah,
         bukan teks tersembunyi khusus bot. --}}
    <section class="sr-only" id="package-summary">
        <h2>{{ $package->translated_name }}</h2>
        <p>{{ $package->translated_description }}</p>
        <p>Price: {{ \App\Helpers\CurrencyHelper::formatIn($package->price, \App\Helpers\CurrencyHelper::PRICE_BASE) }}</p>
        {{-- Sumbernya kolom includes/excludes — yang benar-benar diisi form
             admin. Dua baris ini dulu membaca pricingDetails['includes'],
             lokasi ketiga yang tidak pernah ditulis oleh siapa pun: kosong di
             kedelapan paket, jadi ringkasan untuk pembaca layar dan crawler
             tidak pernah menyebut satu pun isi paket. --}}
        @if(!empty($package->includes))
        <h3>{{ __('Termasuk') }}</h3>
        <ul>
            @foreach($package->includes as $inc)
                <li>{{ is_array($inc) ? ($inc['text'] ?? '') : $inc }}</li>
            @endforeach
        </ul>
        @endif
        @if(!empty($package->excludes))
        <h3>{{ __('Tidak Termasuk') }}</h3>
        <ul>
            @foreach($package->excludes as $exc)
                <li>{{ is_array($exc) ? ($exc['text'] ?? '') : $exc }}</li>
            @endforeach
        </ul>
        @endif
        @if(!empty($package->itinerary))
        <h3>{{ __('Rute Perjalanan') }}</h3>
        <ol>
            @foreach($package->itinerary as $i => $day)
                @if(is_array($day) && ! empty($day['title']))
                <li>{{ __('Hari ke-') }} {{ $day['day'] ?? $i + 1 }}: {{ $day['title'] }}</li>
                @endif
            @endforeach
        </ol>
        @endif
    </section>

    <!-- Gallery & Hero Section -->
    {{-- pb ekstra di mobile khusus halaman tanpa form: batang lengket di bawah
         menutupi ~72px terakhir, dan tanpa ini tombol penutup tersembunyi
         permanen di baliknya. --}}
    <section class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop py-6 {{ $showBookingForm ? '' : 'layout-rapat pb-24 md:pb-6' }} grid grid-cols-1 md:grid-cols-12 gap-8">
        
        <!-- LEFT COLUMN WRAPPER -->
        {{-- Tanpa form, tidak ada lagi yang perlu duduk di sebelah kanan:
             kolomnya dilebarkan penuh dan panel harga turun ke paling bawah. --}}
        <div class="contents md:block {{ $showBookingForm ? 'md:col-span-8' : 'md:col-span-12' }}">

        <!-- Hero/Gallery Part -->
        @if(! $showBookingForm)
        {{-- ================= HERO HALAMAN /tour/detail =================
             Disusun dari ponsel dulu, baru dinaikkan ke desktop.

             Kartu kaca yang menumpang di atas foto dilepas di sini. Di layar
             390px ia menutupi sepertiga foto, memaksa judul patah jadi empat
             baris, dan menyimpan harga 3.000px di bawah -- padahal harga dan
             satu tombol WhatsApp adalah dua hal yang dicari tamu ponsel dalam
             lima detik pertama. Judul, harga, dan ajakan sekarang duduk di alur
             normal tepat di bawah foto.

             Judulnya juga jadi teks server, bukan x-text: crawler yang tidak
             menjalankan JavaScript ikut membaca H1 halaman ini. --}}
        <div class="animate-in fade-in duration-700 order-1 mb-6 md:mb-8">

            {{-- Galeri geser khusus ponsel. Semua foto berjajar dan digeser
                 dengan jempol memakai scroll-snap bawaan browser -- tanpa
                 pustaka geser, tanpa penangan sentuh buatan sendiri. Deretan
                 thumbnail lama menuntut ketukan tepat pada kotak 140px;
                 menggeser foto seukuran layar tidak menuntut apa pun.

                 Fotonya dicetak server dengan @foreach, BUKAN template x-for:
                 isi <template> baru ada setelah Alpine bangun, jadi foto
                 terbesar halaman ini -- yang menentukan LCP -- akan menunggu
                 satu berkas JavaScript selesai diunduh dan dijalankan sebelum
                 permintaan gambarnya bahkan dimulai. Dengan src biasa, pemindai
                 pramuat browser menemukannya sejak byte pertama HTML. Alpine di
                 sini hanya mengurus titik penanda. --}}
            <div class="bleed-mobile md:hidden">
                <div x-ref="strip"
                     @scroll.passive="activeImg = Math.round($refs.strip.scrollLeft / Math.max(1, $refs.strip.clientWidth))"
                     class="flex overflow-x-auto snap-x snap-mandatory overscroll-x-contain no-scrollbar">
                    @foreach($packageImagesArray as $i => $imgObj)
                        <img class="w-full shrink-0 snap-center aspect-[4/3] object-cover bg-slate-100 cursor-zoom-in"
                             @click="$dispatch('zoom-image', { images: package_images, index: {{ $i }}, title: package.translated_name })"
                             src="{{ $imgObj['url'] }}"
                             @if($imgObj['srcset']) srcset="{{ $imgObj['srcset'] }}" sizes="100vw" @endif
                             alt="{{ $package->translated_name }}"
                             fetchpriority="{{ $i === 0 ? 'high' : 'low' }}"
                             loading="{{ $i === 0 ? 'eager' : 'lazy' }}" decoding="async"
                             onerror="this.src='{{ asset('images/home/tour.webp') }}'">
                    @endforeach
                </div>

                {{-- Titik penanda. Kotak ketuknya dibesarkan lewat padding di
                     tombol, bukan lewat titiknya: titik setinggi 6px yang bisa
                     diketuk memang mustahil kena. --}}
                @if(count($packageImagesArray) > 1)
                <div class="flex justify-center items-center gap-1 pt-3">
                    @foreach($packageImagesArray as $i => $imgObj)
                        <button type="button"
                                @click="$refs.strip.scrollTo({ left: {{ $i }} * $refs.strip.clientWidth, behavior: 'smooth' })"
                                aria-label="{{ __('Foto') }} {{ $i + 1 }}"
                                class="px-1 py-2.5">
                            <span class="block h-1.5 rounded-full transition-all duration-300"
                                  :class="activeImg === {{ $i }} ? 'w-6 bg-toba-green' : 'w-1.5 bg-slate-300'"></span>
                        </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Versi desktop: satu foto besar + deretan thumbnail, seperti
                 sisa situs. --}}
            <div class="hidden md:block space-y-4">
                <div class="relative h-[550px] overflow-hidden group rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] cursor-zoom-in"
                     @click="$dispatch('zoom-image', { images: package_images, index: activeImg, title: package.translated_name })"
                     title="{{ __('Klik untuk perbesar foto') }}">
                    {{-- src biasa lebih dulu, baru :src menimpanya saat Alpine
                         bangun: keduanya bernilai sama untuk foto pertama, jadi
                         yang berubah hanya kapan browser boleh mulai mengunduh. --}}
                    <img class="w-full h-full object-cover ken-burns group-hover:scale-105 transition-transform duration-700"
                         fetchpriority="high" decoding="async"
                         alt="{{ $package->translated_name }}"
                         src="{{ $packageImagesArray[0]['url'] ?? imageUrl($package->images[0] ?? null) }}"
                         @if(!empty($packageImagesArray[0]['srcset'])) srcset="{{ $packageImagesArray[0]['srcset'] }}" @endif
                         :src="package_images[activeImg] ? package_images[activeImg].url : '{{ imageUrl($package->images[0] ?? null) }}'"
                         :srcset="package_images[activeImg] ? package_images[activeImg].srcset : ''"
                         sizes="(max-width: 1024px) 50vw, 33vw"
                         onerror="this.src='{{ asset('images/home/tour.webp') }}'"/>

                    {{-- Badge Zoom Hover --}}
                    <div class="absolute bottom-4 right-4 bg-slate-900/80 hover:bg-slate-900 text-white backdrop-blur-md px-3.5 py-2 rounded-xl text-xs font-bold flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 shadow-lg border border-white/10 pointer-events-none">
                        <span class="material-symbols-outlined text-[16px]">zoom_in</span>
                        <span>{{ __('Perbesar Foto') }}</span>
                    </div>
                </div>
                <div x-show="package_images && package_images.length > 1" class="flex gap-4 overflow-x-auto no-scrollbar pb-2">
                    <template x-for="(imgObj, i) in package_images" :key="i">
                        <div @click="activeImg = i"
                             :class="activeImg === i ? 'ring-2 ring-green-500 ring-offset-2' : 'border border-slate-200/50'"
                             class="min-w-[180px] h-32 rounded-xl overflow-hidden flex-shrink-0 cursor-pointer hover:opacity-90 transition duration-300 shadow-sm">
                            <img class="w-full h-full object-cover" loading="lazy" decoding="async" :src="imgObj.url" alt="" onerror="this.src='{{ asset('images/home/tour.webp') }}'"/>
                        </div>
                    </template>
                </div>
            </div>

            {{-- ---- Kepala halaman: judul, harga, ajakan ---- --}}
            <div class="mt-3.5 md:mt-8">
                <x-breadcrumb class="mb-2" :items="[
                    ['label' => __('Paket Wisata'), 'url' => route('tour.packages')],
                    ['label' => $package->translated_name ?? $package->name],
                ]" />

                <div class="flex flex-wrap items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-toba-green uppercase tracking-[0.14em]">
                        <span class="w-1.5 h-1.5 rounded-full bg-toba-green"></span>
                        <span x-text="locationDisplay"></span>
                    </span>
                    @if(isset($originCity) && $originCity)
                        <span class="bg-toba-green text-white text-[10px] font-black uppercase tracking-widest px-2.5 py-1 rounded-full">
                            Dari {{ $originCity }}
                        </span>
                    @endif
                </div>

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <h1 class="font-headline-lg text-[26px] leading-[1.15] md:text-4xl md:leading-tight font-bold text-primary flex-1">
                        {{ $package->translated_name }}{{ $originSuffix }}
                    </h1>

                    <!-- Tombol Bagikan Paket ke WhatsApp & Salin Tautan -->
                    <div class="flex items-center gap-2 shrink-0 self-start mt-1" x-data="{
                            copied: false,
                            shareUrl: window.location.href,
                            shareWa() {
                                const txt = 'Halo! Lihat paket liburan Danau Toba ini: *' + @js($package->translated_name ?? $package->name) + '*\n' + this.shareUrl;
                                window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(txt), '_blank');
                            },
                            copyLink() {
                                navigator.clipboard.writeText(this.shareUrl);
                                this.copied = true;
                                setTimeout(() => { this.copied = false; }, 2500);
                            }
                        }">
                        <button type="button" @click="shareWa()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-green-50 text-green-800 hover:bg-green-700 hover:text-white border border-green-200 text-xs font-bold transition shadow-xs"
                                title="{{ __('Bagikan ke WhatsApp') }}">
                            <x-icon name="whatsapp" class="w-3.5 h-3.5" />
                            <span>{{ __('Bagikan') }}</span>
                        </button>
                        <button type="button" @click="copyLink()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition relative select-none"
                                title="{{ __('Salin Tautan Paket') }}">
                            <span class="material-symbols-outlined text-[15px]">link</span>
                            <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Salin') }}'"></span>
                            <span x-show="copied" x-cloak
                                  class="absolute -top-8 left-1/2 -translate-x-1/2 px-2.5 py-1 bg-slate-900 text-white text-[10px] rounded-lg shadow-lg whitespace-nowrap z-30">
                                {{ __('Tautan berhasil disalin!') }}
                            </span>
                        </button>
                        <button type="button" @click="$store.wishlist.toggle(@js($package->slug))"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold transition shadow-2xs select-none"
                                :class="$store.wishlist.has(@js($package->slug)) ? 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200'"
                                :title="$store.wishlist.has(@js($package->slug)) ? '{{ __('Hapus dari Favorit') }}' : '{{ __('Simpan ke Favorit') }}'">
                            <span class="material-symbols-outlined text-[15px]" :class="$store.wishlist.has(@js($package->slug)) ? 'text-red-500' : 'text-slate-500'" style="font-variation-settings: 'FILL' 1;">favorite</span>
                            <span x-text="$store.wishlist.has(@js($package->slug)) ? '{{ __('Tersimpan') }}' : '{{ __('Simpan') }}'"></span>
                        </button>
                    </div>
                </div>

                {{-- Fakta cepat: dua hal yang selalu ditanyakan lebih dulu di
                     obrolan WhatsApp -- berapa hari, dan berangkat dari mana. --}}
                @php
                    // Hanya kunci terjemahan yang sudah ada di ketiga bahasa
                    // yang dipakai di sini (Hari, Malam) -- blok ini tidak boleh
                    // jadi satu-satunya tempat di halaman yang jatuh ke bahasa
                    // Indonesia saat tamu membuka versi Inggris.
                    $jumlahMalam = count($package->accommodationList());
                    $jumlahHari = is_array($package->itinerary) ? count($package->itinerary) : 0;
                    // Satu keping waktu saja. Kolom duration umumnya sudah berbunyi
                    // "3 Hari 2 Malam"; menambah keping "3 Hari" dari jumlah baris
                    // rencana perjalanan di sebelahnya hanya mengulang hal yang sama.
                    $kepingWaktu = trim((string) $package->duration) !== ''
                        ? $package->duration
                        : ($jumlahHari ? $jumlahHari . ' ' . __('Hari') : null);
                    $faktaCepat = array_values(array_filter([
                        $kepingWaktu ? ['schedule', $kepingWaktu] : null,
                        $jumlahMalam ? ['hotel', $jumlahMalam . ' ' . __('Malam')] : null,
                    ]));
                @endphp
                @if(count($faktaCepat))
                <div class="flex flex-wrap gap-1.5 mt-2.5">
                    @foreach($faktaCepat as [$ikon, $teks])
                        <span class="inline-flex items-center gap-1.5 bg-primary/5 text-primary px-2.5 py-1.5 rounded-lg text-[13px] font-semibold">
                            <span class="material-symbols-outlined text-[15px]">{{ $ikon }}</span>
                            {{ $teks }}
                        </span>
                    @endforeach
                </div>
                @endif

                <!-- Trust & Assurance Chips (Halal Food, Private Tour, Fleksibel) -->
                <div class="flex flex-wrap gap-2 mt-3 pt-3 border-t border-slate-100">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 text-green-900 border border-green-200 text-xs font-bold shadow-2xs">
                        <span class="material-symbols-outlined text-[15px] text-green-700">restaurant</span>
                        <span>{{ __('100% Halal Food Guaranteed') }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-green-50 text-green-900 border border-green-200 text-xs font-bold shadow-2xs">
                        <span class="material-symbols-outlined text-[15px] text-green-700">directions_car</span>
                        <span>{{ __('Private Tour (Tanpa Gabung)') }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200 text-xs font-medium">
                        <span class="material-symbols-outlined text-[15px] text-slate-600">airport_shuttle</span>
                        <span>{{ __('Jemput Bandara KNO / Silangit') }}</span>
                    </span>
                </div>

                {{-- Harga dinaikkan ke atas. Sebelumnya ia hanya ada di batang
                     lengket dan di blok paling bawah halaman. Harga dan satuan
                     per-orangnya disatukan sebaris: bentuk tiga barisnya (label,
                     angka, "Setiap Orang") memakan tinggi tiga kali lipat untuk
                     satu keping informasi yang sama.

                     Tombol Pesan naik ke SEBELAH harga. Situs ini berjalan tanpa
                     rating Google, jadi separuh kanan baris harga selama ini
                     kosong melompong sementara tombolnya antre di baris sendiri
                     di bawah -- ruang yang sudah ada dibayar penuh tapi tidak
                     dipakai. Kalau ratingnya nanti diisi, ia kembali menempati
                     tempatnya dan tombolnya turun lagi: tidak ada yang hilang di
                     kedua keadaan, hanya barisnya yang berkurang satu. --}}
                @php
                    $__ratingHero = siteRating();
                    $heroPesan = __('Halo, saya berminat dengan paket *:name*.', ['name' => $package->translated_name ?? $package->name])
                        ."\n".url()->current();
                    // Tanpa ukuran huruf: dua pemakainya butuh ukuran berbeda, dan
                    // menulis dua kelas text-* pada satu elemen membuat pemenangnya
                    // ditentukan urutan di dalam CSS hasil build -- bukan urutan
                    // penulisan di atribut class, yang tidak berpengaruh apa pun.
                    $kelasPesan = 'inline-flex items-center justify-center gap-2 min-h-[46px] border-2 border-primary/15 text-primary rounded-xl font-bold active:scale-[0.98] transition-transform';
                @endphp
                {{-- flex-wrap: di layar yang sangat sempit (320px) tombolnya turun
                     sendiri ke baris berikutnya alih-alih menggencet angka harga
                     sampai terpotong. --}}
                <div class="flex flex-wrap items-end justify-between gap-3 mt-3 pt-3 border-t border-slate-200">
                    <div class="min-w-0">
                        <!-- In-Context Currency Switcher -->
                        <div class="flex items-center gap-1 mb-1.5 select-none" x-data>
                            <span class="text-[10px] text-slate-400 font-semibold mr-1">{{ __('Mata Uang:') }}</span>
                            <button type="button" @click="AppCurrency.setCurrency('MYR')"
                                    :class="AppCurrency.currency === 'MYR' ? 'bg-green-700 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded-md text-[10px] transition">
                                🇲🇾 MYR
                            </button>
                            <button type="button" @click="AppCurrency.setCurrency('IDR')"
                                    :class="AppCurrency.currency === 'IDR' ? 'bg-green-700 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded-md text-[10px] transition">
                                🇮🇩 IDR
                            </button>
                            <button type="button" @click="AppCurrency.setCurrency('SGD')"
                                    :class="AppCurrency.currency === 'SGD' ? 'bg-green-700 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded-md text-[10px] transition">
                                🇸🇬 SGD
                            </button>
                        </div>
                        <span class="font-label-caps text-[10px] text-slate-500 uppercase tracking-widest">{{ __('Mulai dari') }}</span>
                        <p class="flex items-baseline gap-1.5 leading-tight">
                            <span class="text-[26px] md:text-3xl font-extrabold text-toba-green" x-text="AppCurrency.format(package.price)"></span>
                            {{-- Kunci 'org', sama dengan kalkulator di kartu paket
                                 dan di grid /tour/packages: satu paket tidak boleh
                                 menyebut satuan harga dengan kata yang berbeda dari
                                 halaman yang baru saja ditinggalkan tamu. --}}
                            <span class="text-[12px] text-slate-500 font-body-md font-normal">/{{ __('org') }}</span>
                        </p>
                    </div>
                    @if($__ratingHero)
                    <div class="text-right shrink-0">
                        <span class="text-secondary font-bold text-lg font-body-md">★ {{ number_format($__ratingHero['value'], 1) }}</span>
                        @if($__ratingHero['count'])
                        <span class="text-slate-500 text-[11px] font-body-md block">
                            {{ number_format($__ratingHero['count']) }} {{ __('ulasan') }}
                        </span>
                        @endif
                    </div>
                    @else
                    <a href="{{ route('tour.package.detail', $package->slug) }}" class="{{ $kelasPesan }} shrink-0 whitespace-nowrap px-3.5 text-[14px]">
                        <span class="material-symbols-outlined text-[19px]">calendar_month</span>
                        {{ __('Pesan Sekarang') }}
                    </a>
                    @endif
                </div>

                {{-- Halaman ini tidak punya form: satu-satunya jalan keluar tamu
                     adalah WhatsApp atau halaman berform, dan keduanya tidak boleh
                     menunggu sampai tamu selesai menggulir. --}}
                <div class="grid grid-cols-1 {{ $__ratingHero ? 'sm:grid-cols-2' : '' }} gap-2 mt-2.5">
                    <a href="https://wa.me/{{ \App\Helpers\ContactHelper::whatsappDigits() }}?text={{ rawurlencode($heroPesan) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center justify-center gap-2.5 min-h-[46px] bg-toba-green text-white rounded-xl font-bold text-[15px] active:scale-[0.98] transition-transform">
                        <x-icon name="whatsapp" class="w-5 h-5" />
                        {{ __('Tanya lewat WhatsApp') }}
                    </a>
                    @if($__ratingHero)
                    <a href="{{ route('tour.package.detail', $package->slug) }}" class="{{ $kelasPesan }} text-[15px]">
                        <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                        {{ __('Pesan Sekarang') }}
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @else
        <div class="space-y-8 animate-in fade-in slide-in-from-left-8 duration-1000 order-1 mb-6 md:mb-8">
            <!-- Main Gallery -->
            <div class="relative h-[min(420px,60dvh)] md:h-[550px] overflow-hidden group rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.12)] cursor-zoom-in"
                 @click="$dispatch('zoom-image', { images: package_images, index: activeImg, title: package.translated_name })"
                 title="{{ __('Klik untuk perbesar foto') }}">
                <img class="w-full h-full object-cover ken-burns group-hover:scale-105 transition-transform duration-700"
                     fetchpriority="high" decoding="async"
                     :src="package_images[activeImg] ? package_images[activeImg].url : '{{ imageUrl($package->images[0] ?? null) }}'"
                     :srcset="package_images[activeImg] ? package_images[activeImg].srcset : ''"
                     sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"
                     :style="package_images[activeImg] && package_images[activeImg].blur_hash ? 'background-image: url(' + package_images[activeImg].blur_hash + '); background-size: cover; background-position: center; filter: blur(8px); transition: filter 0.5s ease-in-out, background-image 0.5s ease-in-out;' : ''"
                     onload="this.style.filter='none'; this.style.backgroundImage='none';"
                     onerror="this.src='{{ asset('images/home/tour.webp') }}'"/>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent pointer-events-none"></div>
                <div class="absolute bottom-6 left-6 md:bottom-10 md:left-10 bg-white/10 backdrop-blur-md border border-white/20 p-6 md:p-8 rounded-[1.5rem] max-w-[92%] md:max-w-[75%] shadow-glass pointer-events-auto">
                    {{-- Nama paket diambil dari sisi server, bukan dari x-text
                         Alpine: breadcrumb ikut dibaca crawler yang tidak
                         menjalankan JavaScript. --}}
                    <x-breadcrumb :dark="true" class="mb-3" :items="[
                        ['label' => __('Paket Wisata'), 'url' => route('tour.packages')],
                        ['label' => $package->translated_name ?? $package->name],
                    ]" />
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                        <span class="font-label-caps text-[10px] md:text-xs text-green-100 uppercase tracking-[0.2em]" x-text="locationDisplay"></span>
                        @if(isset($originCity) && $originCity)
                            <span class="ml-2 bg-toba-green/80 text-white text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full">
                                Dari {{ $originCity }}
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <h1 class="font-headline-lg text-2xl md:text-4xl text-white font-bold leading-tight drop-shadow-sm flex-1" x-text="package.translated_name + '{{ isset($originCity) && $originCity ? ' dari ' . $originCity : '' }}'"></h1>
                        
                        <!-- Tombol Bagikan Paket ke WhatsApp & Salin Tautan (Hero Dark) -->
                        <div class="flex items-center gap-2 shrink-0 self-start mt-1" x-data="{
                                copied: false,
                                shareUrl: window.location.href,
                                shareWa() {
                                    const txt = 'Halo! Lihat paket liburan Danau Toba ini: *' + @js($package->translated_name ?? $package->name) + '*\n' + this.shareUrl;
                                    window.open('https://api.whatsapp.com/send?text=' + encodeURIComponent(txt), '_blank');
                                },
                                copyLink() {
                                    navigator.clipboard.writeText(this.shareUrl);
                                    this.copied = true;
                                    setTimeout(() => { this.copied = false; }, 2500);
                                }
                            }">
                            <button type="button" @click="shareWa()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 hover:bg-green-600 text-white border border-white/30 text-xs font-bold transition backdrop-blur-md shadow-xs"
                                    title="{{ __('Bagikan ke WhatsApp') }}">
                                <x-icon name="whatsapp" class="w-3.5 h-3.5" />
                                <span>{{ __('Bagikan') }}</span>
                            </button>
                            <button type="button" @click="copyLink()"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition backdrop-blur-md border border-white/30 relative select-none"
                                    title="{{ __('Salin Tautan Paket') }}">
                                <span class="material-symbols-outlined text-[15px]">link</span>
                                <span x-text="copied ? '{{ __('Tersalin!') }}' : '{{ __('Salin') }}'"></span>
                                <span x-show="copied" x-cloak
                                      class="absolute -top-8 left-1/2 -translate-x-1/2 px-2.5 py-1 bg-slate-900 text-white text-[10px] rounded-lg shadow-lg whitespace-nowrap z-30">
                                    {{ __('Tautan berhasil disalin!') }}
                                </span>
                            </button>
                            <button type="button" @click="$store.wishlist.toggle(@js($package->slug))"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-bold transition backdrop-blur-md shadow-xs select-none"
                                    :class="$store.wishlist.has(@js($package->slug)) ? 'bg-red-500/40 text-white border-red-300' : 'bg-white/20 hover:bg-white/30 text-white border-white/30'"
                                    :title="$store.wishlist.has(@js($package->slug)) ? '{{ __('Hapus dari Favorit') }}' : '{{ __('Simpan ke Favorit') }}'">
                                <span class="material-symbols-outlined text-[15px]" :class="$store.wishlist.has(@js($package->slug)) ? 'text-red-300' : 'text-white'" style="font-variation-settings: 'FILL' 1;">favorite</span>
                                <span x-text="$store.wishlist.has(@js($package->slug)) ? '{{ __('Tersimpan') }}' : '{{ __('Simpan') }}'"></span>
                            </button>
                        </div>
                    </div>

                    <!-- In-Context Currency Quick Switcher (Hero Mode) -->
                    <div class="inline-flex items-center p-1 rounded-xl bg-black/30 backdrop-blur-md border border-white/20 text-xs font-bold gap-1 mt-3 self-start select-none" x-data>
                        <button type="button" @click="AppCurrency.setCurrency('MYR')"
                                :class="AppCurrency.currency === 'MYR' ? 'bg-white text-green-900 shadow-xs' : 'text-white/80 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg text-[11px] transition">
                            🇲🇾 MYR
                        </button>
                        <button type="button" @click="AppCurrency.setCurrency('IDR')"
                                :class="AppCurrency.currency === 'IDR' ? 'bg-white text-green-900 shadow-xs' : 'text-white/80 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg text-[11px] transition">
                            🇮🇩 IDR
                        </button>
                        <button type="button" @click="AppCurrency.setCurrency('SGD')"
                                :class="AppCurrency.currency === 'SGD' ? 'bg-white text-green-900 shadow-xs' : 'text-white/80 hover:text-white'"
                                class="px-2.5 py-1 rounded-lg text-[11px] transition">
                            🇸🇬 SGD
                        </button>
                    </div>

                    <!-- Trust Chips di Form Mode Hero -->
                    <div class="flex flex-wrap gap-2 mt-4 pt-3 border-t border-white/15">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/20 text-white border border-white/20 text-xs font-bold backdrop-blur-md shadow-2xs">
                            <span class="material-symbols-outlined text-[15px] text-green-300">restaurant</span>
                            <span>{{ __('100% Halal Food Guaranteed') }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/20 text-white border border-white/20 text-xs font-bold backdrop-blur-md shadow-2xs">
                            <span class="material-symbols-outlined text-[15px] text-green-300">directions_car</span>
                            <span>{{ __('Private Tour (Tanpa Gabung)') }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/10 text-white/90 border border-white/15 text-xs font-medium backdrop-blur-md">
                            <span class="material-symbols-outlined text-[15px] text-white/80">airport_shuttle</span>
                            <span>{{ __('Jemput Bandara KNO / Silangit') }}</span>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Thumbnails/Secondary Gallery -->
            <div x-show="package_images && package_images.length > 1" class="flex gap-4 overflow-x-auto no-scrollbar pb-2 snap-x snap-mandatory overscroll-x-contain">
                <template x-for="(imgObj, i) in package_images" :key="i">
                    <div @click="activeImg = i" 
                         :class="activeImg === i ? 'ring-2 ring-green-500 ring-offset-2' : 'border border-slate-200/50'"
                         class="min-w-[140px] md:min-w-[180px] h-24 md:h-32 rounded-xl overflow-hidden flex-shrink-0 snap-start cursor-pointer hover:opacity-90 transition duration-300 shadow-sm">
                        <img class="w-full h-full object-cover" loading="lazy" decoding="async" :src="imgObj.url" :style="imgObj.blur_hash ? 'background-image: url(' + imgObj.blur_hash + '); background-size: cover; background-position: center; filter: blur(4px);' : ''" onload="this.style.filter='none'; this.style.backgroundImage='none';" onerror="this.src='{{ asset('images/home/tour.webp') }}'"/>
                    </div>
                </template>
            </div>
        </div>
        @endif

        <!-- Content Part -->
        <div class="space-y-8 animate-in fade-in slide-in-from-left-8 duration-1000 order-3 mt-8 md:mt-0">
            
            <!-- Section: Itinerary -->
            <div class="space-y-8" id="section-itinerary">
                <!-- Header Section Itinerary -->
                <div class="flex items-center gap-2.5 md:gap-3">
                    <div class="w-9 h-9 md:w-12 md:h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[18px] md:text-[24px]">map</span>
                    </div>
                    <h2 class="font-headline-md text-lg md:text-2xl text-primary font-bold">{{ __('AGENDA PERJALANAN') }}</h2>
                </div>

                <!-- Ringkasan Pengalaman -->
                <div class="{{ $sales ? 'md:bg-white md:p-8 md:rounded-2xl md:border md:border-slate-200' : 'bg-white p-6 md:p-8 rounded-2xl border border-slate-200' }}">
                    <h2 class="font-headline-md text-xl md:text-headline-md text-primary mb-3 md:mb-6">{{ __('Ringkasan Pengalaman') }}</h2>
                    <div class="prose prose-slate max-w-none text-slate-600 font-body-md text-body-md leading-relaxed" x-html="package.translated_description"></div>

                    @if($coverExif)
                    <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap gap-4 items-center justify-between text-xs text-slate-500 bg-slate-50 p-4 rounded-xl">
                        <div class="flex items-center gap-3">
                            <span class="text-lg">📸</span>
                            <div>
                                <p class="font-semibold text-slate-700">Metadata Foto Wisata</p>
                                <p class="text-slate-500">
                                    @if(!empty($coverExif['camera_brand']) || !empty($coverExif['camera_model']))
                                        {{ $coverExif['camera_brand'] ?? '' }} {{ $coverExif['camera_model'] ?? '' }}
                                    @endif
                                    @if(!empty($coverExif['aperture'])) • {{ $coverExif['aperture'] }} @endif
                                    @if(!empty($coverExif['iso'])) • ISO {{ $coverExif['iso'] }} @endif
                                    @if(!empty($coverExif['shutter_speed'])) • {{ $coverExif['shutter_speed'] }} @endif
                                </p>
                            </div>
                        </div>
                        @if(!empty($coverExif['gps']['lat']) && !empty($coverExif['gps']['lng']))
                        <a href="https://www.google.com/maps/search/?api=1&query={{ $coverExif['gps']['lat'] }},{{ $coverExif['gps']['lng'] }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-50 text-green-700 hover:bg-green-100 rounded-lg font-semibold tracking-wide transition-colors">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            {{ __('Lihat Lokasi Persis') }}
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- pSEO: Internal Linking Block — "Paket ini tersedia dari kota berikut" --}}
                @php
                    $originsString = $siteSettings['general']['seo_pseo_origins'] ?? 'Jakarta, Surabaya, Bandung, Bali, Batam, Palembang, Makassar, Semarang, Yogyakarta, Kuala Lumpur, Singapore, Penang, Pekanbaru, Padang, Malaysia';
                    $pSEOCities    = array_filter(array_map('trim', explode(',', $originsString)));
                @endphp
                @if(count($pSEOCities) > 0)
                {{-- Daftar kota keberangkatan itu 15 tautan. Di ponsel ia
                     menyita satu layar penuh tepat di tengah bacaan, padahal
                     tugasnya menautkan halaman untuk mesin pencari -- bukan
                     dibaca berurutan oleh tamu. Jadi ia terlipat di ponsel
                     (tautannya tetap ada di DOM, hanya tidak tampak) dan
                     terbuka apa adanya mulai md. --}}
                <div x-data="{ bukaKota: window.innerWidth >= 768 }" class="bg-toba-green/5 border border-toba-green/15 rounded-2xl p-4 md:p-8">
                    <button type="button" @click="bukaKota = !bukaKota"
                            :aria-expanded="bukaKota ? 'true' : 'false'"
                            class="w-full flex items-center justify-between gap-3 text-left min-h-[44px] md:min-h-0 md:pointer-events-none">
                        <span class="text-[11px] font-black text-toba-green uppercase tracking-widest flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px]">flight_takeoff</span>
                            Paket ini tersedia keberangkatan dari:
                        </span>
                        <span class="material-symbols-outlined text-toba-green text-[22px] md:hidden transition-transform duration-300"
                              :class="bukaKota ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="bukaKota" x-cloak class="flex flex-wrap gap-2 mt-4">
                        @foreach($pSEOCities as $cityLink)
                            @php
                                $citySlug = \Illuminate\Support\Str::slug($cityLink);
                                $isActive = (isset($originCity) && strtolower($originCity) === strtolower(trim($cityLink)));
                            @endphp
                            <a href="{{ url('/tour/package/' . $package->slug . '-dari-' . $citySlug) }}"
                               class="inline-flex items-center gap-1.5 px-3.5 min-h-[40px] rounded-full text-[13px] font-bold transition
                                      {{ $isActive ? 'bg-toba-green text-white shadow-sm' : 'bg-white text-toba-green border border-toba-green/25 hover:bg-toba-green hover:text-white hover:border-toba-green' }}">
                                @if($isActive)<span class="material-symbols-outlined text-[13px]">check_circle</span>@endif
                                {{ ucwords(trim($cityLink)) }}
                            </a>
                        @endforeach
                    </div>
                </div>
                @endif


                <div x-show="package.itinerary || package.translated_itinerary_text" class="space-y-5 md:space-y-6 py-6 md:py-8 border-t border-outline-variant">
                    <h2 class="font-headline-md text-xl md:text-headline-md text-primary">{{ __('Rencana Perjalanan') }}</h2>

                    <div x-show="package.translated_itinerary_text" class="{{ $sales ? 'md:bg-white md:rounded-2xl md:p-8 md:border md:border-slate-200 md:shadow-sm' : 'bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm' }} whitespace-pre-line text-slate-600 font-body-md text-body-md leading-relaxed" x-text="package.translated_itinerary_text"></div>
                    
                    {{-- Kegiatan satu hari dikumpulkan dalam SATU kotak, bukan
                         satu kotak per kegiatan. Bentuk lamanya memberi tiap
                         baris bingkai, sudut lengkung, dan padding 12px
                         sendiri-sendiri: satu hari dengan lima kegiatan jadi
                         lima kotak setinggi hampir satu layar penuh, dan
                         pembacanya harus menggulir jauh hanya untuk tahu isi
                         satu hari. Bingkainya juga tidak memisahkan apa pun --
                         semua isinya setara. Satu kotak dengan garis pemisah
                         tipis menyampaikan hal yang sama dalam sepertiga
                         tinggi. --}}
                    <div x-show="!package.translated_itinerary_text && package.itinerary"
                         x-data="{
                             openDays: { 0: true },
                             expandAll: false,
                             toggleDay(idx) {
                                 this.openDays[idx] = !this.openDays[idx];
                             },
                             isOpen(idx) {
                                 return this.expandAll || !!this.openDays[idx];
                             },
                             toggleAll() {
                                 this.expandAll = !this.expandAll;
                                 if (!this.expandAll) {
                                     this.openDays = { 0: true };
                                 }
                             }
                         }"
                         class="space-y-3.5 relative">

                        <!-- Travel Route Comfort Badges (Estimasi Waktu Tempuh) -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 p-3.5 rounded-2xl bg-green-50/70 border border-green-200/80 text-xs text-slate-700 shadow-2xs">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-green-100 text-green-800 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">directions_car</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-[11px] truncate">KNO ➔ Parapat</p>
                                    <p class="text-[10px] text-slate-500">~3.5 Jam (Tol Trans-Sumatera)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-green-100 text-green-800 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">directions_boat</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-[11px] truncate">Parapat ➔ Samosir</p>
                                    <p class="text-[10px] text-slate-500">~30 Menit (Kapal Feri)</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-green-100 text-green-800 flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">schedule</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="font-bold text-slate-900 text-[11px] truncate">Silangit (DTB)</p>
                                    <p class="text-[10px] text-slate-500">~1.5 Jam ke Tepian Danau</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Kontrol Buka / Tutup Semua -->
                        <div class="flex items-center justify-between pb-1">
                            <span class="text-xs text-slate-500 font-medium">
                                {{ __('Ketuk setiap hari untuk melihat atau menutup agenda perjalanan.') }}
                            </span>
                            <button type="button" @click="toggleAll()"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-toba-green hover:underline shrink-0 select-none">
                                <span class="material-symbols-outlined text-[16px] transition-transform" :class="expandAll && 'rotate-180'">expand_more</span>
                                <span x-text="expandAll ? '{{ __('Tutup Semua') }}' : '{{ __('Buka Semua Hari') }}'"></span>
                            </button>
                        </div>

                        <template x-for="(day, i) in package.itinerary" :key="i">
                            <div class="rounded-2xl border border-slate-200 bg-white shadow-2xs overflow-hidden transition-all duration-200 hover:border-green-200">
                                <!-- Accordion Header -->
                                <button type="button" @click="toggleDay(i)"
                                        class="w-full flex items-center justify-between p-4 md:p-5 text-left bg-slate-50/70 hover:bg-slate-50 transition cursor-pointer select-none">
                                    <div class="flex items-center gap-3 md:gap-4 min-w-0 flex-1">
                                        <div class="w-9 h-9 md:w-10 md:h-10 rounded-xl bg-green-100 text-green-800 flex items-center justify-center text-xs md:text-sm font-black shrink-0 shadow-2xs"
                                             x-text="'H-' + (day.day || (i + 1))"></div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="font-bold text-slate-900 text-sm md:text-base leading-snug truncate" x-text="day.title"></h3>
                                            <p class="text-[11px] text-slate-500 truncate" x-text="day.description || 'Agenda perjalanan'"></p>
                                        </div>
                                    </div>
                                    <div class="w-7 h-7 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 ml-3 transition-transform duration-300"
                                         :class="isOpen(i) && 'rotate-180 bg-green-50 text-green-700 border-green-200'">
                                        <span class="material-symbols-outlined text-[16px]">expand_more</span>
                                    </div>
                                </button>

                                <!-- Accordion Body -->
                                <div x-show="isOpen(i)" x-collapse
                                     class="p-4 md:p-6 border-t border-slate-100 space-y-3 bg-white">
                                    <p class="text-xs md:text-sm text-slate-600 leading-relaxed font-body-md" x-text="day.description"></p>

                                    <template x-if="day.activities && day.activities.length > 0">
                                        <ul class="mt-2 bg-slate-50/80 border border-slate-200/80 rounded-xl px-4 py-1.5 space-y-1">
                                            <template x-for="(act, j) in day.activities" :key="j">
                                                <li class="flex items-start gap-2.5 py-1.5 border-b border-slate-200/60 last:border-b-0 text-xs text-slate-700">
                                                    <span class="mt-1 w-1.5 h-1.5 bg-green-600 rounded-full shrink-0"></span>
                                                    <span class="leading-relaxed font-medium" x-text="act"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Section: Pricing & Facilities -->
            <div class="space-y-8 pt-8 border-t border-slate-200" id="section-pricing">
                <!-- Header Section Pricing -->
                <div class="flex items-center gap-2.5 md:gap-3">
                    <div class="w-9 h-9 md:w-12 md:h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[18px] md:text-[24px]">payments</span>
                    </div>
                    <h2 class="font-headline-md text-lg md:text-2xl text-primary font-bold">{{ __('BIAYA & FASILITAS') }}</h2>
                </div>
                <!-- Rincian Biaya -->
                <div x-show="package.pricingDetails && package.pricingDetails.length > 0" class="{{ $kartu }}">
                    <div class="flex flex-row justify-between items-center mb-5 md:mb-8 gap-4">
                        <div>
                            <span class="font-label-caps text-label-caps text-secondary block mb-1">{{ __('RINCIAN BIAYA') }}</span>
                            <h3 class="font-headline-md text-xl md:text-headline-md text-primary">{{ __('Investasi Perjalanan') }}</h3>
                        </div>
                        <div class="px-4 py-2 bg-slate-900 rounded-lg text-on-primary">
                            <span class="font-label-caps text-[10px] uppercase tracking-wider opacity-60 block">{{ __('Musim') }}</span>
                            <p class="text-xs font-bold font-body-md">2026/2027</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 mb-6 md:mb-8">
                        <template x-for="price in package.pricingDetails" :key="price.pax">
                            <div class="flex items-center justify-between p-4 md:p-5 bg-slate-50 rounded-xl border border-slate-200 group">
                                <div class="flex items-center gap-3 md:gap-4">
                                    <div class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-secondary transition shrink-0">
                                        <span class="material-symbols-outlined text-[20px]">group</span>
                                    </div>
                                    <div>
                                        <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider mb-0.5">{{ __('Peserta') }}</p>
                                        <p class="text-sm font-semibold text-slate-900 font-body-md" x-text="price.label || (price.pax + ' ' + (AppCurrency.locale === 'en' ? 'People' : 'Orang'))"></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-label-caps text-[10px] text-on-surface-variant uppercase tracking-wider mb-0.5">{{ __('Per Orang') }}</p>
                                    <p class="text-base font-semibold text-primary font-body-md">
                                        <span x-text="AppCurrency.format(price.price || price.price_per_person || price.pricePerPerson)"></span>
                                    </p>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="p-4 md:p-6 bg-slate-50 rounded-xl border border-slate-200 flex items-start gap-3 md:gap-4">
                        <div class="w-10 h-10 rounded-lg bg-white shadow-sm flex items-center justify-center text-primary shrink-0">
                            <span class="material-symbols-outlined text-[20px]">info</span>
                        </div>
                        <div>
                            <p class="font-label-caps text-xs font-semibold text-primary mb-1 uppercase tracking-wider">{{ __('Catatan Penting') }}</p>
                            <p class="text-[13px] md:text-xs text-slate-600 font-body-md font-normal leading-relaxed">{{ __('Harga bisa berubah sesuai musim dan ketersediaan. Untuk grup besar, kami bisa bantu buat penawaran khusus.') }}</p>
                        </div>
                    </div>
                </div>

                {{-- Inclusion / Exclusion

                     Dibaca dari package.includes / package.excludes -- kolom
                     JSON yang benar-benar diisi lewat form admin. Sebelumnya
                     kedua daftar ini membaca package.package_includes dan
                     package.package_excludes, yaitu relasi yang TIDAK PERNAH
                     ikut dimuat: kuncinya bahkan tidak ada di objek package,
                     jadi x-for berjalan atas undefined dan tidak merender
                     apa-apa. Akibatnya kedua kotak ini kosong di SETIAP paket,
                     tanpa error, sementara datanya duduk lengkap di
                     package.includes. Ini justru informasi yang paling
                     menentukan orang jadi memesan atau tidak. --}}
                {{-- Isi paket itu bacaan penentu, bukan keterangan kaki. Ukuran
                     12px dengan leading-tight praktis tidak terbaca di ponsel;
                     di sana ia dinaikkan ke 15px dan diberi napas antar-baris. --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8">
                    <div class="{{ $kartu }}" x-show="(package.includes || []).length" x-cloak>
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 md:mb-6 flex items-center gap-3 font-headline-md">
                            <div class="w-9 h-9 rounded-lg bg-primary/10 text-primary flex items-center justify-center shadow-sm shrink-0">
                                <span class="material-symbols-outlined text-[20px]">check_circle</span>
                            </div>
                            {{ __('Termasuk') }}
                        </h3>
                        <ul class="space-y-3 md:space-y-4">
                            <template x-for="(item, i) in (package.includes || [])" :key="i">
                                <li class="flex items-start gap-3">
                                    <div class="mt-2 w-1.5 h-1.5 bg-primary rounded-full shrink-0 shadow-sm"></div>
                                    <span class="text-slate-700 font-medium text-[15px] md:text-xs leading-snug md:leading-tight font-body-md" x-text="item"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <div class="{{ $kartu }}" x-show="(package.excludes || []).length" x-cloak>
                        <h3 class="text-lg font-semibold text-slate-900 mb-4 md:mb-6 flex items-center gap-3 font-headline-md">
                            <div class="w-9 h-9 rounded-lg bg-red-100 text-error flex items-center justify-center shrink-0">
                                <span class="material-symbols-outlined text-[20px]">cancel</span>
                            </div>
                            {{ __('Tidak Termasuk') }}
                        </h3>
                        <ul class="space-y-3 md:space-y-4">
                            <template x-for="(item, i) in (package.excludes || [])" :key="i">
                                <li class="flex items-start gap-3">
                                    <div class="mt-2 w-1.5 h-1.5 bg-error rounded-full shrink-0"></div>
                                    <span class="text-slate-700 font-medium text-[15px] md:text-xs leading-snug md:leading-tight font-body-md" x-text="item"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>

            @include('tour.partials.package-gallery', ['foto' => $packageImagesArray, 'salesMode' => ! $showBookingForm])

            @include('tour.partials.package-media', ['salesMode' => ! $showBookingForm])

            @if(! $showBookingForm)
                @include('tour.partials.package-sales-blocks')
            @endif
            {{-- ============ ULASAN ============
                 Turun ke paling bawah. Ulasan itu penguat keputusan, bukan
                 bahan pertimbangan: tamu membacanya SESUDAH tahu isi paket dan
                 harganya, bukan di tengah membaca rencana perjalanan. Di
                 tempatnya yang lama ia memotong bacaan tepat sebelum galeri dan
                 blok penutup.

                 Bentuknya geseran mendatar, bukan tumpukan kartu: empat ulasan
                 bertumpuk memakan hampir dua layar penuh untuk hal yang tamu
                 baca sekilas. Digeser, ia cukup satu layar dan tetap utuh. --}}
            @php
                // Baris kosong dibuang. Dua dari empat testimoni di CMS tidak
                // punya nama maupun teks, dan selama ini ia tetap dirender:
                // kartu berisi tanda tanya dan sepasang tanda kutip kosong,
                // yang terbaca sebagai situs yang rusak -- bukan sebagai
                // ulasan yang belum diisi.
                $testimonials = array_values(array_filter(
                    (array) ($siteSettings['cms_tour']['testimonials'] ?? []),
                    fn ($t) => is_array($t)
                        && trim((string) ($t['name'] ?? '')) !== ''
                        && trim((string) ($t['text'] ?? '')) !== ''
                ));
            @endphp
            <div class="space-y-4 md:space-y-6 pt-8 border-t border-slate-200" id="section-reviews">
                <div class="flex items-center gap-2.5 md:gap-3">
                    <div class="w-9 h-9 md:w-12 md:h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary shrink-0">
                        <span class="material-symbols-outlined text-[18px] md:text-[24px]">grade</span>
                    </div>
                    <div class="min-w-0">
                        <h2 class="font-headline-md text-lg md:text-2xl text-primary font-bold leading-tight">{{ __('ULASAN') }}</h2>
                        <p class="text-[13px] md:text-sm text-slate-600 font-body-md">{{ __('Cerita dari mereka yang sudah bepergian bersama kami.') }}</p>
                    </div>
                </div>

                @if(count($testimonials))
                    {{-- bleed-mobile + padding tepi: kartu terakhir tidak menempel
                         mati di tepi kanan, dan kartu berikutnya mengintip sedikit
                         di tepi layar -- itu satu-satunya petunjuk bahwa daftarnya
                         masih bisa digeser. --}}
                    <div class="{{ $sales ? 'bleed-mobile' : '' }} flex gap-3 overflow-x-auto snap-x snap-mandatory overscroll-x-contain no-scrollbar pb-1 {{ $sales ? 'px-margin-mobile md:px-0' : '' }}">
                        @foreach($testimonials as $t)
                            <figure class="snap-start shrink-0 w-[85%] sm:w-[48%] md:w-[32%] p-4 md:p-6 bg-slate-50 rounded-2xl border border-slate-200">
                                <div class="flex items-center gap-3 mb-3">
                                    @if(!empty($t['image']))
                                        <img src="{{ imageUrl($t['image']) }}" loading="lazy" decoding="async" class="w-10 h-10 rounded-lg object-cover bg-slate-200 shrink-0" alt="{{ $t['name'] }}" onerror="this.style.display='none'">
                                    @else
                                        <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-base shrink-0">
                                            {{ strtoupper(mb_substr($t['name'], 0, 1)) }}
                                        </div>
                                    @endif
                                    <figcaption class="min-w-0">
                                        <p class="font-semibold text-slate-900 text-sm font-body-md truncate">{{ $t['name'] }}</p>
                                        <p class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider font-label-caps truncate">{{ __($t['location'] ?? '') }}</p>
                                    </figcaption>
                                </div>
                                <blockquote class="text-[14px] md:text-xs text-slate-600 font-body-md font-normal leading-relaxed italic">"{{ __($t['text']) }}"</blockquote>
                            </figure>
                        @endforeach
                    </div>
                @else
                    <div class="{{ $kartu }} text-center py-6">
                        <div class="w-16 h-16 bg-primary/5 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-[32px] text-primary/40">chat_bubble</span>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-2 font-headline-md">{{ __('Bagikan Pengalaman Anda') }}</h3>
                        <p class="text-slate-600 font-body-md max-w-sm mx-auto mb-6 text-sm leading-relaxed">{{ __('Sudah pernah bepergian bersama kami? Ceritamu akan sangat membantu orang lain memilih.') }}</p>
                        <a :href="'https://wa.me/' + waNumber + '?text=' + encodeURIComponent('Halo Sujai Toba Sumatera, saya ingin berbagi pengalaman wisata bersama kalian 😊')" target="_blank"
                           class="inline-flex items-center gap-2 bg-primary text-on-primary px-8 min-h-[46px] rounded-xl font-semibold text-xs uppercase tracking-wider hover:bg-primary-container transition shadow-sm">
                            <span class="material-symbols-outlined text-[18px]">chat</span>
                            {{ __('Ceritakan Perjalananmu') }}
                        </a>
                    </div>
                @endif
            </div>
            <!-- Travel Specialist & PDF CTA Row -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-8 pt-8">
                {{-- Brosur yang diunggah admin menang atas itinerary PDF yang
                     dibangkitkan sistem: kalau admin sampai menyiapkan brosur
                     sendiri untuk paket ini, itulah yang ingin ia berikan.

                     Dua tindakan, bukan satu. Sebelumnya satu tombol "Unduh"
                     berperilaku berbeda tergantung ada tidaknya brosur -- yang
                     diunggah TERBUKA di tab baru, yang dibangkitkan sistem
                     dipaksa TERUNDUH -- sementara tulisannya "Unduh" di kedua
                     keadaan. Sekarang keduanya sama: buka dulu, unduh kalau
                     memang mau disimpan.

                     Atribut download pada tautan kedua hanya berlaku untuk
                     sumber satu domain. Brosur duduk di /storage domain yang
                     sama, jadi ia benar-benar mengunduh, bukan sekadar hiasan. --}}
                @php $brochureUrl = $package->brochureUrl(); @endphp
                <div class="flex flex-col items-center justify-center p-6 md:p-8 bg-white border border-outline-variant rounded-2xl shadow-lg hover:border-secondary hover:shadow-xl transition duration-300 group text-center h-full">
                    <div class="w-12 h-12 rounded-full bg-secondary/10 flex items-center justify-center text-secondary mb-3 md:mb-4 group-hover:scale-110 transition-transform">
                        {{-- article, bukan picture_as_pdf: font ikon situs ini
                             dipangkas jadi subset, dan nama yang tidak ada di
                             dalamnya tampil sebagai teks ligatur mentah. --}}
                        <span class="material-symbols-outlined text-[24px]">article</span>
                    </div>
                    <h3 class="font-headline-md text-body-lg font-semibold text-primary mb-1">
                        {{ $brochureUrl ? __('Brosur Paket') : __('Rencana Perjalanan PDF') }}
                    </h3>
                    <p class="text-[13px] md:text-xs text-on-surface-variant font-body-md mb-4">{{ __('Dapatkan detail jadwal & informasi lengkap offline.') }}</p>

                    <div class="flex items-center gap-2 w-full">
                        <a href="{{ $brochureUrl ?? route('itinerary.download', $package->slug) }}"
                           target="_blank" rel="noopener"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 min-h-[44px] bg-primary/5 text-primary border border-primary/20 rounded-xl font-bold text-[13px] hover:bg-primary hover:text-on-primary transition-colors">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            {{ __('Lihat') }}
                        </a>
                        <a href="{{ $brochureUrl ?? route('itinerary.download', [$package->slug, 'unduh' => 1]) }}"
                           @if($brochureUrl) download @endif
                           class="flex-1 inline-flex items-center justify-center gap-1.5 min-h-[44px] bg-primary text-on-primary rounded-xl font-bold text-[13px] hover:bg-primary-container transition-colors">
                            <span class="material-symbols-outlined text-[18px]">download</span>
                            {{ __('Unduh') }}
                        </a>
                    </div>
                </div>

                <!-- Contact Specialist Card -->
                <div class="bg-white rounded-2xl p-6 md:p-8 border border-slate-200 shadow-sm relative overflow-hidden group">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-primary/5 rounded-full -mr-10 -mt-10 group-hover:scale-125 transition-transform duration-500"></div>
                    <div class="flex items-center gap-4 mb-4 relative z-10">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-primary overflow-hidden border-2 border-white shadow-md">
                            <img src="{{ imageUrl($siteSettings['cms_tour']['specialist_image_url'] ?? null, 'staff1') }}" loading="lazy" decoding="async" class="w-full h-full object-cover" onerror="this.src='{{ imageUrl('staff1') }}'">
                        </div>
                        <div>
                            <p class="font-label-caps text-[9px] font-bold text-on-surface-variant uppercase tracking-wider">{{ __($siteSettings['cms_tour']['specialist_title'] ?? 'Travel Specialist') }}</p>
                            <p class="text-sm font-bold text-primary font-body-md">{{ $siteSettings['cms_tour']['specialist_name'] ?? 'Sarah Anggraini' }}</p>
                        </div>
                    </div>
                    <p class="text-[13px] md:text-[11px] text-slate-600 font-body-md font-normal leading-relaxed mb-4 relative z-10">{{ __($siteSettings['cms_tour']['specialist_desc'] ?? 'Punya pertanyaan khusus? Kami siap bantu pilih paket yang paling pas.') }}</p>
                    <a :href="'https://wa.me/{{ \App\Helpers\ContactHelper::specialistDigits() }}?text=' + encodeURIComponent('Halo ' + ('{{ $siteSettings['cms_tour']['specialist_name'] ?? 'Sarah' }}').split(' ')[0] + ', saya tertarik bertanya tentang paket: ' + package.translated_name)" 
                       target="_blank"
                       class="flex items-center justify-center gap-1.5 min-h-[48px] md:min-h-0 md:py-2.5 bg-primary/5 text-primary rounded-xl font-semibold text-[12px] md:text-[10px] uppercase tracking-wider hover:bg-primary hover:text-on-primary transition relative z-10 border border-primary/20">
                        <span class="material-symbols-outlined text-[16px]">chat</span>
                        {{ __('Tanya Sekarang') }}
                    </a>
                </div>
            </div>



        </div>
        </div> <!-- END LEFT COLUMN WRAPPER -->

        <!-- Booking Form Sidebar (Sticky) -->
        {{-- order-2 menaruh panel ini tepat setelah hero, di TENGAH bacaan.
             Di halaman tanpa form ia dipindah ke order paling akhir: isinya
             harga + kalkulator, yang baru relevan setelah tamu selesai
             membaca, bukan sebelum. Sticky & max-h ikut dilepas -- tidak ada
             gunanya melengket kalau ia sudah jadi blok terakhir. --}}
        <div id="booking-form-sidebar" class="{{ $showBookingForm ? 'md:col-span-4 relative order-2 h-full' : 'md:col-span-12 relative order-last' }}">
            <div class="bg-white rounded-2xl shadow-md border border-slate-200 space-y-6 {{ $showBookingForm ? 'p-6 md:p-8 md:sticky md:top-28 md:max-h-[85vh] md:overflow-y-auto custom-scroll' : 'p-4 md:p-8 md:max-w-lg md:mx-auto' }}">
                @if(session('success'))
                    {{-- Pesanan yang benar-benar tercatat sekarang dialihkan ke
                         halaman pelacakan (URL permanen), jadi panel ini hanya
                         tersisa untuk balasan honeypot. Countdown 2 detik yang
                         melempar tab ke WhatsApp, beserta URL yang disuntik
                         mentah ke dalam string JS, ikut hilang bersamanya. --}}
                    <div class="py-6 px-4 bg-primary/5 rounded-2xl border border-primary/10 text-center animate-in zoom-in duration-500">
                        <div class="w-14 h-14 bg-white text-secondary rounded-full flex items-center justify-center text-2xl shadow-sm border border-secondary/20 mx-auto mb-4">
                            <span class="material-symbols-outlined text-[32px]">check_circle</span>
                        </div>
                        <h4 class="text-xl font-semibold font-headline-md text-primary mb-2">{{ __('Reservasi Terkirim') }}</h4>
                        
                        <p class="text-slate-600 font-body-md text-sm leading-relaxed">{{ __('Pesanan Anda berhasil kami catat. Tim kami akan menghubungi Anda.') }}</p>

                        <a href="{{ route('booking.track.form') }}"
                           class="mt-4 inline-flex items-center justify-center gap-1.5 text-[11px] font-semibold text-primary hover:text-secondary transition-colors">
                            <span class="material-symbols-outlined text-[16px]">travel_explore</span>
                            {{ __('Lacak status pesanan Anda') }}
                        </a>
                    </div>
                @else
                        <div class="flex justify-between items-end border-b border-slate-200 pb-4">
                        <div>
                            <span class="font-label-caps text-[10px] text-slate-500 uppercase tracking-wider">{{ __('Mulai dari') }}</span>
                            <div class="font-headline-md text-headline-md text-primary" x-text="AppCurrency.format(package.price)"></div>
                        </div>
                        @php $__rating = siteRating(); @endphp
                        @if($__rating)
                        <div class="text-right">
                            <span class="text-secondary font-semibold font-body-md">★ {{ number_format($__rating['value'], 1) }}</span>
                            @if($__rating['count'])
                            <span class="text-slate-500 text-[11px] font-body-md block">
                                @if($__rating['url'])
                                    <a href="{{ $__rating['url'] }}" target="_blank" rel="noopener" class="hover:text-secondary transition-colors">{{ number_format($__rating['count']) }} {{ __('ulasan Google') }}</a>
                                @else
                                    {{ number_format($__rating['count']) }} {{ __('ulasan') }}
                                @endif
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>

                    @if(session('error'))
                        <div class="p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg text-xs font-body-md">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(! $showBookingForm)
                        {{-- Halaman /tour/detail: tanpa form pemesanan.
                             Kalkulatornya partial yang sama dengan kartu paket,
                             jadi angkanya tidak mungkin berbeda dari yang di
                             halaman lain. Tombolnya membawa ke halaman berform,
                             tamu tidak dibuat buntu. --}}
                        @php
                            $quotePricing = is_array($package->pricingDetails ?? null) ? $package->pricingDetails : [];
                            $quoteXdata = 'paxCalc('
                                . (float) ($package->price ?? 0) . ', '
                                . \Illuminate\Support\Js::from($package->childPrice ?? null) . ', '
                                . \Illuminate\Support\Js::from($package->slug) . ', '
                                . \Illuminate\Support\Js::from(array_values($quotePricing['tiers'] ?? [])) . ', '
                                . \Illuminate\Support\Js::from($package->translated_name ?? $package->name) . ')';
                        @endphp
                        {{-- Margin negatifnya harus sama persis dengan padding
                             kotak induk, kalau tidak kalkulatornya menjorok
                             keluar kartu. Induk: p-4 di ponsel, p-8 mulai md. --}}
                        <div class="-mx-4 md:-mx-8">
                            @include('partials.pax-calc', [
                                'xdata' => $quoteXdata,
                                'priceImage' => \Illuminate\Support\Js::from($package->price_image_url ?? null),
                            ])
                        </div>
                        <p class="text-center text-[11px] text-slate-500 font-body-md">{{ __('Konfirmasi cepat tersedia untuk tanggal terpilih.') }}</p>
                    @else
                    <form id="booking-form" action="{{ route('tour.booking.submit') }}" method="POST" class="space-y-5" @submit="isSubmitting = true">
                        @csrf
                        <input type="hidden" name="packageId" :value="package.id">
                        <input type="hidden" name="slug" :value="package.slug">
                        <input type="hidden" name="notes" :value="serializedNotes">
                        {{-- paxChildren dikirim oleh input angka yang terlihat di bawah; hidden
                             duplikat dihapus (dulu mengirim field yang sama dua kali). --}}
                        <template x-for="(service, idx) in services.filter(s => Number(s.qty) > 0)" :key="idx">
                            <span>
                                <input type="hidden" name="selected_services[]" :value="service.name">
                                {{-- Jumlah peserta dikirim terpisah, berkunci nama layanan.
                                     Server tetap membatasinya ke jumlah tamu -- form boleh
                                     mengirim angka apa pun, harga tidak boleh ikut. --}}
                                <input type="hidden" :name="'service_qty[' + service.name + ']'" :value="service.qty">
                            </span>
                        </template>
                        {{-- Dikirim hanya bila benar-benar berlaku. Server memeriksa
                             ambang pax-nya lagi, jadi ini sekadar niat tamu. --}}
                        <input type="hidden" name="use_van" :value="vanAktif ? 1 : 0">
                        
                        <!-- Nama Lengkap -->
                        <div>
                            <label for="bk-customerName" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Nama lengkap') }} <span class="text-red-500">*</span></label>
                            <input type="text" id="bk-customerName" name="customerName" x-model="customerName" required placeholder="{{ __('Nama sesuai identitas') }}" autocomplete="name"
                                class="w-full border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition">
                            @error('customerName') <span class="text-xs text-error font-body-md mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Kolom email dihapus. Pasarnya berjalan lewat
                             WhatsApp dan email tidak pernah dipakai menghubungi
                             tamu -- notifikasi booking hanya ke admin. Pengenal
                             pelanggan kini nomor telepon (Customer::phoneKey),
                             jadi tamu tanpa email tidak lagi bertabrakan jadi
                             satu baris pelanggan yang sama. --}}
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label for="bk-customerPhone" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Nomor WhatsApp') }} <span class="text-red-500">*</span></label>
                                <input type="tel" id="bk-customerPhone" name="customerPhone" x-model="customerPhone" required placeholder="{{ __('0812-xxxx-xxxx') }}" autocomplete="tel" inputmode="tel"
                                    pattern="[\+0-9\-\s]+" minlength="9" maxlength="15" title="Hanya angka, spasi, atau tanda tambah (+)"
                                    class="w-full border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition">
                                @error('customerPhone') <span class="text-xs text-error font-body-md mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Tanggal Keberangkatan -->
                        <div>
                            <label for="bk-startDate" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Pilih tanggal') }} <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="date" id="bk-startDate" name="startDate" x-model="startDate" required
                                    min="{{ now()->addDays((int) (optional(\App\Models\Setting::where('key','booking_settings')->first())->value['min_advance_days'] ?? 1))->format('Y-m-d') }}"
                                    class="w-full border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition uppercase">
                            </div>
                            @error('startDate') <span class="text-xs text-error font-body-md mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Harga Bertingkat -->
                        <template x-if="pkgTiers && pkgTiers.length > 0">
                            <div class="mb-4 bg-primary-container/20 rounded-xl p-4 border border-primary/20">
                                <h3 class="text-sm font-semibold text-on-surface mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-primary text-[18px]">group</span>
                                    {{ __('Harga Khusus (Lebih Banyak Lebih Murah!)') }}
                                </h3>
                                <div class="grid gap-2">
                                    <template x-for="(tier, idx) in pkgTiers" :key="idx">
                                        <div class="flex justify-between items-center text-xs md:text-sm" :class="pax >= tier.min_pax && (pax <= tier.max_pax || (idx === pkgTiers.length - 1 && pax > tier.max_pax)) ? 'font-bold text-primary bg-white p-1 rounded px-2 -mx-2 shadow-sm' : 'text-slate-600'">
                                            <span>
                                                <span x-text="tier.min_pax"></span>
                                                <template x-if="tier.max_pax > tier.min_pax">
                                                    <span> - <span x-text="tier.max_pax"></span></span>
                                                </template>
                                                <template x-if="tier.min_pax === tier.max_pax">
                                                    <span></span>
                                                </template>
                                                {{ __('Pax') }}
                                            </span>
                                            <span x-text="AppCurrency.format(tier.price) + ' / pax'"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Pax Dewasa & Anak -->
                        <!-- Input Pax Dewasa & Anak -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="bk-pax" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Tamu dewasa') }} <span class="text-red-500">*</span></label>
                                <div class="relative flex items-center">
                                    <button type="button" @click="if(pax > 1) pax--" aria-label="{{ __('Kurangi tamu dewasa') }}" class="absolute left-0 top-0 bottom-0 px-4 text-gray-500 hover:bg-gray-100 rounded-l-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2"><span class="material-symbols-outlined text-[16px]">remove</span></button>
                                    <input type="number" id="bk-pax" name="pax" x-model.number="pax" required min="1" max="99" class="w-full text-center border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition hide-arrows">
                                    <button type="button" @click="pax++" class="absolute right-0 top-0 bottom-0 px-4 text-gray-500 hover:bg-gray-100 rounded-r-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2"><span class="material-symbols-outlined text-[16px]">add</span></button>
                                </div>
                                <template x-if="pkgTiers && pkgTiers.length > 0">
                                    <p class="text-[10px] text-primary mt-1 font-semibold" x-text="`${AppCurrency.format(currentUnitPrice)} / pax`"></p>
                                </template>
                                @error('pax') <span class="text-xs text-error font-body-md mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="bk-paxChildren" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Anak-anak') }} <span class="normal-case tracking-normal text-[10px] text-slate-500">({{ __('50% harga dewasa') }})</span></label>
                                <div class="relative flex items-center">
                                    <button type="button" @click="if(paxChildren > 0) paxChildren--" aria-label="{{ __('Kurangi anak-anak') }}" class="absolute left-0 top-0 bottom-0 px-4 text-gray-500 hover:bg-gray-100 rounded-l-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2"><span class="material-symbols-outlined text-[16px]">remove</span></button>
                                    <input type="number" id="bk-paxChildren" name="paxChildren" x-model.number="paxChildren" min="0" max="99" class="w-full text-center border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition hide-arrows">
                                    <button type="button" @click="paxChildren++" class="absolute right-0 top-0 bottom-0 px-4 text-gray-500 hover:bg-gray-100 rounded-r-lg transition focus:outline-none focus-visible:ring-2 focus-visible:ring-toba-green focus-visible:ring-offset-2"><span class="material-symbols-outlined text-[16px]">add</span></button>
                                </div>
                            </div>
                        </div>

                        <!-- Pilihan Kendaraan: muncul hanya setelah rombongan
                             mencapai ambang. Ini tarif per pax PENGGANTI, jadi
                             tempatnya di atas layanan tambahan, bukan di dalamnya. -->
                        <div class="space-y-2" x-show="vanTersedia" x-cloak>
                            <label class="font-label-caps text-label-caps text-slate-700 mb-1 block uppercase tracking-wider">{{ __('Kendaraan') }}</label>
                            {{-- Begitu kursi Innova habis, pilihannya bukan pilihan lagi.
                                 Barisnya disembunyikan, bukan sekadar dinonaktifkan:
                                 menampilkan opsi yang tidak bisa dipakai hanya membuat
                                 tamu mengira harganya bisa ditawar turun. --}}
                            <p x-show="vanWajib" x-cloak class="text-[11px] text-on-surface-variant font-body-md -mt-1 mb-1">
                                {{ __('Rombongan Anda melebihi kapasitas Innova, jadi perjalanan ini memakai Van.') }}
                            </p>
                            <label x-show="!vanWajib" class="flex items-center justify-between p-3 border border-outline-variant rounded-lg cursor-pointer hover:border-secondary transition"
                                   :class="!useVan ? 'border-secondary bg-secondary/5' : ''">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-secondary text-[22px]">directions_car</span>
                                    <div>
                                        <div class="font-body-md font-semibold text-slate-900 text-xs">{{ __('Innova (standar)') }}</div>
                                        <div class="text-[10px] text-on-surface-variant font-body-md"><span x-text="AppCurrency.format(activeTier && activeTier.price != null ? activeTier.price : package.price)"></span>/{{ __('orang') }}</div>
                                    </div>
                                </div>
                                <input type="radio" name="pilih_kendaraan" :checked="!useVan" @change="useVan = false" class="w-4 h-4 text-secondary border-outline-variant focus:ring-0">
                            </label>
                            <label class="flex items-center justify-between p-3 border border-outline-variant rounded-lg cursor-pointer hover:border-secondary transition"
                                   :class="useVan ? 'border-secondary bg-secondary/5' : ''">
                                <div class="flex items-center gap-3">
                                    <span class="material-symbols-outlined text-secondary text-[22px]">airport_shuttle</span>
                                    <div>
                                        <div class="font-body-md font-semibold text-slate-900 text-xs" x-text="(pkgVehicle && pkgVehicle.name) || 'Van'"></div>
                                        <div class="text-[10px] text-on-surface-variant font-body-md">
                                            <span x-text="AppCurrency.format(currentUnitPrice)"></span>/{{ __('orang') }}
                                            <span x-show="!vanWajib"> &middot; {{ __('bagasi lebih lega') }}</span>
                                            <span x-show="vanWajib" class="text-secondary font-semibold"> &middot; {{ __('wajib') }}</span>
                                        </div>
                                    </div>
                                </div>
                                {{-- Saat wajib, radionya diganti ikon centang mati: tidak ada
                                     yang bisa dipilih, jadi jangan menampilkan kontrol yang
                                     seolah bisa diklik. --}}
                                <input x-show="!vanWajib" type="radio" name="pilih_kendaraan" :checked="useVan" @change="useVan = true" class="w-4 h-4 text-secondary border-outline-variant focus:ring-0">
                                <span x-show="vanWajib" x-cloak class="material-symbols-outlined text-secondary text-[20px]">check_circle</span>
                            </label>
                        </div>

                        <!-- Layanan Tambahan -->
                        <div class="space-y-3" x-show="services && services.length > 0">
                            <label class="font-label-caps text-label-caps text-slate-700 mb-1 block uppercase tracking-wider">{{ __('Layanan tambahan') }}</label>
                            
                            <template x-for="(service, idx) in services" :key="idx">
                                {{-- Penghitung, bukan centangan. Layanan ini bertarif per
                                     orang dan tidak selalu diikuti seluruh rombongan --
                                     Hiking Sibayak sering cuma sebagian yang naik. --}}
                                <div class="flex items-center justify-between p-3 border border-outline-variant rounded-lg transition" :class="Number(service.qty) > 0 ? 'border-secondary bg-secondary/5' : ''">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <span class="material-symbols-outlined text-secondary text-[22px]" x-text="service.icon || 'help'"></span>
                                        <div class="min-w-0">
                                            <div class="font-body-md font-semibold text-slate-900 text-xs" x-text="service.name"></div>
                                            <div class="text-[10px] text-on-surface-variant font-body-md">
                                                <span x-text="AppCurrency.format(service.price)"></span>/{{ __('orang') }}
                                                <span x-show="Number(service.qty) > 0"> &middot; <span x-text="AppCurrency.format(service.price * service.qty)"></span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" @click="turunLayanan(service)" :disabled="Number(service.qty) === 0"
                                                :aria-label="'{{ __('Kurangi') }} ' + service.name"
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-secondary hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition disabled:opacity-30 disabled:hover:bg-slate-100 disabled:hover:text-slate-600">&minus;</button>
                                        <span class="w-7 text-center text-[13px] font-bold text-slate-800" x-text="Number(service.qty) || 0"></span>
                                        <button type="button" @click="naikLayanan(service)" :disabled="Number(service.qty) >= jiwaTotal"
                                                :aria-label="'{{ __('Tambah') }} ' + service.name"
                                                class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-secondary hover:text-white text-slate-600 flex items-center justify-center text-base font-bold leading-none transition disabled:opacity-30 disabled:hover:bg-slate-100 disabled:hover:text-slate-600">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Catatan User -->
                        <div>
                            <label for="bk-notes" class="font-label-caps text-label-caps text-slate-700 mb-2 block uppercase tracking-wider">{{ __('Catatan tambahan') }} <span class="text-[9px] text-slate-500">({{ __('Opsional') }})</span></label>
                            <textarea id="bk-notes" x-model="notesUser" placeholder="{{ __('Permintaan khusus, hotel, alergi, penjemputan, dll.') }}" rows="2"
                                class="w-full border border-outline-variant rounded-lg p-3 text-sm text-on-surface bg-background focus:ring-1 focus:ring-secondary focus:border-secondary outline-none font-body-md transition resize-none"></textarea>
                        </div>

                        <!-- Real-time Pricing Summary Card -->
                        <div class="bg-slate-50 p-4 rounded-lg space-y-2 border border-slate-200">
                            <div class="flex justify-between text-xs text-slate-600 font-body-md">
                                <span>{{ __('Ekspedisi Dewasa') }} (<span x-text="pax"></span>x)</span>
                                <span x-text="AppCurrency.format(priceDewasa)"></span>
                            </div>
                            <div x-show="paxChildren > 0" class="flex justify-between text-xs text-slate-600 font-body-md">
                                <span>{{ __('Ekspedisi Anak-Anak') }} (<span x-text="paxChildren"></span>x)</span>
                                <span x-text="AppCurrency.format(priceAnak)"></span>
                            </div>
                            <template x-for="(service, idx) in services" :key="idx">
                                <div x-show="Number(service.qty) > 0" class="flex justify-between text-xs text-slate-600 font-body-md">
                                    <span><span x-text="service.name"></span> (<span x-text="service.qty"></span>x)</span>
                                    <span x-text="AppCurrency.format(parseFloat(service.price || 0) * (Number(service.qty) || 0))"></span>
                                </div>
                            </template>
                            <template x-for="(item, idx) in surchargeItems" :key="idx">
                                <div class="flex justify-between text-xs text-slate-600 font-body-md">
                                    <span x-text="item.label"></span>
                                    <span x-text="AppCurrency.format(item.amount)"></span>
                                </div>
                            </template>
                            <div class="flex justify-between text-xs text-slate-600 font-body-md">
                                <span>{{ __('Pajak & Layanan') }} (<span x-text="taxPercentage"></span>%)</span>
                                <span x-text="AppCurrency.format(pajakLayanan)"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200 flex justify-between font-semibold text-primary text-base font-body-md transition duration-300 origin-right"
                                 :class="totalChanged ? 'scale-[1.03] text-secondary font-bold' : ''">
                                <span>Total Ringkasan</span>
                                <span x-text="AppCurrency.format(totalAkhir)"></span>
                            </div>
                        </div>

                        <!-- Honeypot Field -->
                        <div style="position: absolute; left: -5000px;" aria-hidden="true">
                            <label for="website_url">Tinggalkan kolom ini kosong jika Anda manusia</label>
                            <input type="text" name="website_url" id="website_url" value="" autocomplete="off" tabindex="-1">
                        </div>

                        {{-- Persetujuan S&K + Kebijakan Privasi.

                             Centangnya dihapus atas permintaan pemilik: satu
                             tindakan lagi sebelum memesan. Persetujuannya tidak
                             ikut hilang -- ia jadi pemberitahuan tepat di atas
                             tombol kirim, pola yang lazim dipakai dan tetap
                             merupakan tindakan afirmatif (menekan tombolnya).
                             Relevan UU PDP 27/2022 dan PDPA untuk tamu SG/MY. --}}
                        <p class="text-[11px] text-slate-600 font-body-md leading-relaxed">
                            {!! __('Dengan menekan :button, Anda menyetujui :terms dan :privacy, termasuk kebijakan pembatalan & pengembalian dana.', [
                                'button' => '<strong>'.__('Pesan Sekarang').'</strong>',
                                'terms' => '<a href="'.route('terms').'" target="_blank" rel="noopener" class="text-secondary font-semibold underline">'.__('Syarat & Ketentuan').'</a>',
                                'privacy' => '<a href="'.route('privacy').'" target="_blank" rel="noopener" class="text-secondary font-semibold underline">'.__('Kebijakan Privasi').'</a>',
                            ]) !!}
                        </p>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="isSubmitting"
                            :class="isSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                            class="w-full bg-primary text-on-primary py-4 rounded-lg font-semibold text-xs uppercase tracking-wider hover:bg-primary-container transition duration-300 shadow-sm flex items-center justify-center gap-2"
                        >
                            <span x-show="!isSubmitting" class="flex items-center justify-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                                {{ __('Pesan Sekarang') }}
                            </span>
                            <span x-show="isSubmitting" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                {{ __('Mengirim...') }}
                            </span>
                        </button>
                        <p class="text-center text-[11px] text-slate-500 font-body-md">{{ __('Konfirmasi cepat tersedia untuk tanggal terpilih.') }}</p>
                        <p class="text-center text-[9px] text-slate-400 font-body-md mt-1 leading-normal">
                            * {{ __('Data Anda akan disimpan di sistem kami. Anda akan diarahkan ke WhatsApp untuk melakukan konfirmasi cepat.') }}
                        </p>
                    </form>
                    @endif
                @endif
            </div>
        </div>


        {{-- ============ MINI FAQ PERTANYAAN UMUM ============ --}}
        <div class="md:col-span-12 py-8 border-t border-slate-200">
            <div class="max-w-3xl mx-auto space-y-6" x-data="{ openFaq: null }">
                <div class="text-center">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-toba-green uppercase tracking-[0.14em] mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-toba-green"></span>
                        {{ __('Informasi Penting') }}
                    </span>
                    <h2 class="font-headline-md text-xl md:text-3xl font-bold text-primary">{{ __('Pertanyaan Yang Sering Ditanyakan') }}</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1.5">{{ __('Hal mendasar yang perlu Anda ketahui sebelum memesan paket wisata ini.') }}</p>
                </div>

                <div class="space-y-3">
                    @php
                        $packageFaqs = [
                            [
                                'q' => 'Apakah makanan selama tour terjamin Halal 100%?',
                                'a' => 'Ya, seluruh restoran dan rumah makan yang kami pilihkan untuk tamu beragama Islam (termasuk wisatawan dari Malaysia/Singapura) adalah restoran bersertifikat Halal atau ramah muslim terpercaya (tidak menyajikan babi/minuman beralkohol).'
                            ],
                            [
                                'q' => 'Bagaimana jika penerbangan kami tiba terlambat (delay) di bandara?',
                                'a' => 'Tidak perlu khawatir. Driver dan pemandu kami selalu memantau nomor penerbangan Anda dan tetap siap menunggu di pintu kedatangan (Bandara Kualanamu KNO maupun Silangit DTB) tanpa biaya tambahan keterlambatan.'
                            ],
                            [
                                'q' => 'Berapa persen uang muka (DP) dan bagaimana sistem pembayarannya?',
                                'a' => 'Cukup membayar DP 30% untuk mengunci tanggal, armada mobil, dan reservasi hotel. Sisa pelunasan 70% dapat dibayarkan saat tiba di Medan/Danau Toba secara tunai atau transfer (mendukung Bank Malaysia via Wise/CIMB/Maybank maupun QRIS).'
                            ],
                            [
                                'q' => 'Apakah itinerary bisa disesuaikan jika membawa anak kecil atau lansia?',
                                'a' => 'Tentu bisa! Ini adalah Private Tour (mobil khusus keluarga Anda sendiri), sehingga ritme perjalanan sangat fleksibel. Anda bebas meminta istirahat lebih sering, melewati destinasi tangga curam, atau menambah spot kuliner santai.'
                            ],
                        ];
                    @endphp

                    @foreach($packageFaqs as $fIdx => $faqItem)
                    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-2xs transition-all hover:border-green-200">
                        <button type="button" @click="openFaq = openFaq === {{ $fIdx }} ? null : {{ $fIdx }}"
                                class="w-full flex items-center justify-between p-4 md:p-5 text-left bg-slate-50/60 hover:bg-slate-50 transition cursor-pointer select-none">
                            <span class="text-xs md:text-sm font-bold text-slate-800 pr-4 leading-snug">{{ __($faqItem['q']) }}</span>
                            <div class="w-7 h-7 rounded-full bg-white border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 transition-transform duration-300"
                                 :class="openFaq === {{ $fIdx }} && 'rotate-180 bg-green-50 text-green-700 border-green-200'">
                                <span class="material-symbols-outlined text-[16px]">expand_more</span>
                            </div>
                        </button>
                        <div x-show="openFaq === {{ $fIdx }}" x-collapse
                             class="p-4 md:p-5 border-t border-slate-100 text-xs md:text-sm text-slate-600 leading-relaxed bg-white">
                            {{ __($faqItem['a']) }}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- CTA penutup. Dulu ia di dalam package-sales-blocks dan jatuh di
             TENGAH halaman: ajakan menutup yang muncul sebelum tamu melihat
             rincian harga, ulasan, dan brosur. Sekarang ia benar-benar
             menutup -- item grid terakhir, sesudah kartu harga. --}}
        @if(! $showBookingForm)
        <div class="md:col-span-12 order-last">
    {{-- ============ CTA WHATSAPP PENUTUP ============ --}}
    @php
        $ctaPesan = __('Halo, saya berminat dengan paket *:name*.', ['name' => $package->translated_name ?? $package->name])
            ."\n".url()->current()
            ."\n\n".__('Boleh minta info lebih lanjut?');
    @endphp
    <div class="pt-8">
        <div class="bleed-mobile bg-primary text-on-primary md:rounded-[1.75rem] px-margin-mobile py-6 md:p-10 text-center">
            <h2 class="font-headline-md text-lg md:text-2xl font-semibold uppercase tracking-[0.12em] md:tracking-normal md:normal-case mb-2">{{ __('Masih Ada Pertanyaan?') }}</h2>
            <p class="text-sm opacity-80 font-body-md max-w-md mx-auto mb-6 leading-relaxed">
                {{ __('Ceritakan rencana Anda — tanggal, jumlah orang, dan hal yang ingin dilihat. Kami susunkan yang paling masuk akal.') }}
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="https://wa.me/{{ \App\Helpers\ContactHelper::whatsappDigits() }}?text={{ rawurlencode($ctaPesan) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-white text-primary px-8 min-h-[52px] rounded-full font-bold text-[15px] hover:bg-secondary hover:text-white transition-colors duration-300">
                    <x-icon name="whatsapp" class="w-4 h-4" />
                    {{ __('Tanya lewat WhatsApp') }}
                </a>
                {{-- Jalur memesan sendiri tetap dibuka: tidak semua tamu mau
                     mengobrol dulu, dan halaman ini tidak punya form. --}}
                <a href="{{ route('tour.package.detail', $package->slug) }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 border border-white/30 px-8 min-h-[52px] rounded-full font-bold text-[15px] hover:bg-white/10 transition-colors duration-300">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    {{ __('Pesan Sekarang') }}
                </a>
            </div>
        </div>
    </div>
        </div>
        @endif

    </section>

    @if(! $showBookingForm)
        {{-- Batang lengket khusus mobile di halaman tanpa form.
             Concierge bar di bawah ini `hidden md:flex` -- di ponsel halaman
             ini sama sekali tidak punya ajakan yang selalu terlihat, padahal
             di sinilah mayoritas tamunya membaca. Halaman rujukan pun hanya
             menaruh WhatsApp di kaki halaman: tamu yang berhenti membaca di
             tengah tidak pernah sampai ke sana. --}}
        <div class="md:hidden fixed inset-x-0 bottom-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200 shadow-xl"
             style="padding-bottom: env(safe-area-inset-bottom);">
            <div class="flex items-center gap-2.5 px-4 py-2.5">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-1 text-[9px] font-bold text-green-700 uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-600 animate-pulse"></span>
                        <span>{{ __('Respon Cepat') }}</span>
                    </div>
                    <p class="text-[15px] font-extrabold text-primary leading-tight truncate" x-text="AppCurrency.format(package.price) + ' / pax'"></p>
                </div>
                {{-- URL-nya dirakit di sisi server, bukan di dalam atribut
                     Alpine: teks terjemahannya mengandung apostrof (mis. "I'm
                     interested"), dan itu memutus string JS di dalam atribut. --}}
                @php
                    $barPesan = __('Halo, saya berminat dengan paket *:name*.', ['name' => $package->translated_name ?? $package->name])
                        ."\n".url()->current();
                @endphp
                <a href="https://wa.me/{{ \App\Helpers\ContactHelper::whatsappDigits() }}?text={{ rawurlencode($barPesan) }}"
                   target="_blank" rel="noopener noreferrer"
                   aria-label="{{ __('Tanya lewat WhatsApp') }}"
                   class="shrink-0 w-11 h-11 rounded-xl bg-green-50 border border-green-200 text-green-700 flex items-center justify-center active:scale-95 transition-transform"
                   title="{{ __('Chat WhatsApp') }}">
                    <x-icon name="whatsapp" class="w-5 h-5" />
                </a>
                <a href="{{ route('tour.package.detail', $package->slug) }}"
                   class="shrink-0 inline-flex items-center justify-center gap-1.5 min-h-[44px] bg-toba-green text-white px-5 rounded-xl font-bold text-[13px] active:scale-95 transition-transform shadow-md">
                    <span class="material-symbols-outlined text-[17px]">calendar_month</span>
                    {{ __('Pesan') }}
                </a>
            </div>
        </div>
    @endif

    <!-- Floating Concierge Bar -->
    <div 
        x-show="showConcierge" 
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0 translate-y-12 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-12 scale-95"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 w-[90%] md:w-auto glass-card border border-slate-200 rounded-2xl px-5 md:px-8 py-3 z-50 hidden md:flex items-center justify-between gap-4 md:gap-12 shadow-lg transition duration-500 transform"
        style="display: none;"
    >
        <div class="hidden md:flex items-center gap-2">
            <span class="material-symbols-outlined text-secondary" style="font-variation-settings: 'FILL' 1;">support_agent</span>
            <span class="text-on-surface text-body-md font-semibold font-body-md">{{ __('Butuh bantuan? Hubungi kami.') }}</span>
        </div>
        <div class="flex items-center gap-4 w-full md:w-auto justify-between">
            <a 
                :href="'https://wa.me/' + waNumber + '?text=' + encodeURIComponent('Halo Sujai Toba Sumatera, saya ingin bertanya tentang paket: *' + package.name + '*') "
                target="_blank"
                class="bg-white text-secondary border border-slate-200 px-4 md:px-6 py-2 rounded-full font-semibold text-xs hover:bg-slate-50 transition-colors"
            >
                Chat
            </a>
            <button 
                @click="document.getElementById('booking-form-sidebar').scrollIntoView({ behavior: 'smooth' })"
                class="bg-primary text-on-primary px-6 md:px-8 py-2 rounded-full font-semibold text-xs hover:bg-primary-container transition-colors"
            >
                Pesan
            </button>
        </div>
    </div>
</div>

@push('scripts')
@if($errors->any() || session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const form = document.getElementById('booking-form');
            if (form) {
                form.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 500);
    });
</script>
@endif
@endpush

<style>
    .glass-card {
        background: rgba(252, 249, 248, 0.85);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }
    .ken-burns {
        animation: kenburns 25s infinite alternate ease-in-out;
    }
    @keyframes kenburns {
        0% { transform: scale(1); }
        100% { transform: scale(1.08); }
    }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection

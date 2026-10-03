@extends('layouts.app')

@section('title', 'Paket Wisata Sumatera Utara – Sujai Toba Sumatera')
@section('description', 'Pilihan paket wisata Danau Toba terbaik mulai dari private tour, group gathering, hingga corporate outing dengan layanan premium.')
@section('keywords', 'paket wisata murah danau toba, private tour danau toba, paket gathering medan, harga paket wisata toba')

@push('schema')
@php
    $itemListElements = [];
    foreach ($packages as $idx => $pkg) {
        $pkgImg  = $pkg->resolveImageUrl($pkg->packageImages->first()?->image_path ?? ($pkg->images[0] ?? null));
        $pkgUrl  = route('tour.package.detail', ['slug' => $pkg->slug ?? $pkg->id]);
        $pkgPrice = $pkg->price ?? 0;
        $itemListElements[] = [
            '@type'    => 'ListItem',
            'position' => $idx + 1,
            'item'     => [
                '@type'       => 'TouristTrip',
                '@id'         => $pkgUrl,
                'name'        => $pkg->translated_name,
                'description' => Str::limit(strip_tags($pkg->translated_description ?? ''), 160),
                'url'         => $pkgUrl,
                'image'       => $pkgImg,
                'touristType' => ['Family', 'Couple', 'Group'],
                'offers'      => [
                    '@type'        => 'Offer',
                    'price'        => (string) $pkgPrice,
                    'priceCurrency'=> \App\Helpers\CurrencyHelper::PRICE_BASE,
                    'availability' => 'https://schema.org/InStock',
                    'seller'       => ['@type' => 'TravelAgency', 'name' => 'Sujai Toba Sumatera'],
                ],
            ],
        ];
    }
    $schemaData = [
        '@context'     => 'https://schema.org',
        '@type'        => 'ItemList',
        'name'         => 'Paket Wisata Sumatera Utara – Sujai Toba Sumatera',
        'description'  => 'Pilihan lengkap paket wisata premium Danau Toba, Samosir, Berastagi, Tangkahan, dan seluruh Sumatera Utara.',
        'url'          => url()->current(),
        'numberOfItems'=> count($packages),
        'itemListElement' => $itemListElements,
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
@endpush

@section('content')
{{-- Grid ini dulu dirender di sisi klien: seluruh koleksi paket disalin utuh ke
     dalam atribut x-data (~50 KB, sepertiga berat halaman) lalu digambar oleh
     x-for. Dua akibatnya berat.

     Pertama, isi <template x-for> TIDAK ADA di HTML. Halaman paket -- halaman
     jualan utama situs ini -- datang kosong bagi apa pun yang tidak menjalankan
     JavaScript: pratinjau tautan WhatsApp, perayap selain Google, dan tamu yang
     skripnya gagal termuat di sinyal buruk.

     Kedua, muatan itu memuat SELURUH kolom setiap paket -- deskripsi panjang,
     terjemahan, daftar gambar, meta SEO -- padahal kartu cuma memakai segelintir
     di antaranya. Tamu ponsel membayar seluruhnya.

     Sekarang dicetak server dengan @foreach. Alpine tetap dipakai untuk yang
     memang butuh interaksi (akordeon detail & kalkulator pax), dan hanya data
     yang benar-benar dipakai keduanya yang ikut diserialisasi. --}}
<div class="min-h-screen flex flex-col bg-slate-50">
    <main class="flex-grow">
        <!-- Hero Section -->
        <div class="relative h-[55dvh] min-h-[320px] flex items-end overflow-hidden">
            @php
                $heroImg = (count($packages) > 0 && count($packages[0]->packageImages) > 0) 
                    ? imageUrl($packages[0]->packageImages[0]->image_path) 
                    : imageUrl('sumatra-panorama');
            @endphp
            <img src="{{ $heroImg }}" alt="Packages Hero" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high" decoding="async">
            <div class="absolute inset-0 bg-gradient-to-b from-slate-900/40 via-slate-900/50 to-slate-50"></div>
            <div class="relative z-10 w-full max-w-7xl mx-auto px-5 md:px-8 pb-6 md:pb-8">
                <div class="animate-in fade-in slide-in-from-bottom-8 duration-1000">
                    <x-breadcrumb :dark="true" class="mb-4" :items="[
                        ['label' => __('Paket Wisata')],
                    ]" />
                    <span class="inline-flex items-center gap-2 px-3 py-1 bg-toba-green/20 backdrop-blur-md border border-white/10 text-white text-[10px] font-semibold uppercase tracking-[0.2em] rounded-full mb-4">Eksplorasi Indonesia & Dunia</span>
                    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-white tracking-tight leading-[1.1]">
                        Paket Wisata <span class="text-toba-green">Pilihan Terbaik</span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- Results Grid -->
        <div class="max-w-7xl mx-auto px-5 md:px-8 mt-8 md:mt-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 mb-0.5">Menampilkan Hasil</h2>
                    <p class="text-slate-500 font-normal text-xs">Ditemukan <span class="text-toba-green font-bold">{{ count($packages) }}</span> paket wisata</p>
                </div>
            </div>

            {{-- items-start: tiap kartu setinggi isinya sendiri. Dengan
                 align-items: stretch bawaan, seluruh kartu dipaksa setinggi
                 yang tertinggi -- begitu satu akordeon dibuka, kartu itu jadi
                 yang tertinggi dan SEMUA tetangganya ikut melar mengikuti,
                 isinya menggantung di ruang kosong. Tinggi kartu tertutup
                 tetap seragam karena judul dan deskripsinya sudah dibatasi
                 line-clamp. --}}
            @php $__pkgRating = siteRating(); @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 items-start">
                @foreach($packages as $i => $pkg)
                    @php
                        $pkgSlug = $pkg->slug ?? $pkg->id;

                        // Urutan yang sama persis dengan versi Alpine-nya: kota dari
                        // relasi many-to-many dulu, baru cityId tunggal, baru cadangan.
                        $pkgLokasi = $pkg->cities->count() > 0
                            ? $pkg->cities->pluck('name')->join(', ')
                            : (optional($cities->firstWhere('id', $pkg->cityId))->name ?? 'Sumatera Utara');

                        $pkgPricing = is_array($pkg->pricingDetails ?? null) ? $pkg->pricingDetails : [];
                        $pkgPaxXdata = 'paxCalc('
                            . (float) ($pkg->price ?? 0) . ', '
                            . \Illuminate\Support\Js::from($pkg->childPrice ?? null) . ', '
                            . \Illuminate\Support\Js::from($pkgSlug) . ', '
                            . \Illuminate\Support\Js::from(array_values($pkgPricing['tiers'] ?? [])) . ', '
                            . \Illuminate\Support\Js::from($pkg->translated_name ?? $pkg->name) . ')';
                        $pkgDetailsXdata = 'pkgDetails('
                            . \Illuminate\Support\Js::from(array_values((array) ($pkg->includes ?? []))) . ', '
                            . \Illuminate\Support\Js::from(array_values((array) ($pkg->excludes ?? []))) . ', '
                            . \Illuminate\Support\Js::from(array_values((array) ($pkg->itinerary ?? []))) . ')';
                    @endphp
                    <div class="animate-in fade-in slide-in-from-bottom-12 duration-1000" style="animation-delay: {{ $i * 100 }}ms">
                        <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 hover:border-slate-200 transition-colors duration-300 group h-full flex flex-col shadow-sm">
                            {{-- Bayangan penanda muat (shimmer) dilepas bersama render
                                 kliennya. Ia menyembunyikan gambar sampai peristiwa load
                                 menyala; dengan src yang sudah ada di HTML, gambar yang
                                 sudah tersimpan di cache sering selesai dimuat SEBELUM
                                 Alpine sempat memasang penyimaknya -- peristiwanya lewat,
                                 dan gambarnya tinggal permanen tak terlihat. --}}
                            <div class="relative aspect-[4/3] overflow-hidden shrink-0 bg-slate-100">
                                <a href="/tour/detail/{{ $pkgSlug }}" class="block w-full h-full"
                                   aria-label="{{ $pkg->translated_name }}">
                                    {{-- sizes: kartunya selebar layar di ponsel, separuh
                                         di tablet, sepertiga kolom 1280px di desktop
                                         (~380px). Tanpa srcset, tiap kartu menarik gambar
                                         ukuran penuh -- halaman ini dulu mengunduh 1 MB
                                         gambar untuk delapan kotak selebar 380px. --}}
                                    <img src="{{ $pkg->first_image }}" alt="{{ $pkg->translated_name }}"
                                         @if($pkgSrcset = imageSrcset($pkg->first_image))
                                             srcset="{{ $pkgSrcset }}"
                                             sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 400px"
                                         @endif
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-[1.5s]"
                                         loading="{{ $i < 3 ? 'eager' : 'lazy' }}"
                                         fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}"
                                         decoding="async">
                                </a>

                                <div class="absolute top-4 left-4 flex flex-col space-y-1.5">
                                    @if($__pkgRating)
                                    <div class="bg-white/95 backdrop-blur-md px-2.5 py-1 rounded-lg flex items-center space-x-1 border border-slate-100 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-star text-amber-400 fill-amber-400"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"></path></svg>
                                        <span class="font-bold text-slate-800 text-[10px] tracking-wider">{{ number_format($__pkgRating['value'], 1) }}</span>
                                    </div>
                                    @endif
                                    @if(trim((string) $pkg->duration) !== '')
                                    <div class="bg-slate-950 text-white px-2.5 py-1 rounded-lg text-[9px] font-semibold uppercase tracking-wider">{{ $pkg->duration }}</div>
                                    @endif
                                </div>

                                {{-- Wishlist Heart Button --}}
                                <button type="button"
                                        @click.prevent.stop="$store.wishlist.toggle(@js($pkgSlug))"
                                        aria-label="{{ __('Simpan paket ke favorit') }}"
                                        class="absolute bottom-3 right-3 z-20 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-slate-600 shadow-md backdrop-blur-sm flex items-center justify-center transition active:scale-90 select-none cursor-pointer"
                                        :class="$store.wishlist.has(@js($pkgSlug)) && 'text-red-500 bg-white'">
                                    <span class="material-symbols-outlined text-[17px]"
                                          :class="$store.wishlist.has(@js($pkgSlug)) ? 'text-red-500' : 'text-slate-500'"
                                          style="font-variation-settings: 'FILL' 1;">favorite</span>
                                </button>
                            </div>

                            <div class="px-6 pt-6 pb-4 flex flex-col flex-grow">
                                <div class="flex items-center text-toba-green text-[9px] font-semibold uppercase tracking-wider mb-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-map-pin mr-1.5"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <span>{{ $pkgLokasi }}</span>
                                </div>
                                {{-- Kartu di grid ini sebelumnya tidak bisa diklik sama
                                     sekali: satu-satunya jalan masuk cuma tombol Booking,
                                     jadi tamu yang cuma ingin membaca detail tidak punya
                                     pintu. Judulnya kini menuju halaman detail tanpa form. --}}
                                <a href="/tour/detail/{{ $pkgSlug }}" class="block">
                                    <h3 class="text-lg font-bold text-slate-900 mb-3 line-clamp-1 group-hover:text-toba-green transition-colors tracking-tight">{{ $pkg->translated_name }}</h3>
                                </a>
                                <p class="text-slate-500 text-xs leading-relaxed mb-4 line-clamp-2 font-normal flex-grow">{{ $pkg->translated_description }}</p>
                            </div>
                            @include('partials.package-details', [
                                'xdata' => $pkgDetailsXdata,
                                'uid' => \Illuminate\Support\Js::from('pkg-detail-grid-'.$pkg->id),
                            ])
                            @include('partials.pax-calc', [
                                'xdata' => $pkgPaxXdata,
                                'priceImage' => \Illuminate\Support\Js::from($pkg->price_image_url ?? null),
                            ])
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Tanpa filter, daftar kosong hanya berarti satu hal: memang belum
                 ada paket aktif. Tombol "Reset Semua Filter" di sini dulu
                 merujuk state yang sudah tidak ada -- sekali diklik, Alpine
                 melempar ReferenceError dan seluruh komponen berhenti bekerja. --}}
            @if(count($packages) === 0)
            <div class="text-center py-12 bg-white rounded-3xl border border-slate-100 shadow-sm animate-in fade-in zoom-in duration-700">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search-x"><path d="m16 16 5 5"></path><circle cx="10" cy="10" r="7"></circle><path d="m7 7 6 6"></path><path d="m13 7-6 6"></path></svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-3 tracking-tight">{{ __('Paket Belum Tersedia') }}</h3>
                <p class="text-slate-500 text-xs font-normal max-w-xs mx-auto mb-8 leading-relaxed">{{ __('Daftar paket sedang kami perbarui. Ceritakan rencana Anda dan kami susunkan penawarannya.') }}</p>
                @php
                    $kosongPesan = __('Halo, saya ingin bertanya tentang paket wisata Danau Toba.');
                @endphp
                <a href="https://wa.me/{{ \App\Helpers\ContactHelper::whatsappDigits() }}?text={{ rawurlencode($kosongPesan) }}"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 bg-slate-950 text-white px-8 py-3 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-toba-green transition-colors duration-300">
                    <x-icon name="whatsapp" class="w-3.5 h-3.5" />
                    {{ __('Tanya lewat WhatsApp') }}
                </a>
            </div>
            @endif
        </div>

        <!-- Custom CTA Section -->
        <div class="max-w-7xl mx-auto px-5 md:px-8 mt-8 md:mt-12 mb-8 md:mb-12">
            <div class="bg-gradient-to-r from-toba-green to-primary-container rounded-3xl p-8 md:p-12 text-center relative overflow-hidden shadow-sm">
                <div class="absolute inset-0 opacity-10">
                    <img src="{{ imageUrl('sumatra-panorama') }}" alt="Paket wisata - destinasi" loading="lazy" decoding="async" class="w-full h-full object-cover">
                </div>
                <div class="relative z-10">
                    <h3 class="text-2xl md:text-3xl font-bold text-white mb-3 tracking-tight">Tidak Menemukan Paket yang Cocok?</h3>
                    <p class="text-white/80 text-sm font-normal mb-8 max-w-lg mx-auto">Kami siap merancang itinerary khusus sesuai kebutuhan dan budget Anda.</p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <a href="{{ \App\Helpers\ContactHelper::whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 bg-white text-toba-green px-6 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-slate-50 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Konsultasi Gratis
                        </a>
                        <a href="{{ \App\Helpers\ContactHelper::whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-center gap-2 bg-white/10 text-white border border-white/20 px-6 py-3.5 rounded-xl font-bold text-xs uppercase tracking-wider hover:bg-white/20 transition">
                            WhatsApp Kami
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>
@endsection

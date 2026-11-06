@props(['package' => null, 'locationName' => 'Sumatera Utara', 'locationData' => null])

@php
    $displayLocation = $locationData
        ? ($locationData->type === 'international'
            ? ($locationData->place ?: $locationData->region) . ', ' . $locationData->country
            : $locationData->name)
        : ($package->locationTag ?? $locationName);

    $isInternational = $locationData && $locationData->type === 'international';
    $rawImage = $package->packageImages?->first()?->image_path
        ?? ((isset($package->images) && count($package->images) > 0) ? $package->images[0] : (isset($package->image) ? $package->image : 'tour'));
    $image = imageUrl($rawImage);
    $name = $package->translated_name ?? $package->name ?? 'Paket Tour';
    $slug = $package->slug ?: $package->id;

    // Ekspresi Alpine untuk kalkulator pax (harga MYR mentah; dikonversi di JS).
    // childPrice dikirim apa adanya (null != 0) dan tier harga grosir ikut,
    // supaya angka kartu sama dengan BookingService.
    $paxPricing = is_array($package->pricingDetails ?? null) ? $package->pricingDetails : [];
    $paxTiers = array_values($paxPricing['tiers'] ?? []);
    $paxXdata = 'paxCalc('
        . (float) ($package->price ?? 0) . ', '
        . \Illuminate\Support\Js::from($package->childPrice ?? null) . ', '
        . \Illuminate\Support\Js::from($slug) . ', '
        . \Illuminate\Support\Js::from($paxTiers) . ', '
        . \Illuminate\Support\Js::from($name) . ')';

    // Ringkasan isi paket. Sumbernya kolom JSON includes/excludes -- yang
    // benar-benar diisi lewat form admin -- bukan relasi packageIncludes.
    $detailsXdata = 'pkgDetails('
        . \Illuminate\Support\Js::from(array_values((array) ($package->includes ?? []))) . ', '
        . \Illuminate\Support\Js::from(array_values((array) ($package->excludes ?? []))) . ', '
        . \Illuminate\Support\Js::from(array_values((array) ($package->itinerary ?? []))) . ')';
    // Ekspresi Alpine yang menghasilkan id, bukan id itu sendiri.
    $detailsUid = \Illuminate\Support\Js::from('pkg-detail-'.($package->id ?? $slug));
@endphp

<div class="group flex flex-col bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-slate-200 hover:shadow-xl transition-all duration-300 h-full">

    {{-- Bagian yang bisa diklik menuju detail: gambar + lokasi + judul.
         Menuju versi TANPA form: klik di sini niatnya "lihat-lihat dulu",
         bukan "saya mau pesan". Yang berniat memesan menekan tombol Booking
         di bawah, yang membawa langsung ke halaman berform beserta jumlah
         pax yang sudah ia setel. --}}
    <a href="/tour/detail/{{ $slug }}" class="flex flex-col flex-grow">
        {{-- Gambar --}}
        {{-- Rasio, bukan tinggi mati: h-44 membuat bentuk gambar berubah-ubah
             ikut lebar kartu (1,48 di ponsel, 1,76 di desktop). 4:3 menjaga
             bentuknya sama di semua layar dan memberi gambar porsi yang
             sepadan dengan kartu pesaing. --}}
        <div class="relative aspect-[4/3] overflow-hidden shrink-0">
            <img
                src="{{ $image }}"
                @if($kartuSrcset = imageSrcset($image))
                    srcset="{{ $kartuSrcset }}"
                    sizes="(max-width: 639px) 100vw, (max-width: 1023px) 50vw, 400px"
                @endif
                alt="{{ $name }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                loading="lazy"
                decoding="async"
            >
            {{-- Badges --}}
            <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                @if($package->isFeatured ?? false)
                <span class="inline-flex items-center gap-1 bg-toba-orange text-white text-[10px] font-bold uppercase tracking-wide px-2.5 py-1 rounded-full shadow-sm">
                    🔥 {{ __('Terpopuler') }}
                </span>
                @endif
            </div>
            @if($package->duration ?? false)
            <div class="absolute top-3 right-3 z-10">
                <span class="bg-slate-900/60 backdrop-blur-sm text-white text-[10px] font-medium px-2.5 py-1 rounded-full">
                    {{ $package->duration }}
                </span>
            </div>
            @endif

            {{-- Wishlist Heart Button --}}
            <button type="button"
                    @click.prevent.stop="$store.wishlist.toggle(@js($slug))"
                    aria-label="{{ __('Simpan paket ke favorit') }}"
                    class="absolute bottom-3 right-3 z-20 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-slate-600 shadow-md backdrop-blur-sm flex items-center justify-center transition active:scale-90 select-none cursor-pointer"
                    :class="$store.wishlist.has(@js($slug)) && 'text-red-500 bg-white'">
                <span class="material-symbols-outlined text-[17px]"
                      :class="$store.wishlist.has(@js($slug)) ? 'text-red-500' : 'text-slate-500'"
                      style="font-variation-settings: 'FILL' 1;">favorite</span>
            </button>
        </div>

        {{-- Info --}}
        <div class="flex flex-col flex-grow px-5 pt-4 pb-3">
            {{-- Lokasi --}}
            <p class="flex items-center gap-1 text-slate-400 text-[10.5px] font-medium uppercase tracking-widest mb-2 truncate">
                <svg class="w-3 h-3 shrink-0 text-toba-green" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                <span>{{ $displayLocation }}</span>
                @if($isInternational) <span>✈️</span> @endif
            </p>

            {{-- Judul --}}
            <h3 class="text-slate-900 font-semibold text-[15px] leading-snug line-clamp-2 flex-grow group-hover:text-toba-green transition-colors duration-200">
                {{ $name }}
            </h3>
        </div>
    </a>

    {{-- Ringkasan isi paket (akordeon) --}}
    @include('partials.package-details', ['xdata' => $detailsXdata, 'uid' => $detailsUid])

    {{-- Kalkulator pax + estimasi total + booking (di luar <a> agar tombol bisa diklik) --}}
    @include('partials.pax-calc', [
        'xdata' => $paxXdata,
        'priceImage' => \Illuminate\Support\Js::from($package->price_image_url ?? null),
    ])
</div>

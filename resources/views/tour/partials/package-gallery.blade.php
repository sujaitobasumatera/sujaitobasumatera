{{-- Galeri foto paket dalam bentuk kisi, plus penampil layar penuh.

     Butuh $foto (larik hasil olahan di package-detail: url, srcset, blur_hash)
     dan $package. $salesMode opsional.

     KENAPA ADA AMBANG: carousel di hero sudah menampilkan SELURUH foto paket.
     Untuk paket berfoto tiga, kisi di bawah cuma mengulang hal yang sama dua
     kali dalam satu halaman. Ia baru punya alasan hidup begitu fotonya banyak
     -- menggeser 15 foto satu per satu itu melelahkan, sementara kisi
     memperlihatkan semuanya sekaligus dan membiarkan tamu melompat ke yang ia
     mau. Jadi bloknya menyalakan dirinya sendiri saat foto ke-5 diunggah, dan
     mati lagi kalau fotonya dikurangi. Tidak ada saklar yang perlu diingat. --}}
@php
    $salesMode = $salesMode ?? false;
    $fotoGaleri = array_values(array_filter((array) $foto, fn ($f) => ! empty($f['url'])));
    $ambangGaleri = 5;

    $judulKelas = $salesMode
        ? 'font-headline-md text-lg md:text-2xl font-semibold text-primary uppercase tracking-[0.12em] md:tracking-normal md:normal-case mb-1'
        : 'font-headline-md text-xl md:text-2xl font-semibold text-primary mb-1';
@endphp

@if(count($fotoGaleri) >= $ambangGaleri)
<div class="pt-8">
    <h2 class="{{ $judulKelas }}">{{ __('Galeri Foto') }}</h2>
    <p class="text-[13px] md:text-xs text-on-surface-variant font-body-md mb-4">{{ __('Ketuk foto untuk melihat ukuran penuh.') }}</p>

    {{-- Kotaknya dicetak server dengan @foreach, bukan template x-for: isi
         <template> baru ada setelah Alpine bangun, dan foto adalah satu-satunya
         alasan blok ini ada. Alpine di sini hanya mengurus penampil layar
         penuhnya -- tanpa JavaScript, kisinya tetap tampil utuh. --}}
    <div x-data="{ foto: @js($fotoGaleri) }">
        <div class="{{ $salesMode ? 'bleed-mobile' : '' }} grid grid-cols-3 gap-1 md:gap-3">
            @foreach($fotoGaleri as $i => $f)
                <button type="button"
                        @click="$dispatch('zoom-image', { images: foto, index: {{ $i }}, title: '{{ addslashes($package->translated_name ?? $package->name) }}' })"
                        class="relative aspect-square overflow-hidden md:rounded-xl bg-slate-100 group cursor-zoom-in"
                        aria-label="{{ __('Foto') }} {{ $i + 1 }}"
                        title="{{ __('Klik untuk perbesar foto') }}">
                    <img src="{{ $f['url'] }}"
                         @if($f['srcset']) srcset="{{ $f['srcset'] }}" sizes="(max-width: 767px) 33vw, 25vw" @endif
                         alt="{{ $package->translated_name }} — {{ __('Foto') }} {{ $i + 1 }}"
                         loading="lazy" decoding="async"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <span class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm text-slate-900 flex items-center justify-center shadow-md">
                            <span class="material-symbols-outlined text-[18px]">zoom_in</span>
                        </span>
                    </div>
                </button>
            @endforeach
        </div>
    </div>
</div>
@endif

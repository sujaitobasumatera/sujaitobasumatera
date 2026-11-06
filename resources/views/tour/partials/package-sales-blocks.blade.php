{{-- Blok khas halaman detail TANPA form (/tour/detail): penginapan per malam,
     pembeda, dan ajakan WhatsApp penutup.

     Tidak dipasang di halaman berform dengan sengaja. Di sana tamu sudah punya
     form di sebelah kanan; menambah tiga blok panjang di bawahnya justru
     mendorong tombol kirim makin jauh dari jangkauan.
     Butuh $package. --}}
@php
    // Didefinisikan lokal, bukan diwarisi dari partial media: variabel yang
    // dibuat di dalam @include tidak menyeberang ke @include lain.
    // Partial ini hanya dirender di /tour/detail, jadi gayanya selalu poster.
    $judulKelas = 'font-headline-md text-lg md:text-2xl font-semibold text-primary uppercase tracking-[0.12em] md:tracking-normal md:normal-case mb-1';

    $stays = $package->accommodationList();

    // Pembeda paket menang atas poin situs. Poin situs berlaku untuk kedelapan
    // paket sekaligus, jadi ia tidak pernah bisa menyebut hal yang cuma benar
    // di satu paket -- padahal justru itu yang membuat orang memilih paket ini
    // dan bukan paket sebelahnya. Begitu admin mengisi poin di form paket,
    // blok ini otomatis memakai punyanya; dikosongkan, ia kembali ke poin situs
    // tanpa perlu menyalakan apa pun.
    $uspPaket = $package->highlightList();
    $usp = $uspPaket !== [] ? $uspPaket : array_values(array_filter(
        (array) ($siteSettings['cms_tour']['detail_usp'] ?? []),
        fn ($u) => is_array($u) && trim((string) ($u['title'] ?? '')) !== ''
    ));

    // Poin situs dilewatkan __() karena ia teks tetap yang punya kunci
    // terjemahan; poin paket TIDAK -- ia tulisan admin untuk satu paket, dan
    // menerjemahkannya hanya akan mengembalikan kalimatnya sendiri sambil
    // meninggalkan kunci sampah di berkas bahasa.
    $terjemahPoin = fn (string $s) => $uspPaket !== [] ? $s : __($s);
@endphp

{{-- ============ PENGINAPAN PER MALAM ============ --}}
@if(count($stays))
    <div class="pt-8">
        <h2 class="{{ $judulKelas }}">{{ __('Menginap di Mana') }}</h2>
        <p class="text-[13px] md:text-xs text-on-surface-variant font-body-md mb-5">{{ __('Penginapan untuk setiap malam perjalanan Anda.') }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($stays as $stay)
                {{-- Videonya langsung terbuka, tanpa tombol buka-tutup.
                     Versi sebelumnya membungkus iframe dalam x-if + x-show:
                     pemutarnya memang muncul, tapi tidak bisa dijalankan.
                     Beratnya ditahan lewat loading=lazy -- pemutar baru ditarik
                     ketika kartunya benar-benar mendekati layar. --}}
                <div class="bg-white border border-outline-variant rounded-2xl hover:border-secondary transition-colors duration-300 overflow-hidden">
                @if($stay['video'])
                    <div class="relative aspect-video bg-slate-900">
                        <iframe src="{{ $stay['video'] }}"
                                title="{{ $stay['name'] }}"
                                class="absolute inset-0 w-full h-full"
                                loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                allowfullscreen></iframe>
                    </div>
                @endif
                <div class="flex items-center gap-4 p-4">
                    @if($stay['image'])
                        <img src="{{ imageUrl($stay['image']) }}" alt="{{ $stay['name'] }}"
                             loading="lazy" decoding="async"
                             class="w-16 h-16 rounded-xl object-cover shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-xl bg-primary/5 text-primary/40 flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined text-[28px]">hotel</span>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="font-label-caps text-[10px] text-secondary uppercase tracking-widest mb-0.5">
                            {{ __('Malam') }} {{ $stay['night'] }}
                        </p>
                        <p class="font-semibold text-sm text-slate-900 leading-snug truncate">{{ $stay['name'] }}</p>
                        @if($stay['class'])
                            <p class="text-[11px] text-on-surface-variant font-body-md mt-0.5">{{ $stay['class'] }}</p>
                        @endif
                    </div>
                </div>
                </div>
            @endforeach
        </div>

        <p class="mt-4 text-[11px] text-on-surface-variant font-body-md italic">
            {{ __('Punya hotel pilihan sendiri? Bisa kami sesuaikan — beri tahu kami saat memesan.') }}
        </p>
    </div>
@endif

{{-- ============ KENAPA KAMI BERBEDA ============ --}}
@if(count($usp))
    <div class="pt-8">
        <div class="bleed-mobile bg-primary/5 border-y md:border border-primary/10 md:rounded-[1.75rem] px-margin-mobile py-7 md:p-8">
            {{-- Judulnya ikut berubah saat poinnya khusus paket ini. "Kenapa
                 Kami Berbeda" itu janji tentang perusahaan; begitu isinya cuma
                 berlaku di satu paket, judul yang sama jadi salah alamat. --}}
            <h2 class="{{ $judulKelas }} mb-5">{{ $uspPaket !== [] ? __('Kenapa Paket Ini Berbeda') : __('Kenapa Kami Berbeda') }}</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                @foreach($usp as $poin)
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full bg-white text-secondary flex items-center justify-center shrink-0 shadow-sm">
                            <span class="material-symbols-outlined text-[16px]">check</span>
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-slate-900 leading-snug">{{ $terjemahPoin($poin['title']) }}</p>
                            @if(trim((string) ($poin['text'] ?? '')) !== '')
                                <p class="text-[14px] md:text-xs text-on-surface-variant font-body-md leading-relaxed mt-1">{{ $terjemahPoin($poin['text']) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- CTA penutup DIPINDAH ke package-detail.blade.php, tepat sesudah kartu
     harga. Di sini ia jatuh di TENGAH halaman -- ajakan menutup yang
     muncul sebelum tamu sempat melihat rincian harga, ulasan, dan brosur. --}}

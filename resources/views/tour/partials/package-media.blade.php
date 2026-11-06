{{-- Video & peta lokasi paket. Dipakai halaman detail berform MAUPUN halaman
     detail tanpa form -- keduanya membaca partial yang sama, jadi media yang
     diisi admin sekali langsung muncul di dua-duanya.
     Butuh $package. --}}
@php
    $mediaVideos = $package->videoList();
    $mediaMap = $package->mapEmbedUrl();

    // Mode "sales page" hanya menyala di /tour/detail. Keterangan alat rekam
    // dan pernyataan keaslian itu argumen jualan -- di halaman berform ia
    // menambah panjang bacaan tepat di titik tamu sudah siap mengisi form.
    $salesMode = $salesMode ?? false;
    $creditNote = trim((string) ($siteSettings['cms_tour']['video_credit_note'] ?? ''));

    // Gaya "poster": di mobile media menembus tepi layar dan judulnya jadi
    // huruf besar berjarak. Hanya di /tour/detail -- halaman berform tetap
    // memakai kartu bersudut lengkung seperti sisa situs.
    $judulKelas = $salesMode
        ? 'font-headline-md text-lg md:text-2xl font-semibold text-primary uppercase tracking-[0.12em] md:tracking-normal md:normal-case mb-1'
        : 'font-headline-md text-xl md:text-2xl font-semibold text-primary mb-1';
    $bingkaiKelas = $salesMode
        ? 'bleed-mobile relative aspect-video overflow-hidden md:rounded-2xl bg-slate-900 md:shadow-lg'
        : 'relative aspect-video overflow-hidden rounded-2xl bg-slate-900 shadow-lg';
@endphp

@if(count($mediaVideos) || $mediaMap)
    <div class="pt-8 space-y-8">
        @if(count($mediaVideos))
            <div>
                <h2 class="{{ $judulKelas }}">{{ __('Video Perjalanan') }}</h2>
                <p class="text-xs text-on-surface-variant font-body-md mb-5">{{ __('Lihat suasananya langsung sebelum memutuskan.') }}</p>

                <div class="grid grid-cols-1 {{ count($mediaVideos) > 1 ? 'md:grid-cols-2' : '' }} gap-5">
                    @foreach($mediaVideos as $video)
                        <figure class="space-y-2">
                            <div class="{{ $bingkaiKelas }}">
                                @if($video['kind'] === 'embed')
                                    {{-- loading=lazy: pemutar tersemat menarik ratusan KB.
                                         Tanpa ini, halaman terberat situs membayarnya
                                         di muka walau tamu tidak pernah menggulir ke sini. --}}
                                    <iframe src="{{ $video['url'] }}"
                                            title="{{ $video['title'] ?: ($package->translated_name ?? $package->name) }}"
                                            class="absolute inset-0 w-full h-full"
                                            loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                                            {{-- fullscreen ditulis di allow, bukan cuma
                                                 lewat atribut allowfullscreen: tanpa itu
                                                 tombol perbesar di pemutar YouTube tidak
                                                 berfungsi di dalam iframe. --}}
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                            allowfullscreen></iframe>
                                @else
                                    <video src="{{ $video['url'] }}" controls preload="metadata" playsinline
                                           class="absolute inset-0 w-full h-full object-cover">
                                        {{ __('Peramban Anda tidak mendukung pemutar video.') }}
                                    </video>
                                @endif
                            </div>
                            @if($video['title'] || ($salesMode && $video['gear']))
                                <figcaption class="{{ $salesMode ? 'px-margin-mobile md:px-0 ' : '' }}flex flex-wrap items-center gap-2 text-xs text-on-surface-variant font-body-md">
                                    @if($video['title'])<span>{{ $video['title'] }}</span>@endif
                                    @if($salesMode && $video['gear'])
                                        <span class="inline-flex items-center gap-1 bg-primary/5 text-primary text-[10px] font-semibold px-2 py-0.5 rounded-full">
                                            <span class="material-symbols-outlined text-[12px]">videocam</span>
                                            {{ $video['gear'] }}
                                        </span>
                                    @endif
                                </figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>

                @if($salesMode && $creditNote)
                    <p class="mt-4 flex items-start gap-2 text-xs text-on-surface-variant font-body-md italic">
                        <span class="material-symbols-outlined text-[16px] text-secondary shrink-0">verified</span>
                        <span>{{ $creditNote }}</span>
                    </p>
                @endif
            </div>
        @endif

        @if($mediaMap)
            <div>
                <h2 class="{{ $judulKelas }}">{{ __('Lokasi') }}</h2>
                <p class="text-xs text-on-surface-variant font-body-md mb-5">{{ __('Titik temu & area tujuan perjalanan ini.') }}</p>
                <div class="relative h-[320px] md:h-[400px] overflow-hidden {{ $salesMode ? 'bleed-mobile md:rounded-2xl md:border md:border-outline-variant' : 'rounded-2xl border border-outline-variant shadow-sm' }}">
                    <iframe src="{{ $mediaMap }}"
                            title="{{ __('Peta lokasi') }} — {{ $package->translated_name ?? $package->name }}"
                            class="absolute inset-0 w-full h-full"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                </div>
            </div>
        @endif
    </div>
@endif

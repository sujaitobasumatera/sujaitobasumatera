@extends('layouts.app')

@section('title', __('Tracking Booking') . ' ' . $booking->bookingCode . ' | Sujai Toba Sumatera')
@section('description', __('Lihat status booking wisata Sujai Toba Sumatera dengan kode booking.'))

@php
    $statusMap = [
        'pending' => [
            'label' => __('Menunggu Konfirmasi'),
            'description' => __('Admin akan menghubungi Anda untuk memastikan ketersediaan paket, harga final, dan instruksi pembayaran.'),
            'class' => 'bg-amber-50 text-amber-700 border-amber-200',
        ],
        'confirmed' => [
            'label' => __('Dikonfirmasi'),
            'description' => __('Booking sudah dikonfirmasi. Silakan simpan kode booking dan invoice Anda.'),
            'class' => 'bg-green-50 text-green-700 border-green-200',
        ],
        'completed' => [
            'label' => __('Selesai'),
            'description' => __('Perjalanan sudah selesai. Terima kasih telah memilih Sujai Toba Sumatera.'),
            'class' => 'bg-slate-50 text-slate-700 border-slate-200',
        ],
        'cancelled' => [
            'label' => __('Dibatalkan'),
            'description' => __('Booking ini tercatat dibatalkan. Hubungi admin jika perlu bantuan.'),
            'class' => 'bg-rose-50 text-rose-700 border-rose-200',
        ],
    ];

    $status = $statusMap[$booking->status] ?? $statusMap['pending'];
    $pax = (int) ($booking->metadata['pax'] ?? 1);
    $packageUrl = $booking->package ? route('tour.package.detail', $booking->package->slug) : route('tour.packages');
    $invoiceUrl = route('invoice.download', $booking->bookingCode);
    // Satu sumber nomor. Rantai ?? sebelumnya menyebut kunci yang sama tiga kali
    // dan tetap bisa menghasilkan nomor yang berbeda dari yang tampil di footer.
    $waNumber = \App\Helpers\ContactHelper::whatsappDigits();
    $waText = urlencode(__('Halo Sujai Toba Sumatera, saya ingin bertanya tentang booking :code.', ['code' => $booking->bookingCode]));

    // Tenggat pembatalan khusus pesanan ini, diturunkan dari aturan di /terms
    // (>14 hari 100%, 7-14 hari 50%, <7 hari hangus). Aturannya sudah tertulis
    // rapi di halaman S&K tapi tidak pernah muncul di tempat tamu memikirkannya.
    $refundFull = $booking->startDate ? $booking->startDate->copy()->subDays(14) : null;
    $refundHalf = $booking->startDate ? $booking->startDate->copy()->subDays(7) : null;
    $showRefund = $refundFull && ! in_array($booking->status, ['cancelled', 'completed'], true);
    $steps = [
        'pending' => [__('Booking Diterima'), __('Menunggu Konfirmasi'), __('Dikonfirmasi'), __('Trip Selesai')],
        'confirmed' => [__('Booking Diterima'), __('Menunggu Konfirmasi'), __('Dikonfirmasi'), __('Trip Selesai')],
        'completed' => [__('Booking Diterima'), __('Menunggu Konfirmasi'), __('Dikonfirmasi'), __('Trip Selesai')],
        'cancelled' => [__('Booking Diterima'), __('Menunggu Konfirmasi'), __('Dibatalkan')],
    ];
    $activeSteps = $steps[$booking->status] ?? $steps['pending'];
    // The furthest step actually reached. Each status lands ON its own label,
    // so the last step of a finished or cancelled booking is filled in rather
    // than left grey — the previous numbers stopped one short every time.
    $currentStep = match ($booking->status) {
        'confirmed' => 3,   // Dikonfirmasi
        'completed' => 4,   // Trip Selesai
        'cancelled' => 3,   // Dibatalkan
        default => 2,       // Menunggu Konfirmasi
    };
    $isCancelled = $booking->status === 'cancelled';
@endphp

@section('content')
<section class="relative overflow-hidden bg-slate-950 text-white" x-data="{ copied: false, copyCode() { navigator.clipboard.writeText('{{ $booking->bookingCode }}'); this.copied = true; setTimeout(() => this.copied = false, 1800); } }">
    <div class="absolute inset-0 opacity-30">
        <div class="h-full w-full bg-[radial-gradient(circle_at_top_left,_rgba(16,185,81,0.35),_transparent_38%),linear-gradient(135deg,_#020617,_#0f172a_46%,_#064e22)]"></div>
    </div>

    <div class="relative mx-auto max-w-5xl px-5 py-10 md:px-8 md:py-14">
        <p class="text-xs font-bold uppercase tracking-[0.28em] text-green-300">{{ __('Tracking Booking') }}</p>
        <h1 class="mt-4 text-3xl font-extrabold leading-tight md:text-5xl">
            {{ $booking->bookingCode }}
        </h1>
        <p class="mt-5 max-w-2xl text-sm leading-7 text-slate-200 md:text-base">
            {{ __('Simpan kode booking ini saat berkomunikasi dengan admin Sujai Toba Sumatera.') }}
        </p>
        <div class="mt-7 flex flex-col gap-3 sm:flex-row">
            <button type="button" @click="copyCode()" class="inline-flex items-center justify-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-slate-950 transition hover:bg-green-50">
                <span class="material-symbols-outlined text-base">content_copy</span>
                {{-- Dua span, bukan x-text dengan literal string: label yang
                     mengandung apostrof (mis. terjemahan Inggris) akan memutus
                     ekspresi Alpine dan mematikan seluruh tombol. --}}
                <span x-show="!copied">{{ __('Copy Kode Booking') }}</span>
                <span x-show="copied" x-cloak>{{ __('Kode Tersalin') }}</span>
            </button>
            <a href="{{ route('booking.track.form') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-white/20 px-5 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                <span class="material-symbols-outlined text-base">search</span>
                {{ __('Cek Kode Lain') }}
            </a>
        </div>
    </div>
</section>

@if(session('success'))
    {{-- Tamu yang baru saja memesan mendarat di sini, bukan lagi di panel yang
         hilang begitu halaman di-refresh. Kode booking-nya ada di judul atas. --}}
    <section class="bg-white">
        <div class="mx-auto max-w-5xl px-5 pt-8 md:px-8">
            <div class="rounded-2xl border border-green-200 bg-green-50 p-5 md:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined mt-0.5 text-green-600">check_circle</span>
                        <div>
                            <p class="text-sm font-extrabold text-green-900">{{ __('Reservasi Terkirim') }}</p>
                            <p class="mt-1 text-xs leading-6 text-green-800">{{ __('Simpan halaman ini. Alamatnya permanen, jadi Anda bisa membukanya lagi kapan saja untuk melihat status pesanan.') }}</p>
                        </div>
                    </div>
                    @if(session('whatsappUrl'))
                        <a href="{{ session('whatsappUrl') }}" target="_blank" rel="noopener"
                           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-toba-green px-5 py-3 text-xs font-bold uppercase tracking-wider text-white transition hover:bg-primary-container">
                            <span class="material-symbols-outlined text-base">chat</span>
                            {{ __('Konfirmasi via WhatsApp') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif

<section class="bg-slate-50 py-6 md:py-8">
    <div class="mx-auto grid max-w-5xl gap-6 px-5 md:grid-cols-[1.1fr_0.9fr] md:px-8">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <div class="flex flex-col gap-4 border-b border-slate-100 pb-6 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Status Saat Ini') }}</p>
                    <h2 class="mt-2 text-2xl font-extrabold text-slate-950">{{ $status['label'] }}</h2>
                </div>
                <span class="inline-flex w-fit items-center rounded-full border px-4 py-2 text-xs font-bold {{ $status['class'] }}">
                    {{ strtoupper($booking->status) }}
                </span>
            </div>

            <p class="mt-6 text-sm leading-7 text-slate-600">
                {{ $status['description'] }}
            </p>

            <div class="mt-8 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Timeline') }}</p>
                <div class="mt-5 relative pl-2">
                    <!-- Connecting Vertical Line -->
                    <div class="absolute left-[1.4rem] top-4 bottom-4 w-px bg-slate-200"></div>
                    
                    <div class="space-y-6 relative">
                        @foreach($activeSteps as $index => $step)
                            @php
                                $stepNumber = $index + 1;
                                $isDone = $stepNumber <= $currentStep;
                                // A cancellation is the one outcome that must not
                                // read as a completed green step.
                                $isCancelStep = $isCancelled && $stepNumber === $currentStep;
                            @endphp
                            <div class="flex items-start gap-4">
                                <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 {{ $isCancelStep ? 'bg-rose-600 border-rose-600 text-white' : ($isDone ? 'bg-toba-green border-toba-green text-white' : 'bg-white border-slate-200') }} shadow-sm transition-all duration-300">
                                    @if($isCancelStep)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @elseif($isDone)
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <span class="h-2.5 w-2.5 rounded-full bg-slate-200"></span>
                                    @endif
                                </div>
                                <div class="pt-1.5">
                                    <p class="text-sm font-bold {{ $isDone ? 'text-slate-900' : 'text-slate-400' }}">{{ $step }}</p>
                                    @if($stepNumber === $currentStep)
                                        <p class="mt-1 text-xs font-semibold {{ $isCancelStep ? 'text-rose-600 bg-rose-50' : 'text-green-600 bg-green-50' }} px-2 py-0.5 rounded w-fit">{{ __('Status saat ini') }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('Paket') }}</p>
                    <p class="mt-2 font-bold text-slate-950">{{ $booking->package->name ?? __('Paket Wisata') }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('Tanggal Berangkat') }}</p>
                    <p class="mt-2 font-bold text-slate-950">{{ optional($booking->startDate)->translatedFormat('d F Y') ?? '-' }}</p>
                    {{-- endDate sudah dihitung backend dari durasi paket sejak lama,
                         tapi tidak pernah ditampilkan ke tamu yang justru perlu
                         tahu kapan ia pulang untuk memesan tiket. --}}
                    @if($booking->endDate && optional($booking->startDate)->notEqualTo($booking->endDate))
                    <p class="mt-1 text-xs text-slate-500">{{ __('s/d') }} {{ $booking->endDate->translatedFormat('d F Y') }}</p>
                    @endif
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('Peserta') }}</p>
                    <p class="mt-2 font-bold text-slate-950">{{ __(':count Orang', ['count' => $pax]) }}</p>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('Estimasi Total') }}</p>
                    <p class="mt-2 font-bold text-slate-950">{{ \App\Helpers\CurrencyHelper::formatRecord($booking->totalPrice, $booking->currency) }}</p>
                    @if($booking->currency !== 'IDR')
                    {{-- The amount agreed is above; this is only a reference for
                         transfers from an Indonesian bank, at the rate frozen
                         when the booking was made. --}}
                    <p class="mt-1 text-xs text-slate-400">≈ {{ \App\Helpers\CurrencyHelper::formatRecord($booking->totalPrice_idr, 'IDR') }} <span class="whitespace-nowrap">({{ __('kurs') }} {{ number_format((float) $booking->exchange_rate_idr, 0, ',', '.') }})</span></p>
                    @endif
                </div>
            </div>

            @if(isset($booking->metadata['price_breakdown']))
            @php
                $pb = $booking->metadata['price_breakdown'];
                // Every figure in this breakdown was recorded in the booking's
                // own currency. Render it as stored — never re-convert, or the
                // customer sees different numbers each time the rate changes.
                $cur = $booking->currency;
            @endphp
            <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">{{ __('Rincian Biaya') }}</p>
                <div class="space-y-2 text-sm text-slate-600">
                    <div class="flex justify-between">
                        <span>{{ __('Ekspedisi Dewasa') }} ({{ $pb['pax_dewasa'] }}x)</span>
                        <span>{{ \App\Helpers\CurrencyHelper::formatRecord($pb['price_dewasa_total'], $cur) }}</span>
                    </div>
                    @if(isset($pb['pax_anak']) && $pb['pax_anak'] > 0)
                    <div class="flex justify-between">
                        <span>{{ __('Ekspedisi Anak-Anak') }} ({{ $pb['pax_anak'] }}x)</span>
                        <span>{{ \App\Helpers\CurrencyHelper::formatRecord($pb['price_anak_total'], $cur) }}</span>
                    </div>
                    @endif
                    @if(isset($pb['additional_services']))
                        @foreach($pb['additional_services'] as $srv)
                        <div class="flex justify-between">
                            <span>{{ $srv['name'] }}</span>
                            <span>{{ \App\Helpers\CurrencyHelper::formatRecord($srv['price'], $cur) }}</span>
                        </div>
                        @endforeach
                    @endif
                    {{-- Hanya bila ada yang dipungut; lihat catatan di
                         invoice/show.blade.php. --}}
                    @if(($pb['tax'] ?? 0) > 0)
                    <div class="flex justify-between">
                        <span>{{ __('Pajak & Layanan') }} ({{ $pb['tax_percentage'] ?? 0 }}%)</span>
                        <span>{{ \App\Helpers\CurrencyHelper::formatRecord($pb['tax'], $cur) }}</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-slate-100 flex justify-between font-bold text-slate-950 mt-2">
                        <span>{{ __('Total Ringkasan') }}</span>
                        <span>{{ \App\Helpers\CurrencyHelper::formatRecord($pb['total'] ?? $booking->totalPrice, $cur) }}</span>
                    </div>
                </div>
            </div>
            @endif

            @if($booking->notes)
                <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ __('Catatan') }}</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        <aside class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
            <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">{{ __('Link Booking') }}</p>
            <div class="mt-5 space-y-3">
                <a href="{{ $invoiceUrl }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 text-sm font-bold text-slate-800 transition hover:border-green-300 hover:bg-green-50">
                    <span>{{ __('Invoice') }}</span>
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                </a>
                {{-- Tanpa tautan ini, halaman yang paling sering dibuka tamu yang
                     SUDAH memesan tidak pernah memberi tahu ke mana harus membayar. --}}
                <a href="{{ route('payment') }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 text-sm font-bold text-slate-800 transition hover:border-green-300 hover:bg-green-50">
                    <span>{{ __('Cara Pembayaran') }}</span>
                    <span class="material-symbols-outlined text-base">account_balance</span>
                </a>
                <a href="{{ $packageUrl }}" class="flex items-center justify-between rounded-xl border border-slate-200 px-4 py-4 text-sm font-bold text-slate-800 transition hover:border-green-300 hover:bg-green-50">
                    <span>{{ __('Lihat Paket') }}</span>
                    <span class="material-symbols-outlined text-base">travel_explore</span>
                </a>
                @if($waNumber)
                    <a href="https://wa.me/{{ $waNumber }}?text={{ $waText }}" target="_blank" rel="noopener" class="flex items-center justify-between rounded-xl bg-toba-green px-4 py-4 text-sm font-bold text-white transition hover:bg-primary-container">
                        <span>{{ __('Hubungi Admin') }}</span>
                        <span class="material-symbols-outlined text-base">chat</span>
                    </a>
                @endif
            </div>

            <div class="mt-6 rounded-xl bg-slate-50 p-4 text-sm leading-7 text-slate-600">
                {{ __('Jika ada perubahan tanggal, jumlah peserta, atau titik penjemputan, kirim kode booking ini ke admin.') }}
            </div>

            @if($showRefund)
                {{-- Tenggat konkret, bukan "lihat S&K". Tanggalnya dihitung dari
                     keberangkatan pesanan ini sendiri. --}}
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-widest text-amber-700">{{ __('Tenggat Pembatalan') }}</p>
                    <ul class="mt-3 space-y-1.5 text-xs leading-6 text-amber-900">
                        <li>{!! __('Batalkan sebelum :date — dana kembali 100% (potong biaya admin).', ['date' => '<strong>' . e($refundFull->translatedFormat('d F Y')) . '</strong>']) !!}</li>
                        <li>{!! __('Sampai :date — dana kembali 50%.', ['date' => '<strong>' . e($refundHalf->translatedFormat('d F Y')) . '</strong>']) !!}</li>
                        <li>{{ __('Setelah itu dana tidak dapat dikembalikan.') }}</li>
                    </ul>
                    <a href="{{ route('terms') }}" class="mt-3 inline-block text-xs font-bold text-amber-800 underline">{{ __('Selengkapnya di Syarat & Ketentuan') }}</a>
                </div>
            @endif
        </aside>
    </div>
</section>
@endsection

<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'my' ? 'ms' : str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $booking->bookingCode }} - {{ $companyName }}</title>
    {{-- Aset di-build sendiri. Sebelumnya halaman ini bergantung pada TIGA
         CDN: cdn.tailwindcss.com (build yang mengompilasi CSS di browser saat
         runtime — resmi bukan untuk produksi), Google Fonts, dan FontAwesome.
         Invoice dibuka di jaringan kantor, disimpan, dicetak, lalu dibuka lagi
         berbulan kemudian; satu CDN diblokir sudah cukup membuat tamu menerima
         teks polos tanpa gaya. Warna brand-* kini hidup di resources/css/app.css. --}}
    @vite(['resources/css/app.css'])
    <style>
        body {
            background-color: #f1f5f9;
            -webkit-font-smoothing: antialiased;
            background-image: radial-gradient(#cbd5e1 1px, transparent 1px);
            background-size: 20px 20px;
        }
        .invoice-card {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            position: relative;
        }
        .invoice-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 8px;
            background: linear-gradient(90deg, #004d1e 0%, #006b29 50%, #d4af37 100%);
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        /* Dulu utility bg-pattern-subtle dari config CDN. */
        .bg-pattern-subtle {
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23006b54' fill-opacity='0.03' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='3'/%3E%3Ccircle cx='13' cy='13' r='3'/%3E%3C/g%3E%3C/svg%3E");
        }
        .table-row-hover:hover {
            background-color: #f8fafc;
            transition: background-color 0.2s ease;
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        @media print {
            body { background: white; }
            .invoice-card { box-shadow: none; margin: 0; padding: 0; max-width: 100%; }
            .invoice-card::before { display: none; }
            .no-print { display: none !important; }
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    </style>
</head>
@php
    $pax           = max((int) ($booking->metadata['pax'] ?? 1), 1);
    $pb            = $booking->metadata['price_breakdown'] ?? null;
    $subtotalBase  = $pb['subtotal_base'] ?? null;
    $surchargeRows = $pb['surcharges'] ?? [];
    $taxAmount     = $pb['tax'] ?? null;
    $taxPercent    = $pb['tax_percentage'] ?? null;

    // Pesanan lama tidak menyimpan price_breakdown sehingga pajak tidak bisa
    // dipisahkan dari totalPrice. Flag ini menyembunyikan kolom Harga Satuan
    // pada path legacy agar invoice tidak menampilkan angka yang menyesatkan
    // (totalPrice/pax = sudah termasuk pajak, jadi bukan harga satuan sejati).
    $legacyOrder   = ($pb === null);

    // Unit price hanya bermakna ketika subtotalBase tersedia (sebelum pajak).
    // Untuk legacy, kolom ini diganti tanda pisah -- lihat tabel di bawah.
    $unitPrice     = ($subtotalBase !== null && $subtotalBase > 0)
        ? ($subtotalBase / $pax)
        : ($booking->totalPrice / $pax);
    $itemLineTotal = ($subtotalBase !== null && $subtotalBase > 0)
        ? $subtotalBase
        : $booking->totalPrice;

    // Tenggat pelunasan: 7 hari sebelum keberangkatan (S&K pasal 2). Tanpa ini
    // invoice tidak pernah menyebut kapan harus dibayar — penyebab paling umum
    // pesanan menggantung.
    $dueDate = $booking->startDate ? $booking->startDate->copy()->subDays(7) : null;

    // The currency this invoice was issued in, frozen when the booking was
    // made. An invoice is a record: it must read the same today as it did the
    // day it was sent, whatever the exchange rate has done since.
    $cur = $booking->currency;

    if ($booking->type === 'package' && $booking->package) {
        $itemName = $booking->package->name;
        $itemDesc = trim(($booking->package->duration ?? '') . ' ' . __('menikmati pesona wisata Sumatera Utara.'));
        $itemDest = $booking->package->city?->name ?? 'Sumatera Utara';
    } else {
        $itemName = __('Layanan :company', ['company' => $companyName]);
        $itemDesc = __('Pemesanan layanan wisata.');
        $itemDest = 'Sumatera Utara';
    }

    $statusMap = [
        'pending'   => ['label' => __('MENUNGGU PEMBAYARAN'), 'bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'dot' => 'bg-amber-500'],
        'confirmed' => ['label' => __('PEMBAYARAN DIKONFIRMASI'), 'bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'completed' => ['label' => __('SELESAI'),              'bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200', 'dot' => 'bg-green-500'],
        'cancelled' => ['label' => __('DIBATALKAN'),           'bg' => 'bg-rose-100',    'text' => 'text-rose-700',    'border' => 'border-rose-200',    'dot' => 'bg-rose-500'],
    ];
    $st = $statusMap[$booking->status] ?? $statusMap['pending'];
@endphp
<body class="py-6 px-4 sm:px-6 lg:px-8 flex justify-center min-h-screen">

    <div class="invoice-card bg-white w-full max-w-[850px] mx-auto rounded-lg overflow-hidden border border-neutral-300">

        <div class="p-5 sm:p-10 md:p-14 relative z-10 bg-pattern-subtle">

            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start mb-6 pb-8 border-b-2 border-brand-light">
                <div class="flex items-center gap-4">
                    @if(!empty($logoUrl))
                        <div class="h-14 flex items-center">
                            <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="h-14 w-auto object-contain">
                        </div>
                    @else
                        <div class="w-14 h-14 bg-brand rounded-lg flex items-center justify-center text-white text-2xl font-bold shadow-md">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($companyName, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h1 class="font-serif text-3xl sm:text-4xl text-brand-dark font-bold tracking-tight mb-1">{{ \Illuminate\Support\Str::upper($companyName) }}</h1>
                        <p class="text-neutral-600 text-sm font-medium tracking-wide">{{ $legalName }}@if($taxId) &middot; NPWP: {{ $taxId }}@endif</p>
                    </div>
                </div>
                <div class="mt-6 sm:mt-0 text-left sm:text-right">
                    <h2 class="text-4xl sm:text-5xl font-serif italic text-brand-light font-bold opacity-30 absolute top-10 right-14 pointer-events-none select-none z-0">INVOICE</h2>
                    <h2 class="text-2xl font-bold text-neutral-800 tracking-wider relative z-10">INVOICE</h2>
                    <p class="text-sm text-neutral-500 mt-1 font-medium relative z-10">#{{ $booking->bookingCode }}</p>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                <!-- Billed To -->
                <div class="bg-neutral-100/50 p-5 rounded-xl border border-neutral-200">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-xs font-bold text-brand uppercase tracking-wider">{{ __('Diterbitkan Untuk') }}</h3>
                    </div>
                    <div class="space-y-2">
                        <p class="text-lg font-bold text-neutral-900">{{ $booking->customerName }}</p>
                        @if($booking->customerEmail)
                        <div class="flex items-center gap-2 text-sm text-neutral-600">
                            <span>{{ $booking->customerEmail }}</span>
                        </div>
                        @endif
                        @if($booking->customerPhone)
                        <div class="flex items-center gap-2 text-sm text-neutral-600">
                            <span>{{ $booking->customerPhone }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Details -->
                <div class="bg-brand-light/30 p-5 rounded-xl border border-brand-light/50">
                    <div class="flex items-center gap-2 mb-3">
                        <h3 class="text-xs font-bold text-brand uppercase tracking-wider">{{ __('Rincian Invoice') }}</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-600">{{ __('No. Referensi:') }}</span>
                            <span class="font-bold text-neutral-900 bg-white px-2 py-1 rounded shadow-sm border border-neutral-200">{{ $booking->bookingCode }}</span>
                        </div>
                        {{-- Label ini dulu berbunyi "Tanggal Pesanan" tapi
                             menampilkan tanggal BERANGKAT. Dua-duanya penting,
                             jadi sekarang keduanya ditulis apa adanya. --}}
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-600">{{ __('Tanggal Pesanan:') }}</span>
                            <span class="font-semibold text-neutral-900">{{ optional($booking->createdAt)->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-600">{{ __('Tanggal Berangkat:') }}</span>
                            <span class="font-semibold text-neutral-900">{{ optional($booking->startDate)->format('d M Y') ?? '-' }}</span>
                        </div>
                        @if($dueDate && ! in_array($booking->status, ['completed', 'cancelled'], true))
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-600">{{ __('Jatuh Tempo:') }}</span>
                            <span class="font-bold text-brand-dark">{{ $dueDate->format('d M Y') }}</span>
                        </div>
                        @endif
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-neutral-600">{{ __('Status Pembayaran:') }}</span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $st['bg'] }} {{ $st['text'] }} border {{ $st['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                                {{ $st['label'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="mb-6 bg-white rounded-xl border border-neutral-200 overflow-x-auto shadow-sm">
                <table class="w-full min-w-[560px] text-left border-collapse">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="py-4 px-6 text-xs font-bold text-neutral-600 uppercase tracking-wider w-1/2">{{ __('Deskripsi Layanan') }}</th>
                            <th class="py-4 px-6 text-xs font-bold text-neutral-600 uppercase tracking-wider text-center">{{ __('Kuantitas') }}</th>
                            <th class="py-4 px-6 text-xs font-bold text-neutral-600 uppercase tracking-wider text-right">{{ __('Harga Satuan') }}</th>
                            <th class="py-4 px-6 text-xs font-bold text-brand uppercase tracking-wider text-right">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @if(isset($booking->metadata['price_breakdown']))
                            @php 
                                $pb = $booking->metadata['price_breakdown']; 
                                $paxDewasa = $pb['pax_dewasa'] ?? $pax;
                                $priceDewasaTotal = $pb['price_dewasa_total'] ?? $booking->totalPrice;
                            @endphp
                            <!-- Ekspedisi Dewasa -->
                            <tr class="table-row-hover border-b border-neutral-100">
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-8 h-8 rounded-full bg-brand-light flex items-center justify-center text-brand flex-shrink-0">
                                        </div>
                                        <div>
                                            <p class="font-bold text-neutral-900 text-sm mb-1">{{ $itemName }} ({{ __('Dewasa') }})</p>
                                            <p class="text-xs text-neutral-500 leading-relaxed">
                                                <span class="inline-block mt-1 px-2 py-0.5 bg-neutral-100 rounded text-xs font-medium text-neutral-600">{{ __('Destinasi:') }} {{ $itemDest }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle text-center font-semibold text-neutral-700">{{ $paxDewasa }}x</td>
                                <td class="py-4 px-6 align-middle text-right text-neutral-700">{{ \App\Helpers\CurrencyHelper::formatRecord($priceDewasaTotal / max($paxDewasa, 1), $cur) }}</td>
                                <td class="py-4 px-6 align-middle text-right text-neutral-900 font-bold">{{ \App\Helpers\CurrencyHelper::formatRecord($priceDewasaTotal, $cur) }}</td>
                            </tr>
                            <!-- Anak-anak -->
                            @if(isset($pb['pax_anak']) && $pb['pax_anak'] > 0)
                            <tr class="table-row-hover border-b border-neutral-100">
                                <td class="py-4 px-6 align-middle">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-400 flex-shrink-0">
                                        </div>
                                        <div>
                                            <p class="font-bold text-neutral-900 text-sm mb-1">{{ $itemName }} ({{ __('Anak-anak') }})</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 align-middle text-center font-semibold text-neutral-700">{{ $pb['pax_anak'] }}x</td>
                                <td class="py-4 px-6 align-middle text-right text-neutral-700">{{ \App\Helpers\CurrencyHelper::formatRecord(($pb['price_anak_total'] ?? 0) / max($pb['pax_anak'], 1), $cur) }}</td>
                                <td class="py-4 px-6 align-middle text-right text-neutral-900 font-bold">{{ \App\Helpers\CurrencyHelper::formatRecord($pb['price_anak_total'] ?? 0, $cur) }}</td>
                            </tr>
                            @endif
                            <!-- Additional Services -->
                            @if(isset($pb['additional_services']))
                                @foreach($pb['additional_services'] as $srv)
                                <tr class="table-row-hover border-b border-neutral-100">
                                    <td class="py-4 px-6 align-middle">
                                        <div class="flex items-start gap-3">
                                            <div class="mt-1 w-8 h-8 rounded-full bg-neutral-100 flex items-center justify-center text-neutral-400 flex-shrink-0">
                                            </div>
                                            <div>
                                                <p class="font-bold text-neutral-900 text-sm mb-1">{{ $srv['name'] }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 align-middle text-center font-semibold text-neutral-700">1x</td>
                                    <td class="py-4 px-6 align-middle text-right text-neutral-700">{{ \App\Helpers\CurrencyHelper::formatRecord($srv['price'], $cur) }}</td>
                                    <td class="py-4 px-6 align-middle text-right text-neutral-900 font-bold">{{ \App\Helpers\CurrencyHelper::formatRecord($srv['price'], $cur) }}</td>
                                </tr>
                                @endforeach
                            @endif
                        @else
                            <tr class="table-row-hover">
                                <td class="py-6 px-6 align-top">
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 w-8 h-8 rounded-full bg-brand-light flex items-center justify-center text-brand flex-shrink-0">
                                        </div>
                                        <div>
                                            <p class="font-bold text-neutral-900 text-base mb-1">{{ $itemName }}</p>
                                            <p class="text-sm text-neutral-500 leading-relaxed">
                                                {{ $itemDesc }} <br>
                                                <span class="inline-block mt-1 px-2 py-0.5 bg-neutral-100 rounded text-xs font-medium text-neutral-600">{{ __('Destinasi:') }} {{ $itemDest }}</span>
                                            </p>
                                            {{-- Pesanan lama: pajak tidak bisa dipisahkan; tampilkan note. --}}
                                            <p class="mt-2 text-[11px] text-amber-600 font-medium">{{ __('Total sudah termasuk pajak & layanan.') }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-6 align-middle text-center font-semibold text-neutral-700">{{ $pax }} {{ __('Pax') }}</td>
                                {{-- Harga Satuan disembunyikan untuk pesanan lama. totalPrice
                                     sudah termasuk pajak, jadi totalPrice/pax bukan harga
                                     satuan yang benar. Tampilkan '—' agar tidak menyesatkan. --}}
                                <td class="py-6 px-6 align-middle text-right text-neutral-700">
                                    @if($legacyOrder)
                                        <span class="text-neutral-400">—</span>
                                    @else
                                        {{ \App\Helpers\CurrencyHelper::formatRecord($unitPrice, $cur) }}
                                    @endif
                                </td>
                                <td class="py-6 px-6 align-middle text-right text-neutral-900 font-bold">{{ \App\Helpers\CurrencyHelper::formatRecord($itemLineTotal, $cur) }}</td>
                            </tr>
                        @endif
                        <tr class="table-row-hover bg-white/50 h-8">
                            <td></td><td></td><td></td><td></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals Section -->
            <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-6">

                <!-- Payment Instructions -->
                <div class="w-full md:w-1/2">
                    <div class="bg-gradient-to-br from-brand-dark to-brand rounded-xl p-6 text-white shadow-md relative overflow-hidden">
                        <div class="flex items-center gap-2 mb-3 relative z-10">
                            <h4 class="text-xs font-bold text-brand-accent uppercase tracking-wider">{{ __('Instruksi Pembayaran') }}</h4>
                        </div>
                        {{-- Kalimatnya menyesuaikan diri: kalau rekening belum
                             dikonfigurasi, "transfer ke rekening berikut" menunjuk
                             ruang kosong dan tamu tidak punya cara membayar. --}}
                        <p class="text-sm text-brand-light font-medium leading-relaxed relative z-10">
                            @php
                                $codeChip = '<span class="bg-white/20 px-1.5 py-0.5 rounded text-white font-bold tracking-wider">' . e($booking->bookingCode) . '</span>';
                            @endphp
                            @if($bankAccount)
                                {!! __('Mohon lakukan transfer ke rekening berikut dan lampirkan kode referensi :code pada berita acara transfer Anda.', ['code' => $codeChip]) !!}
                            @else
                                {!! __('Untuk mendapatkan nomor rekening dan instruksi pembayaran, silakan hubungi kami di :contact dengan menyebut kode referensi :code.', [
                                    'contact' => '<a href="' . e(\App\Helpers\ContactHelper::whatsappLink(__('Halo Sujai Toba Sumatera, saya ingin membayar pesanan :code.', ['code' => $booking->bookingCode]))) . '" class="font-bold text-white underline">' . e(\App\Helpers\ContactHelper::whatsappDisplay()) . '</a>',
                                    'code' => $codeChip,
                                ]) !!}
                            @endif
                            @if($dueDate && ! in_array($booking->status, ['completed', 'cancelled'], true))
                                <span class="mt-2 block">{!! __('Pelunasan paling lambat :date.', ['date' => '<strong class="text-white">' . e($dueDate->format('d M Y')) . '</strong>']) !!}</span>
                            @endif
                        </p>

                        @if($bankAccount)
                        <div class="mt-4 pt-4 border-t border-white/20 relative z-10">
                            <div class="flex items-center gap-3">
                                <div class="bg-white/15 rounded p-2 flex items-center justify-center">
                                </div>
                                <div>
                                    <p class="text-xs text-brand-light">{{ __('a.n') }} {{ $bankAccountName }}</p>
                                    <p class="font-bold tracking-wider">{{ $bankAccount }}
                                        <button class="ml-2 text-brand-accent hover:text-white transition-colors no-print" onclick="copyAccount(this)" data-account="{{ $bankAccount }}" title="{{ __('Salin Rekening') }}" aria-label="{{ __('Salin Rekening') }}"><svg data-icon="copy" class="inline w-4 h-4 align-text-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h8a2 2 0 002-2v-2M10 3h8a2 2 0 012 2v10a2 2 0 01-2 2h-8a2 2 0 01-2-2V5a2 2 0 012-2z"/></svg><svg data-icon="done" class="hidden w-4 h-4 align-text-bottom" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></button>
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Calculation -->
                <div class="w-full md:w-5/12 bg-neutral-50 p-6 rounded-xl border border-neutral-200">
                    <div class="space-y-4">
                        @if($subtotalBase !== null)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-neutral-600 font-medium">{{ __('Subtotal') }}</span>
                                <span class="font-bold text-neutral-800">{{ \App\Helpers\CurrencyHelper::formatRecord($subtotalBase, $cur) }}</span>
                            </div>
                            @foreach($surchargeRows as $sc)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-neutral-600 font-medium">{{ $sc['name'] }}</span>
                                <span class="font-bold text-neutral-800">{{ \App\Helpers\CurrencyHelper::formatRecord($sc['amount'] ?? 0, $cur) }}</span>
                            </div>
                            @endforeach
                            {{-- Baris pajak hanya muncul bila memang ada yang
                                 dipungut. "Pajak & Layanan RM 0,00" pada
                                 dokumen keuangan bukan informasi, ia pertanyaan.

                                 JANGAN menempelkan @if langsung di belakang huruf
                                 (mis. "Layanan@if(...)"): Blade tidak mengenalinya
                                 sebagai direktif dan membiarkannya jadi teks, tapi
                                 @endif-nya tetap dikompilasi -- blok if jadi tidak
                                 seimbang dan seluruh view gagal parse. --}}
                            @if(($taxAmount ?? 0) > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-neutral-600 font-medium">{{ __('Pajak & Layanan') }}{{ $taxPercent ? ' (' . $taxPercent . '%)' : '' }}</span>
                                <span class="font-bold text-neutral-800">{{ \App\Helpers\CurrencyHelper::formatRecord($taxAmount, $cur) }}</span>
                            </div>
                            @endif
                        @else
                            {{-- Pesanan lama tanpa rincian tersimpan. Menampilkan
                                 "Pajak 0" untuk pesanan yang pajaknya sebenarnya
                                 sudah termasuk di dalam total akan lebih menyesatkan
                                 daripada tidak menampilkan barisnya sama sekali. --}}
                            <p class="text-[11px] leading-relaxed text-neutral-500">{{ __('Rincian pajak tidak tersimpan untuk pesanan ini. Nilai di bawah adalah total akhir yang berlaku.') }}</p>
                        @endif

                        <div class="pt-4 border-t border-neutral-300 border-dashed">
                            <div class="flex justify-between items-end">
                                <div>
                                    <span class="block text-brand-dark font-bold uppercase tracking-wider text-xs mb-1">{{ __('Total Tagihan') }}</span>
                                    <span class="block text-[10px] text-neutral-500">({{ $cur }})</span>
                                </div>
                                <span class="text-2xl font-bold text-brand-dark">{{ \App\Helpers\CurrencyHelper::formatRecord($booking->totalPrice, $cur) }}</span>
                            </div>
                            @if($cur !== 'IDR')
                            {{-- Reference only, at the rate frozen on the booking date.
                                 The amount owed is the figure above. --}}
                            <p class="mt-3 text-right text-[11px] leading-relaxed text-neutral-500">
                                {{ __('Setara :amount (kurs 1 :currency = Rp :rate, dikunci :date).', [
                                    'amount' => \App\Helpers\CurrencyHelper::formatRecord($booking->totalPrice_idr, 'IDR'),
                                    'currency' => $cur,
                                    'rate' => number_format((float) $booking->exchange_rate_idr, 0, ',', '.'),
                                    'date' => optional($booking->createdAt)->format('d/m/Y'),
                                ]) }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-8 pt-8 border-t border-neutral-200 text-center">
                <p class="font-serif italic text-lg text-brand-dark mb-3">"{{ __('Terima kasih telah memilih kami untuk petualangan Anda selanjutnya.') }}"</p>
                <div class="flex flex-col sm:flex-row justify-center items-center gap-2 sm:gap-4 text-xs text-neutral-500">
                    <span class="flex items-center gap-1">{{ $address }}</span>
                    @if($email)
                    <span class="hidden sm:inline text-neutral-300">|</span>
                    <a href="mailto:{{ $email }}" class="flex items-center gap-1 hover:text-brand transition-colors">{{ $email }}</a>
                    @endif
                    @if($instagram)
                    <span class="hidden sm:inline text-neutral-300">|</span>
                    <a href="https://instagram.com/{{ ltrim($instagram, '@') }}" target="_blank" class="flex items-center gap-1 hover:text-brand transition-colors">{{ '@' . ltrim($instagram, '@') }}</a>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <!-- Action Buttons (Visible only on screen) -->
    <div class="fixed bottom-8 right-8 flex flex-col gap-3 no-print z-50">
        <button onclick="window.print()" class="group relative bg-brand hover:bg-brand-dark text-white font-medium w-12 h-12 rounded-full shadow-lg transition flex items-center justify-center hover:scale-105" title="{{ __('Cetak / Simpan PDF') }}">
            {{-- SVG inline: ikon di dokumen ini tidak boleh bergantung pada CDN. --}}
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
            </svg>
            <span class="absolute right-14 bg-neutral-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap">{{ __('Cetak / Simpan PDF') }}</span>
        </button>
    </div>

    <script>
        // Ikon di tombol ini sudah jadi <svg> inline sejak CDN FontAwesome dilepas,
        // tapi fungsinya masih mencari <i> — querySelector mengembalikan null dan
        // baris berikutnya melempar, jadi tombol tidak pernah memberi konfirmasi.
        function copyAccount(btn) {
            const acc = (btn.getAttribute('data-account') || '').replace(/\s/g, '');
            const copyIcon = btn.querySelector('[data-icon="copy"]');
            const doneIcon = btn.querySelector('[data-icon="done"]');
            navigator.clipboard.writeText(acc).then(() => {
                if (!copyIcon || !doneIcon) return;
                copyIcon.classList.add('hidden');
                doneIcon.classList.remove('hidden');
                setTimeout(() => {
                    copyIcon.classList.remove('hidden');
                    doneIcon.classList.add('hidden');
                }, 1500);
            });
        }
    </script>

</body>
</html>

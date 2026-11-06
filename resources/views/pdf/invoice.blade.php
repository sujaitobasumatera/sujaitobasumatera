<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $booking->bookingCode }}</title>
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', sans-serif; color: #2d3748; margin: 0; padding: 0; font-size: 12px; line-height: 1.5; }
        .accent-bar { height: 8px; background-color: #006b29; }
        .accent-bar td { padding: 0; height: 8px; }
        .gold { background-color: #d4af37; }
        .mid  { background-color: #006b29; }
        .dark { background-color: #004d1e; }
        .container { padding: 36px 44px; }

        h1.brand { font-family: 'Times', serif; font-size: 26px; color: #004d1e; font-weight: bold; margin: 0 0 2px 0; letter-spacing: -0.5px; }
        .legal { color: #718096; font-size: 11px; }
        .invoice-word { font-size: 20px; font-weight: bold; color: #2d3748; letter-spacing: 2px; text-align: right; }
        .invoice-no { color: #718096; font-size: 12px; text-align: right; margin-top: 2px; }

        .divider { border-bottom: 2px solid #e0f2e7; margin: 18px 0 22px 0; }

        .box { border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 16px; }
        .box-billed { background-color: #f7fafc; }
        .box-detail { background-color: #effaf3; border-color: #cdeeda; }
        .box-label { font-size: 10px; font-weight: bold; color: #006b29; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .cust-name { font-size: 15px; font-weight: bold; color: #1a202c; margin-bottom: 4px; }
        .cust-line { font-size: 12px; color: #718096; margin-bottom: 2px; }
        .detail-row td { padding: 3px 0; font-size: 12px; }
        .detail-key { color: #718096; }
        .detail-val { text-align: right; font-weight: bold; color: #1a202c; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 10px; font-weight: bold; text-transform: uppercase; }

        table.items { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; border-radius: 10px; margin-top: 22px; }
        table.items th { background-color: #f7fafc; text-align: left; padding: 12px 16px; font-size: 10px; text-transform: uppercase; color: #718096; letter-spacing: 0.5px; border-bottom: 1px solid #e2e8f0; }
        table.items td { padding: 16px; border-bottom: 1px solid #f1f5f9; font-size: 12px; vertical-align: top; }
        .item-name { font-weight: bold; color: #1a202c; font-size: 13px; margin-bottom: 3px; }
        .item-desc { color: #718096; font-size: 11px; }
        .item-tag { display: inline-block; margin-top: 4px; background-color: #f7fafc; border-radius: 4px; padding: 2px 6px; font-size: 10px; color: #718096; }

        .pay-box { background-color: #004d1e; border-radius: 10px; padding: 16px 18px; color: #e0f2e7; }
        .pay-label { font-size: 10px; font-weight: bold; color: #d4af37; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; }
        .pay-text { font-size: 11px; color: #e0f2e7; line-height: 1.6; }
        .pay-acc { margin-top: 12px; padding-top: 12px; border-top: 1px solid #2c6b44; }
        .pay-acc .name { font-size: 10px; color: #b2dfc3; }
        .pay-acc .num { font-size: 14px; font-weight: bold; letter-spacing: 1px; color: #ffffff; }

        .calc { background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px 18px; }
        .calc td { padding: 5px 0; font-size: 12px; }
        .calc .k { color: #718096; }
        .calc .v { text-align: right; font-weight: bold; color: #2d3748; }
        .calc .total-k { color: #004d1e; font-weight: bold; text-transform: uppercase; font-size: 11px; padding-top: 12px; }
        .calc .total-v { text-align: right; color: #004d1e; font-weight: bold; font-size: 20px; padding-top: 12px; }
        .calc .sep td { border-top: 1px dashed #cbd5e1; padding-top: 0; }

        .footer { margin-top: 32px; padding-top: 18px; border-top: 1px solid #e2e8f0; text-align: center; }
        .quote { font-family: 'Times', serif; font-style: italic; font-size: 14px; color: #004d1e; margin-bottom: 8px; }
        .footer-meta { font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    @php
        $companyName     = $siteSettings['general']['site_name'] ?? 'Sujai Toba Sumatera';
        $legalName       = $siteSettings['company']['legal_name'] ?? 'PT Sujai Toba Sumatera Experience';
        $bankAccount     = $siteSettings['company']['bank_account'] ?? null;
        $bankAccountName = $siteSettings['company']['bank_account_name'] ?? $legalName;
        $taxId           = $siteSettings['company']['tax_id'] ?? null;
        $address         = $siteSettings['general']['office_address'] ?? 'Sumatera Utara';
        $email           = $siteSettings['general']['contact_email'] ?? '';

        $pax           = max((int) ($booking->metadata['pax'] ?? 1), 1);
        $pb            = $booking->metadata['price_breakdown'] ?? null;
        $subtotalBase  = $pb['subtotal_base'] ?? null;
        $surchargeRows = $pb['surcharges'] ?? [];
        $taxAmount     = $pb['tax'] ?? 0;

        $unitPrice     = ($subtotalBase !== null && $subtotalBase > 0)
            ? ($subtotalBase / $pax)
            : ($booking->totalPrice / $pax);
        $itemLineTotal = ($subtotalBase !== null && $subtotalBase > 0)
            ? $subtotalBase
            : $booking->totalPrice;

        $statusMap = [
            'pending'   => ['label' => 'MENUNGGU PEMBAYARAN',     'bg' => '#fef3c7', 'fg' => '#b45309'],
            'confirmed' => ['label' => 'PEMBAYARAN DIKONFIRMASI', 'bg' => '#d1fae1', 'fg' => '#047830'],
            'completed' => ['label' => 'SELESAI',                 'bg' => '#d1fae1', 'fg' => '#047830'],
            'cancelled' => ['label' => 'DIBATALKAN',              'bg' => '#ffe4e6', 'fg' => '#be123c'],
        ];
        $st = $statusMap[$booking->status] ?? $statusMap['pending'];

        $logoUrl  = $siteSettings['general']['logo_light_url'] ?? ($siteSettings['cms_landing']['brand_logo_url'] ?? null);
        $logoData = null;
        if (!empty($logoUrl)) {
            $logoPath = ltrim(str_replace('storage/', '', $logoUrl), '/');
            $fullPath = public_path('storage/' . $logoPath);
            if (file_exists($fullPath)) {
                $mime = mime_content_type($fullPath) ?: 'image/png';
                $logoData = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($fullPath));
            }
        }
    @endphp

    {{-- Top accent bar (green -> green -> gold) --}}
    <table class="accent-bar" width="100%" cellpadding="0" cellspacing="0">
        <tr><td class="dark" width="33%"></td><td class="mid" width="34%"></td><td class="gold" width="33%"></td></tr>
    </table>

    <div class="container">
        {{-- Header --}}
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td style="vertical-align: top;" width="60%">
                    @if($logoData)
                        <img src="{{ $logoData }}" style="height: 42px; width: auto; margin-bottom: 6px;">
                    @endif
                    <h1 class="brand">{{ strtoupper($companyName) }}</h1>
                    <div class="legal">{{ $legalName }}@if($taxId) &middot; NPWP: {{ $taxId }}@endif</div>
                </td>
                <td style="vertical-align: top;" width="40%">
                    <div class="invoice-word">INVOICE</div>
                    <div class="invoice-no">#{{ $booking->bookingCode }}</div>
                </td>
            </tr>
        </table>

        <div class="divider"></div>

        {{-- Info boxes --}}
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td width="49%" style="vertical-align: top;">
                    <div class="box box-billed">
                        <div class="box-label">Diterbitkan Untuk</div>
                        <div class="cust-name">{{ $booking->customerName }}</div>
                        @if($booking->customerEmail)<div class="cust-line">{{ $booking->customerEmail }}</div>@endif
                        @if($booking->customerPhone)<div class="cust-line">{{ $booking->customerPhone }}</div>@endif
                    </div>
                </td>
                <td width="2%"></td>
                <td width="49%" style="vertical-align: top;">
                    <div class="box box-detail">
                        <div class="box-label">Rincian Invoice</div>
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr class="detail-row"><td class="detail-key">No. Referensi</td><td class="detail-val">{{ $booking->bookingCode }}</td></tr>
                            <tr class="detail-row"><td class="detail-key">Tanggal Pesanan</td><td class="detail-val">{{ optional($booking->startDate)->format('d M Y') ?? optional($booking->created_at)->format('d M Y') ?? '-' }}</td></tr>
                            <tr class="detail-row">
                                <td class="detail-key">Status</td>
                                <td style="text-align: right;"><span class="badge" style="background-color: {{ $st['bg'] }}; color: {{ $st['fg'] }};">{{ $st['label'] }}</span></td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Items --}}
        <table class="items" width="100%" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="50%">Deskripsi Layanan</th>
                    <th style="text-align: center;">Kuantitas</th>
                    <th style="text-align: right;">Harga Satuan</th>
                    <th style="text-align: right; color: #006b29;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @if($booking->type === 'package' && $booking->package)
                            <div class="item-name">{{ $booking->package->name }}</div>
                            <div class="item-desc">{{ $booking->package->duration ?? '' }} menikmati pesona wisata Sumatera Utara.</div>
                            <span class="item-tag">Destinasi: {{ $booking->package->city?->name ?? 'Sumatera Utara' }}</span>
                        @else
                            <div class="item-name">Layanan {{ $companyName }}</div>
                            <div class="item-desc">Pemesanan layanan wisata.</div>
                        @endif
                    </td>
                    <td style="text-align: center;">{{ $pax }} Pax</td>
                    <td style="text-align: right;">{{ \App\Helpers\CurrencyHelper::formatRecord($unitPrice, $booking->currency) }}</td>
                    <td style="text-align: right; font-weight: bold; color: #1a202c;">{{ \App\Helpers\CurrencyHelper::formatRecord($itemLineTotal, $booking->currency) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Payment + totals --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 24px;">
            <tr>
                <td width="52%" style="vertical-align: top;">
                    <div class="pay-box">
                        <div class="pay-label">Instruksi Pembayaran</div>
                        <div class="pay-text">Mohon transfer ke rekening berikut dan lampirkan kode referensi <strong style="color:#fff;">{{ $booking->bookingCode }}</strong> pada berita transfer Anda.</div>
                        @if($bankAccount)
                        <div class="pay-acc">
                            <div class="name">a.n {{ $bankAccountName }}</div>
                            <div class="num">{{ $bankAccount }}</div>
                        </div>
                        @endif
                    </div>
                </td>
                <td width="4%"></td>
                <td width="44%" style="vertical-align: top;">
                    <div class="calc">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr><td class="k">Subtotal</td><td class="v">{{ \App\Helpers\CurrencyHelper::formatRecord($itemLineTotal, $booking->currency) }}</td></tr>
                            @if(!empty($surchargeRows))
                                @foreach($surchargeRows as $surcharge)
                                <tr><td class="k">{{ $surcharge['name'] ?? 'Biaya Tambahan' }}</td><td class="v">{{ \App\Helpers\CurrencyHelper::formatRecord($surcharge['amount'] ?? 0, $booking->currency) }}</td></tr>
                                @endforeach
                            @endif
                            @if($taxAmount > 0)
                            <tr><td class="k">Pajak</td><td class="v">{{ \App\Helpers\CurrencyHelper::formatRecord($taxAmount, $booking->currency) }}</td></tr>
                            @endif
                            <tr><td class="k">Diskon</td><td class="v">- {{ \App\Helpers\CurrencyHelper::formatRecord(0, $booking->currency) }}</td></tr>
                            <tr class="sep"><td class="total-k">Total Tagihan ({{ $booking->currency }})</td><td class="total-v">{{ \App\Helpers\CurrencyHelper::formatRecord($booking->totalPrice, $booking->currency) }}</td></tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Footer --}}
        <div class="footer">
            <div class="quote">"Terima kasih telah memilih kami untuk petualangan Anda selanjutnya."</div>
            <div class="footer-meta">{{ $address }}@if($email) &nbsp;|&nbsp; {{ $email }}@endif</div>
        </div>
    </div>
</body>
</html>

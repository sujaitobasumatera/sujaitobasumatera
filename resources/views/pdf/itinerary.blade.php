<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Itinerary - {{ $package->translated_name }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #334155;
            line-height: 1.5;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #05963d;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #0f172a;
            margin: 0;
            font-size: 28px;
            text-transform: uppercase;
        }
        .header p {
            color: #05963d;
            font-weight: bold;
            margin: 5px 0 0 0;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
            border-left: 4px solid #05963d;
            padding-left: 10px;
            margin-bottom: 15px;
            background: #f8fafc;
            padding-top: 5px;
            padding-bottom: 5px;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-grid td {
            padding: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            width: 150px;
        }
        .itinerary-day {
            margin-bottom: 20px;
            padding-left: 15px;
            border-left: 2px solid #e2e8f0;
        }
        .day-title {
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 5px;
        }
        .day-desc {
            font-size: 14px;
            color: #475569;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
        }
        .list-item {
            font-size: 14px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $package->translated_name }}</h1>
        <p>{{ $siteSettings['general']['site_name'] ?? 'Sujai Toba Sumatera' }} – Sumatera Utara</p>
    </div>

    <div class="section">
        <div class="section-title">Informasi Paket</div>
        <table class="info-grid">
            <tr>
                <td class="label">Destinasi</td>
                <td>{{ $city ? $city->name : 'Sumatera Utara' }}</td>
            </tr>
            <tr>
                <td class="label">Durasi</td>
                <td>{{ $package->duration }}</td>
            </tr>
            <tr>
                <td class="label">Harga Mulai Dari</td>
                <td>{{ \App\Helpers\CurrencyHelper::formatIn($package->price, \App\Helpers\CurrencyHelper::PRICE_BASE) }} / orang</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Deskripsi</div>
        <div style="font-size: 14px;">{!! $package->translated_description !!}</div>
    </div>

    @if($package->translated_itinerary_text || ($package->itinerary && count($package->itinerary) > 0))
    <div class="section">
        <div class="section-title">Jadwal Perjalanan</div>
        @if($package->translated_itinerary_text)
            <div style="font-size: 14px; white-space: pre-line;">{!! $package->translated_itinerary_text !!}</div>
        @else
            @foreach($package->itinerary as $day)
                <div class="itinerary-day">
                    <div class="day-title">Hari {{ $day['day'] ?? ($loop->index + 1) }}: {{ $day['title'] ?? '' }}</div>
                    <div class="day-desc">{{ $day['description'] ?? '' }}</div>
                </div>
            @endforeach
        @endif
    </div>
    @endif

    <div style="width: 100%; display: table;">
        <div style="display: table-cell; width: 50%;">
            <div class="section">
                <div class="section-title">Sudah Termasuk</div>
                @foreach($package->includes ?? [] as $item)
                    <div class="list-item">• {{ $item }}</div>
                @endforeach
            </div>
        </div>
        <div style="display: table-cell; width: 50%;">
            <div class="section">
                <div class="section-title">Tidak Termasuk</div>
                @foreach($package->excludes ?? [] as $item)
                    <div class="list-item">• {{ $item }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $siteSettings['general']['site_name'] ?? 'Sujai Toba Sumatera' }}. All rights reserved.</p>
        <p>Hubungi kami: {{ \App\Helpers\ContactHelper::whatsappDisplay() }} | {{ \App\Helpers\ContactHelper::email() }}</p>
    </div>
</body>
</html>

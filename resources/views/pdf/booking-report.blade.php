<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Report</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #10b951; padding-bottom: 10px; }
        .title { font-size: 18px; font-weight: bold; color: #166534; }
        .stats { margin-bottom: 20px; }
        .stat-box { display: inline-block; width: 30%; background: #f8fafc; padding: 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background: #10b951; color: white; padding: 10px; text-align: left; }
        .table td { padding: 10px; border-bottom: 1px solid #e2e8f0; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #94a3b8; }
        .status-pending { color: #f59e0b; font-weight: bold; }
        .status-confirmed { color: #10b951; font-weight: bold; }
        .status-cancelled { color: #ef4444; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">LAPORAN PEMESANAN SUJAI LAKETOBA</div>
        <div style="margin-top: 5px;">Periode: {{ $startDate }} s/d {{ $endDate }}</div>
    </div>

    <div class="stats">
        <div class="stat-box">
            <div style="color: #64748b; font-size: 9px; text-transform: uppercase;">Total Pesanan</div>
            <div style="font-size: 16px; font-weight: bold;">{{ $totalBookings }}</div>
        </div>
        <div class="stat-box" style="margin-left: 3%;">
            <div style="color: #64748b; font-size: 9px; text-transform: uppercase;">Total Pendapatan</div>
            <div style="font-size: 16px; font-weight: bold; color: #05963d;">{{ \App\Helpers\CurrencyHelper::formatIn($totalRevenue, 'IDR') }}</div>
        </div>
        <div class="stat-box" style="margin-left: 3%;">
            <div style="color: #64748b; font-size: 9px; text-transform: uppercase;">Dicetak Pada</div>
            <div style="font-size: 14px; font-weight: bold;">{{ $generatedAt }}</div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th>Tanggal Tour</th>
                <th>Pax</th>
                <th>Total Harga</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookings as $b)
            <tr>
                <td><strong>{{ $b->bookingCode }}</strong></td>
                <td>{{ $b->customerName }}<br><span style="color: #64748b; font-size: 9px;">{{ $b->customerPhone }}</span></td>
                <td>{{ $b->package->name ?? 'N/A' }}</td>
                <td>{{ $b->startDate }}</td>
                <td>{{ $b->metadata['pax'] ?? 1 }}</td>
                <td>{{ \App\Helpers\CurrencyHelper::formatIn($b->totalPrice, $b->currency) }}</td>
                <td>
                    <span class="status-{{ strtolower($b->status) }}">{{ ucfirst($b->status) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        © {{ date('Y') }} Sujai Toba Sumatera – Laporan Sistem Internal. Rahasia & Terbatas.
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'my' ? 'ms' : str_replace('_', '-', app()->getLocale()) }}" class="antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — {{ __('Terjadi Kesalahan') }} | Sujai Toba Sumatera</title>
    <meta name="robots" content="noindex, nofollow">
    {{-- Sama seperti 503: tanpa database, tanpa aset dari luar. Halaman yang
         muncul saat sistem gagal tidak boleh ikut bergantung pada sistem itu. --}}
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #0b0f19; color: #e2e8f0; padding: 24px;
            font-family: "Plus Jakarta Sans", ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        .card { max-width: 560px; width: 100%; text-align: center; }
        .badge {
            display: inline-block; padding: 6px 14px; border-radius: 999px; font-size: 11px;
            font-weight: 800; letter-spacing: .18em; text-transform: uppercase;
            color: #fca5a5; background: rgba(186, 26, 26, .18); border: 1px solid rgba(252, 165, 165, .25);
        }
        h1 { margin: 24px 0 12px; font-size: 28px; line-height: 1.25; font-weight: 800; color: #fff; }
        p { margin: 0 auto 28px; max-width: 44ch; font-size: 14px; line-height: 1.8; color: #94a3b8; }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; }
        a.btn {
            display: inline-block; padding: 14px 26px; border-radius: 14px; text-decoration: none;
            font-size: 11px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase;
        }
        a.primary { background: #166534; color: #fff; }
        a.ghost { background: rgba(255,255,255,.05); color: #fff; border: 1px solid rgba(255,255,255,.12); }
        .note { margin-top: 28px; font-size: 11px; color: #64748b; }
        @media (min-width: 768px) { h1 { font-size: 34px; } }
    </style>
</head>
<body>
    <div class="card">
        <span class="badge">500</span>
        <h1>{{ __('Terjadi kesalahan di sisi kami') }}</h1>
        <p>{{ __('Tim kami sudah menerima laporannya. Kalau Anda sedang menyelesaikan pemesanan, jangan diulang dari awal — cek dulu status pesanan Anda dengan kode booking.') }}</p>
        <div class="actions">
            <a class="btn primary" href="{{ url('/') }}">{{ __('Kembali ke Beranda') }}</a>
            <a class="btn ghost" href="{{ url('/track-booking') }}">{{ __('Lacak Pesanan') }}</a>
        </div>
        <p class="note">{{ __('Kode kesalahan') }}: 500</p>
    </div>
</body>
</html>

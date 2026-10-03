<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance - Sujai Toba Sumatera</title>
    @vite(['resources/css/app.css'])
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="max-w-xl w-full text-center space-y-6">
        <div class="relative inline-block">
            <div class="w-32 h-32 bg-slate-900 rounded-[2.5rem] flex items-center justify-center mx-auto shadow-2xl shadow-slate-200">
                <i class="fas fa-hammer text-4xl text-white"></i>
            </div>
            <div class="absolute -bottom-2 -right-2 w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center shadow-lg border-4 border-slate-50">
                <i class="fas fa-cog text-slate-900 animate-spin"></i>
            </div>
        </div>

        <div class="space-y-4">
            <h1 class="text-4xl font-black text-slate-900 tracking-tight leading-tight">Sedang Pembaruan</h1>
            <p class="text-slate-500 font-bold leading-relaxed">Sujai Toba Sumatera sedang melakukan pemeliharaan rutin untuk meningkatkan pengalaman perjalanan Anda. Kami akan segera kembali!</p>
        </div>

        <div class="bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm flex flex-col items-center">
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Butuh bantuan mendesak?</p>
            {{-- Nomor diambil dari config/const, BUKAN dari ContactHelper:
                 halaman ini tampil saat aplikasi sedang mati, jadi tidak boleh
                 menyentuh database. '628123456789' yang dulu tertulis di sini
                 adalah nomor contoh — tombol darurat yang tidak menghubungi
                 siapa pun. --}}
            @php
                $waMaintenance = config('services.whatsapp.number') ?: \App\Helpers\ContactHelper::DEFAULT_WHATSAPP;
            @endphp
            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $waMaintenance) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-3 px-8 py-4 bg-toba-green text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-primary-container transition shadow-xl shadow-toba-green/20">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.46 1.32 4.96L2 22l5.25-1.38a9.9 9.9 0 004.79 1.22h.01c5.46 0 9.9-4.45 9.9-9.91C21.95 6.45 17.5 2 12.04 2zm0 18.15h-.01a8.2 8.2 0 01-4.19-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.19 8.19 0 01-1.26-4.38c0-4.54 3.7-8.23 8.25-8.23a8.2 8.2 0 018.23 8.24c0 4.54-3.69 8.23-8.23 8.23z"/></svg>
                Hubungi WhatsApp
            </a>
        </div>

        <p class="text-[9px] font-black text-slate-300 uppercase tracking-[0.4em]">Sujai Toba Sumatera &bull; Management v3.0</p>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>

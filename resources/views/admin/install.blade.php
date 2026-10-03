<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Install Aplikasi Sujai Admin</title>

    {{-- Manifest publik khusus untuk packaging APK (TWA) --}}
    <link rel="manifest" href="{{ url('/admin-app/manifest.webmanifest') }}">
    <meta name="theme-color" content="#1e40af">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Sujai Admin">
    <link rel="apple-touch-icon" href="/icon-192.png">
    <link rel="icon" href="/icon-192.png">

    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { font-family: 'Inter', system-ui, sans-serif; } </style>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen p-6">
    <div class="text-center p-8 bg-white rounded-2xl shadow-sm border border-slate-200 max-w-sm mx-auto">
        <img src="/icon-192.png" alt="Sujai Admin" class="w-20 h-20 mx-auto mb-4 rounded-2xl shadow">
        <h1 class="text-2xl font-bold text-slate-800 mb-2">Sujai Admin</h1>
        <p class="text-slate-600 mb-6 text-sm">Panel manajemen wisata Sujai Toba Sumatera. Khusus admin.</p>
        <a href="/admin" class="inline-block bg-blue-600 text-white font-semibold py-2.5 px-8 rounded-lg hover:bg-blue-700 transition">
            Buka Panel Admin
        </a>
        <p class="text-slate-400 mt-6 text-xs">Untuk meng-install, gunakan menu browser → "Install app".</p>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/admin-sw.js', { scope: '/admin/' })
                .catch(function (err) { console.warn('Admin SW registration failed:', err); });
        }
    </script>
</body>
</html>

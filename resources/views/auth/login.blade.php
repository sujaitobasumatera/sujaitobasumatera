<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sujai Toba Sumatera Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-toba-green to-green-700 min-h-screen flex items-center justify-center p-4" x-data="{ isSubmitting: false }">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            @php
                $logoUrl = $siteSettings['general']['logo_light_url'] ?? ($siteSettings['cms_landing']['brand_logo_url'] ?? null);
                if ($logoUrl && !Str::startsWith($logoUrl, ['http', '//', 'data:', 'blob:'])) {
                    $cleanLogo = ltrim($logoUrl, '/');
                    if (Str::startsWith($cleanLogo, 'assets/')) {
                        $logoUrl = asset($cleanLogo);
                    } else {
                        $logoUrl = asset('storage/' . ltrim(str_replace('storage/', '', $cleanLogo), '/'));
                    }
                }
            @endphp

            @if(!empty($logoUrl))
                <img src="{{ $logoUrl }}" class="h-16 w-auto object-contain mx-auto brightness-0 invert drop-shadow-xl mb-2">
            @else
                <h1 class="text-4xl font-black text-white mb-2">{{ $siteSettings['general']['site_name'] ?? 'Sujai Toba Sumatera' }}</h1>
            @endif
            <p class="text-white/80 font-black uppercase tracking-[0.3em] text-[10px]">Management Panel</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-2xl font-black text-gray-900 mb-6">Login</h2>

            @if($errors->any())
                <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6" @submit="isSubmitting = true">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                        Email Address
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}"
                        required 
                        autofocus
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-toba-green focus:border-transparent transition"
                        placeholder="your@email.com"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-toba-green focus:border-transparent transition"
                        placeholder="••••••••"
                    >
                </div>

                <!-- Remember Me -->
                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="w-4 h-4 text-toba-green border-gray-300 rounded focus:ring-toba-green"
                    >
                    <label for="remember" class="ml-2 text-sm text-gray-700">
                        Remember me
                    </label>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    :disabled="isSubmitting"
                    class="w-full bg-toba-green text-white py-3 rounded-lg font-bold hover:bg-toba-green/90 transition transform hover:scale-105 flex items-center justify-center gap-2 disabled:opacity-50"
                >
                    <span x-show="!isSubmitting">Login</span>
                    <div x-show="isSubmitting" class="flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Authenticating...
                    </div>
                </button>
            </form>

            <!-- Back to Website -->
            <div class="mt-6 text-center">
                <a href="{{ route('index') }}" class="text-sm text-toba-green hover:text-toba-green/80 font-bold">
                    ← Back to Website
                </a>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 text-white/60 text-sm">
            <p>&copy; {{ date('Y') }} Sujai Toba Sumatera. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

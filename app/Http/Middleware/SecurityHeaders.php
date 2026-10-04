<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Jangan beri tahu dunia versi PHP yang dipakai.
        header_remove('X-Powered-By');
        $response->headers->remove('X-Powered-By');

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', $this->contentSecurityPolicy());

        return $response;
    }

    /**
     * CSP produksi tidak lagi mengizinkan 127.0.0.1 (dulu bocor dari konfigurasi
     * Vite dev server). Host lokal hanya dibuka di environment `local`.
     *
     * frame-src WAJIB sejalan dengan Package::videoEmbedUrl() dan
     * Package::mapEmbedUrl(). Keduanya yang menentukan host apa saja yang
     * bisa muncul sebagai <iframe> di halaman detail paket; host yang lolos
     * di sana tapi tidak terdaftar di sini gagal DIAM-DIAM -- browser cuma
     * menampilkan kotak kelabu "This content is blocked", tanpa error di
     * log server. Menambah penyedia video/peta baru = ubah dua tempat.
     *
     * 'unsafe-inline'/'unsafe-eval' dipertahankan karena Alpine.js dan skrip
     * inline Blade memerlukannya; ruang serang lainnya ditutup lewat
     * object-src, base-uri, dan frame-ancestors.
     */
    private function contentSecurityPolicy(): string
    {
        $local = app()->environment('local');
        $dev = $local ? ' http://127.0.0.1:* http://localhost:*' : '';
        $devWs = $local ? ' http://127.0.0.1:* http://localhost:* ws://127.0.0.1:* ws://localhost:*' : '';

        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'{$dev} https://cdn.jsdelivr.net https://www.youtube.com https://s.ytimg.com https://www.googletagmanager.com https://connect.facebook.net",
            "style-src 'self' 'unsafe-inline'{$dev} https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com",
            "img-src 'self' data: blob: https:",
            "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net",
            "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com https://*.google.com https://*.google.co.id https://*.google.com.my",
            "connect-src 'self'{$devWs} https://www.google-analytics.com https://*.google-analytics.com https://*.analytics.google.com https://www.googletagmanager.com https://www.facebook.com https://connect.facebook.net",
            "object-src 'none'",
            "base-uri 'self'",
            "frame-ancestors 'self'",
        ];

        if (! $local) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives).';';
    }
}

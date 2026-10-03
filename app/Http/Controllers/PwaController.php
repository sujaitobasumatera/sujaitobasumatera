<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaController extends Controller
{
    /**
     * PWA Android (.apk) packaging — public manifest.
     */
    public function manifestPublic()
    {
        return response()->json([
            'name'             => 'Sujai Admin',
            'short_name'       => 'Sujai Admin',
            'description'      => 'Panel manajemen wisata Sujai Toba Sumatera',
            'start_url'        => '/admin/',
            'scope'            => '/',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#f8fafc',
            'theme_color'      => '#1e40af',
            'icons'            => [
                ['src' => '/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
                ['src' => '/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json']);
    }

    /**
     * Digital Asset Links
     */
    public function assetLinks()
    {
        $fingerprints = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) config('services.pwa_android.fingerprint'))
        )));

        return response()->json([[
            'relation' => ['delegate_permission/common.handle_all_urls'],
            'target'   => [
                'namespace'                => 'android_app',
                'package_name'             => config('services.pwa_android.package'),
                'sha256_cert_fingerprints' => $fingerprints,
            ],
        ]]);
    }

    /**
     * PWA - Admin Panel Progressive Web App Manifest (Auth required).
     */
    public function manifestAdmin()
    {
        // Only Superadmin can access the manifest — returns 403 otherwise
        if (!auth()->check() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Access denied.');
        }
        $manifest = [
            'name'             => 'Sujai Admin Panel',
            'short_name'       => 'Sujai Admin',
            'description'      => 'Panel manajemen wisata Sujai Toba Sumatera — khusus Superadmin',
            'start_url'        => '/admin/',
            'scope'            => '/admin/',
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#f8fafc',
            'theme_color'      => '#1e40af',
            'categories'       => ['business', 'productivity'],
            'icons'            => [
                [
                    'src'     => '/icon-192.png',
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
                [
                    'src'     => '/icon-512.png',
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];
        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    /**
     * PWA Offline page.
     */
    public function offline()
    {
        return view('admin.offline');
    }
}

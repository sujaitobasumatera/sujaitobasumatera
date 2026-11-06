<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Peran di tabel users tertinggal dari kosakata yang dipakai kode.
 *
 * Kode hanya mengenal empat peran — superadmin, admin_umum, admin_tour, user
 * (validasi UserController.php:63) — dan gerbang panel di routes/web.php:63
 * cuma mengizinkan superadmin, admin_tour, admin_umum.
 *
 * Baris lama masih memakai 'admin', 'admin_finance', dan 'super_admin'.
 * Akibatnya admin@ dan finance@ kena 403 di SELURUH panel admin, sementara
 * superadmin@ lolos hanya karena RoleMiddleware kebetulan membuang underscore
 * sebelum membandingkan — bukan karena dirancang begitu.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 'admin' (Admin Utama) diperlakukan setara pemilik.
        DB::table('users')->where('role', 'admin')->update(['role' => 'superadmin']);

        // Selama ini lolos karena normalisasi underscore. Dibakukan supaya tidak
        // ikut terkunci bila normalisasi itu suatu saat dirapikan.
        DB::table('users')->where('role', 'super_admin')->update(['role' => 'superadmin']);

        // admin_umum = "Admin Umum (Keuangan & Pengaturan)" di users/create.blade.php:74,
        // pengganti yang dimaksud untuk admin_finance.
        DB::table('users')->where('role', 'admin_finance')->update(['role' => 'admin_umum']);
    }

    public function down(): void
    {
        // 'admin' dan 'super_admin' sama-sama menjadi 'superadmin', jadi arah
        // baliknya tidak bisa disimpulkan dari nilai peran. Dikunci ke email.
        DB::table('users')->where('email', 'admin@sujaitobasumatera.com')->update(['role' => 'admin']);
        DB::table('users')->where('email', 'superadmin@sujaitobasumatera.com')->update(['role' => 'super_admin']);
        DB::table('users')->where('email', 'finance@sujaitobasumatera.com')->update(['role' => 'admin_finance']);
    }
};

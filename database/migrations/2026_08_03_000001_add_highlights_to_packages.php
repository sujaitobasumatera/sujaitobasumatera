<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pembeda khusus paket ini.
     *
     * Blok "Kenapa Kami Berbeda" selama ini dibaca dari setelan situs, jadi
     * kalimatnya sama persis di kedelapan paket. Yang benar-benar menjual satu
     * paket justru hal yang cuma berlaku di paket itu -- misalnya satu titik
     * pandang yang operator lain tidak mau datangi karena jalannya sempit.
     * Kalimat sebesar itu tidak punya tempat menetap sampai sekarang.
     *
     * Formatnya JSON, satu baris per poin: {title, text}. Sengaja sama persis
     * dengan bentuk cms_tour.detail_usp supaya keduanya bisa dirender oleh satu
     * potong Blade yang sama, dan paket yang belum diisi jatuh ke poin situs
     * tanpa cabang tambahan di tampilan.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('highlights')->nullable()->after('accommodations');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('highlights');
        });
    }
};

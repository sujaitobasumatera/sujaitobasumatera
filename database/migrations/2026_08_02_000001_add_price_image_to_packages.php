<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gambar informasi harga: satu berkas per paket, tampil di bawah tombol
     * Booking & WhatsApp pada kartu paket.
     *
     * Kolom sendiri, bukan disisipkan ke `images`: isi `images` adalah galeri
     * foto destinasi yang dirender sebagai carousel dan ikut jadi kandidat
     * `first_image`. Daftar harga bukan foto destinasi -- kalau ikut ke sana
     * ia bisa terpilih jadi gambar sampul paket.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('priceImage')->nullable()->after('brochure');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('priceImage');
        });
    }
};

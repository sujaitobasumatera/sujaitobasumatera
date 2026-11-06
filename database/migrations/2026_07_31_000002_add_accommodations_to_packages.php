<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar penginapan per malam.
     *
     * Tamu ingin tahu tidur di mana sebelum bertanya harga -- itu blok paling
     * menonjol di halaman kompetitor dan yang paling sering ditanyakan lewat
     * chat. Formatnya JSON, satu baris per malam:
     * {night, name, class, image}.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('accommodations')->nullable()->after('itinerary');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('accommodations');
        });
    }
};

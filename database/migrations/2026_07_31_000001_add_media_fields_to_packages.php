<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Media tambahan paket: video (tautan ATAU berkas unggahan, boleh lebih
     * dari satu), peta lokasi, dan brosur PDF.
     *
     * `videos` sengaja satu kolom JSON, bukan tabel terpisah: isinya daftar
     * pendek yang selalu dibaca bersama paketnya dan tidak pernah dicari
     * sendiri -- sama persis dengan pola `images`/`itinerary` di tabel ini.
     * Tiap barisnya {type: link|file, src, title}.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->json('videos')->nullable()->after('images');
            $table->text('mapEmbed')->nullable()->after('locationTag');
            $table->string('brochure')->nullable()->after('videos');
        });
    }

    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['videos', 'mapEmbed', 'brochure']);
        });
    }
};

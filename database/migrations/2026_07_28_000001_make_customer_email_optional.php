<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Email tidak lagi diminta saat memesan.
 *
 * Pasarnya berjalan lewat WhatsApp; email adalah kolom paling memberatkan di
 * form dan tidak pernah dipakai untuk menghubungi tamu — notifikasi booking
 * hanya dikirim ke admin. Tapi email dulu merangkap KUNCI PENGENAL pelanggan
 * (`Customer::firstOrNew(['email' => ...])`). Tanpa pengganti, setiap tamu
 * tanpa email akan bertabrakan jadi satu baris pelanggan.
 *
 * Karena itu ditambahkan `phone_key`: sembilan digit terakhir nomor telepon.
 * Sembilan digit terakhir stabil untuk bentuk 08xx, +628xx, maupun 628xx —
 * jadi tamu yang menulis nomornya berbeda-beda tetap dikenali satu orang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customerEmail')->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('email')->nullable()->change();

            if (! Schema::hasColumn('customers', 'phone_key')) {
                $table->string('phone_key', 20)->nullable()->after('phone')->index();
            }
        });

        // Backfill: pelanggan lama diberi kunci dari nomor yang sudah tersimpan.
        foreach (DB::table('customers')->select('id', 'phone')->get() as $row) {
            $key = self::phoneKey($row->phone);
            if ($key !== null) {
                DB::table('customers')->where('id', $row->id)->update(['phone_key' => $key]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'phone_key')) {
                $table->dropIndex(['phone_key']);
                $table->dropColumn('phone_key');
            }
        });

        // email dikembalikan wajib hanya bila tidak ada baris yang kosong,
        // supaya rollback tidak gagal di tengah jalan.
        if (DB::table('customers')->whereNull('email')->doesntExist()) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('email')->nullable(false)->change();
            });
        }

        if (DB::table('bookings')->whereNull('customerEmail')->doesntExist()) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('customerEmail')->nullable(false)->change();
            });
        }
    }

    private static function phoneKey(?string $phone): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', (string) $phone);

        return strlen($digits) >= 9 ? substr($digits, -9) : null;
    }
};

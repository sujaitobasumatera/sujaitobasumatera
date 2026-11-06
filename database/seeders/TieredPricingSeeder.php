<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Harga bertingkat per jumlah pax untuk paket 3D2N, 4D3N, dan 5D4N.
 *
 * SEMUA ANGKA DALAM MYR (ringgit), per orang dewasa. Basis harga jual memang
 * ringgit sejak 2026_07_21_000002.
 *
 * Jalankan:
 *   php artisan db:seed --class=TieredPricingSeeder
 *
 * Aman diulang: menimpa pricingDetails paket yang disebut, tidak menyentuh
 * yang lain, dan tidak menduplikasi paket 5D4N bila sudah ada.
 *
 * CATATAN SOAL VAN
 * Daftar harga aslinya memuat dua harga untuk 6 pax — biasa dan Van. Tier
 * dicocokkan dari jumlah pax, jadi satu jumlah hanya bisa punya satu harga.
 * Di sini tier 6 memakai harga BIASA, dan Van menjadi layanan tambahan
 * opsional. Alasannya: memakai harga Van membuat kurva harga patah — 6 orang
 * jadi lebih mahal per kepala (RM 850) daripada 5 orang (RM 775), padahal
 * 10 orang kembali turun ke RM 650. Pembeli akan memesan sebagai 5 pax lalu
 * menambah satu orang belakangan.
 *
 * Harga layanan Van = selisih per pax x 6 pax, jadi persis benar untuk
 * rombongan 6. Layanan tambahan dihitung FLAT oleh BookingService, bukan per
 * pax, sehingga akan salah bila dipilih rombongan berukuran lain.
 */
class TieredPricingSeeder extends Seeder
{
    /**
     * Tier harga per paket. Kunci = slug.
     *
     * min_pax/max_pax inklusif. Tier terakhir sengaja dibuka lebar: bila pax
     * melampaui tier tertinggi, BookingService memakai tier tertinggi itu.
     */
    protected array $plans = [
        'paket-danau-toba-3d2n-premium-hotel-entrance-fee' => [
            'label' => '3D2N',
            'create_if_missing' => false,
            'tiers' => [
                ['min_pax' => 1,  'max_pax' => 2,  'price' => 800],
                ['min_pax' => 3,  'max_pax' => 3,  'price' => 600],
                ['min_pax' => 4,  'max_pax' => 4,  'price' => 550],
                ['min_pax' => 5,  'max_pax' => 5,  'price' => 500],
                ['min_pax' => 6,  'max_pax' => 9,  'price' => 475],
                ['min_pax' => 10, 'max_pax' => 99, 'price' => 450],
            ],
            // (600 - 475) x 6 pax
            'van_price' => 750,
            'base_price' => 800,
        ],

        'paket-samosir-adventure-4d3n' => [
            'label' => '4D3N',
            'create_if_missing' => false,
            'tiers' => [
                ['min_pax' => 1,  'max_pax' => 2,  'price' => 900],
                ['min_pax' => 3,  'max_pax' => 3,  'price' => 850],
                ['min_pax' => 4,  'max_pax' => 4,  'price' => 700],
                ['min_pax' => 5,  'max_pax' => 5,  'price' => 650],
                ['min_pax' => 6,  'max_pax' => 9,  'price' => 600],
                ['min_pax' => 10, 'max_pax' => 99, 'price' => 600],
            ],
            // (700 - 600) x 6 pax
            'van_price' => 600,
            'base_price' => 900,
        ],

        'paket-tour-danau-toba-5d4n' => [
            'label' => '5D4N',
            'create_if_missing' => true,
            'tiers' => [
                ['min_pax' => 1,  'max_pax' => 2,  'price' => 1100],
                ['min_pax' => 3,  'max_pax' => 3,  'price' => 900],
                ['min_pax' => 4,  'max_pax' => 4,  'price' => 800],
                ['min_pax' => 5,  'max_pax' => 5,  'price' => 775],
                ['min_pax' => 6,  'max_pax' => 9,  'price' => 750],
                ['min_pax' => 10, 'max_pax' => 99, 'price' => 650],
            ],
            // (850 - 750) x 6 pax
            'van_price' => 600,
            'base_price' => 1100,
        ],
    ];

    public function run(): void
    {
        foreach ($this->plans as $slug => $plan) {
            $package = DB::table('packages')->where('slug', $slug)->first();

            if (! $package) {
                // Hanya paket yang memang belum ada yang boleh dibuat. Tanpa
                // penjaga ini, satu salah ketik pada slug diam-diam melahirkan
                // paket duplikat alih-alih memperbarui yang dimaksud —
                // persis yang terjadi saat seeder ini pertama diuji.
                if (empty($plan['create_if_missing'])) {
                    $this->command?->error(
                        "Paket {$plan['label']} dengan slug '{$slug}' tidak ditemukan. ".
                        'Tidak dibuat baru — periksa slugnya. Tidak ada yang diubah.'
                    );

                    continue;
                }

                $package = $this->createPlaceholder($slug, $plan);
                if (! $package) {
                    $this->command?->warn("Lewati {$slug}: gagal dibuat.");

                    continue;
                }
            }

            $details = is_string($package->pricingDetails)
                ? (json_decode($package->pricingDetails, true) ?: [])
                : ((array) $package->pricingDetails ?: []);

            $details['tiers'] = $plan['tiers'];
            $details['additional_services'] = $this->mergeVanService(
                $details['additional_services'] ?? [],
                $plan['van_price']
            );

            DB::table('packages')->where('id', $package->id)->update([
                // Kartu paket menampilkan harga dasar ini. Dipasang ke tier
                // 2 pax — yang benar-benar dibayar pasangan — bukan tier
                // termurah, supaya angka di kartu tidak lebih rendah daripada
                // yang akan mereka bayar.
                'price' => $plan['base_price'],
                'pricingDetails' => json_encode($details),
                'updatedAt' => now(),
            ]);

            $this->command?->info("{$plan['label']} ({$slug}): ".count($plan['tiers']).' tier + layanan Van.');
        }
    }

    /**
     * Sisipkan/segarkan layanan Van tanpa menghapus layanan lain yang sudah ada.
     */
    protected function mergeVanService(array $services, float $price): array
    {
        $name = 'Upgrade Van (6 pax)';

        foreach ($services as $i => $service) {
            if (($service['name'] ?? null) === $name) {
                $services[$i]['price'] = $price;

                return array_values($services);
            }
        }

        $services[] = [
            'name' => $name,
            'icon' => 'airport_shuttle',
            'price' => $price,
        ];

        return array_values($services);
    }

    /**
     * Paket 5D4N belum ada. Dibuat sebagai rangka yang TIDAK aktif, supaya
     * tidak tampil ke pengunjung sebelum deskripsi dan fotonya diisi.
     */
    protected function createPlaceholder(string $slug, array $plan)
    {
        $id = DB::table('packages')->insertGetId([
            'slug' => $slug,
            'name' => 'Paket Tour Danau Toba '.$plan['label'],
            'shortDescription' => 'Perjalanan '.$plan['label'].' menyusuri Danau Toba dan sekitarnya.',
            'description' => 'Draf. Lengkapi deskripsi, itinerary, dan foto sebelum diaktifkan.',
            'duration' => '5 Hari 4 Malam',
            'priceDisplay' => 'per orang',
            'price' => $plan['base_price'],
            'images' => json_encode([]),
            'includes' => json_encode([]),
            'excludes' => json_encode([]),
            'pricingDetails' => json_encode([]),
            'status' => 'inactive',
            'isFeatured' => false,
            'createdAt' => now(),
            'updatedAt' => now(),
        ]);

        $this->command?->warn("Paket {$plan['label']} dibuat sebagai DRAF (status inactive) — lengkapi isinya lalu aktifkan.");

        return DB::table('packages')->where('id', $id)->first();
    }
}

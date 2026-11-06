<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageContentTest extends TestCase
{
    use RefreshDatabase;

    private function makePackage(): Package
    {
        return Package::create([
            'slug' => 'paket-isi-lengkap',
            'name' => 'Paket Isi Lengkap',
            'shortDescription' => 'Ringkas',
            'description' => 'Lengkap',
            'images' => [],
            'includes' => ['Hotel bintang 3', 'Transportasi AC', 'Tiket masuk'],
            'excludes' => ['Tiket pesawat', 'Pengeluaran pribadi'],
            'itinerary' => [
                ['day' => 1, 'title' => 'Penjemputan - Parapat', 'activities' => ['Jemput bandara', 'Makan siang']],
                ['day' => 2, 'title' => 'Samosir', 'activities' => ['Tomok', 'Tuktuk']],
            ],
            'pricingDetails' => [],
            'translations' => [],
            'price' => 500,
            'duration' => '2 Hari',
            'status' => 'active',
            'isFeatured' => true,
        ]);
    }

    /**
     * Ambil objek `package` yang benar-benar dikirim ke Alpine di halaman detail.
     */
    private function payloadFrom(string $html): array
    {
        $this->assertMatchesRegularExpression("/package:\s*JSON\.parse\('/", $html, 'payload package tidak ditemukan');
        preg_match("/package:\s*JSON\.parse\('(.*?)'\),/s", $html, $m);
        // Isinya literal string JS (Js::from meng-escape kutip jadi " agar
        // aman di dalam atribut). Dibungkus tanda kutip lalu di-decode sekali
        // sebagai string JSON — itu membalik escape JS-nya persis seperti yang
        // dilakukan browser sebelum JSON.parse berjalan.
        $jsLiteral = json_decode('"'.$m[1].'"');
        $this->assertIsString($jsLiteral, 'literal JS payload tidak bisa dibaca');

        $decoded = json_decode($jsLiteral, true);
        $this->assertIsArray($decoded, 'payload package bukan JSON yang sah');

        return $decoded;
    }

    public function test_detail_page_binds_only_to_keys_that_exist_in_the_payload(): void
    {
        // Penjaga umum untuk seluruh kelas bug ini. "Termasuk" dan "Tidak
        // Termasuk" dulu membaca package.package_includes / package_excludes --
        // relasi yang tidak pernah ikut dimuat, jadi kuncinya bahkan tidak ada
        // di objek package. x-for berjalan atas undefined dan tidak merender
        // apa pun: kedua kotak kosong di SETIAP paket, tanpa satu pun error,
        // sementara datanya duduk lengkap di package.includes.
        $package = $this->makePackage();

        $html = $this->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->getContent();

        $payload = $this->payloadFrom($html);

        preg_match_all('/x-for="\([^)]*\) in \(package\.([a-zA-Z_]+)/', $html, $matches);
        $this->assertNotEmpty($matches[1], 'tidak ada x-for atas package.* yang ditemukan');

        foreach (array_unique($matches[1]) as $key) {
            $this->assertArrayHasKey(
                $key,
                $payload,
                "template merender package.{$key}, tapi kunci itu tidak ada di payload -- daftarnya akan terbit kosong tanpa error"
            );
        }
    }

    public function test_detail_page_no_longer_reads_the_unloaded_relation(): void
    {
        $package = $this->makePackage();

        $this->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->assertDontSee('package.package_includes', false)
            ->assertDontSee('package.package_excludes', false)
            ->assertSee('package.includes', false)
            ->assertSee('package.excludes', false);
    }

    public function test_screen_reader_summary_lists_the_inclusions_too(): void
    {
        // Ringkasan sr-only ini dulu membaca pricingDetails['includes'] --
        // lokasi ketiga yang tidak pernah ditulis form admin dan kosong di
        // seluruh paket. Akibatnya pembaca layar dan crawler tidak pernah
        // mendengar satu pun isi paket, walau daftarnya terpampang di layar.
        $package = $this->makePackage();

        $this->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->assertSee('Hotel bintang 3', false)
            ->assertSee('Tiket pesawat', false)
            ->assertSee('Penjemputan - Parapat', false);
    }

    public function test_detail_payload_actually_carries_the_inclusions(): void
    {
        $package = $this->makePackage();

        $payload = $this->payloadFrom(
            $this->get(route('tour.package.detail', $package->slug))->getContent()
        );

        $this->assertSame(['Hotel bintang 3', 'Transportasi AC', 'Tiket masuk'], $payload['includes']);
        $this->assertSame(['Tiket pesawat', 'Pengeluaran pribadi'], $payload['excludes']);
    }

    public function test_detail_page_has_customer_experience_features(): void
    {
        $package = $this->makePackage();

        // 1. Halaman /tour/package/{slug} (mode form)
        $response = $this->get(route('tour.package.detail', $package->slug));
        $response->assertOk();

        $response->assertSee('Bagikan', false);
        $response->assertSee('Salin', false);
        $response->assertSee('api.whatsapp.com/send?text=', false);
        $response->assertSee('100% Halal Food Guaranteed', false);
        $response->assertSee('Private Tour (Tanpa Gabung)', false);
        $response->assertSee('Jemput Bandara KNO / Silangit', false);
        $response->assertSee('Buka Semua Hari', false);
        $response->assertSee('toggleDay', false);
        $response->assertSee('Pertanyaan Yang Sering Ditanyakan', false);
        $response->assertSee('Apakah makanan selama tour terjamin Halal 100%?', false);
        $response->assertSee('Bagaimana jika penerbangan kami tiba terlambat (delay) di bandara?', false);

        // Fitur Kenyamanan Baru (UX/CX Perfection)
        $response->assertSee('$store.wishlist', false);
        $response->assertSee("AppCurrency.setCurrency('MYR')", false);
        $response->assertSee("AppCurrency.setCurrency('IDR')", false);
        $response->assertSee("AppCurrency.setCurrency('SGD')", false);
        $response->assertSee('KNO ➔ Parapat', false);
        $response->assertSee('Parapat ➔ Samosir', false);
        $response->assertSee('Silangit (DTB)', false);
        $response->assertSee('Kembali ke atas', false);

        // 2. Halaman /tour/detail/{slug} (mode plain tanpa form)
        $responsePlain = $this->get(route('tour.package.detail.plain', $package->slug));
        $responsePlain->assertOk();

        $responsePlain->assertSee('Bagikan', false);
        $responsePlain->assertSee('100% Halal Food Guaranteed', false);
        $responsePlain->assertSee('Buka Semua Hari', false);
        $responsePlain->assertSee('Pertanyaan Yang Sering Ditanyakan', false);
        $responsePlain->assertSee('Respon Cepat', false);
        $responsePlain->assertSee('$store.wishlist', false);
        $responsePlain->assertSee("AppCurrency.setCurrency('MYR')", false);
        $responsePlain->assertSee('KNO ➔ Parapat', false);
        $responsePlain->assertSee('Kembali ke atas', false);
    }
}

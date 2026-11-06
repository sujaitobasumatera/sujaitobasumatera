<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageCardDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function makePackage(): Package
    {
        return Package::create([
            'slug' => 'paket-kartu-detail',
            'name' => 'Paket Kartu Detail',
            'shortDescription' => 'Ringkas',
            'description' => 'Lengkap',
            'images' => [],
            'includes' => ['Hotel bintang 3', 'Transportasi AC'],
            'excludes' => ['Tiket pesawat'],
            'itinerary' => [
                ['day' => 1, 'title' => 'Penjemputan - Parapat', 'activities' => ['Jemput bandara']],
                ['day' => 2, 'title' => 'Samosir', 'activities' => ['Tomok']],
            ],
            'pricingDetails' => [],
            'translations' => [],
            'price' => 500,
            'duration' => '2 Hari',
            'status' => 'active',
            'isFeatured' => true,
        ]);
    }

    public function test_grid_card_carries_the_summary_accordion(): void
    {
        $paket = $this->makePackage();

        $html = $this->get(route('tour.packages'))->assertOk()->getContent();

        // Akordeon menerima isi paket INI, bukan nilai tetap. Sejak grid
        // dicetak server, nilainya diserialisasi per kartu -- bukan lagi
        // ekspresi pkg.* yang dibaca dari satu larik besar di x-data.
        $this->assertStringContainsString('pkgDetails(', $html);
        $this->assertStringNotContainsString('pkgDetails(pkg.includes', $html);
        $this->assertStringContainsString('Hotel bintang 3', $html);

        // Locale default pengunjung baru = 'my' (LocaleCurrencyMiddleware).
        $this->assertStringContainsString('Butiran Pakej', $html);
    }

    public function test_daftar_paket_ada_di_html_tanpa_javascript(): void
    {
        // Grid ini dulu digambar Alpine dari satu larik JSON di x-data, jadi
        // isi <template x-for> TIDAK ADA di HTML: halaman jualan utama datang
        // kosong bagi pratinjau tautan, perayap selain Google, dan tamu yang
        // skripnya gagal termuat. Nama paket, harga, dan tautannya kini wajib
        // ada di HTML apa adanya.
        $paket = $this->makePackage();

        $html = $this->get(route('tour.packages'))->assertOk()->getContent();

        $this->assertStringContainsString('Paket Kartu Detail', $html);
        $this->assertStringContainsString('/tour/detail/'.$paket->slug, $html);
        $this->assertStringContainsString('2 Hari', $html);

        // Dan koleksi paket tidak lagi disalin utuh ke dalam atribut Alpine.
        $this->assertStringNotContainsString('packages: ', $html);
        $this->assertStringNotContainsString('x-text="packages.length"', $html);
    }

    public function test_accordion_panel_ids_are_generated_per_card_not_fixed(): void
    {
        // Satu id yang sama terpasang di semua kartu membuat aria-controls tiap
        // tombol menunjuk panel kartu pertama. Dulu bahayanya datang dari id
        // statis di dalam x-for; sekarang dari @foreach yang lupa menyertakan
        // id paket. Yang diuji hasilnya, bukan caranya.
        $this->makePackage();
        Package::create([
            'slug' => 'paket-kartu-kedua', 'name' => 'Paket Kartu Kedua',
            'description' => 'Lengkap', 'images' => [], 'includes' => ['Sarapan'],
            'excludes' => [], 'itinerary' => [], 'price' => 700, 'status' => 'active',
        ]);

        $html = html_entity_decode(
            $this->get(route('tour.packages'))->assertOk()->getContent(),
            ENT_QUOTES,
            'UTF-8'
        );

        $this->assertStringContainsString(':aria-controls=', $html);

        // Dua kartu, dua id berbeda -- dan tidak satu pun yang kehilangan
        // bagian id paketnya.
        preg_match_all('/pkg-detail-grid-(\d+)/', $html, $cocok);
        $this->assertNotEmpty($cocok[1]);
        $this->assertCount(2, array_unique($cocok[1]));
        $this->assertStringNotContainsString('pkg-detail-grid-"', $html);
    }

    public function test_card_containers_do_not_stretch_siblings(): void
    {
        // Wadah flex dan grid memakai align-items: stretch secara bawaan, jadi
        // seluruh kartu dipaksa setinggi yang tertinggi. Begitu satu akordeon
        // dibuka, kartu itu menjadi yang tertinggi dan SEMUA tetangganya ikut
        // melar mengikutinya -- isinya menggantung di ruang kosong. Akordeon
        // di dalam wadah yang stretch tidak akan pernah benar tanpa ini.
        $this->makePackage();

        $grid = $this->get(route('tour.packages'))->assertOk()->getContent();
        $this->assertStringContainsString('gap-6 md:gap-8 items-start', $grid);

        $beranda = $this->get(route('index'))->assertOk()->getContent();
        $this->assertStringContainsString('flex items-start gap-6', $beranda);
    }

    public function test_accordion_is_labelled_for_screen_readers(): void
    {
        $this->makePackage();

        $html = $this->get(route('tour.packages'))->assertOk()->getContent();

        $this->assertStringContainsString(':aria-expanded=', $html);
    }
}

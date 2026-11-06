<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarStackingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Ambil nilai z-index dari kelas Tailwind sembarang, mis. z-[130].
     */
    private function zOf(string $html, string $pattern): int
    {
        $this->assertMatchesRegularExpression($pattern, $html, 'elemen tidak ditemukan di navbar');
        preg_match($pattern, $html, $m);

        return (int) $m[1];
    }

    public function test_currency_dropdown_stacks_above_the_main_nav(): void
    {
        // Panel pilih bahasa/mata uang duduk di topbar tapi terbuka ke bawah,
        // melintasi batas topbar dan bertumpuk dengan isi <nav>. Tombol
        // "Hubungi Kami" di dalam nav menimpanya.
        //
        // z-[200] pada panelnya sendiri tidak menolong: nilai itu hanya
        // berlaku DI DALAM konteks penumpukan yang dibuat pembungkusnya.
        // Yang menentukan adalah z pembungkus itu terhadap z milik <nav>.
        $html = $this->get(route('index'))->assertOk()->getContent();

        $pembungkusDropdown = $this->zOf($html, '/class="relative z-\[(\d+)\]"/');
        $nav = $this->zOf($html, '/class="sticky top-0 [^"]*z-\[(\d+)\]"/');

        $this->assertGreaterThan(
            $nav,
            $pembungkusDropdown,
            "pembungkus dropdown (z-{$pembungkusDropdown}) harus di atas <nav> (z-{$nav}), ".
            'kalau tidak panelnya tertimpa isi nav'
        );
    }

    public function test_menu_mobile_berupa_strip_geser_bukan_burger(): void
    {
        // Drawer penuh layar beserta tombol burger-nya sudah diganti strip
        // menu horizontal. Tesnya menjaga keduanya tidak diam-diam kembali:
        // dua pola navigasi mobile sekaligus berarti satu di antaranya
        // membusuk tanpa ada yang tahu.
        $html = $this->get(route('index'))->assertOk()->getContent();

        $this->assertStringNotContainsString(
            'M4 6h16M4 12h16M4 18h16',
            $html,
            'ikon burger seharusnya sudah tidak dirender'
        );
        $this->assertStringContainsString('overflow-x-auto no-scrollbar', $html, 'strip menu mobile hilang');
    }

    public function test_pemilih_bahasa_tetap_terjangkau_di_layar_ponsel(): void
    {
        // Topbar -- rumah asli pemilih bahasa -- disembunyikan di bawah 640px.
        // Tanpa kembarannya di baris logo, tamu ponsel TIDAK punya cara sama
        // sekali mengganti bahasa atau mata uang.
        $html = $this->get(route('index'))->assertOk()->getContent();

        $this->assertStringContainsString('class="sm:hidden relative"', $html, 'chip bahasa versi ponsel hilang');

        // Tiga bahasa, dua tempat (topbar + chip ponsel).
        foreach (['my', 'id', 'en'] as $kode) {
            $this->assertSame(
                2,
                substr_count($html, route('change-locale', $kode)),
                "tautan ganti bahasa '{$kode}' harus ada di topbar DAN chip ponsel"
            );
        }
    }
}

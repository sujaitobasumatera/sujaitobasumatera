<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Nilai old() pada form pemesanan tidak boleh pernah diinterpolasi ke dalam
 * string JavaScript.
 *
 * Dulu ditulis begini:  customerName: '{{ old('customerName') }}'
 * Blade mengubah apostrof menjadi &#039;, browser mengembalikannya menjadi ',
 * string JS terputus, dan seluruh Alpine di halaman itu mati — form pemesanan
 * berhenti bekerja sepenuhnya. Satu calon pembeli bernama O'Brien yang gagal
 * validasi sekali sudah cukup, dan tidak meninggalkan jejak di log.
 */
class BookingFormEscapingTest extends TestCase
{
    use RefreshDatabase;

    protected function makePackage(): Package
    {
        return Package::create([
            'slug' => 'escaping-package',
            'name' => 'Escaping Package',
            'shortDescription' => 'Short',
            'description' => 'Full',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'pricingDetails' => [],
            'itinerary' => [],
            'translations' => [],
            'price' => 500,
            'duration' => '3 Hari',
            'status' => 'active',
        ]);
    }

    public function test_apostrophe_in_old_input_does_not_break_the_page(): void
    {
        $package = $this->makePackage();

        $html = $this->withSession(['_old_input' => [
            'customerName' => "O'Brien",
            'notesUser' => "Jangan lupa, kami bawa anak's mainan",
            'customerEmail' => "o'brien@test.local",
        ]])->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->getContent();

        // Apostrof mentah di dalam atribut x-data adalah pemutus string itu.
        $this->assertStringNotContainsString("customerName: 'O'Brien'", $html);
        $this->assertStringNotContainsString('&#039;', $this->extractXData($html));

        // Nilainya tetap harus sampai ke halaman, dalam bentuk apa pun yang aman.
        $this->assertStringContainsString('Brien', $html);
    }

    public function test_empty_pax_does_not_produce_broken_javascript(): void
    {
        $package = $this->makePackage();

        // pax kosong dulu menghasilkan "pax: ," — galat sintaks yang
        // mematikan seluruh komponen.
        $html = $this->withSession(['_old_input' => ['pax' => '', 'paxChildren' => '']])
            ->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('pax: ,', $html);
        $this->assertStringNotContainsString('paxChildren: ,', $html);
        $this->assertStringContainsString('pax: 1', $html);
    }

    public function test_script_tag_in_old_input_is_not_executable(): void
    {
        $package = $this->makePackage();

        $html = $this->withSession(['_old_input' => ['customerName' => '</script><script>alert(1)</script>']])
            ->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_demo_private_jet_price_is_gone(): void
    {
        $package = $this->makePackage();

        $html = $this->get(route('tour.package.detail', $package->slug))
            ->assertOk()
            ->getContent();

        // Layanan contoh yang tertinggal dan bisa benar-benar dipesan.
        $this->assertStringNotContainsString('Private Jet Charter', $html);
        $this->assertStringNotContainsString('120000000', $html);
    }

    /**
     * Ambil isi atribut x-data pertama, tempat bug ini dulu bersarang.
     */
    protected function extractXData(string $html): string
    {
        preg_match('/x-data="(.*?)"\s/s', $html, $m);

        return $m[1] ?? '';
    }
}

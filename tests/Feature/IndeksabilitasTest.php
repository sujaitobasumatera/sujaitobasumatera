<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\GalleryImage;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isi halaman publik harus ADA di HTML, bukan digambar Alpine setelahnya.
 *
 * Sebelum tes ini ditulis, tiga halaman jualan utama datang kosong bagi apa pun
 * yang tidak menjalankan JavaScript: daftar paket, daftar artikel, dan seluruh
 * galeri foto dibangun dari <template x-for> atas satu larik JSON di x-data.
 * Yang paling terasa bukan peringkat pencarian -- Google memang menjalankan JS --
 * melainkan pratinjau tautan WhatsApp, jalur pemasaran utama biro ini, yang
 * tidak menjalankan apa pun.
 *
 * Yang diperiksa: teks setelah SELURUH tag dibuang. Judul yang cuma menumpang di
 * dalam atribut x-data akan lolos pencarian biasa pada HTML mentah, dan itulah
 * persis keadaan yang salah.
 */
class IndeksabilitasTest extends TestCase
{
    use RefreshDatabase;

    private function teksTerender(string $html): string
    {
        // Buang <script>/<style> beserta isinya dulu, baru semua tag. Tanpa itu,
        // muatan JSON di dalam <script> ikut terbaca sebagai "teks terender".
        $html = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', ' ', $html);

        return preg_replace('/\s+/', ' ', strip_tags($html));
    }

    private function paket(string $slug, string $nama): Package
    {
        return Package::create([
            'slug' => $slug, 'name' => $nama,
            'description' => 'Perjalanan uji menyusuri Danau Toba, Samosir, dan Parapat selama tiga hari dua malam bersama pemandu lokal.',
            'price' => 500, 'duration' => '3 Hari 2 Malam', 'images' => [],
            'includes' => [], 'excludes' => [], 'status' => 'active',
        ]);
    }

    public function test_daftar_paket_terbaca_tanpa_javascript(): void
    {
        $this->paket('paket-uji-satu', 'Paket Uji Satu');
        $this->paket('paket-uji-dua', 'Paket Uji Dua');

        $teks = $this->teksTerender($this->get(route('tour.packages'))->assertOk()->getContent());

        $this->assertStringContainsString('Paket Uji Satu', $teks);
        $this->assertStringContainsString('Paket Uji Dua', $teks);
    }

    public function test_daftar_artikel_terbaca_tanpa_javascript(): void
    {
        foreach ([['artikel-uji-a', 'Judul Artikel Uji A'], ['artikel-uji-b', 'Judul Artikel Uji B']] as [$slug, $judul]) {
            Blog::create([
                'slug' => $slug, 'title' => $judul, 'content' => 'Isi artikel uji yang cukup panjang.',
                'excerpt' => 'Cuplikan uji.', 'category' => 'Inspirasi', 'status' => 'published',
                'author' => 'Tim Uji',
                'published_at' => now()->subDay(),
            ]);
        }

        $teks = $this->teksTerender($this->get(route('tour.blog'))->assertOk()->getContent());

        // Satu jadi sorotan, satu lagi di kisi -- keduanya wajib terbaca.
        $this->assertStringContainsString('Judul Artikel Uji A', $teks);
        $this->assertStringContainsString('Judul Artikel Uji B', $teks);
    }

    public function test_foto_galeri_ada_sebagai_tag_img_bukan_template(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            GalleryImage::create([
                'imageUrl' => 'images/sumut/toba_hero.webp',
                'caption' => "Keterangan Foto Uji {$i}",
                'category' => 'Danau Toba',
                'isActive' => true,
            ]);
        }

        $html = $this->get(route('tour.gallery'))->assertOk()->getContent();
        $teks = $this->teksTerender($html);

        $this->assertStringContainsString('Keterangan Foto Uji 1', $teks);
        $this->assertStringContainsString('Keterangan Foto Uji 4', $teks);

        // Dan fotonya benar-benar <img>, bukan cuma teks keterangannya.
        $this->assertGreaterThanOrEqual(4, substr_count($html, '<img'));
    }

    public function test_setiap_halaman_publik_punya_deskripsi_meta_yang_terisi(): void
    {
        $this->paket('paket-uji-meta', 'Paket Uji Meta');

        $rute = [
            'beranda' => '/',
            'tentang' => route('about'),
            'paket' => route('tour.packages'),
            'blog' => route('tour.blog'),
            'galeri' => route('tour.gallery'),
            'detail paket' => route('tour.package.detail.plain', 'paket-uji-meta'),
        ];

        foreach ($rute as $label => $url) {
            $html = $this->get($url)->assertOk()->getContent();

            preg_match('/<meta name="description" content="(.*?)"/s', $html, $m);
            $isi = trim($m[1] ?? '');

            // 50 huruf itu ambang kasar, bukan aturan SEO: yang mau ditangkap
            // adalah deskripsi KOSONG, karena Google lalu mengarang cuplikannya
            // sendiri dan pratinjau WhatsApp terbit tanpa satu baris penjelasan.
            $this->assertGreaterThan(50, mb_strlen($isi), "deskripsi meta halaman {$label} kosong atau terlalu pendek");
        }
    }

    public function test_gambar_besar_menyediakan_srcset(): void
    {
        $this->paket('paket-uji-srcset', 'Paket Uji Srcset');

        foreach ([route('tour.packages'), '/'] as $url) {
            $html = $this->get($url)->assertOk()->getContent();
            $this->assertStringContainsString(
                'srcset=',
                $html,
                "halaman {$url} mengirim gambar tanpa srcset -- kartu selebar ~380px akan menarik berkas ukuran penuh"
            );
        }
    }
}

<?php

namespace Tests\Feature;

use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PackageMediaTest extends TestCase
{
    use RefreshDatabase;

    private int $urutan = 0;

    private function paket(array $extra = []): Package
    {
        $this->urutan++;

        return Package::create(array_merge([
            'slug' => 'paket-uji-media-'.$this->urutan,
            'name' => 'Paket Uji Media',
            'description' => 'Deskripsi uji.',
            'price' => 400,
            'duration' => '3D2N',
            'images' => [],
            'includes' => [],
            'excludes' => [],
            'status' => 'active',
        ], $extra));
    }

    public function test_tautan_youtube_dan_vimeo_jadi_url_sematan(): void
    {
        $paket = $this->paket(['videos' => [
            ['type' => 'link', 'src' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s', 'title' => 'Hari 1'],
            ['type' => 'link', 'src' => 'https://youtu.be/abc123XYZ', 'title' => ''],
            ['type' => 'link', 'src' => 'https://vimeo.com/76979871', 'title' => ''],
        ]]);

        $list = $paket->videoList();

        $this->assertCount(3, $list);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $list[0]['url']);
        $this->assertSame('Hari 1', $list[0]['title']);
        $this->assertSame('https://www.youtube-nocookie.com/embed/abc123XYZ', $list[1]['url']);
        $this->assertSame('https://player.vimeo.com/video/76979871', $list[2]['url']);
    }

    public function test_tautan_video_berbahaya_dibuang_bukan_diteruskan(): void
    {
        // Nilai ini mendarat di atribut src <iframe>. Meloloskan skema
        // javascript:/data: dari form admin berarti satu akun admin yang jebol
        // bisa menanam skrip di halaman publik paling ramai.
        $paket = $this->paket(['videos' => [
            ['type' => 'link', 'src' => 'javascript:alert(1)'],
            ['type' => 'link', 'src' => 'data:text/html;base64,PHNjcmlwdD4='],
            ['type' => 'link', 'src' => 'https://situs-asing.example/video-halaman'],
        ]]);

        $this->assertSame([], $paket->videoList());
    }

    public function test_peta_hanya_menerima_domain_google(): void
    {
        $this->assertNull($this->paket(['mapEmbed' => 'https://penyerang.example/maps?q=1'])->mapEmbedUrl());
        $this->assertNull($this->paket(['mapEmbed' => '<iframe src="https://penyerang.example/x"></iframe>'])->mapEmbedUrl());
        $this->assertNull($this->paket(['mapEmbed' => ''])->mapEmbedUrl());

        // Kode <iframe> utuh dari Google Maps: yang diambil hanya src-nya.
        $iframe = '<iframe src="https://www.google.com/maps/embed?pb=!1m18" width="600" height="450"></iframe>';
        $this->assertSame('https://www.google.com/maps/embed?pb=!1m18', $this->paket(['mapEmbed' => $iframe])->mapEmbedUrl());

        // Koordinat mentah dirakit jadi URL sematan.
        $this->assertSame(
            'https://maps.google.com/maps?q=-2.6845,98.8756&z=14&output=embed',
            $this->paket(['mapEmbed' => ' -2.6845, 98.8756 '])->mapEmbedUrl()
        );
    }

    public function test_halaman_detail_tanpa_form_tetap_menampilkan_media_dan_menunjuk_canonical(): void
    {
        $paket = $this->paket([
            'videos' => [['type' => 'link', 'src' => 'https://youtu.be/abc123XYZ', 'title' => 'Cuplikan']],
            'mapEmbed' => '-2.6845, 98.8756',
        ]);

        $berform = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail', $paket->slug))->assertOk()->getContent();
        $tanpaForm = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();

        // Form pemesanan: ada di satu halaman, tidak di halaman lainnya.
        $this->assertStringContainsString('id="booking-form"', $berform);
        $this->assertStringNotContainsString('id="booking-form"', $tanpaForm);

        // Media yang sama muncul di KEDUANYA -- itu inti permintaannya.
        foreach (['berform' => $berform, 'tanpa form' => $tanpaForm] as $label => $html) {
            $this->assertStringContainsString('youtube-nocookie.com/embed/abc123XYZ', $html, "video hilang di halaman {$label}");
            $this->assertStringContainsString('maps.google.com/maps?q=-2.6845,98.8756', $html, "peta hilang di halaman {$label}");
        }

        // Halaman kembar tidak boleh saling menggerus di hasil pencarian.
        $canonical = route('tour.package.detail', $paket->slug);
        $this->assertStringContainsString('<link rel="canonical" href="'.$canonical.'">', $tanpaForm);
    }

    public function test_blok_sales_hanya_muncul_di_halaman_tanpa_form(): void
    {
        \App\Models\Setting::updateOrCreate(['key' => 'cms_tour'], ['value' => [
            'video_credit_note' => 'Semua video rekaman tim kami sendiri.',
            'detail_usp' => [
                ['title' => 'Masuk ke lokasi sulit', 'text' => 'Bukan cuma titik yang mudah dijangkau.'],
                ['title' => '', 'text' => 'baris kosong harus diabaikan'],
            ],
        ]]);

        $paket = $this->paket([
            'accommodations' => [
                ['night' => 2, 'name' => 'Hotel Malam Kedua', 'class' => 'Bintang 4', 'image' => ''],
                ['night' => 1, 'name' => 'Hotel Malam Pertama', 'class' => 'Bintang 3', 'image' => ''],
                ['night' => 3, 'name' => '', 'class' => 'tanpa nama, harus dibuang', 'image' => ''],
            ],
            'videos' => [
                ['type' => 'link', 'src' => 'https://youtu.be/abc123XYZ', 'title' => 'Hari 1', 'gear' => 'DJI Mavic 3'],
            ],
        ]);

        $tanpaForm = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();
        $berform = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail', $paket->slug))->assertOk()->getContent();

        // Blok sales lengkap di halaman tanpa form.
        $this->assertStringContainsString('Hotel Malam Pertama', $tanpaForm);
        $this->assertStringContainsString('Hotel Malam Kedua', $tanpaForm);
        $this->assertStringContainsString('Masuk ke lokasi sulit', $tanpaForm);
        $this->assertStringContainsString('DJI Mavic 3', $tanpaForm);
        $this->assertStringContainsString('Semua video rekaman tim kami sendiri.', $tanpaForm);
        $this->assertStringContainsString('Masih Ada Pertanyaan?', $tanpaForm);

        // Malam 1 harus tercetak sebelum malam 2 walau urutan datanya terbalik.
        //
        // Dicari HANYA di dalam blok penginapan: seluruh objek paket ikut
        // diserialisasi ke x-data lebih awal di halaman, jadi mencari di
        // seluruh HTML akan menemukan nama hotel di blob JSON itu -- yang
        // urutannya memang urutan input admin -- bukan di kartu terender.
        $blok = substr($tanpaForm, (int) strpos($tanpaForm, 'Menginap di Mana'));
        $this->assertLessThan(
            strpos($blok, 'Hotel Malam Kedua'),
            strpos($blok, 'Hotel Malam Pertama'),
            'penginapan harus urut menurut malam, bukan urutan input admin'
        );

        // Baris tanpa nama hotel tidak boleh jadi kartu kosong.
        $this->assertStringNotContainsString('tanpa nama, harus dibuang', $blok);

        // Halaman berform tetap ramping: tak satu pun blok sales ikut.
        //
        // Yang diperiksa PENANDA BLOKNYA, bukan nilai datanya. Seluruh objek
        // paket ikut diserialisasi ke x-data di kedua halaman, jadi mencari
        // 'Hotel Malam Pertama' akan selalu ketemu -- di payload Alpine,
        // bukan di blok yang dirender.
        $penanda = [
            'judul blok penginapan' => 'Menginap di Mana',
            'judul blok pembeda' => 'Kenapa Kami Berbeda',
            'judul CTA penutup' => 'Masih Ada Pertanyaan?',
            'lencana alat rekam' => 'text-[12px]">videocam',
        ];
        foreach ($penanda as $apa => $jangan) {
            $this->assertStringNotContainsString($jangan, $berform, "{$apa} seharusnya tidak ikut ke halaman berform");
            $this->assertStringContainsString($jangan, $tanpaForm, "{$apa} hilang di halaman tanpa form");
        }
    }

    public function test_tata_letak_poster_mobile_hanya_di_halaman_tanpa_form(): void
    {
        $paket = $this->paket([
            'videos' => [['type' => 'link', 'src' => 'https://youtu.be/abc123XYZ', 'title' => 'Hari 1']],
            'mapEmbed' => '-2.6845, 98.8756',
        ]);

        $tanpaForm = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();
        $berform = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail', $paket->slug))->assertOk()->getContent();

        $poster = [
            'media menembus tepi layar' => 'bleed-mobile',
            'batang WhatsApp lengket' => 'md:hidden fixed inset-x-0 bottom-0',
            'judul kapital berjarak' => 'uppercase tracking-[0.12em]',
            // Tanpa ruang bawah, batang lengket menutupi tombol terakhir
            // secara permanen -- tidak ada gejalanya selain tombol yang
            // "tidak bisa ditekan".
            'ruang bawah untuk batang lengket' => 'pb-24 md:pb-6',
        ];

        foreach ($poster as $apa => $penanda) {
            $this->assertStringContainsString($penanda, $tanpaForm, "{$apa} hilang di halaman tanpa form");
            $this->assertStringNotContainsString($penanda, $berform, "{$apa} seharusnya tidak ikut ke halaman berform");
        }
    }

    public function test_halaman_tanpa_form_memberi_jalan_memesan_lewat_kalkulator(): void
    {
        $paket = $this->paket();

        $html = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();

        // Tanpa form, tamu tetap harus punya jalan keluar: kalkulator pax
        // dengan tombol Booking (ke halaman berform) dan WhatsApp.
        $this->assertStringContainsString('paxCalc(', $html);
        $this->assertStringContainsString(':href="bookingUrl"', $html);
        $this->assertStringContainsString(':href="waUrl"', $html);
    }

    public function test_tabel_harga_grosir_menggantikan_gambar_harga_di_kalkulator(): void
    {
        $paket = $this->paket([
            'pricingDetails' => ['tiers' => [
                ['min_pax' => 1, 'max_pax' => 2, 'price' => 800, 'child_price' => 400],
                ['min_pax' => 3, 'max_pax' => 3, 'price' => 600, 'child_price' => 300],
            ]],
        ]);

        $html = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();

        // Tabelnya muncul, dan angkanya datang dari larik tiers milik paxCalc --
        // larik yang SAMA yang dipakai menghitung total, bukan salinan kedua.
        $this->assertStringContainsString('Harga Khusus (Lebih Banyak Lebih Murah!)', $html);
        $this->assertStringContainsString('x-for="(t, i) in tiers"', $html);
        $this->assertStringContainsString('x-text="fmt(t.price)"', $html);

        // Sorotan baris aktif WAJIB dibandingkan lewat min_pax. Alpine membungkus
        // tiap objek dalam Proxy, jadi tierFor(adults) === t bisa diam-diam selalu
        // bernilai salah dan sorotannya tidak pernah muncul -- tanpa satu pun galat.
        $this->assertStringContainsString('tierFor(adults).min_pax === t.min_pax', $html);
        $this->assertStringNotContainsString('tierFor(adults) === t ', $html);

        // Gambar harga hanya untuk paket yang belum punya tier; kalau tier ada,
        // ia tidak boleh ikut tampil dan menyajikan angka kedua yang bisa basi.
        $this->assertStringContainsString('!tiers.length &&', $html);
    }

    public function test_pembeda_paket_menggantikan_poin_situs_hanya_bila_diisi(): void
    {
        // Poin situs WAJIB diisi di sini: tanpa itu blok pembeda tidak pernah
        // dirender untuk paket polos, dan tesnya cuma membuktikan bahwa blok
        // kosong tetap kosong -- bukan bahwa cadangannya benar-benar bekerja.
        \App\Models\Setting::updateOrCreate(['key' => 'cms_tour'], ['value' => [
            'detail_usp' => [
                ['title' => 'Poin bawaan situs', 'text' => 'Berlaku di semua paket.'],
            ],
        ]]);

        $polos = $this->paket();
        $khusus = $this->paket([
            'highlights' => [
                ['title' => 'Titik pandang yang tidak didatangi operator lain', 'text' => 'Jalannya sempit, kami tetap ke sana.'],
                ['title' => '', 'text' => 'baris tanpa judul, harus dibuang'],
            ],
        ]);

        // Baris tanpa judul tidak pernah sampai ke tampilan.
        $this->assertCount(1, $khusus->highlightList());

        $htmlKhusus = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $khusus->slug))->assertOk()->getContent();
        $htmlPolos = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $polos->slug))->assertOk()->getContent();

        // Judul blok jadi penanda yang dicari, BUKAN isi poinnya. Seluruh objek
        // paket ikut diserialisasi ke x-data lebih awal di halaman, jadi teks
        // poin -- termasuk baris yang mestinya dibuang -- selalu ketemu di blob
        // JSON itu betapapun benarnya tampilan yang dirender.
        $this->assertStringContainsString('Kenapa Paket Ini Berbeda', $htmlKhusus);
        $blok = substr($htmlKhusus, (int) strpos($htmlKhusus, 'Kenapa Paket Ini Berbeda'));
        $this->assertStringContainsString('Titik pandang yang tidak didatangi operator lain', $blok);
        $this->assertStringNotContainsString('baris tanpa judul, harus dibuang', $blok);

        // Paket yang belum diisi TIDAK ikut kehilangan bloknya -- ia kembali ke
        // poin situs. Ini yang membuat kolom baru ini aman dinyalakan tanpa
        // menyentuh tujuh paket lain yang belum sempat diisi.
        $this->assertStringNotContainsString('Kenapa Paket Ini Berbeda', $htmlPolos);
        $this->assertStringContainsString('Kenapa Kami Berbeda', $htmlPolos);
        $this->assertStringContainsString('Poin bawaan situs', $htmlPolos);

        // Dan sebaliknya: paket yang punya poin sendiri tidak ikut menampilkan
        // poin situs di bawahnya. Menampilkan keduanya berarti tamu membaca dua
        // daftar "kenapa kami" yang bersaing di satu halaman.
        $this->assertStringNotContainsString('Poin bawaan situs', $blok);
    }

    public function test_ulasan_berada_di_dalam_kolom_berurutan_bukan_anak_langsung_grid(): void
    {
        // Pembungkus kolom kiri memakai `display: contents` di ponsel, jadi
        // anak-anaknya NAIK jadi item grid langsung dan kelas order-* merekalah
        // yang menentukan urutan tampil -- bukan urutan penulisannya di berkas.
        // Blok tanpa kelas order-* mendapat order 0, yaitu SEBELUM hero yang
        // order-1: ulasan yang ditulis paling bawah muncul paling atas di
        // ponsel, sementara di desktop (tanpa display:contents) ia tampak benar.
        // Tidak ada galat, tidak ada gejala lain selain urutan yang aneh.
        $paket = $this->paket();

        \App\Models\Setting::updateOrCreate(['key' => 'cms_tour'], ['value' => [
            'testimonials' => [['name' => 'Uji Tamu', 'location' => 'Medan', 'text' => 'Perjalanannya menyenangkan.']],
        ]]);

        $html = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();

        $dom = new \DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();

        $ulasan = (new \DOMXPath($dom))->query('//*[@id="section-reviews"]')->item(0);
        $this->assertNotNull($ulasan, 'blok ulasan tidak ditemukan');

        $indukKelas = (string) $ulasan->parentNode->getAttribute('class');
        $this->assertStringContainsString(
            'order-3',
            $indukKelas,
            'ulasan harus bersarang di kolom konten yang punya order-3; sebagai anak langsung grid ia melompat ke paling atas di ponsel'
        );
    }

    public function test_itinerary_pdf_dibuka_dulu_dan_baru_diunduh_bila_diminta(): void
    {
        $paket = $this->paket([
            'itinerary' => [['day' => 1, 'title' => 'Hari pertama', 'activities' => ['Jemput bandara']]],
        ]);

        // Bawaannya ditampilkan. Memaksa unduh berkas yang belum dilihat isinya
        // itu hambatan, dan di ponsel berkasnya sering hilang ke folder unduhan
        // tanpa pernah dibuka.
        $lihat = $this->get(route('itinerary.download', $paket->slug))->assertOk();
        $this->assertStringStartsWith('inline;', (string) $lihat->headers->get('content-disposition'));

        // Yang memang ingin menyimpan menekan tautan unduh.
        $unduh = $this->get(route('itinerary.download', [$paket->slug, 'unduh' => 1]))->assertOk();
        $this->assertStringStartsWith('attachment;', (string) $unduh->headers->get('content-disposition'));

        // Halaman detail menawarkan KEDUA tindakan, bukan satu tombol yang
        // perilakunya berubah-ubah tergantung ada tidaknya brosur unggahan.
        $html = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();

        // Ditandai lewat ikon dan parameter rutenya, bukan lewat teks tombol:
        // kata "Lihat" juga muncul di tempat lain halaman ini, dan Blade
        // menyisipkan baris baru antara > dan teksnya sehingga penanda seperti
        // ">Lihat" tidak pernah cocok walau tombolnya benar-benar ada.
        $this->assertStringContainsString('text-[18px]">visibility', $html);
        $this->assertStringContainsString('unduh=1', $html);
    }

    public function test_hanya_satu_batang_bawah_yang_tampil_di_ponsel(): void
    {
        $paket = $this->paket();

        // Pil melayang bawaan layout (z-90) dan batang halaman detail (z-50)
        // menempati sudut layar yang sama persis. Kalau keduanya ikut dirender,
        // pil menutupi batang halaman -- dan justru batang halamanlah yang
        // membawa harga serta tombol untuk paket yang sedang dibaca.
        $pilGlobal = 'fixed bottom-4 left-4 right-4 z-[90]';
        $batangHalaman = 'md:hidden fixed inset-x-0 bottom-0';

        $tanpaForm = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $paket->slug))->assertOk()->getContent();
        $berform = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail', $paket->slug))->assertOk()->getContent();
        $beranda = $this->withSession(['locale' => 'id'])->get('/')->assertOk()->getContent();

        // Halaman tanpa form: batangnya sendiri yang bertahan.
        $this->assertStringContainsString($batangHalaman, $tanpaForm);
        $this->assertStringNotContainsString($pilGlobal, $tanpaForm);

        // Halaman berform tidak punya batang sendiri, jadi ia TETAP butuh pil
        // itu -- mematikannya di seluruh situs akan menghapus satu-satunya
        // ajakan yang selalu terlihat di halaman-halaman lain.
        $this->assertStringNotContainsString($batangHalaman, $berform);
        $this->assertStringContainsString($pilGlobal, $berform);
        $this->assertStringContainsString($pilGlobal, $beranda);
    }

    public function test_galeri_kisi_menyala_sendiri_setelah_foto_cukup(): void
    {
        $sedikit = $this->paket(['images' => ['a.webp', 'b.webp', 'c.webp']]);
        $banyak = $this->paket(['images' => ['a.webp', 'b.webp', 'c.webp', 'd.webp', 'e.webp', 'f.webp']]);

        $htmlSedikit = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $sedikit->slug))->assertOk()->getContent();
        $htmlBanyak = $this->withSession(['locale' => 'id'])
            ->get(route('tour.package.detail.plain', $banyak->slug))->assertOk()->getContent();

        // Penandanya kalimat ajakan, BUKAN judul "Galeri Foto": judul itu juga
        // dipakai tautan galeri di kaki halaman, jadi ia ada di SETIAP halaman
        // dan tidak pernah bisa membuktikan apa pun tentang blok ini.
        $penanda = 'Ketuk foto untuk melihat ukuran penuh.';

        // Di bawah ambang, kisinya cuma mengulang carousel hero -- jadi diam.
        $this->assertStringNotContainsString($penanda, $htmlSedikit);

        // Di atas ambang ia menyala sendiri, tanpa saklar yang perlu diingat.
        $this->assertStringContainsString($penanda, $htmlBanyak);

        // Fotonya dicetak server, bukan menunggu Alpine: kisi ini SATU-SATUNYA
        // alasan bloknya ada, dan tanpa JavaScript ia tetap harus utuh.
        // Di halaman dengan 6 foto vs 3 foto, terdapat 9 kemunculan zoom-image tambahan (3 di mobile strip + 6 di kisi galeri).
        $this->assertSame(9, substr_count($htmlBanyak, 'zoom-image') - substr_count($htmlSedikit, 'zoom-image'));
    }

    public function test_modal_zoom_gambar_hadir_secara_global(): void
    {
        $paket = $this->paket(['images' => ['foto1.webp', 'foto2.webp']]);

        $response = $this->get(route('tour.package.detail.plain', $paket->slug));
        $response->assertOk();

        // Modal zoom interaktif harus terpasang di DOM dan mendengarkan event @zoom-image
        $response->assertSee('@zoom-image.window="openModal($event.detail)"', false);
        $response->assertSee('zoomIn()', false);
        $response->assertSee('zoomOut()', false);
        $response->assertSee('resetZoom()', false);
    }
}

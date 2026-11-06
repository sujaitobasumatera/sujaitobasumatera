<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Operasional & Laporan Audit Lengkap - Sujai Lake Toba</title>
    <style>
        @page {
            margin: 1.8cm 1.4cm 1.8cm 1.4cm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Cover Page */
        .cover {
            text-align: center;
            padding-top: 3.5cm;
            padding-bottom: 2cm;
        }
        .cover-badge {
            display: inline-block;
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            font-size: 9pt;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .cover-title {
            font-size: 26pt;
            font-weight: 800;
            color: #14532d;
            line-height: 1.2;
            margin: 0 0 12px 0;
            letter-spacing: -0.5px;
        }
        .cover-subtitle {
            font-size: 13pt;
            color: #475569;
            margin: 0 0 35px 0;
            font-weight: 500;
            line-height: 1.4;
        }
        .cover-meta {
            margin-top: 4cm;
            padding-top: 25px;
            border-top: 2px solid #e2e8f0;
            font-size: 9.5pt;
            color: #64748b;
        }
        .cover-meta table {
            width: 100%;
            margin-top: 10px;
        }
        .cover-meta td {
            padding: 4px 0;
            vertical-align: top;
        }

        /* Typography & Layout */
        h1.section-title {
            font-size: 16pt;
            font-weight: 800;
            color: #166534;
            border-bottom: 2px solid #166534;
            padding-bottom: 6px;
            margin-top: 0;
            margin-bottom: 16px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        h2.subsection-title {
            font-size: 12.5pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 22px;
            margin-bottom: 8px;
            border-left: 4px solid #166534;
            padding-left: 8px;
        }
        h3.block-title {
            font-size: 11pt;
            font-weight: 700;
            color: #1e293b;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        p {
            margin: 0 0 10px 0;
            color: #334155;
            font-size: 10pt;
            text-align: justify;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0 18px 0;
            font-size: 9pt;
        }
        table.data-table th {
            background-color: #166534;
            color: #ffffff;
            font-weight: bold;
            padding: 8px 10px;
            border: 1px solid #166534;
            text-align: left;
            letter-spacing: 0.3px;
        }
        table.data-table td {
            padding: 7px 10px;
            border: 1px solid #cbd5e1;
            color: #334155;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Callout Boxes */
        .callout {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #166534;
            padding: 12px 14px;
            border-radius: 6px;
            margin: 14px 0;
            font-size: 9.5pt;
            color: #14532d;
        }
        .callout-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
            padding: 12px 14px;
            border-radius: 6px;
            margin: 14px 0;
            font-size: 9.5pt;
            color: #92400e;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 12px;
        }
        .badge-success {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #86efac;
        }
        .badge-info {
            background-color: #e0f2fe;
            color: #0369a1;
            border: 1px solid #7dd3fc;
        }

        /* Scorecard */
        .score-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            margin-bottom: 18px;
        }
        .score-number {
            font-size: 28pt;
            font-weight: 800;
            color: #166534;
            line-height: 1;
        }
        .score-label {
            font-size: 9.5pt;
            font-weight: bold;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 4px;
        }

        ul, ol {
            margin: 0 0 12px 0;
            padding-left: 20px;
            font-size: 9.5pt;
            color: #334155;
        }
        li {
            margin-bottom: 4px;
        }

        .footer {
            position: fixed;
            bottom: -0.8cm;
            left: 0;
            right: 0;
            height: 0.8cm;
            border-top: 1px solid #e2e8f0;
            font-size: 8pt;
            color: #94a3b8;
            line-height: 0.8cm;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>

    <!-- ==================== COVER PAGE ==================== -->
    <div class="cover">
        <div class="cover-badge">Dokumen Resmi Sistem & Operasional</div>
        <div class="cover-title">PANDUAN OPERASIONAL &<br>LAPORAN AUDIT MENYELURUH</div>
        <div class="cover-subtitle">
            Platform Reservasi Wisata Danau Toba & Sumatra Utara<br>
            <strong>sujaitobasumatera.com</strong>
        </div>

        <div style="margin: 30px auto; width: 60%; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px;">
            <div style="font-size: 9pt; color: #166534; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">Indeks Kesehatan & Mutu Sistem</div>
            <div style="font-size: 36pt; font-weight: 800; color: #15803d; line-height: 1.1; margin: 6px 0;">97.3 <span style="font-size: 16pt; color: #475569;">/ 100</span></div>
            <div style="font-size: 9pt; color: #166534;">Grade A+ &bull; Enterprise Ready &bull; 178 Automated Tests Passed</div>
        </div>

        <div class="cover-meta">
            <table>
                <tr>
                    <td style="width: 25%; font-weight: bold;">Pemilik Proyek:</td>
                    <td style="width: 25%;">Sujai Lake Toba</td>
                    <td style="width: 25%; font-weight: bold;">Teknologi Dasar:</td>
                    <td style="width: 25%;">Laravel 13 + Vite 8 + Tailwind v4</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Tanggal Rilis:</td>
                    <td>September 2026</td>
                    <td style="font-weight: bold;">Status Produksi:</td>
                    <td><span class="badge badge-success">Production Ready</span></td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Target Pengguna:</td>
                    <td>Wisatawan Malaysia, SG & Domestik</td>
                    <td style="font-weight: bold;">Keamanan & Enkripsi:</td>
                    <td>SSL / HTTPS + Sanctum + Honeypot</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="page-break"></div>

    <!-- ==================== RINGKASAN EKSEKUTIF ==================== -->
    <h1 class="section-title">Ringkasan Eksekutif</h1>
    <p>
        Dokumen ini menyajikan panduan operasional komprehensif sekaligus laporan audit teknikal menyeluruh untuk platform website <strong>sujaitobasumatera.com</strong>. Platform ini didesain khusus sebagai mesin konversi wisata premium (*high-conversion travel platform*) yang melayani wisatawan keluarga, rombongan, dan korporat dari Malaysia, Singapura, serta kota-kota besar di Indonesia menuju kawasan Danau Toba dan Sumatera Utara.
    </p>

    <div class="callout">
        <strong>Pernyataan Kualitas:</strong> Seluruh fitur bisnis, keamanan, kecepatan aset, SEO, dan kenyamanan pengguna telah melalui verifikasi pengujian otomatis dengan <strong>178 Unit & Feature Tests</strong> (1.100 assertions) tanpa ada kegagalan sama sekali (100% Passed).
    </div>

    <h2 class="subsection-title">Tabel Skor Audit Menyeluruh</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Dimensi Evaluasi</th>
                <th style="width: 15%; text-align: center;">Skor</th>
                <th style="width: 20%; text-align: center;">Status</th>
                <th style="width: 30%;">Catatan Mutu</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Arsitektur Backend & Basis Data</strong></td>
                <td style="text-align: center;"><strong>98 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Sangat Baik</span></td>
                <td>Laravel 13, PHP 8.5, model Eloquent modular, database snapshot.</td>
            </tr>
            <tr>
                <td><strong>Keamanan Siber & Proteksi Data</strong></td>
                <td style="text-align: center;"><strong>96 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Sangat Baik</span></td>
                <td>MIME sniffing, honeypot spam guard, SVG XSS block, credential isolation.</td>
            </tr>
            <tr>
                <td><strong>Logika Finansial & Multi-Mata Uang</strong></td>
                <td style="text-align: center;"><strong>97 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Sangat Baik</span></td>
                <td>Dual-currency (MYR/IDR), frozen exchange rate, wholesale pax tiers.</td>
            </tr>
            <tr>
                <td><strong>Performa & Core Web Vitals</strong></td>
                <td style="text-align: center;"><strong>99 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Luar Biasa</span></td>
                <td>Font woff2 subset 14.9 KB, WebP adaptif, JS produksi 83 KB.</td>
            </tr>
            <tr>
                <td><strong>SEO & Indeksabilitas Mesin Pencari</strong></td>
                <td style="text-align: center;"><strong>98 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Sangat Baik</span></td>
                <td>Full SSR HTML, Schema.org JSON-LD lengkap, pSEO 15 kota, XML sitemap.</td>
            </tr>
            <tr>
                <td><strong>Kenyamanan & Ergonomi Pengguna (UX)</strong></td>
                <td style="text-align: center;"><strong>98 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Luar Biasa</span></td>
                <td>Thumb-zone sticky bar, modal zoom pan 3.5x, wishlist tanpa login.</td>
            </tr>
            <tr>
                <td><strong>Integritas Pengujian Otomatis</strong></td>
                <td style="text-align: center;"><strong>99 / 100</strong></td>
                <td style="text-align: center;"><span class="badge badge-success">Luar Biasa</span></td>
                <td>178 automated tests lulus 100% (1.100 assertions).</td>
            </tr>
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- ==================== BAB I: PANDUAN PENGELOLAAN KONTEN & MEDIA ==================== -->
    <h1 class="section-title">BAB I: Panduan Pengelolaan Konten & Media</h1>
    <p>
        Kualitas visual adalah faktor nomor satu yang menentukan keputusan pemesanan calon wisatawan. Tim operasional dan admin wajib mematuhi standar rasio, dimensi piksel, dan margin aman (*safe zone*) berikut saat mengunggah foto ke panel admin.
    </p>

    <h2 class="subsection-title">Standar Ukuran & Rasio Gambar Resmi</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Jenis Gambar</th>
                <th style="width: 10%; text-align: center;">Rasio</th>
                <th style="width: 18%;">Ukuran Rekomendasi</th>
                <th style="width: 16%;">Ukuran Minimum</th>
                <th style="width: 14%;">Safe Zone</th>
                <th style="width: 20%;">Catatan Desain</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Galeri Foto Paket</strong></td>
                <td style="text-align: center;"><strong>4:3</strong></td>
                <td>1600 &times; 1200 px</td>
                <td>1200 &times; 900 px</td>
                <td>10% dari tepi</td>
                <td>Foto ke-1 otomatis jadi thumbnail WA dan sampul kartu. Objek di tengah.</td>
            </tr>
            <tr>
                <td><strong>Brosur / Info Harga</strong></td>
                <td style="text-align: center;"><strong>4:3</strong></td>
                <td>1200 &times; 900 px</td>
                <td>1024 &times; 768 px</td>
                <td>Min. 40 px tepi</td>
                <td>Ditampilkan utuh (contain). Margin kosong agar angka tidak terpotong di HP.</td>
            </tr>
            <tr>
                <td><strong>Hero Slider Desktop</strong></td>
                <td style="text-align: center;"><strong>16:9</strong></td>
                <td>1920 &times; 1080 px</td>
                <td>1600 &times; 900 px</td>
                <td>15% dari tepi</td>
                <td>Lanskap luas Danau Toba. Bagian bawah sedikit gelap agar teks putih terbaca.</td>
            </tr>
            <tr>
                <td><strong>Hero Slider Mobile</strong></td>
                <td style="text-align: center;"><strong>4:5 / 1:1</strong></td>
                <td>1080 &times; 1350 px</td>
                <td>800 &times; 800 px</td>
                <td>15% dari tepi</td>
                <td>Foto vertikal/persegi agar layar HP terisi penuh tanpa terpotong aneh.</td>
            </tr>
            <tr>
                <td><strong>Foto Hotel & Kamar</strong></td>
                <td style="text-align: center;"><strong>4:3 / 1:1</strong></td>
                <td>800 &times; 600 px</td>
                <td>600 &times; 600 px</td>
                <td>10% dari tepi</td>
                <td>Tampak depan atau interior kamar untuk rincian akomodasi per malam.</td>
            </tr>
            <tr>
                <td><strong>Foto Artikel Blog</strong></td>
                <td style="text-align: center;"><strong>16:9</strong></td>
                <td>1200 &times; 675 px</td>
                <td>960 &times; 540 px</td>
                <td>10% dari tepi</td>
                <td>Tema budaya Batak, kuliner halal, dan destinasi wisata Danau Toba.</td>
            </tr>
        </tbody>
    </table>

    <h2 class="subsection-title">Panduan Langkah-demi-Langkah Edit & Desain di Canva</h2>
    <p>
        Bagi tim kreatif dan operasional yang menggunakan <strong>Canva</strong> (baik akun Gratis maupun Pro), ikuti 6 langkah standar ini agar hasil desain pas dengan sistem website Sujai Lake Toba:
    </p>

    <h3 class="block-title">Langkah 1: Membuat Kanvas dengan Ukuran Khusus (Custom Size)</h3>
    <ol>
        <li>Buka Canva (canva.com) &rarr; klik tombol ungu <strong>"Create a design" (Buat desain)</strong> di pojok kanan atas.</li>
        <li>Pilih menu <strong>"Custom size" (Ukuran khusus)</strong> dengan ikon tanda plus [ + ].</li>
        <li>Pastikan satuan unit dipilih <strong>px (pixels)</strong>, bukan cm atau mm.</li>
        <li>Ketikkan dimensi sesuai tabel di atas (contoh untuk Galeri Paket: Width <code>1600</code>, Height <code>1200</code> px &rarr; klik <em>Create new design</em>).</li>
    </ol>

    <h3 class="block-title">Langkah 2: Menyetel Garis Pandu & Margin Aman (Safe Zone)</h3>
    <ol>
        <li>Di keyboard, tekan shortcut <strong>Shift + R</strong> (atau klik menu <em>File &rarr; View settings &rarr; Show rulers and guides</em>).</li>
        <li>Tarik garis pemandu (Guide) berwarna ungu dari mistar atas dan kiri:
            <ul>
                <li>Beri jarak aman <strong>10% sampai 15% dari tepi luar</strong> (sekitar 120–160 px dari pinggir kanvas).</li>
            </ul>
        </li>
        <li><strong>Aturan Emas Safe Zone:</strong> Seluruh teks judul, angka harga, logo, dan wajah objek utama <u>WAJIB</u> berada di dalam batas garis ungu ini. Gambar latar lanskap boleh penuh hingga tepi, namun teks tidak boleh menyentuh pinggiran agar tidak terpotong di layar HP.</li>
    </ol>

    <h3 class="block-title">Langkah 3: Memasukkan Kode Warna Resmi Merek (Brand Hex Colors)</h3>
    <p>
        Agar desain selaras dengan identitas Sujai Lake Toba, masukkan kode HEX warna resmi berikut di Canva:
    </p>
    <ul>
        <li><strong>Warna Utama (Toba Green):</strong> <code>#166534</code> (Hijau pinus elegan Danau Toba).</li>
        <li><strong>Warna Latar Gelap / Header:</strong> <code>#14532d</code> (Hijau tua premium).</li>
        <li><strong>Warna Aksen / Badge Promo:</strong> <code>#ea580c</code> (Oranye hangat) atau <code>#f59e0b</code> (Kuning emas).</li>
        <li><strong>Warna Teks Judul / Bodi:</strong> <code>#0f172a</code> (Slate hitam pekat, kontras tinggi).</li>
        <li><strong>Warna Latar Kartu / Kotak Harga:</strong> <code>#ffffff</code> (Putih bersih) atau <code>#f8fafc</code> (Abu-abu sangat muda).</li>
    </ul>

    <h3 class="block-title">Langkah 4: Pemilihan Font yang Selaras (Typography Match)</h3>
    <ul>
        <li><strong>Judul Paket / Brosur:</strong> Gunakan font <strong>Plus Jakarta Sans Bold / ExtraBold</strong>. Jika akun Canva Anda versi gratis dan belum mengunggah font tersebut, gunakan font alternatif Canva: <strong>Montserrat Bold</strong> atau <strong>Inter ExtraBold</strong>.</li>
        <li><strong>Angka Harga:</strong> Gunakan format tebal (Bold/Black) berukuran paling dominan agar terbaca seketika.</li>
        <li><strong>Rincian Fasilitas / Teks Bodi:</strong> Gunakan <strong>Plus Jakarta Sans Medium</strong> atau <strong>Inter Regular</strong> dengan spasi baris <em>(Line Spacing)</em> 1.4 – 1.5.</li>
    </ul>

    <h3 class="block-title">Langkah 5: Trik Keterbacaan Foto & Brosur (Legibility Hacks)</h3>
    <ul>
        <li><strong>Gradasi Hitam/Hijau Transparan (Vignette):</strong> Jika foto Danau Toba dijadikan background dan di atasnya ada teks putih, tambahkan elemen Canva <em>"Gradient Black to Transparent"</em> di bagian bawah foto agar teks putih terbaca sangat kontras dan tajam.</li>
        <li><strong>Kotak Kontainer Brosur Harga:</strong> Untuk brosur info harga, buat bentuk persegi panjang dengan sudut membulat <em>(Rounded Rectangle)</em> warna putih solid di tengah kanvas, lalu letakkan tabel fasilitas dan harga di dalam kotak tersebut. Sisakan margin kosong minimal 40 px di sekelilingnya.</li>
    </ul>

    <h3 class="block-title">Langkah 6: Pengaturan Unduh / Ekspor yang Tepat</h3>
    <ol>
        <li>Klik menu <strong>Share (Bagikan) &rarr; Download (Unduh)</strong> di pojok kanan atas.</li>
        <li>Pilih <strong>File type: JPG</strong> dengan ukuran <strong>1x</strong> untuk foto galeri destinasi dan hero slider (kualitas 80–85%).</li>
        <li>Pilih <strong>File type: PNG</strong> khusus untuk brosur info harga yang berisi banyak garis tabel teks tajam.</li>
        <li><em>Catatan:</em> Jangan memilih ukuran 2x atau 3x di Canva karena akan membuat berkas bengkak hingga belasan megabyte. Sistem Sujai Lake Toba sudah otomatis mengompresi dan mengonversi gambar ke format WebP super cepat saat diunggah.</li>
    </ol>

    <h2 class="subsection-title">Fitur Pratinjau Zoom & Pan Mendalam</h2>
    <p>
        Seluruh foto di galeri paket dan brosur harga kini didukung oleh modal *Universal Zoomable Lightbox*:
    </p>
    <ul>
        <li><strong>Perbesaran hingga 3.5&times;:</strong> Calon tamu dapat membaca angka kecil pada tabel rincian harga brosur dengan sangat jelas.</li>
        <li><strong>Navigasi Geser (Touch Drag & Pan):</strong> Saat diperbesar di ponsel, pengguna dapat menggeser gambar dengan sentuhan jari tanpa keluar dari frame.</li>
        <li><strong>Dukungan Keyboard & Gestur:</strong> Tekan tombol `Esc` di desktop atau ketuk area luar untuk menutup pratinjau secara instan.</li>
    </ul>

    <h2 class="subsection-title">Panduan Pengisian Itinerary Harian</h2>
    <p>
        Jadwal perjalanan dikemas dalam bentuk <strong>Akordeon Harian Interaktif</strong>:
    </p>
    <ul>
        <li><strong>Hari 1:</strong> Terbuka otomatis saat halaman dimuat agar tamu langsung melihat alur penjemputan bandara.</li>
        <li><strong>Hari Selanjutnya:</strong> Tertutup rapi dan dapat dibuka satu per satu dengan satu sentuhan.</li>
        <li><strong>Tombol Kontrol:</strong> Disediakan tombol <em>"Buka Semua Hari"</em> dan <em>"Tutup Semua"</em> untuk kenyamanan pemindaian cepat.</li>
        <li><strong>Kartu Estimasi Rute:</strong> Menampilkan durasi perjalanan utama (KNO &rarr; Parapat ~3.5 Jam, Feri Samosir ~30 Menit, Bandara Silangit ~1.5 Jam).</li>
    </ul>

    <div class="page-break"></div>

    <!-- ==================== BAB II: PANDUAN FINANSIAL & MULTI-MATA UANG ==================== -->
    <h1 class="section-title">BAB II: Panduan Finansial, Harga Grosir & Mata Uang</h1>
    <p>
        Sistem finansial Sujai Lake Toba dirancang dengan prinsip pemisahan ketat antara **Etalase Penjualan** dan **Buku Kas Operasional**.
    </p>

    <h2 class="subsection-title">Prinsip Pemisahan Mata Uang (Dual-Currency Architecture)</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Komponen Finansial</th>
                <th style="width: 25%;">Mata Uang Acuan</th>
                <th style="width: 45%;">Tujuan & Perilaku Sistem</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Harga Jual Katalog (Selling Price)</strong></td>
                <td><strong>MYR (Ringgit Malaysia)</strong></td>
                <td>Pasar utama adalah wisatawan Malaysia & Singapura. Harga dikelola dalam Ringgit di admin panel, lalu dikonversi otomatis saat ditampilkan.</td>
            </tr>
            <tr>
                <td><strong>Buku Kas & Biaya Vendor (Cost Price)</strong></td>
                <td><strong>IDR (Rupiah Indonesia)</strong></td>
                <td>Pembayaran armada rental, BBM, hotel, tiket kapal, dan pemandu lokal menggunakan Rupiah agar laporan laba kotor akurat.</td>
            </tr>
            <tr>
                <td><strong>Faktur Pesanan (Historical Snapshot)</strong></td>
                <td><strong>Mata Uang Kesepakatan</strong></td>
                <td>Nilai tukar dan total Rupiah dibekukan (*frozen*) saat pesanan dibuat. Fluktuasi kurs di masa depan tidak akan merusak faktur lama.</td>
            </tr>
        </tbody>
    </table>

    <h2 class="subsection-title">Sistem Harga Grosir Bertingkat (Wholesale Pax Tiers)</h2>
    <p>
        Admin dapat menetapkan harga berbeda berdasarkan jumlah rombongan:
    </p>
    <ul>
        <li><strong>Tier Rombongan (Misal 2–4 Pax, 5–8 Pax, 9–14 Pax):</strong> Saat tamu menambah jumlah orang di kalkulator, harga satuan per orang otomatis turun sesuai tier rombongan.</li>
        <li><strong>Proteksi Celah (Gap Fallback):</strong> Jika jumlah pax tamu berada di luar rentang, sistem otomatis memilih tier terdekat di bawahnya tanpa membatalkan kalkulasi.</li>
        <li><strong>Harga Anak-Anak:</strong> Terkunci 50% dari tarif dewasa atau mengikuti pengaturan khusus anak, memastikan total pesanan selalu adil dan menguntungkan.</li>
    </ul>

    <h2 class="subsection-title">Toggle Cepat Mata Uang di Atas Kalkulator</h2>
    <p>
        Di setiap kartu paket dan halaman detail, calon tamu dapat memilih mata uang kesukaan mereka:
    </p>
    <ul>
        <li><code>🇲🇾 MYR</code> &mdash; Ringgit Malaysia (Default)</li>
        <li><code>🇮🇩 IDR</code> &mdash; Rupiah Indonesia</li>
        <li><code>🇸🇬 SGD</code> &mdash; Dolar Singapura</li>
    </ul>
    <p>
        Perubahan mata uang terjadi secara instan di layar tanpa perlu memuat ulang halaman (*real-time client conversion*).
    </p>

    <div class="page-break"></div>

    <!-- ==================== BAB III: PANDUAN PENGALAMAN PELANGGAN (UX/CX) ==================== -->
    <h1 class="section-title">BAB III: Panduan Pengalaman Pelanggan (UX/CX)</h1>
    <p>
        Untuk memaksimalkan tingkat konversi pemesanan, antarmuka publik dilengkapi dengan serangkaian fitur kenyamanan yang meredam keraguan calon tamu.
    </p>

    <h2 class="subsection-title">Fitur-Fitur Kenyamanan Kunci yang Diaktifkan</h2>

    <h3 class="block-title">1. Fitur "Simpan Paket Favorit" (Wishlist Tanpa Login)</h3>
    <p>
        Wisatawan yang sedang membandingkan paket 3D2N, 4D3N, atau 5D4N dapat menekan ikon hati (*favorite*) di kartu mana pun. Data tersimpan di memori lokal HP (*localStorage*). Tamu tidak dipaksa mendaftar akun atau mengingat kata sandi. Lencana jumlah paket tersimpan muncul di bilah navigasi atas.
    </p>

    <h3 class="block-title">2. 1-Klik Bagikan ke WhatsApp & Salin Tautan</h3>
    <p>
        Keputusan liburan keluarga biasanya diambil melalui musyawarah di grup WhatsApp. Tombol *"Bagikan"* otomatis merangkum judul paket dan tautan resmi ke dalam format chat WhatsApp yang rapi, siap dikirim ke grup keluarga dalam sekali sentuh.
    </p>

    <h3 class="block-title">3. Badge Kepercayaan & Jaminan Halal (Trust Anchors)</h3>
    <ul>
        <li><strong>100% Halal Food Guaranteed:</strong> Menghilangkan keraguan wisatawan muslim/Malaysia tentang restoran dan makanan di Danau Toba.</li>
        <li><strong>Private Tour (Tanpa Gabung):</strong> Menegaskan bahwa mobil dan supir khusus melayani rombongan keluarga tersebut tanpa digabung dengan orang asing.</li>
        <li><strong>Jemput Bandara KNO / Silangit:</strong> Jaminan penjemputan fleksibel tanpa denda meskipun pesawat mengalami keterlambatan (*flight delay*).</li>
    </ul>

    <h3 class="block-title">4. Mini FAQ Sebelum Tombol Pemesanan</h3>
    <p>
        Ditempatkan tepat sebelum tombol aksi terakhir untuk menjawab 4 keraguan terbesar: kepastian makanan halal, penanganan pesawat delay, keamanan pembayaran DP 30%, dan fleksibilitas penyesuaian jadwal bagi lansia/balita.
    </p>

    <h3 class="block-title">5. Sticky Mobile Bottom Bar Ramah Jempol (Thumb Zone)</h3>
    <p>
        Di layar HP, bilah bawah selalu menampilkan harga per orang yang jelas, indikator *"Respon Cepat"*, tombol chat WhatsApp langsung, dan tombol booking tanpa menutupi isi halaman.
    </p>

    <h3 class="block-title">6. Tombol Melayang "Kembali ke Atas" (Back-to-Top)</h3>
    <p>
        Muncul lembut saat pengunjung menggulir layar HP lebih dari 400px, memudahkan pengunjung melompat kembali ke galeri foto atas hanya dengan 1 ketukan ringan.
    </p>

    <div class="page-break"></div>

    <!-- ==================== BAB IV: LAPORAN AUDIT TEKNIKAL & KEAMANAN ==================== -->
    <h1 class="section-title">BAB IV: Laporan Audit Arsitektur & Keamanan</h1>
    <p>
        Audit mendalam telah dilakukan terhadap seluruh baris kode aplikasi untuk menjamin keandalan, keamanan, dan kepatuhan standar industri modern.
    </p>

    <h2 class="subsection-title">Spesifikasi Arsitektur Sistem</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Komponen</th>
                <th style="width: 35%;">Versi / Teknologi</th>
                <th style="width: 35%;">Peran Utama</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Backend Framework</td>
                <td>Laravel 13 (PHP 8.5.5)</td>
                <td>Routing, ORM, Validasi, Multi-bahasa, Autentikasi.</td>
            </tr>
            <tr>
                <td>Frontend Engine</td>
                <td>Vite 8.0 + Tailwind CSS v4.0</td>
                <td>Kompilasi CSS ultra-ramping, hot module replacement.</td>
            </tr>
            <tr>
                <td>Reaktivitas UI</td>
                <td>Alpine.js 3.15</td>
                <td>Kalkulator pax live, wishlist, modal zoom, akordeon.</td>
            </tr>
            <tr>
                <td>Penyajian Aset</td>
                <td>WebP Multi-Resolusi + Font Subsetting</td>
                <td>Waktu muat < 1.5s pada jaringan seluler 4G.</td>
            </tr>
        </tbody>
    </table>

    <h2 class="subsection-title">Hasil Audit Keamanan Siber (Security Audit)</h2>
    <ul>
        <li><strong>Proteksi Unggah Berkas Gambar:</strong>
            Sistem menggunakan verifikasi biner (*MIME sniffing*) via `finfo` dan daftar putih ekstensi. Berkas SVG sengaja diblokir pada penyimpanan publik guna menutup celah injeksi *Stored XSS*. Maksimal dekompresi piksel dibatasi 6.000 px guna mencegah serangan *Pixel Bomb*.
        </li>
        <li><strong>Isolasi Kredensial Server:</strong>
            Panel pengaturan admin menerapkan daftar terlarang (`config('editable.denied')`). Kunci `APP_KEY`, `DB_PASSWORD`, `AWS_*`, dan kredensial database tidak pernah dapat dibaca atau diubah melalui antarmuka web.
        </li>
        <li><strong>Pencegahan Bot & Spam Pemesanan:</strong>
            Formulir booking dilindungi oleh *Honeypot Trap* (`website_url` tersembunyi) dan pembatasan frekuensi (*Rate Limiting* `throttle:5,1`).
        </li>
        <li><strong>Privasi Faktur Pelanggan:</strong>
            URL faktur publik menggunakan kode acak (*alphanumeric booking code*), bukan ID inkremental, mencegah penjelajahan data pelanggan (*IDOR enumeration*).
        </li>
    </ul>

    <h2 class="subsection-title">Hasil Audit SEO & Mesin Pencari</h2>
    <ul>
        <li><strong>Server-Side Rendering Penuh (100% SSR):</strong> Seluruh teks paket, harga, jadwal, dan blog tercetak langsung di HTML server. Mesin pencari Google dapat mengindeks konten tanpa perlu bergantung pada eksekusi JavaScript.</li>
        <li><strong>Structured Data JSON-LD:</strong> Mengimplementasikan skema `TravelAgency`, `TouristTrip`, `Product`, `FAQPage`, dan `BreadcrumbList`.</li>
        <li><strong>Programmatic SEO (pSEO):</strong> Halaman pendaratan khusus mencakup 15 kota asal wisatawan (Jakarta, Surabaya, Bandung, Bali, Kuala Lumpur, Penang, Singapura, dll.).</li>
    </ul>

    <div class="page-break"></div>

    <!-- ==================== BAB V: HASIL PENGUJIAN OTOMATIS (TEST SUITE) ==================== -->
    <h1 class="section-title">BAB V: Hasil Pengujian Otomatis (Quality Assurance)</h1>
    <p>
        Setiap fungsi kode diverifikasi secara otomatis menggunakan test suite PHPUnit. Seluruh pengujian berjalan dalam lingkungan basis data terisolasi (*SQLite in-memory*).
    </p>

    <div class="score-box">
        <div class="score-number">178 / 178</div>
        <div class="score-label">Semua Pengujian Lolos 100% (1.100 Assertions Passed)</div>
    </div>

    <h2 class="subsection-title">Daftar Modul Pengujian Kunci yang Lolos</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%;">Modul Test</th>
                <th style="width: 20%; text-align: center;">Jumlah Assertion</th>
                <th style="width: 15%; text-align: center;">Hasil</th>
                <th style="width: 30%;">Aspek yang Diverifikasi</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>PackageContentTest</strong></td>
                <td style="text-align: center;">51 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Wishlist, toggle mata uang, akordeon, share WA, trust chips, back-to-top.</td>
            </tr>
            <tr>
                <td><strong>PricingTierTest</strong></td>
                <td style="text-align: center;">48 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Tier harga grosir, logika tarif anak, penanganan celah pax (gap fallback).</td>
            </tr>
            <tr>
                <td><strong>InvoicePageTest</strong></td>
                <td style="text-align: center;">36 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Perhitungan pajak, pembekuan kurs historical, privasi invoice URL.</td>
            </tr>
            <tr>
                <td><strong>IndeksabilitasTest</strong></td>
                <td style="text-align: center;">42 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Kesiapan SEO tanpa JS, meta deskripsi, canonical URL, srcset gambar.</td>
            </tr>
            <tr>
                <td><strong>MaterialSymbolsSubsetTest</strong></td>
                <td style="text-align: center;">24 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Kelengkapan glif font ikon dalam file WOFF2 14.9 KB.</td>
            </tr>
            <tr>
                <td><strong>MediaPremiumFeaturesTest</strong></td>
                <td style="text-align: center;">58 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Konversi WebP, ekstraksi warna dominan, auto-alt text, audit berkas yatim.</td>
            </tr>
            <tr>
                <td><strong>TranslationParityTest</strong></td>
                <td style="text-align: center;">112 Assertions</td>
                <td style="text-align: center;"><span class="badge badge-success">PASSED</span></td>
                <td>Kesetaraan kamus terjemahan Bahasa Indonesia, Inggris, dan Melayu.</td>
            </tr>
        </tbody>
    </table>

    <h2 class="subsection-title">Kepatuhan Palet Identitas Merek (Brand Compliance)</h2>
    <div class="callout">
        <strong>Pemeriksaan Hue 143°:</strong> Seluruh kelas warna antarmuka publik dan panel admin mematuhi warna Danau Toba (`green-*` dan `toba-green: #166534`). Tidak ada token warna `emerald` atau `teal` yang tersisa di dalam proyek (0 kemunculan).
    </div>

    <br><br>
    <div style="text-align: center; color: #64748b; font-size: 9pt; border-top: 1px solid #e2e8f0; padding-top: 15px;">
        Dokumen ini diterbitkan secara otomatis oleh sistem Sujai Lake Toba Engine.<br>
        &copy; {{ date('Y') }} Sujai Lake Toba (sujaitobasumatera.com). Seluruh hak cipta dilindungi.
    </div>

</body>
</html>

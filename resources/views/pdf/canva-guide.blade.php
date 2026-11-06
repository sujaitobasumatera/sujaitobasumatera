<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panduan Praktis Desain & Edit di Canva - Sujai Lake Toba</title>
    <style>
        @page {
            margin: 1.6cm 1.4cm 1.6cm 1.4cm;
            size: a4 portrait;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 10pt;
            margin: 0;
            padding: 0;
        }

        .page-break {
            page-break-after: always;
        }

        /* Header / Banner */
        .doc-header {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 6px solid #166534;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 22px;
        }
        .doc-badge {
            display: inline-block;
            background-color: #166534;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            padding: 3px 10px;
            border-radius: 12px;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .doc-title {
            font-size: 20pt;
            font-weight: 800;
            color: #14532d;
            margin: 0 0 6px 0;
            line-height: 1.2;
        }
        .doc-subtitle {
            font-size: 11pt;
            color: #475569;
            margin: 0;
        }

        h1.section-title {
            font-size: 14pt;
            font-weight: 800;
            color: #166534;
            border-bottom: 2px solid #166534;
            padding-bottom: 5px;
            margin-top: 18px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        h2.subsection-title {
            font-size: 11.5pt;
            font-weight: 700;
            color: #0f172a;
            margin-top: 16px;
            margin-bottom: 6px;
            border-left: 3px solid #166534;
            padding-left: 8px;
        }
        h3.block-title {
            font-size: 10.5pt;
            font-weight: 700;
            color: #1e293b;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        p {
            margin: 0 0 8px 0;
            color: #334155;
            font-size: 9.5pt;
            text-align: justify;
        }

        /* Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0 16px 0;
            font-size: 8.5pt;
        }
        table.data-table th {
            background-color: #166534;
            color: #ffffff;
            font-weight: bold;
            padding: 7px 9px;
            border: 1px solid #166534;
            text-align: left;
        }
        table.data-table td {
            padding: 6px 9px;
            border: 1px solid #cbd5e1;
            color: #334155;
            vertical-align: top;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Color Palette Chips */
        .color-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 8px;
            font-size: 8.5pt;
        }
        .color-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 3px;
            vertical-align: middle;
            margin-right: 6px;
            border: 1px solid rgba(0,0,0,0.1);
        }

        /* Callout */
        .callout {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-left: 4px solid #166534;
            padding: 10px 12px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 9pt;
            color: #14532d;
        }
        .callout-warning {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            border-left: 4px solid #d97706;
            padding: 10px 12px;
            border-radius: 6px;
            margin: 12px 0;
            font-size: 9pt;
            color: #92400e;
        }

        ul, ol {
            margin: 0 0 10px 0;
            padding-left: 20px;
            font-size: 9pt;
            color: #334155;
        }
        li {
            margin-bottom: 3px;
        }

        /* Checklist */
        .checklist-item {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 6px 10px;
            border-radius: 6px;
            margin-bottom: 5px;
            font-size: 8.5pt;
        }
        .check-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1.5px solid #166534;
            border-radius: 2px;
            margin-right: 6px;
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- Header Dokumen -->
    <div class="doc-header">
        <div class="doc-badge">Panduan Praktis Tim Kreatif & Desain</div>
        <div class="doc-title">PANDUAN DESAIN & EDIT DI CANVA</div>
        <div class="doc-subtitle">
            Standar Resmi Ukuran Kanvas, Rasio, Safe Zone, Warna Merek & Tipografi untuk <strong>sujaitobasumatera.com</strong>
        </div>
    </div>

    <!-- ==================== BAGIAN 1: TABEL UKURAN ==================== -->
    <h1 class="section-title">1. Tabel Ukuran Kanvas Canva (Custom Size)</h1>
    <p>
        Setiap kali membuat desain baru di Canva, klik <strong>"Create a design" &rarr; "Custom size"</strong> dan pastikan satuannya <strong>px (pixels)</strong>:
    </p>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 22%;">Jenis Kebutuhan</th>
                <th style="width: 10%; text-align: center;">Rasio</th>
                <th style="width: 20%;">Ukuran Kanvas</th>
                <th style="width: 18%;">Ukuran Minimum</th>
                <th style="width: 14%;">Safe Zone</th>
                <th style="width: 16%;">Fungsi di Web</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Galeri Foto Paket</strong></td>
                <td style="text-align: center;"><strong>4:3</strong></td>
                <td><strong>1600 &times; 1200 px</strong></td>
                <td>1200 &times; 900 px</td>
                <td>10% dari tepi</td>
                <td>Foto ke-1 jadi thumbnail WA & cover kartu paket.</td>
            </tr>
            <tr>
                <td><strong>Brosur / Info Harga</strong></td>
                <td style="text-align: center;"><strong>4:3</strong></td>
                <td><strong>1200 &times; 900 px</strong></td>
                <td>1024 &times; 768 px</td>
                <td>Min. 40 px tepi</td>
                <td>Tampil utuh (contain), dapat di-zoom 3.5&times;.</td>
            </tr>
            <tr>
                <td><strong>Hero Slider Desktop</strong></td>
                <td style="text-align: center;"><strong>16:9</strong></td>
                <td><strong>1920 &times; 1080 px</strong></td>
                <td>1600 &times; 900 px</td>
                <td>15% dari tepi</td>
                <td>Lanskap luas untuk layar monitor & laptop.</td>
            </tr>
            <tr>
                <td><strong>Hero Slider Mobile</strong></td>
                <td style="text-align: center;"><strong>4:5</strong></td>
                <td><strong>1080 &times; 1350 px</strong></td>
                <td>800 &times; 800 px</td>
                <td>15% dari tepi</td>
                <td>Foto vertikal agar pas di layar smartphone.</td>
            </tr>
            <tr>
                <td><strong>Foto Hotel & Kamar</strong></td>
                <td style="text-align: center;"><strong>4:3</strong></td>
                <td><strong>800 &times; 600 px</strong></td>
                <td>600 &times; 600 px</td>
                <td>10% dari tepi</td>
                <td>Tampak luar hotel dan interior kamar tidur.</td>
            </tr>
            <tr>
                <td><strong>Foto Artikel Blog</strong></td>
                <td style="text-align: center;"><strong>16:9</strong></td>
                <td><strong>1200 &times; 675 px</strong></td>
                <td>960 &times; 540 px</td>
                <td>10% dari tepi</td>
                <td>Budaya Batak, kuliner halal & destinasi wisata.</td>
            </tr>
        </tbody>
    </table>

    <div class="callout-warning">
        <strong>Penting tentang Brosur Harga:</strong> Brosur info harga ditampilkan secara utuh (<em>contain</em>) di website. Sisakan margin kosong minimal <strong>40 px di sekeliling kanvas</strong> agar angka harga tidak tergencet tepi layar ponsel pengunjung.
    </div>

    <!-- ==================== BAGIAN 2: SAFE ZONE ==================== -->
    <h1 class="section-title">2. Mengaktifkan Safe Zone (Batas Aman) di Canva</h1>
    <p>
        Batas aman bertujuan agar teks tidak terpotong saat gambar ditampilkan di layar HP berukuran kecil:
    </p>
    <ol>
        <li>Di Canva, tekan shortcut keyboard <strong>Shift + R</strong> (atau klik <em>File &rarr; View settings &rarr; Show rulers and guides</em>).</li>
        <li>Tarik garis pemandu (Guide) berwarna ungu dari penggaris atas dan kiri kanvas.</li>
        <li>Beri jarak aman <strong>10% sampai 15% dari tepi luar</strong> (sekitar 120–160 px dari pinggir kanvas).</li>
        <li><strong>Aturan Emas Safe Zone:</strong> Seluruh teks judul, angka harga, logo Sujai, dan wajah objek utama <u>WAJIB</u> berada di dalam batas garis ungu ini. Gambar latar belakang lanskap boleh penuh hingga tepi kanvas.</li>
    </ol>

    <div class="page-break"></div>

    <!-- ==================== BAGIAN 3: WARNA & FONT ==================== -->
    <h1 class="section-title">3. Palet Warna Resmi & Tipografi Sujai Lake Toba</h1>
    <p>
        Gunakan kode HEX warna resmi berikut di Canva agar desain selaras dengan tampilan website:
    </p>

    <div class="color-card">
        <span class="color-dot" style="background-color: #166534;"></span>
        <strong>Warna Utama (Toba Green): <code>#166534</code></strong> &mdash; Hijau pinus Danau Toba yang elegan & terpercaya.
    </div>
    <div class="color-card">
        <span class="color-dot" style="background-color: #14532d;"></span>
        <strong>Warna Gelap Header/Footer: <code>#14532d</code></strong> &mdash; Hijau tua premium untuk kontras teks putih.
    </div>
    <div class="color-card">
        <span class="color-dot" style="background-color: #ea580c;"></span>
        <strong>Warna Aksen Promo / Terpopuler: <code>#ea580c</code></strong> &mdash; Oranye hangat untuk badge diskon & promo.
    </div>
    <div class="color-card">
        <span class="color-dot" style="background-color: #0f172a;"></span>
        <strong>Warna Teks Judul & Harga: <code>#0f172a</code></strong> &mdash; Hitam pekat modern, sangat mudah dibaca.
    </div>
    <div class="color-card">
        <span class="color-dot" style="background-color: #ffffff;"></span>
        <strong>Warna Kartu / Kotak Harga: <code>#ffffff</code></strong> &mdash; Putih bersih dengan bayangan lembut.
    </div>

    <h2 class="subsection-title">Rekomendasi Font di Canva</h2>
    <ul>
        <li><strong>Judul Paket / Brosur:</strong> Gunakan <strong>Plus Jakarta Sans Bold / ExtraBold</strong>. Pilihan alternatif di Canva Gratis: <strong>Montserrat Bold</strong> atau <strong>Inter ExtraBold</strong>.</li>
        <li><strong>Angka Harga:</strong> Gunakan gaya <strong>ExtraBold / Black</strong> dengan ukuran paling dominan di antara elemen teks lainnya.</li>
        <li><strong>Rincian Fasilitas / Teks Bodi:</strong> Gunakan <strong>Plus Jakarta Sans Medium</strong> atau <strong>Inter Regular</strong>. Atur jarak baris (<em>Line Spacing</em>) ke angka <strong>1.4</strong> agar tidak rapat.</li>
    </ul>

    <!-- ==================== BAGIAN 4: TRIK DESAIN ==================== -->
    <h1 class="section-title">4. Trik Desain & Keterbacaan Foto Wisata</h1>
    <ul>
        <li><strong>Gradasi Hitam/Hijau Transparan (Vignette):</strong> Jika foto Danau Toba dijadikan background dan di atasnya ada teks putih, cari elemen Canva <code>gradient black to transparent</code>. Letakkan di bagian bawah foto dengan transparansi 40–60% agar teks putih terbaca sangat tajam.</li>
        <li><strong>Wadah Kartu Brosur Harga (Card Container):</strong> Untuk brosur info harga, buat bentuk persegi panjang bersudut tumpul (<em>Rounded Rectangle</em>) warna putih solid di tengah kanvas. Letakkan tabel harga dan daftar fasilitas di dalam kotak tersebut.</li>
    </ul>

    <!-- ==================== BAGIAN 5: EKSPOR ==================== -->
    <h1 class="section-title">5. Pengaturan Download / Ekspor dari Canva</h1>
    <ol>
        <li>Klik menu <strong>Share (Bagikan) &rarr; Download (Unduh)</strong> di pojok kanan atas Canva.</li>
        <li>Pilih <strong>File type: JPG</strong> dengan ukuran <strong>1x</strong> untuk foto galeri destinasi dan hero slider (kualitas 80–85%).</li>
        <li>Pilih <strong>File type: PNG</strong> khusus untuk brosur info harga yang berisi banyak baris tabel teks tajam.</li>
        <li><em>Jangan memilih ukuran 2x atau 3x di Canva</em> karena akan membuat ukuran berkas bengkak hingga puluhan megabyte. Sistem website Sujai Lake Toba sudah otomatis mengompresi dan mengonversi gambar ke format WebP super cepat saat diunggah.</li>
    </ol>

    <!-- ==================== BAGIAN 6: CHECKLIST ==================== -->
    <h1 class="section-title">6. Ceklis Sebelum Mengunggah ke Website</h1>
    <div class="checklist-item"><span class="check-box"></span> Apakah ukuran kanvas sudah sesuai tabel (misal 1600 &times; 1200 px untuk galeri)?</div>
    <div class="checklist-item"><span class="check-box"></span> Apakah teks harga dan judul berada di dalam Safe Zone (tidak menempel ke tepi)?</div>
    <div class="checklist-item"><span class="check-box"></span> Apakah teks memiliki kontras yang cukup (tidak pudar di atas foto terang)?</div>
    <div class="checklist-item"><span class="check-box"></span> Apakah warna utama sudah menggunakan kode HEX resmi <code>#166534</code>?</div>
    <div class="checklist-item"><span class="check-box"></span> Apakah berkas diekspor dalam ukuran 1x (bukan 2x/3x)?</div>

    <br>
    <div style="text-align: center; color: #64748b; font-size: 8pt; border-top: 1px solid #e2e8f0; padding-top: 10px;">
        Dokumen Panduan Canva Resmi &bull; &copy; {{ date('Y') }} Sujai Lake Toba (sujaitobasumatera.com).
    </div>

</body>
</html>

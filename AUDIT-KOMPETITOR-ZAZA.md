# Audit Kompetitor — zazatourjogja.com

**Tanggal:** 21 Juli 2026
**Objek banding:** sujailaketoba.com (kita) vs zazatourjogja.com (Zaza Tour)
**Catatan:** Zaza bukan kompetitor langsung (pasar Jogja, bukan Sumut). Nilainya sebagai
pembanding *model bisnis dan struktur komersial*, bukan sebagai rival perebutan kata kunci.

---

## 1. Ringkasan Eksekutif

| | Zaza Tour | Sujai Toba Sumatera |
|---|---|---|
| **Jenis** | Situs brosur (company profile + katalog) | Aplikasi web (katalog + transaksi) |
| **Kekuatan** | Cakupan segmen pasar, lini pendapatan ganda | Rekayasa produk, otomasi, infrastruktur SEO |
| **Kelemahan** | Struktur URL berantakan, tanpa sistem, tanpa bukti klaim | Cakupan segmen sempit, bukti sosial lemah, aset menganggur |

**Kesimpulan inti:**
Kita unggul jauh secara teknis. Zaza unggul dalam satu hal yang justru paling dekat dengan
uang — **mereka menjual ke lebih banyak jenis pembeli.** Selisih ini bisa ditutup dengan
pekerjaan yang relatif murah, karena sebagian besar infrastrukturnya sudah kita miliki dan
hanya belum dipakai.

---

## 2. Yang Zaza Lakukan Lebih Baik

### 2.1 Segmentasi pasar sebagai pintu masuk utama

Homepage Zaza menampilkan **8 kartu kategori** sebagai navigasi utama:

| Berdasarkan durasi | Berdasarkan keperluan |
|---|---|
| Paket 1 Hari | Honeymoon |
| Paket 2H1M | Outbound |
| Paket 3H2M | Company Gathering |
| Paket 4H3M | Study Tour / Studi Banding |

Kita hanya punya satu sumbu: **daftar paket unggulan** (`resources/views/tour/index.blade.php:85-192`).

**Kenapa ini penting.** Orang tidak menelusuri katalog, mereka mencari kecocokan dengan
keperluannya. Calon pembeli mengetik *"paket honeymoon Danau Toba"* atau *"gathering kantor
ke Samosir"*, bukan *"paket wisata"*. Kartu kategori adalah cara menangkap niat itu, dan
setiap kartu jadi satu halaman yang bisa diindeks mesin pencari.

**Kabar baiknya:** kita sudah punya polanya. `resources/views/tour/landing-origin.blade.php`
(588 baris) melayani `/paket-wisata-danau-toba-dari-{kota}` — landing per kota asal. Landing
per *segmen* adalah pola identik dengan variabel berbeda.

### 2.2 Lini pendapatan kedua: sewa kendaraan

Zaza menampilkan 8 unit dengan harga terbuka dan pembanding jelas:

| Unit | 12 Jam | Fullday |
|---|---|---|
| Grand New Avanza | Rp 550.000 | Rp 650.000 |
| … | … | … |
| Fortuner | Rp 1.800.000 | Rp 2.000.000 |

Setiap unit punya ikon fasilitas seragam (Driver, BBM, AC, Audio) dan tombol WhatsApp sendiri.

**Nilainya dua lapis:**
1. **Pendapatan** — melayani pembeli yang belum siap beli paket lengkap tapi butuh transport.
2. **SEO ekor panjang** — *"sewa avanza jogja"* jauh lebih mudah diperingkat daripada
   *"paket wisata jogja"*.

Padanan untuk kita: **sewa mobil + driver Medan–Parapat**, penjemputan Bandara Silangit /
Kualanamu, sewa untuk rute Berastagi–Tangkahan.

> ⚠️ Ini keputusan bisnis, bukan teknis. Jangan dibangun sebelum dipastikan armadanya ada.

### 2.3 Telepon terlihat di header

Zaza menaruh nomor telepon langsung di header, berdampingan dengan WhatsApp.
Kita hanya punya tombol WhatsApp mengambang (`resources/views/layouts/app.blade.php:166`).

Segmen rombongan, korporat, dan wisatawan usia lanjut masih menelepon. Menyembunyikan nomor
berarti kehilangan justru segmen bertiket paling besar.

### 2.4 Halaman "Kenapa Pilih Kami" di homepage

Zaza: 4 ikon tepat setelah hero — Booking Mudah, Proses Cepat, Banyak Pilihan, Harga Terjangkau.

Kita **punya** section ini, tetapi terletak di `/about`
(`resources/views/pages/about.blade.php:179`), bukan di homepage. Homepage kita melompat dari
hero langsung ke daftar paket — tidak pernah menjawab *"kenapa kalian, bukan yang lain."*

Pengunjung yang mendarat dari iklan atau pencarian tidak akan mengklik "Tentang Kami" hanya
untuk mencari alasan mempercayai kita. Alasan itu harus ditemuinya tanpa diminta.

---

## 3. Yang Kita Lakukan Lebih Baik

Bagian ini penting agar audit tidak berubah jadi daftar rasa rendah diri.

### 3.1 Kita punya sistem, mereka punya halaman

| Kemampuan | Zaza | Kita |
|---|---|---|
| Sistem pemesanan | ✗ (WhatsApp manual) | ✓ |
| Invoice & itinerary PDF | ✗ | ✓ (`PdfController`) |
| Pelacakan pesanan pelanggan | ✗ | ✓ (`/track-booking/{code}`) |
| Multi-mata-uang | ✗ | ✓ |
| Multi-bahasa (id/en/ms) | ✗ | ✓ |
| Panel admin + CMS | ✗ | ✓ |
| Log aktivitas & error | ✗ | ✓ |
| PWA / aplikasi admin Android | ✗ | ✓ |

### 3.2 Struktur URL & SEO teknis

URL Zaza tidak konsisten dan saling memakan:

```
/paket-wisata-paket-wisata-1-hari-1      ← kata "paket wisata" dua kali
/detail-paket-5-5                        ← tanpa makna, tanpa kata kunci
/post-paket-tour-jogja-4h3m-620
/jogja-4d3n/                             ← duplikat isi dengan yang di atas
/paket-wisata-jogja/1-hari/paket-11/     ← pola ketiga untuk hal yang sama
```

Empat pola penamaan berbeda untuk jenis halaman yang sama, sebagian berisi materi kembar.
Ini melemahkan peringkat mereka sendiri.

Kita: slug bersih, satu pola, ditambah `sitemap.xml` otomatis, Schema.org `TravelAgency` +
`WebSite` + `SearchAction` (`resources/views/tour/index.blade.php:27-74`), dan banner
OpenGraph dinamis. **Fondasi SEO kita lebih kuat.** Yang kurang bukan kualitasnya, melainkan
jumlah halamannya.

### 3.3 Positioning

Judul Zaza: *"Wujudkan Liburan Impianmu Bersama Kami!"* — kalimat yang dipakai ratusan agen
perjalanan dan karenanya tidak menandai siapa pun.

Danau Toba punya bahan yang Jogja tidak punya: kaldera vulkanik terbesar di dunia, budaya
Batak, Samosir. Positioning kita bisa jauh lebih tajam. **Jangan turun ke bahasa template.**

---

## 4. Temuan Internal (di luar perbandingan)

Ditemukan saat menelusuri kode untuk audit ini:

### 4.1 🔴 Endpoint outbound tanpa form

`routes/web.php:200` mendaftarkan `POST /outbound/quote/submit` →
`PublicController::submitOutboundQuote()` (`app/Http/Controllers/PublicController.php:447-458`),
lengkap dengan penyusunan pesan WhatsApp dan pesan sukses.

**Tidak ada satu pun form di `resources/views/` yang mengarah ke sana.** Fitur ini sudah
dibangun tetapi tidak pernah bisa dijangkau pengunjung.

Ini justru kabar baik untuk rekomendasi §2.1 — segmen *outbound* sudah setengah jadi,
tinggal diberi halaman dan form.

### 4.2 🟡 Testimoni masih data contoh

`resources/views/tour/index.blade.php:357-372` memuat testimoni cadangan atas nama
"Julian Thorne (London, UK)" dan "Isabella Chen (Singapura)".

Selama CMS belum diisi testimoni sungguhan, **inilah yang dilihat publik.** Nama asing
dengan foto stok adalah pola yang sudah dikenali pembaca sebagai karangan, dan justru
menurunkan kepercayaan alih-alih menaikkannya.

### 4.3 🟡 Klaim angka tanpa penopang

`resources/views/pages/about.blade.php:86,97` menampilkan "Tahun Pengalaman" dan
"Wisatawan Puas", serta "Dipercaya Oleh Institusi Terkemuka" (baris 242).

Pastikan angka-angka ini benar dan logo institusinya memang memberi izin. Klaim yang tidak
bisa dibuktikan lebih berisiko daripada tidak mengklaim apa-apa.

---

## 5. Yang Sebaiknya TIDAK Ditiru

| Praktik Zaza | Alasan |
|---|---|
| Kredit pengembang di footer ("Jogja Media Web") | Menurunkan kesan premium; menjadikan situs tampak sebagai proyek pesanan, bukan merek |
| Dua nomor WhatsApp berbeda | Kita sudah sengaja menyatukan sumber nomor (commit `f22e766`). Pertahankan. |
| Empat pola URL untuk satu jenis halaman | Memecah kekuatan peringkat sendiri |
| Judul generik | Menghapus pembeda yang justru kita miliki |
| Testimoni foto stok tanpa bukti | Lihat §4.2 — pembaca mengenali polanya |

---

## 6. Rekomendasi Berurutan

Diurut berdasarkan **dampak dibagi usaha**, bukan berdasarkan kemudahan.

### Prioritas 1 — Landing page per segmen 🔴
**Usaha:** ~1 hari · **Dampak:** Tertinggi

Buat halaman untuk: Honeymoon, Gathering Kantor, Study Tour, Keluarga, Outbound.
Gunakan `landing-origin.blade.php` sebagai acuan pola. Mulai dari **Outbound**, karena
backend-nya sudah ada (§4.1) — jadi hasil pertama datang paling cepat.

Tambahkan grid kategori di homepage sebagai pintu masuknya.

### Prioritas 2 — Section "Kenapa Pilih Kami" di homepage 🟠
**Usaha:** ~1 jam · **Dampak:** Sedang, langsung terasa

Materinya sudah ada di `/about`. Ini pekerjaan memindahkan dan memadatkan, bukan menulis
dari nol. Letakkan tepat setelah hero.

### Prioritas 3 — Ganti testimoni contoh dengan yang asli 🟠
**Usaha:** ~2 jam (mayoritas mengumpulkan bahan) · **Dampak:** Sedang

Tangkapan layar percakapan asli atau tautan ulasan Google mengalahkan kutipan berfoto stok.

### Prioritas 4 — Nomor telepon di header 🟡
**Usaha:** ~15 menit · **Dampak:** Sedang

`tel:` di ponsel, teks biasa di desktop.

### Prioritas 5 — Sewa mobil & penjemputan bandara 🟡
**Usaha:** ~1 hari · **Dampak:** Tinggi — **tetapi butuh keputusan bisnis dulu**

Jangan dibangun sebelum dipastikan layanannya memang akan dijalankan.

---

## 7. Catatan Arsitektur

Muncul pertanyaan wajar: apakah landing kota dan landing segmen sebaiknya digabung menjadi
satu sistem landing generik?

**Rekomendasi: jangan digabung dulu.**

Menggeneralisasi sekarang berarti menyentuh halaman yang sudah berjalan dan menghasilkan,
demi keluwesan yang belum terbukti dibutuhkan. Buat dulu satu landing segmen secara terpisah,
lihat apakah polanya benar-benar sama setelah dipakai sungguhan, baru satukan bila memang
terbukti kembar.

Duplikasi yang terbaca jelas lebih murah daripada abstraksi yang salah.

---

## Sumber

- [Zaza Tour — Beranda](https://www.zazatourjogja.com/)
- [Zaza Tour — Tentang Kami](https://zazatourjogja.com/tentang-kami)
- [Zaza Tour — Paket Outbound Jogja](https://www.zazatourjogja.com/paket-outbound-jogja/)
- [Zaza Tour — Paket Honeymoon Jogja](https://www.zazatourjogja.com/paket-wisata-paket-honeymoon-jogja-6)
- [Zaza Tour — Paket Tour Jogja 4H3M](https://www.zazatourjogja.com/post-paket-tour-jogja-4h3m-620)

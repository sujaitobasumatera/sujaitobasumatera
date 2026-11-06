# Panduan Standar Ukuran Desain Gambar — Sujai Toba Sumatera

Dokumen ini adalah acuan resmi ukuran dan rasio desain aset visual untuk website **Sujai Toba Sumatera**.
Pastikan setiap desainer grafis dan administrator konten mengikuti panduan ini agar tampilan visual di desktop maupun smartphone tetap tajam, proporsional, dan tidak terpotong.

---

## 1. Tabel Ringkasan Ukuran & Rasio Desain

| Lokasi Aset | Rasio | Dimensi Rekomendasi (px) | Dimensi Minimal (px) | Fit Type | Safe Zone (Margin) | Catatan Penting |
| :--- | :---: | :---: | :---: | :---: | :---: | :--- |
| **Galeri Foto Paket** | **4:3** | **1600 &times; 1200** | 1200 &times; 900 | `cover` | 10% dari tepi | Foto urutan pertama otomatis menjadi **sampul kartu** dan pratinjau WhatsApp. Subjek utama wajib di tengah. |
| **Brosur / Gambar Info Harga** | **4:3** | **1200 &times; 900** | 1024 &times; 768 | `contain` | **Minimal 40 px** | Ditampilkan utuh di kartu paket. Hindari menaruh teks/tabel harga terlalu mepet ke tepi bingkai. |
| **Hero Slider Beranda (Desktop)** | **16:9** | **1920 &times; 1080** | 1600 &times; 900 | `cover` | 15% dari tepi | Gambar lanskap luas Danau Toba. Gelapkan area tengah-bawah untuk keterbacaan teks judul putih. |
| **Hero Beranda (Mobile)** | **4:5 / 1:1** | **1080 &times; 1350** | 800 &times; 800 | `cover` | 15% dari tepi | Foto vertikal/persegi untuk layar ponsel. |
| **Foto Penginapan / Hotel** | **4:3 / 1:1** | **800 &times; 600** | 600 &times; 600 | `cover` | 10% dari tepi | Foto tampak depan atau kamar hotel untuk rincian akomodasi per malam. |
| **Foto Artikel Blog / Berita** | **16:9** | **1200 &times; 675** | 960 &times; 540 | `cover` | 10% dari tepi | Gambar tajam bertema budaya, kuliner, dan destinasi wisata Toba. |
| **Logo Website (Header)** | **Bebas** | **Tinggi 48–60 px** | Tinggi 40 px | `contain` | Transparan (PNG/SVG) | Format PNG transparan atau SVG vektor dengan kontras tinggi. |

---

## 2. Aturan Komposisi & Safe Zone (Zona Aman)

1. **Rasio 4:3 Adalah Standar Utama Wisata:**
   - Semua kartu paket wisata, galeri kisi, dan pratinjau foto menggunakan rasio **4:3**.
   - Menghindari rasio ekstrem (seperti panorama 21:9 atau vertikal 9:16 untuk galeri paket) karena sisi atas/bawah akan terpotong saat dipasang di kartu.

2. **Safe Zone Teks Brosur Harga (Padding 40 px):**
   - Saat membuat grafis brosur harga bertingkat, sisakan ruang kosong minimal **40 px** di sisi atas, bawah, kiri, dan kanan.
   - Ini memastikan angka jutaan rupiah dan rincian fasilitas terbaca 100% utuh di layar HP terkecil (360 px) tanpa tertutup bezel atau terpotong.

3. **Titik Fokus (Rule of Thirds):**
   - Letakkan wajah orang, kapal feri, rumah adat Batak, atau pulau Samosir di 70% area tengah.
   - Sisi 15% di sekeliling bingkai dapat mengalami pemangkasan responsif (cropping) tergantung rasio layar pengguna.

---

## 3. Format File & Optimasi Sistem

- **Format Master:** Ekspor desain dalam format **WebP** (kualitas 85–90%) atau **JPG** berkualitas tinggi.
- **Ukuran Berkas Maksimal:** 
  - Maksimal upload di form admin: **15 MB**.
  - Rekomendasi berat berkas optimal: **300 KB – 1.5 MB** agar cepat diunggah.
- **Otomatisasi Server:**
  Sistem server Sujai Toba Sumatera telah dilengkapi mesin kompresi otomatis:
  1. Mengonversi gambar ke format modern **WebP**.
  2. Menghasilkan salinan multi-resolusi (`srcset`: 400w, 800w, 1200w, 1600w).
  3. Menyimpan gambar asli untuk penampil zoom layar penuh resolusi tinggi.

---

## 4. Fitur Pratinjau & Zoom di Sistem

- **Layar Penuh & Perbesaran:**
  Setiap foto di website (hero detail paket, galeri foto, brosur harga) dan form admin kini dapat diklik untuk membuka **Modal Zoom Layar Penuh**.
- **Fitur Kontrol Zoom:**
  - Tombol **Zoom In (`+`)** & **Zoom Out (`-`)** hingga **3.5&times;** perbesaran.
  - **Double Click / Double Tap** untuk memperbesar/mengembalikan ukuran normal seketika.
  - **Drag / Pan:** Geser gambar dengan mouse atau jari saat di-zoom untuk membaca detail angka atau teks terkecil.
  - **Indikator Dimensi:** Menampilkan resolusi asli gambar (misal: `1600 × 1200 px`) secara otomatis.

# Update — Sesi 3 Agustus 2026

Catatan serah-terima. Mulai baca dari **"Lanjut dari sini"** di bagian bawah.

Semua sudah ter-commit dan ter-push ke remote `sujai`, dan **sudah hidup di
produksi**. Working tree bersih. Tes: **176/176 lulus**.

---

## 1. Yang dikerjakan malam ini

Delapan commit, dari `8258b89` sampai `082b580`.

| commit | isi |
|---|---|
| `42ec816` | halaman detail mobile-first, galeri kisi, pembeda per-paket, batang bawah |
| `e1652ca` | grid `/tour/packages` dicetak server (bukan digambar Alpine dari JSON) |
| `a78f0b3` | `srcset` di kartu paket + perintah `media:variants` |
| `6974f7d` | **hentikan kebocoran `cost_price`** ke HTML publik + ulasan pindah ke bawah |
| `e2e184d` | `.gitignore` untuk screenshot & unggahan media |
| `d4e3f57` | PDF itinerary: tombol Lihat + Unduh terpisah |
| `cccb1d7` | perbaikan: ulasan melompat ke paling atas di ponsel |
| `889f422` | audit SEO menyeluruh: isi halaman dicetak server, gambar 9,79 MB → 3,08 MB |
| `082b580` | pratinjau WhatsApp tidak lagi menampilkan kode HTML |

### Angka yang berubah

| | sebelum | sesudah |
|---|---|---|
| Gambar diunduh ponsel (13 halaman) | 9,79 MB | **3,08 MB** (−68%) |
| Halaman yang isinya hilang tanpa JS | 3 | **0** |
| `<img>` pakai `srcset` | 20 | **67** |
| Muatan JSON `/tour/packages` | 50,5 KB | 10,7 KB |

### Dua temuan keamanan / privasi

1. **`cost_price` (harga modal ke pemasok) tercetak di HTML publik** halaman
   detail, lewat `@js($package)` yang mengirim seluruh objek. Sekarang hanya 13
   kolom yang benar-benar dipakai Alpine yang dikirim — **daftar putih**, supaya
   kolom sensitif berikutnya tidak ikut lolos otomatis.
2. **11 berkas PHP dapat dieksekusi publik di `public/`**, termasuk
   `shell.php` — eksekusi perintah jarak jauh **tanpa autentikasi**, terbuka
   sejak 18 Juli. Sudah dicabut ke `~/karantina-*` di server; semuanya 404.
   Kredensial dinyatakan sudah ditangani oleh pemilik.

---

## 2. Keadaan produksi

Semua sudah beres di server malam ini:

- 6 migrasi tertunda dijalankan (tertua 24 Juli) — kolom `highlights` &
  `priceImage` sekarang ada
- Media disatukan ke `persistent_uploads`: **135 berkas, 0 hilang**
- Unggahan admin & berkas lama dua-duanya `200`
- Pratinjau WhatsApp bersih, terverifikasi di 3 paket

### Gotcha produksi yang WAJIB diingat

Ini yang memakan waktu paling lama untuk diurai:

1. **Auto-deploy Rumah Web menarik kode tapi TIDAK PERNAH menjalankan migrasi.**
   Selalu jalankan `bash ~/deploy.sh` di server setiap habis push.
2. **Auto-deploy juga menghapus isi folder di dalam proyek.** Terbukti langsung:
   `storage/app/public` menyusut 34 MB → 16 MB di tengah pekerjaan. Media aman
   karena `persistent_uploads` berada di LUAR folder proyek.
3. **JANGAN `php artisan storage:link`.** `function_exists('symlink')` = `false`
   di hosting ini; artisan diam-diam membuat direktori kosong. Disk `public`
   memakai `'serve' => true` — Laravel sendiri yang melayani `/storage/*`.
4. **`npm run build` sesudah `view:clear` membuang ~12 KB kelas CSS** tanpa
   error, karena Tailwind memindai `storage/framework/views`. Urutannya selalu
   `view:cache` dulu, baru `build`. Ukuran benar ≈ 205 KB, bukan ~192 KB.

---

## 3. Lanjut dari sini

### A. Milik pemilik — bukan pekerjaan kode

- [ ] **Uji di peramban** (belum dilakukan sama sekali; semua verifikasi malam
      ini lewat tes & angka, bukan mata):
  1. Admin → Paket → Edit → **Simpan**
  2. **Unggah satu foto** ke paket, buka halaman detailnya ← paling menentukan
  3. `/tour/gallery` — foto tampil
  4. Kirim `https://sujaitobasumatera.com/tour/detail/paket-samosir-adventure-4d3n`
     ke WhatsApp sendiri — pratinjau harus bawa judul, kalimat, foto
- [ ] **Isi konten 7 paket** yang masih kosong (lihat tabel di bawah). Ini
      berdampak jauh lebih besar daripada perbaikan kode mana pun yang tersisa.
- [ ] Hapus `~/storage-lama-*.tar.gz`, `~/backup-*.sql`, `~/karantina-*/` di
      server — **hanya setelah** keempat ujian di atas lolos.

### B. Kekosongan konten (per 3 Agustus)

Hanya `paket-samosir-adventure-4d3n` yang punya video, hotel, dan peta.
Tujuh paket lain kosong, jadi blok-blok yang sudah dibangun tidak pernah tampil.
Harga bertingkat juga baru terisi di satu paket.

Fitur yang menyala sendiri begitu diisi lewat panel admin:

| isi | efek |
|---|---|
| Video paket | blok "Video Perjalanan" muncul |
| Penginapan per malam | blok "Menginap di Mana" muncul |
| Peta lokasi | blok "Lokasi" muncul |
| Foto ke-5 dan seterusnya | **galeri kisi** muncul |
| "Kenapa Paket Ini Berbeda" | menggantikan poin situs, judul ikut berganti |
| Harga bertingkat | **tabel harga grosir** menggantikan gambar harga di kartu |
| Foto pertama paket | jadi gambar pratinjau WhatsApp paket itu |

### C. Pekerjaan kode yang tersisa (opsional, urut manfaat)

1. **Video per hotel** — satu-satunya fitur yang situs pembanding
   (jelajahwisatasumatera.my.id) punya dan kita belum: video tiap hotel yang
   akan dipakai. Butuh kolom baru di `accommodations` + form admin + render.
   Saran: tunggu sampai videonya benar-benar ada.
2. **Tes penjaga `display:contents`** — perluas jadi "setiap anak langsung wadah
   itu wajib punya kelas `order-*`". Sekarang tesnya hanya menjaga blok ulasan;
   blok baru yang lupa `order-*` akan melompat ke paling atas di ponsel lagi.
3. **`<style>` menumpang di dalam grid** halaman detail — tidak berbahaya,
   tempatnya di `app.css`. Kosmetik.
4. **`str_contains(base_path(), 'public_html')`** di `config/filesystems.php`
   itu rapuh — ia menebak lingkungan dari potongan path. Lebih baik dikendalikan
   variabel `.env` eksplisit.

---

## 4. Perintah yang sering dipakai

```bash
# Lokal — urutan ini tidak boleh dibalik
php artisan view:cache && npm run build && php artisan test

# Server — setiap habis push
bash ~/deploy.sh

# Bangkitkan varian gambar -400/-800 sesudah menambah aset baru
php artisan media:variants
```

Catatan lokal: MySQL XAMPP tidak terdaftar sebagai service dan biasanya mati.
Nyalakan dengan
`& 'C:\xampp\mysql\bin\mysqld.exe' --defaults-file='C:\xampp\mysql\bin\my.ini' --standalone`
sebagai proses latar, lalu tunggu port 3306 terbuka.

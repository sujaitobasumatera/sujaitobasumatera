# PROJECT_KNOWLEDGE — sujaitobasumatera.com

Catatan teknis ringkas yang tidak tersurat dari kode. Lihat juga `CLAUDE.md` untuk aturan kerja.

---

## Palet Merek

### ATURAN UTAMA: semua hijau wajib di hue 143°

Ini satu-satunya aturan yang tidak boleh dilanggar. Yang bikin warna terasa "beda-beda" bukan gelap-terangnya, tapi **hue**-nya. Per commit terakhir, ke-53 hijau di seluruh kode duduk di 142–144°.

**Konsekuensi praktis:**

- **Pakai skala `green-*` Tailwind, JANGAN `emerald-*` / `teal-*`.** Ini jebakan utamanya: `emerald` ada di hue ~160°, `teal` ~175°, sementara merek kita 143°. Sekilas sama-sama hijau, tapi disandingkan langsung terasa dari dua merek berbeda. Seluruh 288 kelas `emerald-*`/`teal-*` sudah ditukar ke `green-*`.
- **Jangan tulis hex mentah di Blade.** Kalau terpaksa (mis. CSS inline untuk PDF), pastikan hue-nya 143°.

Cek cepat sebelum commit — kalau keluarannya bukan `0`, ada hijau nyasar:

```bash
grep -rhoE "(emerald|teal)-[0-9]{2,3}" resources/ | wc -l
```

**Token hijau:**

| Token | Hex | Peran | Kontras di putih |
|---|---|---|---|
| `--color-toba-green` | `#166534` | **hijau merek** — logo, link, ikon, tombol, aksen admin | 7.13:1 |
| `--color-primary` | `#052e15` | latar gelap besar (hero, band CTA) agar teks putih terbaca | 14.91:1 |
| `--color-primary-container` | `#0b3c1e` | hover & blok sorot | 12.51:1 |
| `--color-on-primary-container` | `#77a789` | teks di atas `primary-container` | — |

Butuh hijau yang lebih terang/gelap? **Jangan bikin hex baru.** Pakai modifier opasitas Tailwind (`bg-toba-green/10`, `shadow-primary/30`) supaya nilainya tetap satu sumber.

**Oranye — warna aksi (CTA):**

| Token | Hex | Peran |
|---|---|---|
| `--color-toba-orange` | `#e67e22` | tombol pill, state aktif navbar |
| `--color-toba-orange-dark` | `#d35400` | hover tombol |

Aturan pakai: **hijau = identitas, oranye = ajakan bertindak.** Oranye jangan dipakai untuk teks isi karena kontrasnya hanya 2.85:1 di putih (gagal AA) — aman hanya sebagai latar tombol dengan teks putih.

`theme-color` di `layouts/app.blade.php` dan `theme_color` di `public/manifest.json` mengikuti `#166534`; ubah bertiga sekaligus kalau hijau merek berganti.

**Catatan build:** `app.css` punya `@source '../../storage/framework/views/*.php'`. Kalau cache view sedang hangat saat `npm run build`, CSS hasil build membengkak ~13 KB dari kelas duplikat. Jalankan `php artisan view:clear` sebelum build produksi.

**Kontras yang diketahui turun:** label lokasi & harga di preview slider `admin/cms/tour.blade.php` adalah teks hijau di atas panel gelap. Dulu `#15803d` (3.56:1), sekarang `#166534` (2.50:1) — keduanya sudah gagal AA sejak awal. Kalau mau diperbaiki, ubah teksnya jadi putih, jangan tambah hijau baru.

**Token warna dibersihkan dari 52 → 25.** Yang dihapus semuanya nol pemakaian: seluruh keluarga `tertiary-*` (maroon), `*-fixed`/`*-fixed-dim`, `inverse-*`, `surface-tint`, `toba-light`, `toba-blue`. Sudah diverifikasi tidak ada satu pun referensi `var(--color-*)` di luar blok `@theme`.

**Emas masih jadi aksen kedua.** `--color-secondary: #735c00` dan turunannya (hue 43–48°) dipakai ~197 kali. Ini keluarga warna terpisah dari hijau & oranye — belum diputuskan mau disatukan atau dipertahankan sebagai aksen sah.

**Utang teknis:** masih ada dua sistem penamaan token yang jalan berdampingan — token Material (`primary`, `secondary`, `surface`, `on-*`) dipakai halaman publik, token `toba-*` dipakai panel admin (±279 pemakaian). Nilainya sudah satu hue, tapi penamaannya belum. Penggabungan penuh = sweep besar di `resources/views/admin`.

---

## Standar Mobile-First Tabel Admin

Seluruh halaman (user + admin) memakai Tailwind yang **mobile-first by default** (kelas tanpa prefix = mobil; `sm:`/`md:`/`lg:` menambah gaya saat layar membesar). Halaman user (`index`, `tour/package-detail`, navbar) sudah mobile-first kualitas tinggi — jangan ditulis ulang.

Untuk **tabel daftar di panel admin**, ikuti pola berikut (acuan asli: `admin/bookings/index.blade.php`):

1. **Sembunyikan kolom sekunder di HP**, tampilkan bertahap:
   - `hidden sm:table-cell` — muncul mulai layar kecil
   - `hidden md:table-cell` — muncul mulai tablet
   - `hidden lg:table-cell` — muncul mulai desktop
   - Terapkan kelas yang sama pada `<th>` **dan** `<td>` kolom tersebut.
2. **Surface info penting secara inline** di sel utama (nama) khusus HP dengan blok `md:hidden` / `sm:hidden` (mis. harga, status, tanggal).
3. **Padding adaptif**: sel tepi pakai `px-5 md:px-8` atau `px-5 md:px-10` agar tidak sempit di HP.
4. **Tombol aksi**: gunakan `md:opacity-0 group-hover:opacity-100` — JANGAN `opacity-0 group-hover:...` saja, karena di HP tidak ada hover sehingga tombol jadi tak terlihat & tak bisa ditekan. Pastikan `<tr>` punya kelas `group`.
5. `min-w-0` + `truncate` pada wadah teks agar tidak melebar/overflow.

### Status penerapan (per audit mobile-first)
Sudah sesuai pola: `bookings`, `cars`, `users`, `customers` (index & show), `blogs`, `cities` (tab kategori), `regencies`, `logs`, `finance`, `reports/financial`.
Grid kartu (sudah responsif, bukan tabel): `packages/index`, `cities` (tab destinasi), `media/index`.

---

## Koneksi FTP dan Akses Server Langsung (100% Control)

Mulai 4 Oktober 2026, kita punya kontrol **100% langsung ke production** via FTP (karena tidak ada SSH di cPanel ini). 

- **Credentials FTP:** dmin@sujaitobasumatera.com / Laketoba_1
- **Metode Sinkronisasi:** File Powershell tp_sync_files.ps1 dan tp_upload.ps1 yang ada di artifact dapat digunakan untuk me-replace kode apa pun di server secara real-time.
- **Eksekusi Artisan via Browser:** Karena cPanel memblokir exec(), perintah Artisan dijalankan dengan melempar file PHP mandiri yang mem-bootstrap Laravel Facade langsung via web request, kemudian file tersebut otomatis menghapus dirinya sendiri setelah dieksekusi (lihat referensi un_once_refresh_og.php).
- Dengan pola ini, ketiadaan SSH tidak lagi jadi masalah. Kita bebas membersihkan cache, regenerasi file, atau patching cepat tanpa harus mendeploy ulang satu project utuh via CI/CD.

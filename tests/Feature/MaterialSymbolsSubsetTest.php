<?php

namespace Tests\Feature;

use Tests\TestCase;

class MaterialSymbolsSubsetTest extends TestCase
{
    /**
     * Setiap ikon Material Symbols yang dipakai Blade harus ada di subset font.
     *
     * Font ikonnya dipangkas dari ~3000 ikon jadi ~100 demi ukuran berkas.
     * Memakai nama ikon di luar daftar itu TIDAK melempar error apa pun --
     * browser sekadar mencetak ligaturnya apa adanya, jadi tamu membaca kata
     * "CHECK" atau "VIDEOCAM" di tengah halaman. Tidak ada di log, tidak ada
     * di konsol; ketahuannya hanya kalau ada yang kebetulan melihat.
     */
    public function test_semua_ikon_yang_dipakai_ada_di_subset_font(): void
    {
        $daftar = file_get_contents(base_path('resources/fonts/material-symbols-subset.py'));
        $this->assertNotFalse($daftar, 'skrip subset font tidak ditemukan');

        $tersedia = [];
        foreach (['USED', 'EXTRA'] as $var) {
            if (preg_match('/'.$var.' = "([^"]*)"/', $daftar, $m)) {
                $tersedia = array_merge($tersedia, preg_split('/\s+/', trim($m[1])));
            }
        }
        $tersedia = array_flip(array_filter($tersedia));
        $this->assertNotEmpty($tersedia, 'daftar ikon di skrip subset kosong');

        $hilang = [];
        $jumlahDipindai = 0;
        $berkas = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'), \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($berkas as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            preg_match_all(
                '/class="[^"]*material-symbols[^"]*"[^>]*>\s*([a-z0-9_]+)\s*</i',
                (string) file_get_contents($file->getPathname()),
                $cocok
            );

            foreach ($cocok[1] as $ikon) {
                $jumlahDipindai++;
                if (! isset($tersedia[$ikon])) {
                    $hilang[$ikon][] = str_replace(resource_path('views').DIRECTORY_SEPARATOR, '', $file->getPathname());
                }
            }
        }

        // Tanpa ini, pemindai yang berhenti cocok (markupnya berubah, regexnya
        // usang) akan lolos selamanya sambil tidak memeriksa apa pun.
        $this->assertGreaterThan(
            30,
            $jumlahDipindai,
            'pemindai ikon hampir tidak menemukan apa-apa -- polanya kemungkinan sudah tidak cocok dengan markup'
        );

        $pesan = '';
        foreach ($hilang as $ikon => $lokasi) {
            $pesan .= "\n  - {$ikon} dipakai di ".implode(', ', array_unique($lokasi));
        }

        $this->assertSame(
            [],
            $hilang,
            'Ikon berikut tidak ada di subset font dan akan tampil sebagai teks mentah.'.$pesan.
            "\n\nPerbaikan: tambahkan namanya ke USED di resources/fonts/material-symbols-subset.py, ".
            'lalu jalankan `python resources/fonts/material-symbols-subset.py` dan `npm run build`.'
        );
    }
}

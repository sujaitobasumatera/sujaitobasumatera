<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Membangkitkan varian "-400/-800" untuk gambar bawaan di public/images.
 *
 * Konvensi itu sudah dipakai imageSrcset(), tapi tidak ada yang membuatnya:
 * varian yang ada sekarang dibangkitkan sekali entah oleh apa, dan gambar yang
 * ditambahkan sesudahnya tidak kebagian. Akibatnya kartu paket menarik berkas
 * ukuran penuh -- gambar cadangan tour.webp sendiri 185 KB untuk kotak selebar
 * 380px, dan ia muncul di setiap paket yang belum punya foto.
 */
class GenerateImageVariants extends Command
{
    protected $signature = 'media:variants {--quality=80 : Mutu WebP} {--force : Timpa varian yang sudah ada}';

    protected $description = 'Bangkitkan varian -400/-800 untuk gambar webp di public/images';

    /** Lebar varian yang dikenali imageSrcset(). */
    private const LEBAR = [400, 800];

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('Ekstensi GD tanpa dukungan WebP. Tidak ada yang bisa dikerjakan.');

            return self::FAILURE;
        }

        $mutu = max(1, min(100, (int) $this->option('quality')));
        $timpa = (bool) $this->option('force');

        $akar = public_path('images');
        if (! is_dir($akar)) {
            $this->error("Direktori tidak ada: {$akar}");

            return self::FAILURE;
        }

        $berkas = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($akar, \FilesystemIterator::SKIP_DOTS));

        $dibuat = 0;
        $dilewati = 0;

        foreach ($berkas as $item) {
            /** @var \SplFileInfo $item */
            if (! $item->isFile() || strtolower($item->getExtension()) !== 'webp') {
                continue;
            }

            $jalur = $item->getPathname();

            // Varian tidak boleh jadi sumber varian berikutnya: hasilnya
            // foo-400-400.webp yang tidak pernah dirujuk siapa pun sambil
            // menggandakan isi folder tiap kali perintah ini dijalankan.
            if (preg_match('/-(400|800)\.webp$/i', $jalur)) {
                continue;
            }

            $ukuran = @getimagesize($jalur);
            if (! $ukuran) {
                $this->warn('Bukan gambar yang bisa dibaca: '.$jalur);

                continue;
            }

            [$lebarAsli, $tinggiAsli] = $ukuran;

            foreach (self::LEBAR as $lebar) {
                $tujuan = preg_replace('/\.webp$/i', "-{$lebar}.webp", $jalur);

                if (! $timpa && is_file($tujuan)) {
                    $dilewati++;

                    continue;
                }

                // Memperbesar gambar kecil hanya menghasilkan berkas yang LEBIH
                // besar dari aslinya untuk gambar yang sama -- browser akan
                // memilihnya di layar sempit dan justru mengunduh lebih banyak.
                if ($lebarAsli <= $lebar) {
                    $dilewati++;

                    continue;
                }

                $sumber = @imagecreatefromwebp($jalur);
                if (! $sumber) {
                    $this->warn('Gagal dibaca: '.$jalur);

                    break;
                }

                $tinggi = (int) round($tinggiAsli * ($lebar / $lebarAsli));
                $kanvas = imagecreatetruecolor($lebar, $tinggi);

                // WebP boleh punya alpha. Tanpa dua baris ini, bagian tembus
                // pandang berubah jadi hitam pekat.
                imagealphablending($kanvas, false);
                imagesavealpha($kanvas, true);

                imagecopyresampled($kanvas, $sumber, 0, 0, 0, 0, $lebar, $tinggi, $lebarAsli, $tinggiAsli);
                imagewebp($kanvas, $tujuan, $mutu);

                imagedestroy($kanvas);
                imagedestroy($sumber);

                $dibuat++;
                $this->line(sprintf(
                    '  %s  %s KB',
                    str_replace(public_path().DIRECTORY_SEPARATOR, '', $tujuan),
                    number_format(filesize($tujuan) / 1024, 1)
                ));
            }
        }

        $this->info("Selesai. {$dibuat} varian dibuat, {$dilewati} dilewati.");

        return self::SUCCESS;
    }
}

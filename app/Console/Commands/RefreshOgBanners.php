<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Package;
use App\Services\OgBannerService;
use Illuminate\Console\Command;

class RefreshOgBanners extends Command
{
    /**
     * Banner OG menyimpan harga ke dalam file .webp dan menggunakannya
     * selamanya (lazy cache). Perintah ini menghapus semua file cache
     * sehingga banner baru dibuat otomatis saat pertama kali diminta
     * — cocok dijalankan setelah migrasi harga atau perubahan massal.
     */
    protected $signature = 'og-banner:refresh
                            {--type= : Tipe yang di-refresh: package, blog, atau kosong = semua}
                            {--id=   : ID spesifik (wajib disertai --type)}';

    protected $description = 'Hapus cache banner OG agar dibuat ulang dengan harga/konten terbaru';

    public function handle(OgBannerService $service): int
    {
        $type = $this->option('type');
        $id   = $this->option('id');

        if ($id && ! $type) {
            $this->error('Opsi --id membutuhkan --type (package atau blog).');
            return self::FAILURE;
        }

        // Satu banner spesifik
        if ($id && $type) {
            $service->forget($type, (int) $id);
            $this->info("Banner {$type}_{$id}.webp dihapus dari cache.");
            return self::SUCCESS;
        }

        $count = 0;

        if (! $type || $type === 'package') {
            Package::all(['id', 'name'])->each(function ($pkg) use ($service, &$count) {
                $service->forget('package', $pkg->id);
                $this->line("  [package] #{$pkg->id} {$pkg->name}");
                $count++;
            });
        }

        if (! $type || $type === 'blog') {
            Blog::all(['id', 'title'])->each(function ($blog) use ($service, &$count) {
                $service->forget('blog', $blog->id);
                $this->line("  [blog] #{$blog->id} {$blog->title}");
                $count++;
            });
        }

        $this->newLine();
        $this->info("Selesai: {$count} banner dihapus dari cache.");
        $this->comment('Banner baru dibuat otomatis saat link dibagikan ke WhatsApp/media sosial.');

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class GenerateProjectDocsPdf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'docs:generate-pdf {--output= : Path tujuan berkas PDF}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate dokumen PDF Panduan Operasional & Laporan Audit Menyeluruh Sujai Lake Toba';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Memulai pembuatan dokumen PDF Panduan & Laporan Lengkap...');

        $pdf = Pdf::loadView('pdf.project-guide-and-report');
        $pdf->setPaper('a4', 'portrait');
        $pdf->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $defaultPath = base_path('PANDUAN_DAN_LAPORAN_LENGKAP_SUJAILAKETOBA.pdf');
        $targetPath = $this->option('output') ?: $defaultPath;

        $directory = dirname($targetPath);
        if (!File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $pdf->save($targetPath);

        // Salin juga ke public/docs/ agar bisa diakses langsung via web jika diinginkan
        $publicDir = public_path('docs');
        if (!File::isDirectory($publicDir)) {
            File::makeDirectory($publicDir, 0755, true);
        }
        $publicPath = public_path('docs/PANDUAN_DAN_LAPORAN_LENGKAP_SUJAILAKETOBA.pdf');
        File::copy($targetPath, $publicPath);

        $this->info("✓ PDF Laporan Lengkap berhasil dibuat di: {$targetPath}");
        $this->info("✓ Salinan publik siap di: {$publicPath}");

        // 2. Buat Dokumen PDF Khusus Panduan Desain di Canva
        $this->info('Memulai pembuatan dokumen PDF Panduan Canva...');
        $pdfCanva = Pdf::loadView('pdf.canva-guide');
        $pdfCanva->setPaper('a4', 'portrait');
        $pdfCanva->setOption([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'sans-serif',
        ]);

        $canvaPath = base_path('PANDUAN_DESAIN_CANVA_SUJAILAKETOBA.pdf');
        $pdfCanva->save($canvaPath);

        $publicCanvaPath = public_path('docs/PANDUAN_DESAIN_CANVA_SUJAILAKETOBA.pdf');
        File::copy($canvaPath, $publicCanvaPath);

        $this->info("✓ PDF Panduan Canva berhasil dibuat di: {$canvaPath}");
        $this->info("✓ Salinan publik siap di: {$publicCanvaPath}");

        return self::SUCCESS;
    }
}

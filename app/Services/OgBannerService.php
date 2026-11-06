<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

class OgBannerService
{
    /**
     * Generate dynamic, beautiful OpenGraph card banners for social shares with Caching.
     */
    /**
     * Buang banner yang sudah tersimpan supaya dibuat ulang saat diminta.
     *
     * Banner ini memuat judul, gambar, dan HARGA ke dalam satu file .webp,
     * lalu file itu dipakai selamanya. Begitu harga paket berubah, kartu yang
     * muncul di WhatsApp dan Facebook masih memajang harga lama — dan tidak
     * ada yang memberi tahu, karena halamannya sendiri sudah benar.
     */
    public function forget(string $type, int $id): void
    {
        Storage::disk('public')->delete("og-banners/{$type}_{$id}.webp");
    }

    public function getOrGenerateBanner(string $type, int $id)
    {
        $filename = "og-banners/{$type}_{$id}.webp";

        // If cached file exists, return the path
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->path($filename);
        }

        $title = '';
        $subtext = '';
        $imagePath = null;
        $priceText = null;

        if ($type === 'package') {
            $package = Package::find($id);
            if ($package) {
                $title = __($package->name);
                $subtext = __($package->locationTag ?? 'Wonderful Lake Toba');
                $priceText = $package->formatted_price;
                if (! empty($package->images) && is_array($package->images)) {
                    $imagePath = $package->images[0];
                }
            }
        } elseif ($type === 'blog') {
            $blog = Blog::find($id);
            if ($blog) {
                $title = __($blog->title);
                $subtext = __($blog->category ?? 'Blog & Info');
                $imagePath = $blog->image;
            }
        }

        // Create canvas
        $width = 1200;
        $height = 630;
        $canvas = imagecreatetruecolor($width, $height);

        $bgImage = null;
        if ($imagePath) {
            $clean = ltrim($imagePath, '/');
            if (str_starts_with($clean, 'storage/')) {
                $clean = substr($clean, 8);
            }
            $absPath = public_path('storage/'.$clean);
            if (file_exists($absPath)) {
                $bgImage = @imagecreatefromstring(file_get_contents($absPath));
            }
        }

        if ($bgImage) {
            $bgW = imagesx($bgImage);
            $bgH = imagesy($bgImage);

            $targetRatio = $width / $height;
            $bgRatio = $bgW / $bgH;

            if ($bgRatio >= $targetRatio) {
                $srcH = $bgH;
                $srcW = floor($bgH * $targetRatio);
                $srcX = floor(($bgW - $srcW) / 2);
                $srcY = 0;
            } else {
                $srcW = $bgW;
                $srcH = floor($bgW / $targetRatio);
                $srcX = 0;
                $srcY = floor(($bgH - $srcH) / 2);
            }

            imagecopyresampled($canvas, $bgImage, 0, 0, $srcX, $srcY, $width, $height, $srcW, $srcH);
            imagedestroy($bgImage);
        } else {
            $startColor = imagecolorallocate($canvas, 15, 23, 42);
            imagefilledrectangle($canvas, 0, 0, $width, $height, $startColor);
        }

        // Overlay with Slate-950 block
        $overlay = imagecolorallocatealpha($canvas, 2, 6, 23, 50);
        imagefilledrectangle($canvas, 0, 0, $width, $height, $overlay);

        // Elegant Card Bg
        $cardBg = imagecolorallocatealpha($canvas, 15, 23, 42, 15); // ~88% opacity slate-900
        imagefilledrectangle($canvas, 60, 60, 660, 570, $cardBg);

        // Subtle Card Border
        $borderColor = imagecolorallocatealpha($canvas, 255, 255, 255, 110);
        imagerectangle($canvas, 60, 60, 660, 570, $borderColor);

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $emerald = imagecolorallocate($canvas, 16, 185, 129);
        $slate400 = imagecolorallocate($canvas, 148, 163, 184);

        // Badge & Brand Label
        imagestring($canvas, 4, 90, 90, 'SUJAILAKETOBA.COM', $emerald);
        if ($subtext) {
            imagestring($canvas, 3, 90, 125, strtoupper($subtext), $slate400);
        }

        // Title wrapped
        $words = explode(' ', $title);
        $lines = [];
        $currentLine = '';
        foreach ($words as $word) {
            if (strlen($currentLine.' '.$word) < 40) {
                $currentLine = empty($currentLine) ? $word : $currentLine.' '.$word;
            } else {
                $lines[] = $currentLine;
                $currentLine = $word;
            }
        }
        if (! empty($currentLine)) {
            $lines[] = $currentLine;
        }

        $startY = 180;
        foreach ($lines as $index => $line) {
            if ($index > 4) {
                break;
            }
            $shadowColor = imagecolorallocatealpha($canvas, 0, 0, 0, 80);
            imagestring($canvas, 5, 91, $startY + ($index * 32) + 1, $line, $shadowColor);
            imagestring($canvas, 5, 90, $startY + ($index * 32), $line, $white);
        }

        // Price Tag Block
        if ($priceText) {
            imagestring($canvas, 3, 90, 440, __('Mulai Dari:'), $slate400);

            $priceBg = imagecolorallocatealpha($canvas, 16, 185, 129, 20);
            imagefilledrectangle($canvas, 90, 470, 420, 530, $priceBg);
            imagerectangle($canvas, 90, 470, 420, 530, $emerald);

            imagestring($canvas, 5, 110, 490, $priceText, $white);
        }

        // Output image
        ob_start();
        imagewebp($canvas, null, 90);
        $output = ob_get_clean();

        imagedestroy($canvas);

        // Save to cache
        Storage::disk('public')->put($filename, $output);

        return Storage::disk('public')->path($filename);
    }
}

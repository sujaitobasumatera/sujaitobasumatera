<?php
// Script untuk menghapus cache tanpa memanggil Laravel
// Pastikan ini dijalankan dari folder public atau sejajarnya
$cacheDir = __DIR__ . '/../bootstrap/cache';

if (!is_dir($cacheDir)) {
    // Coba path alternatif jika c.php diletakkan di root
    $cacheDir = __DIR__ . '/bootstrap/cache';
}

if (is_dir($cacheDir)) {
    $files = glob($cacheDir . '/*.php');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "<h1>Sukses!</h1>";
    echo "<p>$count file cache berhasil dihapus.</p>";
    echo "<p>Sekarang silakan buka halaman web Anda dan Refresh!</p>";
} else {
    echo "<h1>Error</h1>";
    echo "<p>Folder bootstrap/cache tidak ditemukan. Silakan hapus file cache secara manual dari File Manager.</p>";
}
?>

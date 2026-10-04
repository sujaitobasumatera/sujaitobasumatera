<?php
// Daftar folder yang terjebak dan tujuan aslinya
$dirs = [
    __DIR__ . '/public/storage/images' => __DIR__ . '/public/images',
    __DIR__ . '/public/storage/assets' => __DIR__ . '/public/assets',
    __DIR__ . '/storage/app/public/images' => __DIR__ . '/public/images',
    __DIR__ . '/storage/app/public/assets' => __DIR__ . '/public/assets',
];

function pindahkan_gambar($src, $dst) {
    if (!is_dir($src)) return false;
    @mkdir($dst, 0755, true);
    $dir = @opendir($src);
    if(!$dir) return false;
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) pindahkan_gambar($src . '/' . $file, $dst . '/' . $file);
            else @copy($src . '/' . $file, $dst . '/' . $file);
        }
    }
    closedir($dir);
    return true;
}

foreach ($dirs as $src => $dst) {
    pindahkan_gambar($src, $dst);
}

echo "<h1>Pemulihan Total Selesai! 📸</h1>";
echo "<p>Semua gambar telah dikembalikan ke folder utama web (public/images & public/assets).</p>";
echo "<br><b>Silakan kembali ke halaman web dan Refresh (F5)!</b>";
?>

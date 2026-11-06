<?php
$badFile = __DIR__ . '/public/storage/.htaccess';
if (file_exists($badFile)) {
    unlink($badFile);
    echo "<h1>Selesai! 🚀</h1><p>Penghalang gambar sudah dihancurkan.</p>";
} else {
    echo "<h1>Aman!</h1><p>File penghalang sudah tidak ada.</p>";
}
echo "<br><b>Silakan Refresh (F5) web Anda sekarang! Gambar pasti langsung muncul.</b>";
?>

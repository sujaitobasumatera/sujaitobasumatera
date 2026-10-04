<?php
$envPath = file_exists(__DIR__ . '/../.env') ? __DIR__ . '/../.env' : __DIR__ . '/.env';

if (!file_exists($envPath)) {
    die("File .env tidak ditemukan!");
}

$env = file_get_contents($envPath);
preg_match('/DB_DATABASE=(.*)/', $env, $db);
preg_match('/DB_USERNAME=(.*)/', $env, $user);
preg_match('/DB_PASSWORD=(.*)/', $env, $pass);
preg_match('/DB_HOST=(.*)/', $env, $host);

try {
    $pdo = new PDO(
        "mysql:host=".trim($host[1] ?? '127.0.0.1').";dbname=".trim($db[1]), 
        trim($user[1]), 
        trim($pass[1] ?? '')
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $queries = [
        "UPDATE cms_slides SET image = REPLACE(image, 'sujailaketoba.com', 'sujaitobasumatera.com')",
        "UPDATE packages SET image = REPLACE(image, 'sujailaketoba.com', 'sujaitobasumatera.com')",
        "UPDATE packages SET media = REPLACE(media, 'sujailaketoba.com', 'sujaitobasumatera.com')",
        "UPDATE blogs SET cover = REPLACE(cover, 'sujailaketoba.com', 'sujaitobasumatera.com')",
        "UPDATE media SET path = REPLACE(path, 'sujailaketoba.com', 'sujaitobasumatera.com')",
        "UPDATE settings SET value = REPLACE(value, 'sujailaketoba.com', 'sujaitobasumatera.com')"
    ];

    echo "<h1>Proses Update Database</h1><ul>";
    
    foreach ($queries as $sql) {
        try {
            $pdo->query($sql);
            echo "<li style='color:green;'>Sukses mengeksekusi di tabel terkait!</li>";
        } catch (Exception $e) {
            echo "<li style='color:orange;'><i>Dilewati (Tabel tidak ada/berbeda)</i></li>";
        }
    }
    
    echo "</ul><h2>Selesai! Semua sisa domain lama telah dibersihkan.</h2>";
} catch (Exception $e) {
    echo "Gagal konek DB: " . $e->getMessage();
}
?>

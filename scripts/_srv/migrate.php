<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->call('migrate', ['--force' => true]);

echo "<h1>Status Database Update:</h1>";
echo "<pre>" . $kernel->output() . "</pre>";
echo "<br><br><b>Jika sukses, silakan hapus file ini dan refresh halaman web Anda!</b>";
?>

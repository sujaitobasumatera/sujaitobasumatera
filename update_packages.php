<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Package;

$packages = Package::where('name', 'LIKE', '%7D6N%')->orWhere('name', 'LIKE', '%7d6n%')->get();
foreach ($packages as $p) {
    echo "Found: ID " . $p->id . " - " . $p->name . "\n";
    $p->name = str_ireplace('7D6N', '6D5N', $p->name);
    // Also adjust duration in days and nights if possible
    if ($p->duration_days == 7) $p->duration_days = 6;
    if ($p->duration_nights == 6) $p->duration_nights = 5;
    
    // Also adjust the slug if it has 7d6n
    $p->slug = str_ireplace('7d6n', '6d5n', $p->slug);
    $p->save();
    echo "Updated to: " . $p->name . "\n";
}

if ($packages->count() == 0) {
    echo "No packages found with 7D6N in name.\n";
}

unlink(__FILE__);

<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Package;

$p = Package::find(7);
echo json_encode($p->itinerary, JSON_PRETTY_PRINT);
unlink(__FILE__);

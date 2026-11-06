<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Cache;

Cache::forget('contact_whatsapp_digits');
Cache::forget('contact_whatsapp_digits_2');

echo "Cache cleared.";
unlink(__FILE__);

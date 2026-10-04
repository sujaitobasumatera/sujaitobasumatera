<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Setting;

$s = Setting::where('key', 'general')->first();
$v = $s->value ?? [];
if (is_string($v)) $v = json_decode($v, true);

// Update address
$v['office_address'] = 'Jl. Trimurti 109 Berastagi Kabupaten Karo';

// Keep only the two numbers if they exist
$v['contact_wa_1'] = '6282277848855';
$v['contact_wa_2'] = '6281397606622';
$v['contact_whatsapp'] = '6282277848855';
$v['contact_whatsapp_2'] = '6281397606622';
$v['site_name'] = 'Sujai Toba Sumatera';

$s->value = $v;
$s->save();

$cms = Setting::where('key', 'cms_tour')->first();
if ($cms) {
    $cv = $cms->value ?? [];
    if (is_string($cv)) $cv = json_decode($cv, true);
    
    $cv['contact_whatsapp'] = '6282277848855';
    $cv['specialist_wa'] = '6281397606622';
    
    $cms->value = $cv;
    $cms->save();
}

echo "Database updated.\n";
unlink(__FILE__);

<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Package;

$p = Package::find(7);
$itinerary = $p->itinerary;

// Combine day 6 and 7
$new_itinerary = [];
foreach ($itinerary as $day) {
    if ($day['day'] < 6) {
        $new_itinerary[] = $day;
    } elseif ($day['day'] == 6) {
        $day['title'] = 'Bukit Lawang - Medan & Departure';
        $day['activities'] = [
            'Morning trek',
            'Perjalanan ke Medan',
            'Belanja oleh-oleh',
            'Transfer ke bandara'
        ];
        $new_itinerary[] = $day;
    }
}
$p->itinerary = $new_itinerary;
$p->save();

echo "Itinerary updated.\n";
unlink(__FILE__);

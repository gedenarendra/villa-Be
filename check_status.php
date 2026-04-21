<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Villa;

$villas = Villa::all();
foreach ($villas as $v) {
    echo "Villa: {$v->name} (ID: {$v->id})\n";
    echo "Status in DB: {$v->status}\n";
    echo "Granular Status: " . $v->getAvailabilityStatus() . "\n";
    echo "Bookings:\n";
    foreach ($v->bookings as $b) {
        echo " - {$b->start_date} to {$b->end_date} ({$b->status})\n";
    }
    echo "-------------------\n";
}

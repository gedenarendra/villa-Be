<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "\n===== VILLAS =====\n";
$villas = \DB::table('villas')->orderBy('id')->get();
foreach ($villas as $v) {
    echo "Villa #{$v->id}: {$v->name}\n";
    echo "  status={$v->status} | location={$v->location} | price_per_year={$v->price_per_year}\n\n";
}

echo "\n===== BOOKINGS =====\n";
$bookings = \DB::table('bookings')->orderBy('id')->get();
foreach ($bookings as $b) {
    echo "Booking #{$b->id}: villa_id={$b->villa_id}\n";
    echo "  customer={$b->customer_name} | {$b->start_date} to {$b->end_date} | status={$b->status}\n\n";
}

echo "\n===== VILLA_IMAGES =====\n";
$images = \DB::table('villa_images')->orderBy('id')->get();
foreach ($images as $img) {
    echo "Image #{$img->id}: villa_id={$img->villa_id} | is_primary={$img->is_primary} | url={$img->image_url}\n";
}

<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$latest = App\Models\Product::latest()->first();

if ($latest) {
    echo "ID: {$latest->id}\n";
    echo "Image: " . ($latest->image ?? 'NULL') . "\n";
    echo "ImagePublicId: " . ($latest->image_public_id ?? 'NULL') . "\n";
} else {
    echo "No products\n";
}

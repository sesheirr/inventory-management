<?php

use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$latest = Product::latest()->first();

if ($latest) {
    echo "ID: {$latest->id}\n";
    echo 'Image: '.($latest->image ?? 'NULL')."\n";
    echo 'ImagePublicId: '.($latest->image_public_id ?? 'NULL')."\n";
} else {
    echo "No products\n";
}

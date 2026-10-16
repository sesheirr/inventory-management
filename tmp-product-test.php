<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Console\Kernel;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$c = Category::first();
if (! $c) {
    echo "no-category\n";
    exit;
}

$p = new Product;
$p->name = 'Test Barang';
$p->category = $c->name;
$p->category_id = $c->id;
$p->stock = 1;
$p->status = 'active';
$p->kode_barang = 'TEST123';
$p->save();

echo 'saved-'.$p->id;

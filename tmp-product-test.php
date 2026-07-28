<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = \App\Models\Category::first();
if (!$c) {
    echo "no-category\n";
    exit;
}

$p = new \App\Models\Product();
$p->name = 'Test Barang';
$p->category = $c->name;
$p->category_id = $c->id;
$p->stock = 1;
$p->status = 'active';
$p->kode_barang = 'TEST123';
$p->save();

echo 'saved-' . $p->id;

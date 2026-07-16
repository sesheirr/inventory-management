<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::delete('products/destroy-selected',[ProductController::class, 'destroySelected'])->name('products.destroySelected');

Route::resource('products', ProductController::class);

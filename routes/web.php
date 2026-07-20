<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');

Route::delete('products/destroy-selected',[ProductController::class, 'destroySelected'])->name('products.destroySelected');

Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);
Route::resource('rooms', RoomController::class);

<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::delete('products/destroy-selected',[ProductController::class, 'destroySelected'])->name('products.destroySelected');

Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class);
Route::resource('rooms', RoomController::class);
Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');

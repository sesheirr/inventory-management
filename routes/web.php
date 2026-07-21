<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTH ROUTES (Rute Login & Logout)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// ==========================================
// PROTECTED ROUTES (Hanya Bisa Diakses Jika Sudah Login)
// ==========================================
Route::middleware('auth')->group(function () {
    
    // Halaman Utama otomatis dialihkan ke Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Rute Dashboard & Laporan
    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');

    // Rute Manajemen Produk (Fitur Hapus Massal, Ekspor Excel & CRUD)
    Route::delete('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected');
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export');
    Route::resource('products', ProductController::class);
    
    // Rute Manajemen Kategori & Ruangan
    Route::resource('categories', CategoryController::class);
    Route::resource('rooms', RoomController::class);
});
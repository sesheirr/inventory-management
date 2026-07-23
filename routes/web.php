<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTH ROUTES (Untuk Orang Yang BELUM Login / Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Route Register DIPINDAHKAN KE SINI agar bisa diakses tanpa login:
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// ==========================================
// PROTECTED ROUTES (Hanya Bisa Diakses Jika SUDAH Login)
// ==========================================
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Halaman Utama otomatis dialihkan ke Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Rute Dashboard & Settings
    Route::get('/dashboard', [ReportController::class, 'index'])->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    // Rute Laporan
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Rute Manajemen Produk (Fitur Hapus Massal, Ekspor Excel & CRUD)
    Route::delete('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected');
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export');
    Route::resource('products', ProductController::class);
    
    // Rute Manajemen Kategori & Ruangan
    Route::resource('categories', CategoryController::class);
    Route::resource('rooms', RoomController::class);
});
<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTH ROUTES (Untuk Orang Yang BELUM Login / Guest)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Route Register:
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // 1. Tampilkan Form Reset Password Langsung
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    // 2. Proses Ubah Password Instant
    Route::post('/forgot-password', function (Request $request) {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email ini tidak terdaftar di sistem.',
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('login')->with('status', 'Password berhasil diperbarui! Silakan login dengan password baru Anda.');
        }

        return back()->withErrors(['email' => 'Gagal memperbarui password.']);
    })->name('password.direct_reset');
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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/settings', 'settings')->name('settings');

    // Rute Laporan
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Rute Manajemen Produk (CRUD tanpa delete untuk semua user)
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export');
    Route::resource('products', ProductController::class)->except(['destroy']);

    // Rute Manajemen Kategori dan Ruangan untuk semua user (read-only)
    Route::resource('categories', CategoryController::class)->only(['index', 'show']);
    Route::resource('rooms', RoomController::class)->only(['index', 'show']);

    // Rute Manajemen Mutasi untuk semua user kecuali hapus
    Route::resource('mutations', MutationController::class)->except(['destroy']);
});

Route::middleware(['auth', 'admin'])->group(function () {
    Route::delete('/dashboard/clear-history', [DashboardController::class, 'clearHistory'])->name('dashboard.clear-history');

    Route::post('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected');
    Route::delete('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected.delete');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::resource('categories', CategoryController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    Route::resource('rooms', RoomController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::delete('mutations/{mutation}', [MutationController::class, 'destroy'])->name('mutations.destroy');

    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    
    // User management (admin only)
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
});
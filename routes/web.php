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
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

// ==========================================
// AUTH ROUTES (Guest / Belum Login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

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
// PROTECTED ROUTES (Sudah Login / Authenticated)
// ==========================================
Route::middleware('auth')->group(function () {
    
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // Dashboard & Settings
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', function () {
        return view('settings', ['user' => auth()->user()]);
    })->name('settings');

    // Profil & Laporan
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export-excel', [ReportController::class, 'exportExcel'])->name('reports.exportExcel');

    // Barcode & Products (Read / Standar User)
    Route::get('/scan-barcode', [ProductController::class, 'scanBarcode'])->name('products.scan');
    Route::post('/scan-barcode/cari', [ProductController::class, 'scanBarcodeSearch'])->name('products.scan.search');
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export');
    Route::resource('products', ProductController::class)->except(['destroy']);
    Route::get('/products/{product}/barcode/print', [ProductController::class, 'printBarcode'])->name('products.barcode.print');

    // Kategori & Ruangan (Akses Baca / Umum untuk semua user yang login)
    Route::resource('categories', CategoryController::class)->only(['index', 'show', 'store']);
    Route::resource('rooms', RoomController::class)->only(['index', 'show']);

    // Mutasi Barang (Kecuali Hapus & Approval khusus admin)
    Route::resource('mutations', MutationController::class)->except(['destroy']);
    Route::get('/mutations/approvals', [MutationController::class, 'approvals'])->name('mutations.approvals');

    // Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// ==========================================
// ADMIN ROUTES (Hanya Admin & Super Admin)
// ==========================================
Route::middleware(['auth', 'admin'])->group(function () {
    // Produk (Hapus)
    Route::post('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected');
    Route::delete('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected.delete');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Kategori & Ruangan (Manajemen Penuh: Tambah, Ubah, Hapus)
    Route::resource('categories', CategoryController::class)->except(['index', 'show', 'store']);
    Route::resource('rooms', RoomController::class)->except(['index', 'show']);

    // Mutasi (Hapus & Approval)
    Route::delete('mutations/{mutation}', [MutationController::class, 'destroy'])->name('mutations.destroy');
    Route::patch('mutations/{mutation}/approve', [MutationController::class, 'approve'])->name('mutations.approve');
    Route::patch('mutations/{mutation}/reject', [MutationController::class, 'reject'])->name('mutations.reject');

    // Log Aktivitas
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
});

// ==========================================
// SUPERADMIN ROUTES (Hanya Super Admin)
// ==========================================
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::delete('/dashboard/clear-history', [DashboardController::class, 'clearHistory'])->name('dashboard.clear-history');

    // Manajemen User
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});
<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveStateController;
use App\Http\Controllers\MediaUploadController;
use App\Http\Controllers\MutationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ObservabilityController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\WebhookController;
use App\Models\Category;
use App\Models\Mutation;
use App\Models\Product;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ==========================================
// PUBLIC ROUTES, LANDING PAGE & DEEP LINK SHARE
// ==========================================
Route::get('/', function (Request $request) {
    $metrics = [
        'products' => 20,
        'total_stock' => 142,
        'rooms' => 8,
        'categories' => 6,
        'mutations' => 15,
    ];

    try {
        $metrics = [
            'products' => Product::count(),
            'total_stock' => (int) Product::sum('stock'),
            'rooms' => Room::count(),
            'categories' => Category::count(),
            'mutations' => Mutation::count(),
        ];
    } catch (Throwable) {
        // Fallback to default metrics if DB is not yet migrated
    }

    if ($request->header('X-Inertia') || $request->boolean('v2')) {
        return Inertia::render('Welcome', [
            'metrics' => $metrics,
            'authenticated' => auth()->check(),
        ]);
    }

    return view('welcome', ['metrics' => $metrics]);
})->name('landing');

Route::get('/maintenance-preview', function () {
    return response()->view('errors.503', [], 503);
})->name('maintenance.preview');

Route::get('/auth/session-token-sync', [AuthController::class, 'refreshCaptcha'])->name('auth.token.sync');
Route::get('/captcha/image', [AuthController::class, 'getCaptchaImage'])->name('captcha.image');
Route::get('/security/code-image', [AuthController::class, 'getCaptchaImage'])->name('security.image');
Route::get('/api/captcha-regenerate', [AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');
Route::get('/api/security-code/regenerate', [AuthController::class, 'refreshCaptcha'])->name('security.refresh');

// Short Deep Link & Rich OpenGraph Share Endpoint
Route::get('/s/{code}', [ShareController::class, 'resolve'])
    ->where('code', '[A-Za-z0-9\-]+')
    ->name('share.short');

// ==========================================
// AUTH ROUTES (Guest / Belum Login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/login/quick', [AuthController::class, 'quickLogin'])->name('login.quick');
    Route::post('/login/forget-profile', [AuthController::class, 'forgetSavedProfile'])->name('login.forget-profile');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', function (Request $request) {
        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Auth/ForgotPassword', [
                'csrfToken' => csrf_token(),
            ]);
        }

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

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            return redirect()->route('login')->with('status', 'Password berhasil diperbarui! Silakan login dengan password baru Anda.');
        }

        return back()->withErrors(['email' => 'Gagal memperbarui password.']);
    })->name('password.direct_reset');
});

// ==========================================
// ADMIN ROUTES (Hanya Admin & Super Admin)
// Note: Registered before generic param routes to avoid /create matching /{id}
// ==========================================
Route::middleware(['auth', 'admin'])->group(function () {
    // Kategori & Ruangan (Manajemen Penuh: Tambah, Ubah, Hapus)
    Route::resource('categories', CategoryController::class)->only(['edit', 'update', 'destroy']);
    Route::resource('rooms', RoomController::class)->except(['index', 'show']);

    // Mutasi (Hapus & Approval)
    Route::delete('mutations/{mutation}', [MutationController::class, 'destroy'])->name('mutations.destroy');
    Route::patch('mutations/{mutation}/approve', [MutationController::class, 'approve'])->name('mutations.approve');
    Route::patch('mutations/{mutation}/reject', [MutationController::class, 'reject'])->name('mutations.reject');

    // Log Aktivitas, Observability / Telescope, & Webhook Studio (Admin & Super Admin Only)
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    Route::get('/observability', [ObservabilityController::class, 'index'])->name('observability.index');
    Route::post('/observability/clear', [ObservabilityController::class, 'clear'])->name('observability.clear');

    Route::get('/webhooks', [WebhookController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::patch('/webhooks/{webhook}/toggle', [WebhookController::class, 'toggle'])->name('webhooks.toggle');
    Route::delete('/webhooks/{webhook}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{webhook}/ping', [WebhookController::class, 'ping'])->name('webhooks.ping');
});

// ==========================================
// SUPERADMIN ROUTES (Hanya Super Admin)
// ==========================================
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::delete('/dashboard/clear-history', [DashboardController::class, 'clearHistory'])->name('dashboard.clear-history');

    // Manajemen User
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::put('/users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
});

// ==========================================
// PROTECTED ROUTES (Sudah Login / Authenticated)
// ==========================================
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Async FilePond Multi-Media Temporary Uploader
    Route::post('/uploads/tmp', [MediaUploadController::class, 'storeTemporary'])->name('uploads.tmp.store');
    Route::delete('/uploads/tmp', [MediaUploadController::class, 'destroyTemporary'])->name('uploads.tmp.destroy');

    // Semi-Realtime Live State Sync API
    Route::get('/api/live-state', [LiveStateController::class, 'index'])->name('live.state');

    // Dashboard & Settings
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', function (Request $request) {
        if ($request->header('X-Inertia') || $request->boolean('v2')) {
            return Inertia::render('Settings/Index');
        }

        return view('settings', ['user' => auth()->user()]);
    })->name('settings');

    // Profil
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::match(['post', 'put'], '/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Barcode & Products (Read / Standar User)
    Route::get('/scan-barcode', [ProductController::class, 'scanBarcode'])->name('products.scan');
    Route::post('/scan-barcode/cari', [ProductController::class, 'scanBarcodeSearch'])->name('products.scan.search');
    Route::get('products/export-excel', [ProductController::class, 'exportExcel'])->name('products.export');
    Route::get('products/export-pdf', [ProductController::class, 'exportPdf'])->name('products.export.pdf');
    Route::post('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected');
    Route::delete('products/destroy-selected', [ProductController::class, 'destroySelected'])->name('products.destroySelected.delete');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::resource('products', ProductController::class)->except(['destroy']);
    Route::get('/products/{product}/barcode/print', [ProductController::class, 'printBarcode'])->name('products.barcode.print');

    // Kategori & Ruangan (Akses Baca / Umum untuk semua user yang login)
    Route::resource('categories', CategoryController::class)->only(['index', 'show', 'store']);
    Route::resource('rooms', RoomController::class)->only(['index', 'show']);

    // Mutasi Barang (Kecuali Hapus)
    Route::get('mutations/export-pdf', [MutationController::class, 'exportPdf'])->name('mutations.export.pdf');
    Route::resource('mutations', MutationController::class)->except(['destroy']);

    // Route Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{id}/go', [NotificationController::class, 'go'])->name('notifications.go');
    Route::post('/notifications/check-reminders', [NotificationController::class, 'checkReminders'])->name('notifications.checkReminders');
    Route::match(['post', 'patch'], '/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

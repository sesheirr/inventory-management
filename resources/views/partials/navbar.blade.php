@php
    // Determine page title and breadcrumb based on current route
    $pageTitle = 'Dashboard';
    $breadcrumb = 'Inventory / Dashboard';
    $showBackButton = false;

    if (Route::is('dashboard')) {
        $pageTitle = 'Dashboard';
        $breadcrumb = 'Inventory / Dashboard';
    } elseif (Route::is('reports.index') || Route::is('reports.*')) {
        $pageTitle = 'Laporan';
        $breadcrumb = 'Inventory / Laporan';
        $showBackButton = true;
    } elseif (Route::is('products.index')) {
        $pageTitle = 'Barang';
        $breadcrumb = 'Inventory / Barang';
        $showBackButton = false;
    } elseif (Route::is('products.*')) {
        $pageTitle = 'Barang';
        $breadcrumb = 'Inventory / Barang';
        $showBackButton = true;
    } elseif (Route::is('categories.index')) {
        $pageTitle = 'Kategori';
        $breadcrumb = 'Inventory / Kategori';
        $showBackButton = false;
    } elseif (Route::is('categories.*')) {
        $pageTitle = 'Kategori';
        $breadcrumb = 'Inventory / Kategori';
        $showBackButton = true;
    } elseif (Route::is('rooms.index')) {
        $pageTitle = 'Ruangan';
        $breadcrumb = 'Inventory / Ruangan';
        $showBackButton = false;
    } elseif (Route::is('rooms.*')) {
        $pageTitle = 'Ruangan';
        $breadcrumb = 'Inventory / Ruangan';
        $showBackButton = true;
    } elseif (Route::is('mutations.index') || Route::is('mutations.*')) {
        $pageTitle = 'Mutasi Barang';
        $breadcrumb = 'Inventory / Mutasi Barang';
        $showBackButton = true;
    } elseif (Route::is('profile')) {
        $pageTitle = 'Profile';
        $breadcrumb = 'Inventory / Profile';
        $showBackButton = true;
    } elseif (Route::is('settings')) {
        $pageTitle = 'Settings';
        $breadcrumb = 'Inventory / Settings';
        $showBackButton = true;
    }

    $backUrl = url()->previous();
    if (!$backUrl || $backUrl === url()->current()) {
        $backUrl = route('dashboard');
    }
@endphp

<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        @if($showBackButton)
            <a href="{{ $backUrl }}" class="btn-back-circle" aria-label="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif

        <div>
            @if($breadcrumb)
                <p class="text-muted mb-0 small">{{ $breadcrumb }}</p>
            @endif
            @if($pageTitle)
                <h3 class="mb-0 fw-bold">{{ $pageTitle }}</h3>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button id="darkModeToggle" class="icon-btn" type="button">
            <i id="themeIcon" class="bi bi-moon"></i>
        </button>

        @php $currentUser = auth()->user(); @endphp

        <!-- PROFILE BUTTON — langsung ke halaman profile, tanpa dropdown -->
        <a href="{{ route('profile') }}" class="profile-pill profile-pill-link" aria-label="Buka halaman profil">
            <div class="avatar">
                @if(!empty($currentUser?->avatar))
                    <img src="{{ $currentUser->avatar }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                @else
                    {{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}
                @endif
            </div>
            <div>
                <div class="fw-semibold">{{ $currentUser->name ?? 'User' }}</div>
                <small class="profile-role-label">
                    @if(($currentUser->role ?? '') === 'admin' || ($currentUser->role ?? '') === 'Administrator')
                        Administrator
                    @else
                        User
                    @endif
                </small>
            </div>
        </a>
    </div>
</header>
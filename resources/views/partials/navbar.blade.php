@php
    // Determine page title and breadcrumb based on current route
    $pageTitle = 'Dashboard';
    $breadcrumb = 'Inventaris / Dashboard';
    $showBackButton = false;

    if (Route::is('dashboard')) {
        $pageTitle = 'Dashboard';
        $breadcrumb = 'Inventaris / Dashboard';
    } elseif (Route::is('reports.index') || Route::is('reports.*')) {
        $pageTitle = 'Laporan';
        $breadcrumb = 'Inventaris / Laporan';
        $showBackButton = true;
    } elseif (Route::is('products.index')) {
        $pageTitle = 'Barang';
        $breadcrumb = 'Inventaris / Barang';
        $showBackButton = false;
    } elseif (Route::is('products.*')) {
        $pageTitle = 'Barang';
        $breadcrumb = 'Inventaris / Barang';
        $showBackButton = true;
    } elseif (Route::is('categories.index')) {
        $pageTitle = 'Kategori';
        $breadcrumb = 'Inventaris / Kategori';
        $showBackButton = false;
    } elseif (Route::is('categories.*')) {
        $pageTitle = 'Kategori';
        $breadcrumb = 'Inventaris / Kategori';
        $showBackButton = true;
    } elseif (Route::is('rooms.index')) {
        $pageTitle = 'Ruangan';
        $breadcrumb = 'Inventaris / Ruangan';
        $showBackButton = false;
    } elseif (Route::is('rooms.*')) {
        $pageTitle = 'Ruangan';
        $breadcrumb = 'Inventaris / Ruangan';
        $showBackButton = true;
    } elseif (Route::is('mutations.index') || Route::is('mutations.*')) {
        $pageTitle = 'Mutasi Barang';
        $breadcrumb = 'Inventaris / Mutasi Barang';
        $showBackButton = true;
    } elseif (Route::is('users.index') || Route::is('users.*')) {
        $pageTitle = 'Manajemen User';
        $breadcrumb = 'Inventaris / Manajemen User';
        $showBackButton = true;
    } elseif (Route::is('activity-logs.index') || Route::is('activity-logs.*')) {
        $pageTitle = 'Log Aktivitas';
        $breadcrumb = 'Inventaris / Log Aktivitas';
        $showBackButton = true;
    } elseif (Route::is('notifications.index')) {
        $pageTitle = 'Notifikasi';
        $breadcrumb = 'Inventaris / Notifikasi';
        $showBackButton = true;
    } elseif (Route::is('profile')) {
        $pageTitle = 'Profile';
        $breadcrumb = 'Inventaris / Profile';
        $showBackButton = true;
    } elseif (Route::is('settings')) {
        $pageTitle = 'Pengaturan';
        $breadcrumb = 'Inventaris / Pengaturan';
        $showBackButton = true;
    }

    $backUrl = url()->previous();
    if (!$backUrl || $backUrl === url()->current()) {
        $backUrl = route('dashboard');
    }

    $currentUser = auth()->user();
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
        {{-- HAMBURGER (mobile only) --}}
        <button class="btn btn-outline-secondary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="fa fa-bars"></i>
        </button>

        {{-- DARK MODE TOGGLE (desktop only) --}}
        <button id="darkModeToggle" class="icon-btn d-none d-lg-inline-flex" type="button">
            <i id="themeIcon" class="bi bi-moon"></i>
        </button>

        {{-- PROFILE BUTTON --}}
        <a href="{{ route('profile') }}" class="profile-pill profile-pill-link" aria-label="Buka halaman profil">
            <div class="avatar" style="width: 40px; height: 40px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                @if(!empty($currentUser?->avatar))
                    <img src="{{ $currentUser->avatar }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                @else
                    <span class="fw-bold">{{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}</span>
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
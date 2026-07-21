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
    } elseif (Route::is('settings')) {
        $pageTitle = '';
        $breadcrumb = null;
        $showBackButton = false;
    }
@endphp

<header class="topbar">
    <div class="d-flex align-items-center gap-3">

        @if($showBackButton)
            <button class="btn btn-light rounded-circle p-2 border-0 shadow-sm" type="button" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i>
            </button>
        @endif

        <div>
            @if($breadcrumb)
                <p class="text-muted mb-0 small">{{ $breadcrumb }}</p>
            @endif

            @if($pageTitle)
                <h3 class="mb-0 fw-semibold">{{ $pageTitle }}</h3>
            @endif
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button id="darkModeToggle" class="icon-btn" type="button">
            <i id="themeIcon" class="bi bi-moon"></i>
        </button>

        <!-- PROFILE DROPDOWN -->
        <div class="dropdown">
            <div class="profile-pill dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                <div class="avatar">{{ substr(auth()->user()->name ?? 'SF', 0, 2) }}</div>
                <div>
                    <div class="fw-semibold">{{ auth()->user()->name ?? 'User' }}</div>
                    <small class="text-muted">
                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'Administrator')
                            Administrator
                        @else
                            User
                        @endif
                    </small>
                </div>
            </div>

            <ul class="dropdown-menu dropdown-menu-end rounded-3 shadow-sm border-0 profile-dropdown-menu">
                <li class="dropdown-header px-3 py-2">
                    <div class="fw-semibold">{{ auth()->user()->name ?? 'User' }}</div>
                    <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <a class="dropdown-item" href="#">
                        <i class="bi bi-person me-2"></i> Profile
                    </a>
                </li>

                <li>
                    <a class="dropdown-item" href="{{ route('settings') }}">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </li>

                <li><hr class="dropdown-divider"></li>

                <li>
                    <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" style="background:none;border:none;width:100%;text-align:left;">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </button>
                </li>
            </ul>
        </div>
    </div>
</header>
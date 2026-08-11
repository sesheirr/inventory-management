<div class="offcanvas-lg offcanvas-start sidebar" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    {{-- Desktop brand (visible on lg and up) --}}
    <div class="brand d-none d-lg-flex align-items-center gap-2 mb-3">
        <div class="brand-logo-container">
            <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut">
        </div>
        <div>
            <div class="fw-bold">Inventaris</div>
            <div class="small" style="color: rgba(255, 255, 255, 0.65);">Diskominfo Garut</div>
        </div>
    </div>
    <div class="offcanvas-header d-lg-none">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-logo-container">
                <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo">
            </div>
            <div>
                <div class="fw-bold" id="sidebarOffcanvasLabel">Inventaris</div>
                <div class="small sidebar-subtitle">Diskominfo Garut</div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebarOffcanvas" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column p-0">
        <nav class="nav flex-column flex-grow-1 p-3">
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
            <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box2"></i><span>Barang</span></a>
            <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Kategori</span></a>
            <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"><i class="bi bi-building"></i><span>Ruangan</span></a>
            <a href="{{ route('mutations.index') }}" class="nav-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i><span>Mutasi Barang</span></a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.index') ? 'active' : '' }}"><i class="fa fa-clock-rotate-left"></i><span>Log Aktivitas</span></a>

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fa fa-users-gear"></i><span>Manajemen User</span></a>
                @endif
            @endif
        </nav>

        <div class="nav-links nav-links-bottom p-3 border-top">
            <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}">
                <i class="bi bi-gear"></i><span>Pengaturan</span>
            </a>

            <button type="button" class="nav-link nav-link-logout text-danger" style="color: #ff4d4d !important;" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i class="bi bi-box-arrow-right"></i><span>Logout</span>
            </button>

            <div class="d-lg-none border-top pt-3 mt-3 w-100">
            <button type="button" class="btn btn-outline-light w-100 d-flex align-items-center justify-content-center gap-2" id="darkModeToggleMobile">
                <i id="darkModeIconMobile" class="fa fa-moon"></i>
                <span id="darkModeTextMobile">Mode Gelap</span>
            </button>
            </div>
        </div>
    </div>
</div>
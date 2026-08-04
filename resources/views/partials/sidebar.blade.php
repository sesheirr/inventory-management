<aside class="sidebar">
    <div class="brand d-flex align-items-center gap-2">
        <div class="brand-logo-container">
            <img src="{{ asset('images/logo-diskominfo.png') }}" alt="Logo Diskominfo Garut">
        </div>
        <div>
            <div class="fw-bold">Inventaris</div>
            <div class="small" style="color: rgba(255, 255, 255, 0.65);">Diskominfo Garut</div>
        </div>
    </div>

    <nav class="nav-links">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            data-nav-transition="true" data-nav-accent="rgba(56, 189, 248, 0.48)"><i
                class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"
            data-nav-transition="true" data-nav-accent="rgba(16, 185, 129, 0.5)"><i
                class="bi bi-box2"></i><span>Barang</span></a>
        <a href="{{ route('categories.index') }}"
            class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" data-nav-transition="true"
            data-nav-accent="rgba(245, 158, 11, 0.5)"><i class="bi bi-tags"></i><span>Kategori</span></a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"
            data-nav-transition="true" data-nav-accent="rgba(139, 92, 246, 0.5)"><i
                class="bi bi-building"></i><span>Ruangan</span></a>
        <a href="{{ route('mutations.index') }}"
            class="nav-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}" data-nav-transition="true"
            data-nav-accent="rgba(244, 63, 94, 0.5)"><i class="bi bi-arrow-left-right"></i><span>Mutasi
                Barang</span></a>

        @if(auth()->user()->isAdmin())

            <a href="{{ route('activity-logs.index') }}"
                class="nav-link {{ request()->routeIs('activity-logs.index') ? 'active' : '' }}"><i
                    class="fa fa-clock-rotate-left"></i><span>Log Aktivitas</span></a>
            <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i
                    class="fa fa-users-gear"></i><span>Manajemen User</span></a>

            <a href="{{ route('activity-logs.index') }}" class="nav-link {{ request()->routeIs('activity-logs.index') ? 'active' : '' }}"><i class="fa fa-clock-rotate-left"></i><span>Log Aktivitas</span></a>
            @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="fa fa-users-gear"></i><span>Manajemen User</span></a>
            @endif

        @endif
    </nav>

    <!-- Bagian bawah sidebar: Pengaturan & Logout -->
    <div class="nav-links nav-links-bottom">
        <hr class="sidebar-divider">

        <a href="{{ route('settings') }}" class="nav-link {{ request()->routeIs('settings') ? 'active' : '' }}">
            <i class="bi bi-gear"></i><span>Pengaturan</span>
        </a>

        <button type="button" class="nav-link nav-link-logout" data-bs-toggle="modal" data-bs-target="#logoutModal">
            <i class="bi bi-box-arrow-right"></i><span>Logout</span>
        </button>
    </div>
</aside>
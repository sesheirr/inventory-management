<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div>
            <h5 class="mb-0">Inventory Management</h5>
            <small>System</small>
        </div>
    </div>

    <nav class="nav-links">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box2"></i><span>Barang</span></a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Kategori</span></a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"><i class="bi bi-building"></i><span>Ruangan</span></a>
        <a href="{{ route('mutations.index') }}" class="nav-link {{ request()->routeIs('mutations.*') ? 'active' : '' }}"><i class="bi bi-arrow-left-right"></i><span>Mutasi Barang</span></a>
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
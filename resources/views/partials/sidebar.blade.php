<aside class="sidebar">
    <div class="brand">
        <div class="brand-icon">
            <i class="bi bi-box-seam"></i>
        </div>
        <div>
            <h5 class="mb-0">Inventaris</h5>
            <small>Manajemen</small>
        </div>
    </div>

    <nav class="nav-links">
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-grid"></i><span>Dashboard</span></a>
        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}"><i class="bi bi-box2"></i><span>Barang</span></a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}"><i class="bi bi-tags"></i><span>Kategori</span></a>
        <a href="{{ route('rooms.index') }}" class="nav-link {{ request()->routeIs('rooms.*') ? 'active' : '' }}"><i class="bi bi-building"></i><span>Ruangan</span></a>
    </nav>

    <div class="sidebar-footer">
        <a href="#" class="nav-link logout"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></a>
    </div>
</aside>

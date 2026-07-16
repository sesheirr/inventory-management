<header class="topbar">
    <div class="d-flex align-items-center gap-3">
        <!-- Tombol Back hanya muncul jika BUKAN di halaman utama produk -->
        @if(!Route::is('products.index'))
            <button class="btn btn-light rounded-circle p-2 border-0 shadow-sm" type="button" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i>
            </button>
        @endif
        
        <div>
            <p class="text-muted mb-0 small">Inventory / Products</p>
            <h3 class="mb-0 fw-semibold">Product</h3>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        <button id="darkModeToggle" class="icon-btn" type="button">
            <i id="themeIcon" class="bi bi-moon"></i>
        </button>
        
        <button class="icon-btn" type="button">
            <i class="bi bi-bell"></i>
        </button>
        
        <div class="profile-pill">
            <div class="avatar">SF</div>
            <div>
                <div class="fw-semibold">Sheira Fitria</div>
                <small class="text-muted">Admin</small>
            </div>
        </div>
    </div>
</header>
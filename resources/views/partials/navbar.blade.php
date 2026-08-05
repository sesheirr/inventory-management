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
    } elseif (Route::is('notifications.index')) {
        $pageTitle = 'Notifikasi';
        $breadcrumb = 'Inventaris / Notifikasi';
        $showBackButton = true;
    } elseif (Route::is('profile')) {
        $pageTitle = 'Profile';
        $breadcrumb = 'Inventaris / Profile';
        $showBackButton = true;
    } elseif (Route::is('settings')) {
        $pageTitle = 'Settings';
        $breadcrumb = 'Inventaris / Settings';
        $showBackButton = true;
    }

    $backUrl = url()->previous();
    if (!$backUrl || $backUrl === url()->current()) {
        $backUrl = route('dashboard');
    }

    $currentUser = auth()->user();
    $unreadCount = $currentUser?->unreadNotifications->count() ?? 0;
    $recentNotifications = $currentUser?->notifications()->take(5)->get() ?? collect();
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

        {{-- DROPDOWN NOTIFIKASI DI NAVBAR --}}
        <div class="dropdown">
            <button class="icon-btn position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                <i class="bi bi-bell"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </button>

            <div class="dropdown-menu dropdown-menu-end dropdown-menu-dark p-0 rounded-4 shadow border border-secondary border-opacity-25" style="width: 320px; max-height: 420px;">
                {{-- Header Dropdown --}}
                <div class="d-flex align-items-center justify-content-between p-3 border-bottom border-secondary border-opacity-25">
                    <h6 class="mb-0 fw-bold text-white small">Notifikasi</h6>
                    @if($unreadCount > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="m-0">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-link text-primary text-decoration-none p-0 style="font-size: 0.75rem;">
                                Tandai dibaca
                            </button>
                        </form>
                    @endif
                </div>

                {{-- List Notifikasi Singkat --}}
                <div class="overflow-auto" style="max-height: 280px;">
                    @forelse($recentNotifications as $notif)
                        @php
                            $isUnread = is_null($notif->read_at);
                            $data = $notif->data;
                        @endphp
                        <a href="{{ $data['url'] ?? route('notifications.index') }}" 
                           class="dropdown-item p-3 border-bottom border-secondary border-opacity-10 d-flex gap-2 align-items-start {{ $isUnread ? 'bg-primary bg-opacity-10' : '' }}" 
                           style="white-space: normal;">
                            <div class="rounded-circle p-1 d-flex align-items-center justify-content-center flex-shrink-0 mt-1" 
                                 style="width: 28px; height: 28px; background-color: {{ isset($data['type']) && $data['type'] == 'rejected' ? 'rgba(220, 53, 69, 0.2)' : 'rgba(13, 110, 253, 0.2)' }};">
                                @if(isset($data['type']) && $data['type'] == 'rejected')
                                    <i class="bi bi-x-circle text-danger small"></i>
                                @elseif(isset($data['type']) && $data['type'] == 'approved')
                                    <i class="bi bi-check-circle text-success small"></i>
                                @else
                                    <i class="bi bi-bell text-primary small"></i>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-white small d-block text-truncate">{{ $data['title'] ?? 'Notifikasi' }}</strong>
                                    @if($isUnread)
                                        <span class="badge bg-primary rounded-circle p-1 ms-1"></span>
                                    @endif
                                </div>
                                <p class="text-secondary mb-1 lh-sm" style="font-size: 0.75rem;">{{ $data['message'] ?? '-' }}</p>
                                <small class="text-secondary font-monospace" style="font-size: 0.65rem;">{{ $notif->created_at->diffForHumans() }}</small>
                            </div>
                        </a>
                    @empty
                        <div class="text-center py-4 text-secondary">
                            <i class="bi bi-bell-slash fs-4 d-block mb-1 opacity-50"></i>
                            <span style="font-size: 0.75rem;">Tidak ada notifikasi</span>
                        </div>
                    @endforelse
                </div>

                {{-- Footer Dropdown --}}
                <div class="p-2 text-center border-top border-secondary border-opacity-25">
                    <a href="{{ route('notifications.index') }}" class="text-primary text-decoration-none fw-semibold" style="font-size: 0.75rem;">
                        Lihat Semua Notifikasi <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

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
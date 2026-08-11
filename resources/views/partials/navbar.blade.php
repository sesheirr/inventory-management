@php
    $showBackButton = false;

    if (Route::is('dashboard')) {
        $showBackButton = false;
    } elseif (Route::is('reports.index') || Route::is('reports.*')) {
        $showBackButton = true;
    } elseif (Route::is('products.index')) {
        $showBackButton = false;
    } elseif (Route::is('products.*')) {
        $showBackButton = true;
    } elseif (Route::is('categories.index')) {
        $showBackButton = false;
    } elseif (Route::is('categories.*')) {
        $showBackButton = true;
    } elseif (Route::is('rooms.index')) {
        $showBackButton = false;
    } elseif (Route::is('rooms.*')) {
        $showBackButton = true;
    } elseif (Route::is('mutations.index') || Route::is('mutations.*')) {
        $showBackButton = true;
    } elseif (Route::is('users.index') || Route::is('users.*')) {
        $showBackButton = true;
    } elseif (Route::is('activity-logs.index') || Route::is('activity-logs.*')) {
        $showBackButton = true;
    } elseif (Route::is('notifications.index')) {
        $showBackButton = true;
    } elseif (Route::is('profile')) {
        $showBackButton = true;
    } elseif (Route::is('settings')) {
        $showBackButton = true;
    }

    $backUrl = url()->previous();
    if (!$backUrl || $backUrl === url()->current()) {
        $backUrl = route('dashboard');
    }

    $currentUser = auth()->user();
@endphp

{{-- MODIFIED: Navbar sticky, breadcrumb & judul dihapus, layout disederhanakan --}}
<header class="topbar" style="position: sticky; top: 0; z-index: 1030;">
    <div class="d-flex align-items-center gap-2">
        {{-- HAMBURGER (mobile only) --}}
        <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" aria-controls="sidebarOffcanvas">
            <i class="fa fa-bars"></i>
        </button>

        {{-- BACK BUTTON --}}
        @if($showBackButton)
            <a href="{{ $backUrl }}" class="btn-back-circle d-none d-lg-inline-flex" aria-label="Kembali">
                <i class="bi bi-arrow-left"></i>
            </a>
        @endif
    </div>

    <div class="d-flex align-items-center gap-2">
        {{-- DARK MODE TOGGLE (desktop only) --}}
        <button id="darkModeToggle" class="icon-btn d-none d-lg-inline-flex" type="button">
            <i id="themeIcon" class="bi bi-moon"></i>
        </button>

        {{-- MODIFIED: Profile pill diperkecil --}}
        <a href="{{ route('profile') }}" class="profile-pill profile-pill-link" aria-label="Buka halaman profil" style="padding: 4px 10px 4px 4px; gap: 8px;">
            <div class="avatar" style="width: 32px; height: 32px; border-radius: 50%; overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                @if(!empty($currentUser?->avatar))
                    <img src="{{ $currentUser->avatar }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                @else
                    <span class="fw-bold" style="font-size: 11px;">{{ strtoupper(substr($currentUser->name ?? 'U', 0, 2)) }}</span>
                @endif
            </div>
            <div>
                <div class="fw-semibold" style="font-size: 12px; line-height: 1.2;">{{ $currentUser->name ?? 'User' }}</div>
                <small class="profile-role-label" style="font-size: 10px;">
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
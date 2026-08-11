@extends('layouts.app')

@section('title', 'Profile')
@section('header')
@endsection

@section('content')
<style>
    html.dark .text-profile-title,
    html.dark .text-profile-body { color: #ffffff !important; }
    html.light .text-profile-title,
    html.light .text-profile-body { color: #192333 !important; }

    .profile-hero-name { color: #192333 !important; }
    html.dark .profile-hero-name { color: #ffffff !important; }

    .profile-hero .hero-text-subtle { color: rgba(25,35,51,0.75) !important; }
    html.dark .profile-hero .hero-text-subtle { color: rgba(255,255,255,0.85) !important; }

    .profile-hero .hero-text-white { color: #192333 !important; }
    html.dark .profile-hero .hero-text-white { color: #ffffff !important; }

    .btn-edit-profile-hero {
        border: 1px solid rgba(25,35,51,0.35);
        color: #192333 !important;
        background-color: rgba(25,35,51,0.06);
    }
    .btn-edit-profile-hero:hover { background-color: rgba(25,35,51,0.12); }
    html.dark .btn-edit-profile-hero {
        border: 1px solid rgba(255,255,255,0.5);
        color: #ffffff !important;
        background-color: rgba(255,255,255,0.1);
    }
    html.dark .btn-edit-profile-hero:hover { background-color: rgba(255,255,255,0.25); }

    /* ── Modern mobile list style ── */
    .profile-list { list-style: none; padding: 0; margin: 0; }
    .profile-list-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    html.light .profile-list-item { border-bottom: 1px solid rgba(0,0,0,0.06); }
    .profile-list-item:last-child { border-bottom: none; }

    .profile-list-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(59,130,246,0.15);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #60a5fa;
        flex-shrink: 0;
    }
    html.light .profile-list-icon {
        background: rgba(59,130,246,0.1);
        color: #2563eb;
    }

    .profile-list-label {
        font-size: 11px;
        color: #6b7280;
        margin-bottom: 1px;
        line-height: 1.2;
    }
    .profile-list-value { color: #111827 !important;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.3;
        word-break: break-word;
    }
    html.dark .profile-list-value {
        color: #f1f5f9 !important;
    }
   html.light .profile-list-value { color: #111827 !important; }
   html.light .profile-list-label { color: #4b5563 !important; }

    /* section header kecil */
    .profile-section-title {
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #6b7280;
        margin: 20px 0 8px;
    }
    html.light .profile-section-title { color: #9ca3af; }
</style>

<div class="profile-page">
    <div class="container-fluid py-2">
        <div class="row g-4">

            {{-- Banner Hero --}}
            <div class="col-12">
                <div class="profile-hero card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5 position-relative">
                        <div class="position-absolute top-0 end-0 w-100 h-100 hero-glow"></div>
                        <div class="row align-items-center g-4 position-relative">
                            <div class="col-lg-8">
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                                    <div class="avatar-circle shadow overflow-hidden">
                                        @if(!empty($user->avatar))
                                            <img src="{{ $user->avatar }}" alt="Foto profil" style="width:100%;height:100%;object-fit:cover;">
                                        @else
                                            <span class="fw-bold fs-2">{{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h2 class="fw-bold mb-0 profile-hero-name">{{ $user->name ?? 'Administrator' }}</h2>
                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">Active</span>
                                        </div>
                                        <p class="hero-text-subtle mb-2">
                                            <i class="fa-solid fa-shield-halved me-2"></i>
                                            <span class="fw-semibold">{{ ucfirst($user->role ?? 'Administrator') }}</span>
                                        </p>
                                        <p class="hero-text-subtle mb-0">
                                            <i class="fa-solid fa-envelope me-2"></i>
                                            {{ $user->email ?? 'admin@inventory.local' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kartu Informasi --}}
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 profile-card">
                    <div class="card-body p-3 p-md-4 p-lg-5">

                        {{-- Header kartu --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h5 class="fw-bold mb-0 text-profile-title">Informasi Profile</h5>
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2">
                                <i class="fa-solid fa-circle-info me-1"></i>Personal
                            </span>
                        </div>
                        <p class="text-muted mb-3" style="font-size:13px">Detail akun Anda secara lengkap.</p>

                        {{-- MOBILE: list style modern --}}
                        <div class="d-md-none">
                            <p class="profile-section-title">Akun</p>
                            <ul class="profile-list">
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <div class="profile-list-label">Nama Lengkap</div>
                                        <div class="profile-list-value">{{ $user->name ?? '-' }}</div>
                                    </div>
                                </li>
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-at"></i></div>
                                    <div>
                                        <div class="profile-list-label">Username</div>
                                        <div class="profile-list-value">{{ $user->username ?? '-' }}</div>
                                    </div>
                                </li>
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div>
                                        <div class="profile-list-label">Email</div>
                                        <div class="profile-list-value">{{ $user->email ?? '-' }}</div>
                                    </div>
                                </li>
                            </ul>

                            <p class="profile-section-title">Pribadi</p>
                            <ul class="profile-list">
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div>
                                        <div class="profile-list-label">Nomor HP</div>
                                        <div class="profile-list-value">{{ $user->phone ?? '-' }}</div>
                                    </div>
                                </li>
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-venus-mars"></i></div>
                                    <div>
                                        <div class="profile-list-label">Jenis Kelamin</div>
                                        <div class="profile-list-value">{{ $user->gender ?? '-' }}</div>
                                    </div>
                                </li>
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-cake-candles"></i></div>
                                    <div>
                                        <div class="profile-list-label">Tanggal Lahir</div>
                                        <div class="profile-list-value">
                                            @php
                                                $birthDateDisplay = '-';
                                                if (!empty($user->birth_date)) {
                                                    try { $birthDateDisplay = \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y'); }
                                                    catch (\Throwable $e) { $birthDateDisplay = $user->birth_date; }
                                                }
                                            @endphp
                                            {{ $birthDateDisplay }}
                                        </div>
                                    </div>
                                </li>
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div>
                                        <div class="profile-list-label">Alamat</div>
                                        <div class="profile-list-value">{{ $user->address ?? '-' }}</div>
                                    </div>
                                </li>
                            </ul>

                            <p class="profile-section-title">Lainnya</p>
                            <ul class="profile-list">
                                <li class="profile-list-item">
                                    <div class="profile-list-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                    <div>
                                        <div class="profile-list-label">Bergabung Sejak</div>
                                        <div class="profile-list-value">{{ isset($user->created_at) ? $user->created_at->translatedFormat('d F Y') : '-' }}</div>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        {{-- DESKTOP: row grid --}}
                        <div class="row g-3 d-none d-md-flex">
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-user"></i></div>
                                    <div><small class="text-muted">Nama Lengkap</small><div class="fw-semibold text-profile-body">{{ $user->name ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-at"></i></div>
                                    <div><small class="text-muted">Username</small><div class="fw-semibold text-profile-body">{{ $user->username ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div><small class="text-muted">Email</small><div class="fw-semibold text-profile-body">{{ $user->email ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div><small class="text-muted">Nomor HP</small><div class="fw-semibold text-profile-body">{{ $user->phone ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-venus-mars"></i></div>
                                    <div><small class="text-muted">Jenis Kelamin</small><div class="fw-semibold text-profile-body">{{ $user->gender ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-md-6 col-lg-4">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-cake-candles"></i></div>
                                    <div>
                                        <small class="text-muted">Tanggal Lahir</small>
                                        <div class="fw-semibold text-profile-body">
                                            @php
                                                $birthDateDisplay = '-';
                                                if (!empty($user->birth_date)) {
                                                    try { $birthDateDisplay = \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y'); }
                                                    catch (\Throwable $e) { $birthDateDisplay = $user->birth_date; }
                                                }
                                            @endphp
                                            {{ $birthDateDisplay }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div><small class="text-muted">Alamat</small><div class="fw-semibold text-profile-body">{{ $user->address ?? '-' }}</div></div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                    <div><small class="text-muted">Bergabung Sejak</small><div class="fw-semibold text-profile-body">{{ isset($user->created_at) ? $user->created_at->translatedFormat('d F Y') : '-' }}</div></div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

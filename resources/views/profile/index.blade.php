@extends('layouts.app')

@section('content')
<div class="profile-page">
    <div class="container-fluid py-4">
        <div class="row g-4">
            <div class="col-12">
                <div class="profile-hero card border-0 shadow-lg overflow-hidden">
                    <div class="card-body p-4 p-lg-5 position-relative">
                        <div class="position-absolute top-0 end-0 w-100 h-100 hero-glow"></div>
                        <div class="row align-items-center g-4 position-relative">
                            <div class="col-lg-8">
                                <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
                                    <div class="avatar-circle shadow">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h2 class="fw-bold mb-0 text-white">{{ $user->name ?? 'User' }}</h2>
                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">Active</span>
                                        </div>
                                        <p class="text-light-emphasis mb-2">
                                            <i class="fa-solid fa-shield-halved me-2"></i>
                                            <span class="fw-semibold">{{ ucfirst($user->role ?? 'user') }}</span>
                                        </p>
                                        <p class="text-light-emphasis mb-0">
                                            <i class="fa-solid fa-envelope me-2"></i>
                                            {{ $user->email ?? '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <a href="#" class="btn btn-outline-light btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-pen me-2"></i>Edit Profile
                                    </a>
                                    <a href="#" class="btn btn-outline-info btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-key me-2"></i>Ubah Password
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-lg rounded-4 h-100 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1">Informasi Profile</h4>
                                <p class="text-muted mb-0">Detail akun Anda secara lengkap dan terorganisir.</p>
                            </div>
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis px-3 py-2">
                                <i class="fa-solid fa-circle-info me-2"></i>Personal
                            </span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-user"></i></div>
                                    <div>
                                        <small class="text-muted">Nama Lengkap</small>
                                        <div class="fw-semibold text-white">{{ $user->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-at"></i></div>
                                    <div>
                                        <small class="text-muted">Username</small>
                                        <div class="fw-semibold text-white">{{ $user->username ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div>
                                        <small class="text-muted">Email</small>
                                        <div class="fw-semibold text-white">{{ $user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div>
                                        <small class="text-muted">Nomor HP</small>
                                        <div class="fw-semibold text-white">{{ $user->phone ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-venus-mars"></i></div>
                                    <div>
                                        <small class="text-muted">Jenis Kelamin</small>
                                        <div class="fw-semibold text-white">{{ $user->gender ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-cake-candles"></i></div>
                                    <div>
                                        <small class="text-muted">Tanggal Lahir</small>
                                        <div class="fw-semibold text-white">{{ $user->birth_date ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div>
                                        <small class="text-muted">Alamat</small>
                                        <div class="fw-semibold text-white">{{ $user->address ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                    <div>
                                        <small class="text-muted">Bergabung Sejak</small>
                                        <div class="fw-semibold text-white">{{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 h-100 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="icon-badge">
                                <i class="fa-solid fa-user-edit"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0">Tentang Saya</h4>
                                <p class="text-muted small mb-0">Ringkasan singkat profil.</p>
                            </div>
                        </div>
                        <p class="text-white mb-0 lh-lg">
                            {{ $user->bio ?? 'Belum ada deskripsi.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="#" class="btn btn-primary rounded-pill px-4 py-2 action-btn">
                                <i class="fa-solid fa-pen me-2"></i>Edit Profile
                            </a>
                            <a href="#" class="btn btn-info rounded-pill px-4 py-2 action-btn">
                                <i class="fa-solid fa-key me-2"></i>Ubah Password
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-light rounded-pill px-4 py-2 action-btn">
                                <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

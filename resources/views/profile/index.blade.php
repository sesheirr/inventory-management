@extends('layouts.app')

{{-- 1. Menimpa Header Bawaan Layout Agar Tulisan "Dashboard" di Atas Hilang --}}
@section('title', 'Profile')
@section('header')
    {{-- Sengaja dikosongkan agar header default layout tidak muncul --}}
@endsection

@section('content')
<style>
    /* Mengatur warna teks judul & isi info agar selalu kontras di Light & Dark Mode */
    [data-bs-theme="dark"] .text-profile-title,
    [data-bs-theme="dark"] .text-profile-body {
        color: #ffffff !important;
    }
    [data-bs-theme="light"] .text-profile-title,
    [data-bs-theme="light"] .text-profile-body {
        color: #192333 !important;
    }
    
    /* Memastikan teks dalam Banner Hero Biru selalu berwarna terang/putih di kedua mode */
    .profile-hero .hero-text-white {
        color: #ffffff !important;
    }
    .profile-hero .hero-text-subtle {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    /* Styling Tombol Edit Profile pada Banner Hero */
    .btn-edit-profile-hero {
        border: 1px solid rgba(255, 255, 255, 0.5);
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1);
    }
    .btn-edit-profile-hero:hover {
        background-color: rgba(255, 255, 255, 0.25);
        color: #ffffff !important;
    }
</style>

<div class="profile-page">
    <div class="container-fluid py-2">
        <div class="row g-4">
            {{-- 3. Banner Hero Profil Utama --}}
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
                                            <h2 class="fw-bold mb-0 hero-text-white">{{ $user->name ?? 'Administrator' }}</h2>
                                            <span class="badge rounded-pill bg-success-subtle text-success-emphasis px-3 py-2">Active</span>
                                        </div>
                                        <p class="hero-text-subtle mb-2">
                                            <i class="fa-solid fa-shield-halved me-2"></i>
                                            <span class="fw-semibold text-white">{{ ucfirst($user->role ?? 'Administrator') }}</span>
                                        </p>
                                        <p class="hero-text-subtle mb-0">
                                            <i class="fa-solid fa-envelope me-2"></i>
                                            {{ $user->email ?? 'admin@inventory.local' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <a href="#" class="btn btn-edit-profile-hero btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-pen me-2"></i>Edit Profile
                                    </a>
                                    <a href="#" class="btn btn-info text-white btn-sm rounded-pill px-3">
                                        <i class="fa-solid fa-key me-2"></i>Ubah Password
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Detail Kartu Informasi Profil --}}
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-lg rounded-4 h-100 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="fw-bold mb-1 text-profile-title">Informasi Profile</h4>
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
                                        <div class="fw-semibold text-profile-body">{{ $user->name ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-at"></i></div>
                                    <div>
                                        <small class="text-muted">Username</small>
                                        <div class="fw-semibold text-profile-body">{{ $user->username ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                                    <div>
                                        <small class="text-muted">Email</small>
                                        <div class="fw-semibold text-profile-body">{{ $user->email ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                                    <div>
                                        <small class="text-muted">Nomor HP</small>
                                        <div class="fw-semibold text-profile-body">{{ $user->phone ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-venus-mars"></i></div>
                                    <div>
                                        <small class="text-muted">Jenis Kelamin</small>
                                        <div class="fw-semibold text-profile-body">{{ $user->gender ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-cake-candles"></i></div>
                                    <div>
                                        <small class="text-muted">Tanggal Lahir</small>
                                        <div class="fw-semibold text-profile-body">{{ isset($user->birth_date) ? \Carbon\Carbon::parse($user->birth_date)->translatedFormat('d F Y') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                                    <div>
                                        <small class="text-muted">Alamat</small>
                                        <div class="fw-semibold text-profile-body">{{ $user->address ?? '-' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="info-item">
                                    <div class="info-icon"><i class="fa-solid fa-calendar-check"></i></div>
                                    <div>
                                        <small class="text-muted">Bergabung Sejak</small>
                                        <div class="fw-semibold text-profile-body">{{ isset($user->created_at) ? $user->created_at->translatedFormat('d F Y') : '-' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 5. Kartu Tentang Saya --}}
            <div class="col-12 col-xl-4">
                <div class="card border-0 shadow-lg rounded-4 h-100 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex align-items-center gap-2 mb-4">
                            <div class="icon-badge">
                                <i class="fa-solid fa-user-edit"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold mb-0 text-profile-title">Tentang Saya</h4>
                                <p class="text-muted small mb-0">Ringkasan singkat profil.</p>
                            </div>
                        </div>
                        <p class="text-profile-body mb-0 lh-lg">
                            {{ $user->bio ?? 'Belum ada deskripsi.' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- 6. Tombol Aksi Bawah --}}
            <div class="col-12">
                <div class="card border-0 shadow-lg rounded-4 profile-card">
                    <div class="card-body p-4 p-lg-5">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="#" class="btn btn-primary rounded-pill px-4 py-2 action-btn">
                                <i class="fa-solid fa-pen me-2"></i>Edit Profile
                            </a>
                            <a href="#" class="btn btn-info text-white rounded-pill px-4 py-2 action-btn">
                                <i class="fa-solid fa-key me-2"></i>Ubah Password
                            </a>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 action-btn">
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
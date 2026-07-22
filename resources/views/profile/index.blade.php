@extends('layouts.app')

{{-- 1. Menimpa Header Bawaan Layout Agar Tulisan "Dashboard" di Atas Hilang --}}
@section('title', 'Profile')
@section('header')
    {{-- Sengaja dikosongkan agar header default layout tidak muncul --}}
@endsection

@section('content')
<style>
    /* Mengatur warna teks judul & isi info agar selalu kontras di Light & Dark Mode
       PENTING: sistem toggle project ini pakai class "dark"/"light" di <html>
       (lihat app.js: docEl.classList.add(...)), BUKAN atribut data-bs-theme.
       Makanya selector di sini pakai html.dark / html.light, bukan [data-bs-theme]. */
    html.dark .text-profile-title,
    html.dark .text-profile-body {
        color: #ffffff !important;
    }
    html.light .text-profile-title,
    html.light .text-profile-body {
        color: #192333 !important;
    }

    /* Nama besar di hero: gelap saat light mode, putih saat dark mode */
    .profile-hero-name {
        color: #192333 !important;
    }
    html.dark .profile-hero-name {
        color: #ffffff !important;
    }

    /* Role & email di hero: sebelumnya dipaksa putih permanen (bug).
       Sekarang ikut adaptif sesuai tema aktif. */
    .profile-hero .hero-text-subtle {
        color: rgba(25, 35, 51, 0.75) !important;
    }
    html.dark .profile-hero .hero-text-subtle {
        color: rgba(255, 255, 255, 0.85) !important;
    }

    /* Kalau ada teks yang memang harus selalu putih terlepas dari tema (jarang dipakai) */
    .profile-hero .hero-text-white {
        color: #192333 !important;
    }
    html.dark .profile-hero .hero-text-white {
        color: #ffffff !important;
    }

    /* Styling Tombol Edit Profile pada Banner Hero: border & teks ikut adaptif juga */
    .btn-edit-profile-hero {
        border: 1px solid rgba(25, 35, 51, 0.35);
        color: #192333 !important;
        background-color: rgba(25, 35, 51, 0.06);
    }
    .btn-edit-profile-hero:hover {
        background-color: rgba(25, 35, 51, 0.12);
        color: #192333 !important;
    }
    html.dark .btn-edit-profile-hero {
        border: 1px solid rgba(255, 255, 255, 0.5);
        color: #ffffff !important;
        background-color: rgba(255, 255, 255, 0.1);
    }
    html.dark .btn-edit-profile-hero:hover {
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
                                    <div class="avatar-circle shadow overflow-hidden">
                                        @if(!empty($user->avatar))
                                            <img src="{{ asset('storage/' . trim(str_replace(['public/', 'storage/'], '', $user->avatar), '/')) }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <i class="fa-solid fa-user"></i>
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
                            <div class="col-lg-4 text-lg-end">
                                <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                    <button type="button" class="btn btn-edit-profile-hero btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                        <i class="fa-solid fa-pen me-2"></i>Edit Profile
                                    </button>
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
                                        <div class="fw-semibold text-profile-body">
                                            @php
                                                $birthDateDisplay = '-';
                                                if (!empty($user->birth_date)) {
                                                    try {
                                                        $parsedBirthDate = \Carbon\Carbon::parse($user->birth_date);
                                                        $birthDateDisplay = $parsedBirthDate->translatedFormat('d F Y');
                                                    } catch (\Throwable $e) {
                                                        $birthDateDisplay = $user->birth_date;
                                                    }
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

           
        </div>
    </div>
</div>

<div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-semibold" id="editProfileModalLabel">Edit Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <label class="form-label">Foto Profil</label>
                        <div class="d-flex flex-column align-items-center gap-2">
                            <div class="avatar-circle shadow overflow-hidden" style="width: 90px; height: 90px; font-size: 2rem;">
                                @if(!empty($user->avatar))
                                    <img src="{{ asset('storage/' . trim(str_replace(['public/', 'storage/'], '', $user->avatar), '/')) }}" alt="Foto profil" style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <i class="fa-solid fa-user"></i>
                                @endif
                            </div>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nomor HP</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone ?? '') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="gender" class="form-select">
                            <option value="" @selected(empty($user->gender))>-- Pilih --</option>
                            <option value="Laki-laki" @selected($user->gender === 'Laki-laki')>Laki-laki</option>
                            <option value="Perempuan" @selected($user->gender === 'Perempuan')>Perempuan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Lahir</label>
                        @php
                            $birthDateValue = old('birth_date');
                            if (empty($birthDateValue) && !empty($user->birth_date)) {
                                try {
                                    $parsedBirthDate = \Carbon\Carbon::parse($user->birth_date);
                                    $birthDateValue = $parsedBirthDate->format('Y-m-d');
                                } catch (\Throwable $e) {
                                    $birthDateValue = (string) $user->birth_date;
                                }
                            }
                            if (!empty($birthDateValue)) {
                                $birthDateValue = substr((string) $birthDateValue, 0, 10);
                            }
                        @endphp
                        <input type="date" name="birth_date" class="form-control" value="{{ $birthDateValue ?? '' }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alamat</label>
                        <textarea name="address" class="form-control" rows="3">{{ old('address', $user->address ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bio</label>
                        <textarea name="bio" class="form-control" rows="3">{{ old('bio', $user->bio ?? '') }}</textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection